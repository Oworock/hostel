<?php

namespace App\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Razorpay\Api\Api as RazorpayApi;
use Square\Environments;
use Square\SquareClient;
use Stripe\StripeClient;

class PaymentGatewayDiagnosticsService
{
    /**
     * @return array<string, array{configured: bool, active: bool, initialization_ready: bool, verification_ready: bool, message: string}>
     */
    public function testConfiguredGateways(): array
    {
        $gateways = PaymentGateway::query()
            ->whereIn('name', ['Paystack', 'Flutterwave', 'Stripe', 'PayPal', 'Razorpay', 'Square'])
            ->orderBy('name')
            ->get();

        $results = [];

        foreach ($gateways as $gateway) {
            $name = strtolower($gateway->name);
            $results[$name] = $this->testSingleGateway($gateway->name, $gateway->public_key, $gateway->secret_key, (bool) $gateway->is_active);
        }

        return $results;
    }

    /**
     * @return array{configured: bool, active: bool, initialization_ready: bool, verification_ready: bool, message: string}
     */
    protected function testSingleGateway(string $name, ?string $publicKey, ?string $secretKey, bool $active): array
    {
        $configured = !empty($publicKey) && !empty($secretKey);

        if (!$configured) {
            return [
                'configured' => false,
                'active' => $active,
                'initialization_ready' => false,
                'verification_ready' => false,
                'message' => 'Missing keys',
            ];
        }

        try {
            return match (strtolower($name)) {
                'paystack' => $this->testPaystack($secretKey, $active),
                'flutterwave' => $this->testFlutterwave($secretKey, $active),
                'stripe' => $this->testStripe($secretKey, $active),
                'paypal' => $this->testPayPal($publicKey, $secretKey, $active),
                'razorpay' => $this->testRazorpay($publicKey, $secretKey, $active),
                'square' => $this->testSquare($secretKey, $active),
                default => [
                    'configured' => true,
                    'active' => $active,
                    'initialization_ready' => false,
                    'verification_ready' => false,
                    'message' => 'Unsupported gateway type',
                ],
            };
        } catch (\Throwable $e) {
            return [
                'configured' => true,
                'active' => $active,
                'initialization_ready' => false,
                'verification_ready' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function testPaystack(string $secretKey, bool $active): array
    {
        $initResponse = Http::acceptJson()
            ->withToken($secretKey)
            ->timeout(20)
            ->get('https://api.paystack.co/bank', ['perPage' => 1]);

        $verifyResponse = Http::acceptJson()
            ->withToken($secretKey)
            ->timeout(20)
            ->get('https://api.paystack.co/transaction/verify/INVALID_REFERENCE');

        $initReady = $initResponse->successful() && $initResponse->json('status') === true;
        $verifyReady = $verifyResponse->status() !== 401 && $verifyResponse->status() !== 403;

        return [
            'configured' => true,
            'active' => $active,
            'initialization_ready' => $initReady,
            'verification_ready' => $verifyReady,
            'message' => $initReady && $verifyReady ? 'API credentials valid' : 'Ping failed',
        ];
    }

    protected function testFlutterwave(string $secretKey, bool $active): array
    {
        $initResponse = Http::acceptJson()
            ->withToken($secretKey)
            ->timeout(20)
            ->get('https://api.flutterwave.com/v3/banks/NG');

        $verifyResponse = Http::acceptJson()
            ->withToken($secretKey)
            ->timeout(20)
            ->get('https://api.flutterwave.com/v3/transactions/0/verify');

        $initReady = $initResponse->json('status') === 'success';
        $verifyReady = $verifyResponse->status() !== 401 && $verifyResponse->status() !== 403;

        return [
            'configured' => true,
            'active' => $active,
            'initialization_ready' => $initReady,
            'verification_ready' => $verifyReady,
            'message' => $initReady && $verifyReady ? 'API credentials valid' : 'Ping failed',
        ];
    }

    protected function testStripe(string $secretKey, bool $active): array
    {
        $stripe = new StripeClient($secretKey);
        $stripe->balance->retrieve();

        return [
            'configured' => true,
            'active' => $active,
            'initialization_ready' => true,
            'verification_ready' => true,
            'message' => 'SDK authentication valid',
        ];
    }

    protected function testPayPal(string $clientId, string $clientSecret, bool $active): array
    {
        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->timeout(20)
            ->post('https://api-m.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        $ready = $response->successful() && !empty($response->json('access_token'));

        return [
            'configured' => true,
            'active' => $active,
            'initialization_ready' => $ready,
            'verification_ready' => $ready,
            'message' => $ready ? 'OAuth token generated' : 'OAuth token request failed',
        ];
    }

    protected function testRazorpay(string $keyId, string $keySecret, bool $active): array
    {
        $api = new RazorpayApi($keyId, $keySecret);
        $api->payment->all(['count' => 1]);

        return [
            'configured' => true,
            'active' => $active,
            'initialization_ready' => true,
            'verification_ready' => true,
            'message' => 'SDK authentication valid',
        ];
    }

    protected function testSquare(string $accessToken, bool $active): array
    {
        $client = new SquareClient(
            token: $accessToken,
            options: ['baseUrl' => Environments::Production->value]
        );

        $client->locations->list();

        return [
            'configured' => true,
            'active' => $active,
            'initialization_ready' => true,
            'verification_ready' => true,
            'message' => 'SDK authentication valid',
        ];
    }
}

