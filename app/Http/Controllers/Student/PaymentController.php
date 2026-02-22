<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\PaymentGateway;
use App\Models\Payment;
use App\Models\ReferralCommission;
use App\Services\OutboundWebhookService;
use App\Services\ReferralNotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    protected const SUPPORTED_GATEWAYS = ['paystack', 'flutterwave', 'stripe', 'paypal', 'razorpay', 'square'];

    public function index()
    {
        $user = auth()->user();

        $payments = Payment::where('user_id', $user->id)
            ->with(['booking.room.hostel', 'createdByAdmin'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $activeGateways = PaymentGateway::whereIn('name', ['Paystack', 'Flutterwave', 'Stripe', 'PayPal', 'Razorpay', 'Square'])
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($gateway) => strtolower($gateway->name));

        $outstandingBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->with(['room.hostel', 'payments'])
            ->latest()
            ->get()
            ->filter(function (Booking $booking) {
                return $booking->outstandingAmount() > 0;
            })
            ->values();

        return view('student.payments.index', compact('payments', 'outstandingBookings', 'activeGateways'));
    }

    public function initialize(Request $request, Booking $booking, string $gateway)
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        if (in_array($booking->status, ['cancelled', 'rejected', 'completed'], true)) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'This booking cannot be paid for.');
        }

        if ($booking->isFullyPaid()) {
            if ($booking->status === 'pending') {
                $booking->update(['status' => 'approved']);
            }

            return redirect()->route('student.bookings.show', $booking)->with('success', 'Booking payment already completed.');
        }

        $gateway = strtolower($gateway);
        if (!in_array($gateway, self::SUPPORTED_GATEWAYS, true)) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unsupported payment gateway.');
        }

        $providerConfig = $this->resolveGatewayConfig($gateway);
        if (!$providerConfig['is_active'] || empty($providerConfig['public_key']) || empty($providerConfig['secret_key'])) {
            return redirect()->route('student.bookings.show', $booking)->with('error', ucfirst($gateway) . ' is not configured.');
        }

        $reference = strtoupper($gateway) . '-' . $booking->id . '-' . Str::uuid();
        $amount = round((float) $booking->outstandingAmount(), 2);

        Payment::updateOrCreate(
            ['transaction_id' => $reference],
            [
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => $gateway,
            ]
        );

        return match ($gateway) {
            'paystack' => $this->initializePaystack($booking, $providerConfig, $reference, $amount),
            'flutterwave' => $this->initializeFlutterwave($booking, $providerConfig, $reference, $amount),
            'stripe' => $this->initializeStripe($booking, $providerConfig, $reference, $amount),
            'paypal' => $this->initializePayPal($booking, $providerConfig, $reference, $amount),
            'razorpay' => $this->initializeRazorpay($booking, $providerConfig, $reference, $amount),
            'square' => $this->initializeSquare($booking, $providerConfig, $reference, $amount),
            default => redirect()->route('student.bookings.show', $booking)->with('error', 'Unsupported payment gateway.'),
        };
    }

    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('student.payments.index')->with('error', 'Missing payment reference.');
        }

        $payment = Payment::where('transaction_id', $reference)->first();
        if (!$payment) {
            return redirect()->route('student.payments.index')->with('error', 'Payment record not found.');
        }

        $provider = $this->resolveGatewayConfig('paystack');
        if (empty($provider['secret_key'])) {
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Paystack is not configured.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($provider['secret_key'])
                ->timeout(30)
                ->retry(2, 300)
                ->get('https://api.paystack.co/transaction/verify/' . $reference);
        } catch (Throwable $e) {
            Log::error('Paystack verification request failed.', [
                'reference' => $reference,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Paystack payment.');
        }

        if (!$response->successful() || !($response->json('status') === true)) {
            Log::warning('Paystack verification returned non-success.', [
                'reference' => $reference,
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Paystack payment.');
        }

        $data = $response->json('data') ?? [];
        $paidInKobo = (int) ($data['amount'] ?? 0);
        $expectedInKobo = (int) round(((float) $payment->amount) * 100);
        $referenceMatches = ($data['reference'] ?? null) === $reference;
        $currencyMatches = strtoupper((string) ($data['currency'] ?? get_setting('system_currency', 'NGN'))) === strtoupper((string) get_setting('system_currency', 'NGN'));
        $isPaid = ($data['status'] ?? '') === 'success'
            && $referenceMatches
            && $currencyMatches
            && $paidInKobo >= $expectedInKobo;

        if ($isPaid) {
            $amount = ((float) $paidInKobo) / 100;
            $payment->update([
                'status' => 'paid',
                'amount' => $amount > 0 ? $amount : $payment->amount,
                'payment_date' => now()->toDateString(),
                'notes' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ]);

            $this->finalizeBookingIfPaid($payment->booking);

            return redirect()->route('student.bookings.show', $payment->booking_id)->with('success', 'Payment verified successfully.');
        }

        Log::warning('Paystack verification failed validation checks.', [
            'reference' => $reference,
            'payment_id' => $payment->id,
            'gateway_status' => $data['status'] ?? null,
            'gateway_amount' => $paidInKobo,
            'expected_amount' => $expectedInKobo,
            'gateway_currency' => $data['currency'] ?? null,
        ]);
        $payment->update(['status' => 'failed']);
        return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Payment was not successful.');
    }

    public function flutterwaveCallback(Request $request)
    {
        $txRef = $request->query('tx_ref');
        $transactionId = $request->query('transaction_id');

        if (!$txRef) {
            return redirect()->route('student.payments.index')->with('error', 'Missing Flutterwave reference.');
        }

        $payment = Payment::where('transaction_id', $txRef)->first();
        if (!$payment) {
            return redirect()->route('student.payments.index')->with('error', 'Payment record not found.');
        }

        $provider = $this->resolveGatewayConfig('flutterwave');
        if (empty($provider['secret_key'])) {
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Flutterwave is not configured.');
        }

        if (!$transactionId) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Missing Flutterwave transaction ID.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($provider['secret_key'])
                ->timeout(30)
                ->retry(2, 300)
                ->get('https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify');
        } catch (Throwable $e) {
            Log::error('Flutterwave verification request failed.', [
                'tx_ref' => $txRef,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Flutterwave payment.');
        }

        if (!$response->successful() || !($response->json('status') === 'success')) {
            Log::warning('Flutterwave verification returned non-success.', [
                'tx_ref' => $txRef,
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Flutterwave payment.');
        }

        $data = $response->json('data') ?? [];
        $gatewayAmount = (float) ($data['amount'] ?? 0);
        $expectedAmount = (float) $payment->amount;
        $currencyMatches = strtoupper((string) ($data['currency'] ?? get_setting('system_currency', 'NGN'))) === strtoupper((string) get_setting('system_currency', 'NGN'));
        $isPaid = ($data['status'] ?? '') === 'successful'
            && ($data['tx_ref'] ?? '') === $txRef
            && $currencyMatches
            && $gatewayAmount >= $expectedAmount;

        if ($isPaid) {
            $payment->update([
                'status' => 'paid',
                'amount' => (float) ($data['amount'] ?? $payment->amount),
                'payment_date' => now()->toDateString(),
                'notes' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ]);

            $this->finalizeBookingIfPaid($payment->booking);

            return redirect()->route('student.bookings.show', $payment->booking_id)->with('success', 'Payment verified successfully.');
        }

        Log::warning('Flutterwave verification failed validation checks.', [
            'tx_ref' => $txRef,
            'payment_id' => $payment->id,
            'gateway_status' => $data['status'] ?? null,
            'gateway_amount' => $gatewayAmount,
            'expected_amount' => $expectedAmount,
            'gateway_currency' => $data['currency'] ?? null,
        ]);
        $payment->update(['status' => 'failed']);
        return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Payment was not successful.');
    }

    public function stripeCallback(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        $reference = (string) $request->query('reference', '');

        if ($sessionId === '' || $reference === '') {
            return redirect()->route('student.payments.index')->with('error', 'Missing Stripe callback details.');
        }

        $payment = Payment::where('transaction_id', $reference)->first();
        if (!$payment) {
            return redirect()->route('student.payments.index')->with('error', 'Payment record not found.');
        }

        $provider = $this->resolveGatewayConfig('stripe');
        if (empty($provider['secret_key'])) {
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Stripe is not configured.');
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withToken($provider['secret_key'])
                ->timeout(30)
                ->retry(2, 300)
                ->get('https://api.stripe.com/v1/checkout/sessions/' . $sessionId);
        } catch (Throwable $e) {
            Log::error('Stripe verification request failed.', [
                'reference' => $reference,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Stripe payment.');
        }

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Stripe payment.');
        }

        $data = $response->json() ?? [];
        $paidInCents = (int) ($data['amount_total'] ?? 0);
        $expectedInCents = (int) round(((float) $payment->amount) * 100);
        $currencyMatches = strtoupper((string) ($data['currency'] ?? get_setting('system_currency', 'NGN'))) === strtoupper((string) get_setting('system_currency', 'NGN'));
        $isPaid = (($data['payment_status'] ?? '') === 'paid')
            && (($data['client_reference_id'] ?? '') === $reference)
            && $currencyMatches
            && $paidInCents >= $expectedInCents;

        if ($isPaid) {
            $payment->update([
                'status' => 'paid',
                'amount' => ((float) $paidInCents) / 100,
                'payment_date' => now()->toDateString(),
                'notes' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ]);
            $this->finalizeBookingIfPaid($payment->booking);

            return redirect()->route('student.bookings.show', $payment->booking_id)->with('success', 'Payment verified successfully.');
        }

        $payment->update(['status' => 'failed']);
        return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Payment was not successful.');
    }

    public function paypalCallback(Request $request)
    {
        $orderId = (string) $request->query('token', '');
        $reference = (string) $request->query('reference', '');

        if ($orderId === '' || $reference === '') {
            return redirect()->route('student.payments.index')->with('error', 'Missing PayPal callback details.');
        }

        $payment = Payment::where('transaction_id', $reference)->first();
        if (!$payment) {
            return redirect()->route('student.payments.index')->with('error', 'Payment record not found.');
        }

        $provider = $this->resolveGatewayConfig('paypal');
        if (empty($provider['public_key']) || empty($provider['secret_key'])) {
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'PayPal is not configured.');
        }

        $baseUrl = $this->paypalBaseUrl();
        $token = $this->paypalAccessToken($provider['public_key'], $provider['secret_key'], $baseUrl);
        if ($token === null) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify PayPal payment.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(30)
                ->retry(2, 300)
                ->post($baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');
        } catch (Throwable $e) {
            Log::error('PayPal capture request failed.', [
                'reference' => $reference,
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify PayPal payment.');
        }

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify PayPal payment.');
        }

        $data = $response->json() ?? [];
        $purchaseUnit = $data['purchase_units'][0] ?? [];
        $capturedAmount = (float) ($purchaseUnit['payments']['captures'][0]['amount']['value'] ?? 0);
        $capturedCurrency = (string) ($purchaseUnit['payments']['captures'][0]['amount']['currency_code'] ?? get_setting('system_currency', 'NGN'));
        $isPaid = ($data['status'] ?? '') === 'COMPLETED'
            && (($purchaseUnit['reference_id'] ?? '') === $reference)
            && strtoupper($capturedCurrency) === strtoupper((string) get_setting('system_currency', 'NGN'))
            && $capturedAmount >= (float) $payment->amount;

        if ($isPaid) {
            $payment->update([
                'status' => 'paid',
                'amount' => $capturedAmount > 0 ? $capturedAmount : (float) $payment->amount,
                'payment_date' => now()->toDateString(),
                'notes' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ]);
            $this->finalizeBookingIfPaid($payment->booking);

            return redirect()->route('student.bookings.show', $payment->booking_id)->with('success', 'Payment verified successfully.');
        }

        $payment->update(['status' => 'failed']);
        return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Payment was not successful.');
    }

    public function razorpayCallback(Request $request)
    {
        $linkId = (string) $request->query('razorpay_payment_link_id', '');
        $reference = (string) $request->query('reference', '');

        if ($linkId === '' || $reference === '') {
            return redirect()->route('student.payments.index')->with('error', 'Missing Razorpay callback details.');
        }

        $payment = Payment::where('transaction_id', $reference)->first();
        if (!$payment) {
            return redirect()->route('student.payments.index')->with('error', 'Payment record not found.');
        }

        $provider = $this->resolveGatewayConfig('razorpay');
        if (empty($provider['public_key']) || empty($provider['secret_key'])) {
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Razorpay is not configured.');
        }

        try {
            $response = Http::withBasicAuth($provider['public_key'], $provider['secret_key'])
                ->acceptJson()
                ->timeout(30)
                ->retry(2, 300)
                ->get('https://api.razorpay.com/v1/payment_links/' . $linkId);
        } catch (Throwable $e) {
            Log::error('Razorpay verification request failed.', [
                'reference' => $reference,
                'link_id' => $linkId,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Razorpay payment.');
        }

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Razorpay payment.');
        }

        $data = $response->json() ?? [];
        $paidInSubunit = (int) ($data['amount_paid'] ?? 0);
        $expectedInSubunit = (int) round(((float) $payment->amount) * 100);
        $currencyMatches = strtoupper((string) ($data['currency'] ?? get_setting('system_currency', 'NGN'))) === strtoupper((string) get_setting('system_currency', 'NGN'));
        $isPaid = ($data['status'] ?? '') === 'paid'
            && ($data['reference_id'] ?? '') === $reference
            && $currencyMatches
            && $paidInSubunit >= $expectedInSubunit;

        if ($isPaid) {
            $payment->update([
                'status' => 'paid',
                'amount' => ((float) $paidInSubunit) / 100,
                'payment_date' => now()->toDateString(),
                'notes' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ]);
            $this->finalizeBookingIfPaid($payment->booking);

            return redirect()->route('student.bookings.show', $payment->booking_id)->with('success', 'Payment verified successfully.');
        }

        $payment->update(['status' => 'failed']);
        return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Payment was not successful.');
    }

    public function squareCallback(Request $request)
    {
        $orderId = (string) $request->query('order_id', '');
        $reference = (string) $request->query('reference', '');

        if ($orderId === '' || $reference === '') {
            return redirect()->route('student.payments.index')->with('error', 'Missing Square callback details.');
        }

        $payment = Payment::where('transaction_id', $reference)->first();
        if (!$payment) {
            return redirect()->route('student.payments.index')->with('error', 'Payment record not found.');
        }

        $provider = $this->resolveGatewayConfig('square');
        if (empty($provider['secret_key'])) {
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Square is not configured.');
        }

        $baseUrl = $this->squareBaseUrl();

        try {
            $response = Http::acceptJson()
                ->withToken($provider['secret_key'])
                ->withHeaders([
                    'Square-Version' => '2025-10-16',
                ])
                ->timeout(30)
                ->retry(2, 300)
                ->get($baseUrl . '/v2/orders/' . $orderId);
        } catch (Throwable $e) {
            Log::error('Square verification request failed.', [
                'reference' => $reference,
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Square payment.');
        }

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Unable to verify Square payment.');
        }

        $data = $response->json('order') ?? [];
        $totalMoney = $data['total_money'] ?? [];
        $paidInSubunit = (int) ($totalMoney['amount'] ?? 0);
        $expectedInSubunit = (int) round(((float) $payment->amount) * 100);
        $currencyMatches = strtoupper((string) ($totalMoney['currency'] ?? get_setting('system_currency', 'NGN'))) === strtoupper((string) get_setting('system_currency', 'NGN'));
        $isPaid = ($data['state'] ?? '') === 'COMPLETED'
            && (($data['reference_id'] ?? '') === $reference)
            && $currencyMatches
            && $paidInSubunit >= $expectedInSubunit;

        if ($isPaid) {
            $payment->update([
                'status' => 'paid',
                'amount' => ((float) $paidInSubunit) / 100,
                'payment_date' => now()->toDateString(),
                'notes' => json_encode($response->json(), JSON_UNESCAPED_SLASHES),
            ]);
            $this->finalizeBookingIfPaid($payment->booking);

            return redirect()->route('student.bookings.show', $payment->booking_id)->with('success', 'Payment verified successfully.');
        }

        $payment->update(['status' => 'failed']);
        return redirect()->route('student.bookings.show', $payment->booking_id)->with('error', 'Payment was not successful.');
    }

    protected function initializePaystack(Booking $booking, array $provider, string $reference, float $amount)
    {
        try {
            $response = Http::acceptJson()
                ->withToken($provider['secret_key'])
                ->timeout(30)
                ->retry(2, 300)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email' => auth()->user()->email,
                    'amount' => (int) round($amount * 100),
                    'reference' => $reference,
                    'currency' => strtoupper((string) get_setting('system_currency', 'NGN')),
                    'callback_url' => route('student.payments.callback.paystack'),
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'user_id' => auth()->id(),
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('Paystack initialize request failed.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Paystack payment.');
        }

        if (!$response->successful() || !($response->json('status') === true)) {
            Log::warning('Paystack initialize returned non-success.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Paystack payment.');
        }

        $paymentUrl = $response->json('data.authorization_url');
        if (!$paymentUrl) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Paystack payment.');
        }
        return redirect()->away($paymentUrl);
    }

    protected function initializeFlutterwave(Booking $booking, array $provider, string $reference, float $amount)
    {
        try {
            $response = Http::acceptJson()
                ->withToken($provider['secret_key'])
                ->timeout(30)
                ->retry(2, 300)
                ->post('https://api.flutterwave.com/v3/payments', [
                    'tx_ref' => $reference,
                    'amount' => $amount,
                    'currency' => strtoupper((string) get_setting('system_currency', 'NGN')),
                    'redirect_url' => route('student.payments.callback.flutterwave'),
                    'payment_options' => 'card,banktransfer,ussd',
                    'customer' => [
                        'email' => auth()->user()->email,
                        'name' => auth()->user()->name,
                    ],
                    'customizations' => [
                        'title' => 'Hostel Booking Payment',
                        'description' => 'Payment for booking #' . $booking->id,
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('Flutterwave initialize request failed.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Flutterwave payment.');
        }

        if (!$response->successful() || !($response->json('status') === 'success')) {
            Log::warning('Flutterwave initialize returned non-success.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Flutterwave payment.');
        }

        $paymentUrl = $response->json('data.link');
        if (!$paymentUrl) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Flutterwave payment.');
        }
        return redirect()->away($paymentUrl);
    }

    protected function initializeStripe(Booking $booking, array $provider, string $reference, float $amount)
    {
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withToken($provider['secret_key'])
                ->timeout(30)
                ->retry(2, 300)
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'success_url' => route('student.payments.callback.stripe', ['reference' => $reference]) . '&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('student.bookings.show', $booking),
                    'client_reference_id' => $reference,
                    'line_items[0][quantity]' => 1,
                    'line_items[0][price_data][currency]' => strtolower((string) get_setting('system_currency', 'NGN')),
                    'line_items[0][price_data][unit_amount]' => (int) round($amount * 100),
                    'line_items[0][price_data][product_data][name]' => 'Booking #' . $booking->id . ' payment',
                    'customer_email' => auth()->user()->email,
                ]);
        } catch (Throwable $e) {
            Log::error('Stripe initialize request failed.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Stripe payment.');
        }

        if (!$response->successful()) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Stripe payment.');
        }

        $paymentUrl = $response->json('url');
        if (!$paymentUrl) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Stripe payment.');
        }

        return redirect()->away($paymentUrl);
    }

    protected function initializePayPal(Booking $booking, array $provider, string $reference, float $amount)
    {
        $baseUrl = $this->paypalBaseUrl();
        $token = $this->paypalAccessToken($provider['public_key'], $provider['secret_key'], $baseUrl);
        if ($token === null) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize PayPal payment.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(30)
                ->retry(2, 300)
                ->post($baseUrl . '/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $reference,
                        'description' => 'Booking #' . $booking->id . ' payment',
                        'amount' => [
                            'currency_code' => strtoupper((string) get_setting('system_currency', 'NGN')),
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ]],
                    'application_context' => [
                        'return_url' => route('student.payments.callback.paypal', ['reference' => $reference]),
                        'cancel_url' => route('student.bookings.show', $booking),
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('PayPal initialize request failed.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize PayPal payment.');
        }

        if (!$response->successful()) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize PayPal payment.');
        }

        $approvalUrl = collect($response->json('links', []))->firstWhere('rel', 'approve')['href'] ?? null;
        if (!$approvalUrl) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize PayPal payment.');
        }

        return redirect()->away($approvalUrl);
    }

    protected function initializeRazorpay(Booking $booking, array $provider, string $reference, float $amount)
    {
        try {
            $response = Http::withBasicAuth($provider['public_key'], $provider['secret_key'])
                ->acceptJson()
                ->timeout(30)
                ->retry(2, 300)
                ->post('https://api.razorpay.com/v1/payment_links', [
                    'amount' => (int) round($amount * 100),
                    'currency' => strtoupper((string) get_setting('system_currency', 'NGN')),
                    'accept_partial' => false,
                    'reference_id' => $reference,
                    'description' => 'Booking #' . $booking->id . ' payment',
                    'customer' => [
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                        'contact' => auth()->user()->phone,
                    ],
                    'notify' => [
                        'sms' => true,
                        'email' => true,
                    ],
                    'callback_url' => route('student.payments.callback.razorpay', ['reference' => $reference]),
                    'callback_method' => 'get',
                ]);
        } catch (Throwable $e) {
            Log::error('Razorpay initialize request failed.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Razorpay payment.');
        }

        if (!$response->successful()) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Razorpay payment.');
        }

        $paymentUrl = $response->json('short_url');
        if (!$paymentUrl) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Razorpay payment.');
        }

        return redirect()->away($paymentUrl);
    }

    protected function initializeSquare(Booking $booking, array $provider, string $reference, float $amount)
    {
        $locationId = (string) get_setting('square_location_id', '');
        if ($locationId === '') {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Square location ID is missing in settings.');
        }

        $baseUrl = $this->squareBaseUrl();

        try {
            $response = Http::acceptJson()
                ->withToken($provider['secret_key'])
                ->withHeaders([
                    'Square-Version' => '2025-10-16',
                ])
                ->timeout(30)
                ->retry(2, 300)
                ->post($baseUrl . '/v2/online-checkout/payment-links', [
                    'idempotency_key' => (string) Str::uuid(),
                    'quick_pay' => [
                        'name' => 'Booking #' . $booking->id . ' payment',
                        'price_money' => [
                            'amount' => (int) round($amount * 100),
                            'currency' => strtoupper((string) get_setting('system_currency', 'NGN')),
                        ],
                        'location_id' => $locationId,
                    ],
                    'checkout_options' => [
                        'redirect_url' => route('student.payments.callback.square', ['reference' => $reference]),
                    ],
                    'order' => [
                        'location_id' => $locationId,
                        'reference_id' => $reference,
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('Square initialize request failed.', [
                'booking_id' => $booking->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Square payment.');
        }

        if (!$response->successful()) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Square payment.');
        }

        $paymentUrl = $response->json('payment_link.url');
        if (!$paymentUrl) {
            return redirect()->route('student.bookings.show', $booking)->with('error', 'Unable to initialize Square payment.');
        }

        return redirect()->away($paymentUrl);
    }

    protected function finalizeBookingIfPaid(Booking $booking): void
    {
        if ($booking->isFullyPaid() && $booking->status === 'pending') {
            $booking->update(['status' => 'approved']);
        }

        $referralEnabled = Addon::isActive('referral-system')
            && filter_var(get_setting('referral_enabled', true), FILTER_VALIDATE_BOOL);

        $booking->loadMissing('user.referralAgent', 'payments');
        $student = $booking->user;
        $agent = $student?->referralAgent;

        if ($referralEnabled && $booking->isFullyPaid() && $agent && $agent->is_active) {
            $alreadyExists = ReferralCommission::query()
                ->where('referral_agent_id', $agent->id)
                ->where('booking_id', $booking->id)
                ->exists();

            if (!$alreadyExists) {
                $base = (float) $booking->total_amount;
                $commission = $agent->commission_type === 'fixed'
                    ? (float) $agent->commission_value
                    : ($base * ((float) $agent->commission_value / 100));
                $commission = round(max(0, $commission), 2);

                if ($commission > 0) {
                    $lastPaidPayment = $booking->payments
                        ->where('status', 'paid')
                        ->sortByDesc('id')
                        ->first();

                    $createdCommission = ReferralCommission::create([
                        'referral_agent_id' => $agent->id,
                        'student_id' => $student->id,
                        'booking_id' => $booking->id,
                        'payment_id' => $lastPaidPayment?->id,
                        'amount' => $commission,
                        'status' => 'pending',
                        'earned_at' => now(),
                    ]);

                    $agent->total_earned = round(((float) $agent->total_earned) + $commission, 2);
                    $agent->balance = round(((float) $agent->balance) + $commission, 2);
                    $agent->last_referred_at = now();
                    $agent->save();

                    app(ReferralNotificationService::class)->notifyCommissionEarned($agent, $createdCommission);
                }
            }
        }

        app(OutboundWebhookService::class)->dispatch('payment.completed', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'status' => $booking->status,
            'total_amount' => $booking->total_amount,
        ]);
    }

    protected function resolveGatewayConfig(string $gateway): array
    {
        $gateway = strtolower($gateway);
        $dbGateway = PaymentGateway::whereRaw('LOWER(name) = ?', [$gateway])->first();

        $publicKeySetting = get_setting($gateway . '_public_key', '');
        $secretKeySetting = get_setting($gateway . '_secret_key', '');
        $enabledSetting = filter_var(get_setting($gateway . '_enabled', false), FILTER_VALIDATE_BOOL);

        return [
            'name' => ucfirst($gateway),
            'is_active' => (bool) ($dbGateway?->is_active ?? false) || $enabledSetting,
            'public_key' => $dbGateway?->public_key ?: $publicKeySetting,
            'secret_key' => $dbGateway?->secret_key ?: $secretKeySetting,
        ];
    }

    protected function paypalBaseUrl(): string
    {
        $environment = strtolower((string) get_setting('paypal_environment', 'live'));

        return $environment === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    protected function squareBaseUrl(): string
    {
        $environment = strtolower((string) get_setting('square_environment', 'live'));

        return $environment === 'sandbox'
            ? 'https://connect.squareupsandbox.com'
            : 'https://connect.squareup.com';
    }

    protected function paypalAccessToken(string $clientId, string $clientSecret, string $baseUrl): ?string
    {
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withBasicAuth($clientId, $clientSecret)
                ->timeout(30)
                ->retry(2, 300)
                ->post($baseUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);
        } catch (Throwable $e) {
            Log::error('PayPal token request failed.', ['error' => $e->getMessage()]);
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        return $response->json('access_token');
    }
}
