<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentGateway;
use App\Models\Room;
use App\Models\AcademicSession;
use App\Models\Hostel;
use App\Models\Semester;
use App\Models\User;
use App\Services\OutboundWebhookService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $bookings = auth()->user()->bookings()->with(['room', 'bed', 'payments'])->paginate(15);
        return view('student.bookings.index', compact('bookings'));
    }

    public function available(Request $request)
    {
        $blockingBooking = $this->getBlockingBooking();

        $query = Room::query()
            ->where('is_available', true)
            ->with(['hostel', 'beds', 'images']);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->integer('hostel_id'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhereHas('hostel', fn ($h) => $h->where('name', 'like', '%' . $search . '%')->orWhere('city', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_month', '<=', (float) $request->input('max_price'));
        }

        $sort = $request->input('sort', 'price_asc');
        match ($sort) {
            'price_desc' => $query->orderByDesc('price_per_month'),
            'recent' => $query->latest(),
            default => $query->orderBy('price_per_month'),
        };

        $rooms = $query->paginate(12)->withQueryString();
        $hostels = Hostel::where('is_active', true)->orderBy('name')->get();

        return view('student.bookings.available', compact('rooms', 'blockingBooking', 'hostels', 'sort'));
    }

    public function create(Room $room)
    {
        $blockingBooking = $this->getBlockingBooking();
        if ($blockingBooking) {
            return redirect()
                ->route('student.bookings.available')
                ->with('error', $this->bookingBlockedMessage($blockingBooking));
        }

        $room->load(['images', 'hostel']);
        $availableBeds = $room->availableBeds()->get();

        if ($availableBeds->isEmpty()) {
            return redirect()->back()->with('error', 'No available beds in this room');
        }

        $periodType = getBookingPeriodType();
        $academicSessions = AcademicSession::where('is_active', true)->get();
        $semesters = Semester::whereHas('academicSession', function($q) {
            $q->where('is_active', true);
        })->get();
        $sessionBookingEnabled = filter_var(get_setting('session_booking_enabled', true), FILTER_VALIDATE_BOOL);
        $trimesterBookingEnabled = filter_var(get_setting('trimester_booking_enabled', false), FILTER_VALIDATE_BOOL);
        $sessionPrice = round(((float) $room->price_per_month) * 2, 2);
        $sessionDiscountType = (string) get_setting('session_booking_discount_type', 'none');
        $sessionDiscountValue = (float) get_setting('session_booking_discount_value', 0);
        $sessionPayable = $this->calculateSessionAmount($sessionPrice, $sessionDiscountType, $sessionDiscountValue);
        $trimesterPrice = $this->calculateTrimesterAmount($sessionPrice);
        $trimesterEligibleSchools = $this->parseSchoolListSetting('trimester_eligible_schools_json');
        $studentSchool = $this->getUserSchool(auth()->user());
        $canBookTrimester = $this->isSchoolEligibleForTrimester($studentSchool, $trimesterEligibleSchools);
        $bookingDiscountPreview = $this->resolveBookingDiscount(auth()->user(), (float) $room->price_per_month, 'semester');

        return view('student.bookings.create', compact(
            'room',
            'availableBeds',
            'periodType',
            'academicSessions',
            'semesters',
            'sessionBookingEnabled',
            'trimesterBookingEnabled',
            'sessionPrice',
            'sessionDiscountType',
            'sessionDiscountValue',
            'sessionPayable',
            'trimesterPrice',
            'studentSchool',
            'canBookTrimester',
            'bookingDiscountPreview'
        ));
    }

    public function store(Request $request)
    {
        $blockingBooking = $this->getBlockingBooking();
        if ($blockingBooking) {
            return redirect()
                ->route('student.bookings.available')
                ->with('error', $this->bookingBlockedMessage($blockingBooking));
        }

        // Check if student has profile image
        if (!auth()->user()->profile_image) {
            return redirect()->route('student.bookings.available')
                ->with('error', 'Please upload a profile picture before booking a room. Go to your profile settings.');
        }

        $periodType = getBookingPeriodType();
        $room = Room::find((int) $request->input('room_id'));

        if ($periodType === 'months') {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'bed_id' => 'nullable|exists:beds,id',
                'check_in_date' => 'required|date|after:today',
                'check_out_date' => 'required|date|after:check_in_date',
            ]);
            $calculatedAmount = null;
        } elseif ($periodType === 'semesters') {
            $sessionBookingEnabled = filter_var(get_setting('session_booking_enabled', true), FILTER_VALIDATE_BOOL);
            $trimesterBookingEnabled = filter_var(get_setting('trimester_booking_enabled', false), FILTER_VALIDATE_BOOL);
            $allowedScopes = ['semester'];
            if ($sessionBookingEnabled) {
                $allowedScopes[] = 'session';
            }
            if ($trimesterBookingEnabled) {
                $allowedScopes[] = 'trimester';
            }
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'bed_id' => 'nullable|exists:beds,id',
                'booking_scope' => 'required|in:' . implode(',', $allowedScopes),
                'semester_id' => 'nullable|exists:semesters,id',
                'academic_session_id' => 'nullable|exists:academic_sessions,id',
            ]);

            $scope = (string) ($validated['booking_scope'] ?? 'semester');
            if ($scope === 'session') {
                if (!$sessionBookingEnabled) {
                    return redirect()->back()->with('error', 'Session booking is currently disabled by admin settings.');
                }

                if (empty($validated['academic_session_id'])) {
                    return redirect()->back()->with('error', 'Academic session is required for session booking.');
                }

                $academicSession = AcademicSession::where('id', $validated['academic_session_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$academicSession) {
                    return redirect()->back()->with('error', 'Invalid academic session selection.');
                }

                $range = $this->resolveSessionDateRange($academicSession->id, $academicSession->start_year, $academicSession->end_year);
                $validated['semester_id'] = null;
                $validated['check_in_date'] = $range['start'];
                $validated['check_out_date'] = $range['end'];
                $calculatedAmount = $this->calculateSessionAmount(
                    round(((float) $room->price_per_month) * 2, 2),
                    (string) get_setting('session_booking_discount_type', 'none'),
                    (float) get_setting('session_booking_discount_value', 0)
                );
            } elseif ($scope === 'trimester') {
                if (!$trimesterBookingEnabled) {
                    return redirect()->back()->with('error', 'Trimester booking is currently disabled by admin settings.');
                }

                if (empty($validated['academic_session_id'])) {
                    return redirect()->back()->with('error', 'Academic session is required for trimester booking.');
                }

                $eligibleSchools = $this->parseSchoolListSetting('trimester_eligible_schools_json');
                $studentSchool = $this->getUserSchool(auth()->user());
                if (!$this->isSchoolEligibleForTrimester($studentSchool, $eligibleSchools)) {
                    return redirect()->back()->with('error', 'Your school is not eligible for trimester booking.');
                }

                $academicSession = AcademicSession::where('id', $validated['academic_session_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$academicSession) {
                    return redirect()->back()->with('error', 'Invalid academic session selection.');
                }

                $range = $this->resolveTrimesterDateRange($academicSession->id, $academicSession->start_year, $academicSession->end_year);
                $validated['semester_id'] = null;
                $validated['check_in_date'] = $range['start'];
                $validated['check_out_date'] = $range['end'];

                $sessionBaseAmount = round(((float) $room->price_per_month) * 2, 2);
                $calculatedAmount = $this->calculateTrimesterAmount($sessionBaseAmount);
            } else {
                if (empty($validated['semester_id']) || empty($validated['academic_session_id'])) {
                    return redirect()->back()->with('error', 'Semester and session are required for semester booking.');
                }

                $semester = Semester::where('id', $validated['semester_id'])
                    ->where('academic_session_id', $validated['academic_session_id'])
                    ->first();

                if (!$semester) {
                    return redirect()->back()->with('error', 'Invalid semester/session selection.');
                }

                $validated['check_in_date'] = $semester->start_date;
                $validated['check_out_date'] = $semester->end_date;
                $calculatedAmount = null;
            }
        } else {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'bed_id' => 'nullable|exists:beds,id',
                'academic_session_id' => 'required|exists:academic_sessions,id',
            ]);

            $academicSession = AcademicSession::where('id', $validated['academic_session_id'])
                ->where('is_active', true)
                ->first();

            if (!$academicSession) {
                return redirect()->back()->with('error', 'Invalid academic session selection.');
            }

            $range = $this->resolveSessionDateRange($academicSession->id, $academicSession->start_year, $academicSession->end_year);
            $validated['semester_id'] = null;
            $validated['check_in_date'] = $range['start'];
            $validated['check_out_date'] = $range['end'];
            $calculatedAmount = $this->calculateSessionAmount(
                round(((float) $room->price_per_month) * 2, 2),
                (string) get_setting('session_booking_discount_type', 'none'),
                (float) get_setting('session_booking_discount_value', 0)
            );
        }

        if (!$room || !$room->is_available) {
            return redirect()->back()->with('error', 'Selected room is not available.');
        }

        if (!empty($validated['bed_id'])) {
            $bed = $room->beds()
                ->where('id', $validated['bed_id'])
                ->where('is_occupied', false)
                ->where('is_approved', true)
                ->first();

            if (!$bed) {
                return redirect()->back()->with('error', 'Selected bed is not available for booking.');
            }
        } else {
            $autoBed = $room->availableBeds()->first();
            $validated['bed_id'] = $autoBed?->id;
        }

        $scopeForDiscount = $periodType === 'months'
            ? 'months'
            : ($periodType === 'sessions' ? 'session' : (string) ($validated['booking_scope'] ?? 'semester'));

        unset($validated['booking_scope']);
        $validated['user_id'] = auth()->id();
        $baseAmount = (float) ($calculatedAmount ?? (float) $room->price_per_month);
        $bookingDiscount = $this->resolveBookingDiscount(auth()->user(), $baseAmount, $scopeForDiscount);
        $validated['total_amount'] = round(max(0, $baseAmount - (float) $bookingDiscount['amount']), 2);

        if ((float) $bookingDiscount['amount'] > 0 && is_array($bookingDiscount['rule'])) {
            $discountLabel = (string) ($bookingDiscount['rule']['name'] ?? 'Booking Discount');
            $validated['notes'] = trim((string) ($validated['notes'] ?? '') . PHP_EOL . sprintf(
                '%s applied (%s%s).',
                $discountLabel,
                $bookingDiscount['rule']['discount_type'] === 'percentage' ? rtrim(rtrim(number_format((float) $bookingDiscount['rule']['discount_value'], 2), '0'), '.') : getCurrencySymbol() . number_format((float) $bookingDiscount['rule']['discount_value'], 2),
                $bookingDiscount['rule']['discount_type'] === 'percentage' ? '%' : ''
            ));
        }

        $booking = Booking::create($validated);
        app(OutboundWebhookService::class)->dispatch('booking.created', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'room_id' => $booking->room_id,
            'bed_id' => $booking->bed_id,
            'status' => $booking->status,
            'total_amount' => $booking->total_amount,
        ]);

        return redirect()->route('student.bookings.show', $booking)->with('success', 'Booking created. Complete payment to conclude your booking.');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['room.hostel', 'bed', 'payments.createdByAdmin']);
        $activeGateways = PaymentGateway::whereIn('name', ['Paystack', 'Flutterwave', 'Stripe', 'PayPal', 'Razorpay', 'Square'])
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($gateway) => strtolower($gateway->name));

        return view('student.bookings.show', compact('booking', 'activeGateways'));
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Cannot cancel this booking');
        }

        if ($booking->payments()->exists()) {
            return redirect()->back()->with('error', 'Booking cannot be cancelled after payment has been initiated.');
        }

        $booking->update(['status' => 'cancelled']);
        app(OutboundWebhookService::class)->dispatch('booking.cancelled', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'status' => $booking->status,
        ]);

        return redirect()->route('student.bookings.index')->with('success', 'Booking cancelled successfully');
    }

    public function receipt(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['room.hostel', 'bed', 'payments.createdByAdmin', 'user']);
        
        $pdf = \PDF::loadView('student.bookings.receipt', compact('booking'));
        return $pdf->download('Receipt-' . $booking->id . '.pdf');
    }

    private function getBlockingBooking(): ?Booking
    {
        return auth()->user()
            ->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();
    }

    private function bookingBlockedMessage(Booking $booking): string
    {
        if ($booking->status === 'pending') {
            return 'You already have a pending booking. Please pay for it or cancel it before booking another room.';
        }

        return 'You already have an active booking. Apply for hostel change or room change instead of creating another booking.';
    }

    /**
     * @return array{start: string, end: string}
     */
    private function resolveSessionDateRange(int $academicSessionId, int $startYear, int $endYear): array
    {
        $semesters = Semester::where('academic_session_id', $academicSessionId)
            ->orderBy('start_date')
            ->get();

        if ($semesters->isNotEmpty()) {
            return [
                'start' => (string) $semesters->first()->start_date,
                'end' => (string) $semesters->last()->end_date,
            ];
        }

        return [
            'start' => Carbon::create($startYear, 1, 1)->toDateString(),
            'end' => Carbon::create($endYear, 12, 31)->toDateString(),
        ];
    }

    private function calculateSessionAmount(float $basePrice, string $discountType, float $discountValue): float
    {
        $base = max(0, $basePrice);
        $discount = 0.0;

        if ($discountType === 'percentage') {
            $discount = $base * (max(0, min(100, $discountValue)) / 100);
        } elseif ($discountType === 'fixed') {
            $discount = max(0, $discountValue);
        }

        return round(max(0, $base - $discount), 2);
    }

    private function calculateTrimesterAmount(float $sessionAmount): float
    {
        return round(max(0, $sessionAmount) / 3, 2);
    }

    /**
     * @return array{start: string, end: string}
     */
    private function resolveTrimesterDateRange(int $academicSessionId, int $startYear, int $endYear): array
    {
        $sessionRange = $this->resolveSessionDateRange($academicSessionId, $startYear, $endYear);
        $start = Carbon::parse($sessionRange['start']);
        $end = Carbon::parse($sessionRange['end']);
        $days = max(1, $start->diffInDays($end) + 1);
        $trimesterDays = max(1, (int) floor($days / 3));

        return [
            'start' => $start->toDateString(),
            'end' => $start->copy()->addDays($trimesterDays - 1)->toDateString(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseSchoolListSetting(string $key): array
    {
        $schools = json_decode((string) get_setting($key, '[]'), true);

        if (!is_array($schools)) {
            return [];
        }

        return collect($schools)
            ->map(fn ($school) => trim((string) $school))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getUserSchool($user): ?string
    {
        $school = trim((string) data_get($user, 'extra_data.school', ''));

        return $school !== '' ? $school : null;
    }

    /**
     * @param array<int, string> $eligibleSchools
     */
    private function isSchoolEligibleForTrimester(?string $studentSchool, array $eligibleSchools): bool
    {
        if (empty($eligibleSchools)) {
            return false;
        }

        $school = mb_strtolower(trim((string) $studentSchool));
        if ($school === '') {
            return false;
        }

        return collect($eligibleSchools)
            ->contains(fn ($item) => mb_strtolower(trim((string) $item)) === $school);
    }

    /**
     * @return array{amount: float, rule: array<string, mixed>|null}
     */
    private function resolveBookingDiscount(User $user, float $baseAmount, string $scope): array
    {
        $baseAmount = max(0, $baseAmount);
        if ($baseAmount <= 0) {
            return ['amount' => 0.0, 'rule' => null];
        }

        $bestAmount = 0.0;
        $bestRule = null;

        foreach ($this->bookingDiscountRules() as $rule) {
            if (!$this->discountRuleAppliesToScope($rule, $scope) || !$this->discountRuleQualifies($rule, $user)) {
                continue;
            }

            $amount = $this->calculateRuleDiscountAmount($baseAmount, $rule);
            if ($amount > $bestAmount) {
                $bestAmount = $amount;
                $bestRule = $rule;
            }
        }

        return ['amount' => round($bestAmount, 2), 'rule' => $bestRule];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bookingDiscountRules(): array
    {
        $raw = json_decode((string) get_setting('booking_discount_rules_json', '[]'), true);
        if (!is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->filter(fn ($rule) => is_array($rule) && (bool) ($rule['is_active'] ?? false))
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function discountRuleAppliesToScope(array $rule, string $scope): bool
    {
        $appliesTo = (string) ($rule['applies_to'] ?? 'all');

        return $appliesTo === 'all' || $appliesTo === $scope;
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function discountRuleQualifies(array $rule, User $user): bool
    {
        $ruleType = (string) ($rule['rule_type'] ?? 'general');
        $now = now();
        $startsAt = trim((string) ($rule['starts_at'] ?? ''));
        $endsAt = trim((string) ($rule['ends_at'] ?? ''));

        if ($startsAt !== '' && $now->lt(Carbon::parse($startsAt))) {
            return false;
        }

        if ($endsAt !== '' && $now->gt(Carbon::parse($endsAt))) {
            return false;
        }

        if ($ruleType === 'early_booker' && $startsAt === '' && $endsAt === '') {
            return false;
        }

        return match ($ruleType) {
            'sibling' => $this->userHasSiblingByNameSimilarity($user, (int) ($rule['sibling_similarity'] ?? 80)),
            'early_booker' => true,
            'returning_student' => $user->bookings()
                ->whereIn('status', ['approved', 'completed'])
                ->whereNotNull('check_out_date')
                ->whereDate('check_out_date', '<', now()->toDateString())
                ->exists(),
            default => true,
        };
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function calculateRuleDiscountAmount(float $baseAmount, array $rule): float
    {
        $discountType = (string) ($rule['discount_type'] ?? 'percentage');
        $value = max(0, (float) ($rule['discount_value'] ?? 0));

        if ($discountType === 'fixed') {
            return min($baseAmount, $value);
        }

        return min($baseAmount, $baseAmount * (max(0, min(100, $value)) / 100));
    }

    private function userHasSiblingByNameSimilarity(User $user, int $threshold): bool
    {
        $threshold = max(50, min(100, $threshold));
        $lastName = mb_strtolower(trim((string) $user->last_name));
        if ($lastName === '') {
            return false;
        }

        $candidates = User::query()
            ->where('id', '!=', $user->id)
            ->whereRaw('LOWER(TRIM(COALESCE(last_name, ""))) <> ""')
            ->whereHas('bookings')
            ->pluck('last_name');

        foreach ($candidates as $candidateLastName) {
            $candidate = mb_strtolower(trim((string) $candidateLastName));
            if ($candidate === '') {
                continue;
            }

            similar_text($lastName, $candidate, $similarityPercent);
            if ($similarityPercent >= $threshold) {
                return true;
            }
        }

        return false;
    }
}
