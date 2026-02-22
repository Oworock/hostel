<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\ReferralAgent;
use App\Models\ReferralPayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReferralDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Addon::isActive('referral-system'), 404);
        abort_unless(filter_var(get_setting('referral_enabled', true), FILTER_VALIDATE_BOOL), 404);
        abort_unless(filter_var(get_setting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL), 403);

        $agent = $this->resolveStudentAgent($request->user());
        $agent->loadCount('referredStudents');
        $agent->load([
            'commissions' => fn ($q) => $q->latest()->limit(25),
            'payoutRequests' => fn ($q) => $q->latest()->limit(15),
        ]);

        return view('student.referrals.index', [
            'agent' => $agent,
            'referralPopup' => $this->resolveReferralPopup($request),
        ]);
    }

    public function storePayoutRequest(Request $request): RedirectResponse
    {
        abort_unless(Addon::isActive('referral-system'), 404);
        abort_unless(filter_var(get_setting('referral_enabled', true), FILTER_VALIDATE_BOOL), 404);
        abort_unless(filter_var(get_setting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL), 403);

        $agent = $this->resolveStudentAgent($request->user());
        $minPayout = (float) get_setting('referral_min_payout', 0);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = (float) $data['amount'];
        if ($amount > (float) $agent->balance) {
            return back()->with('error', 'Requested amount exceeds your available referral balance.');
        }
        if ($amount < $minPayout) {
            return back()->with('error', 'Requested amount is below minimum payout threshold.');
        }

        ReferralPayoutRequest::create([
            'referral_agent_id' => $agent->id,
            'amount' => $amount,
            'bank_name' => $data['bank_name'] ?? null,
            'account_name' => $data['account_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Referral payout request submitted.');
    }

    public function dismissPopup(Request $request): RedirectResponse
    {
        $hash = trim((string) $request->input('popup_hash', ''));
        if ($hash === '') {
            return back();
        }

        $dismissed = collect($request->session()->get('dismissed_referral_popup_hashes', []))
            ->map(fn ($value) => (string) $value)
            ->filter()
            ->push($hash)
            ->unique()
            ->values()
            ->all();

        $request->session()->put('dismissed_referral_popup_hashes', $dismissed);

        return back();
    }

    private function resolveStudentAgent($user): ReferralAgent
    {
        $agent = ReferralAgent::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if ($agent) {
            if (!$agent->user_id) {
                $agent->user_id = $user->id;
                $agent->save();
            }

            return $agent;
        }

        $defaultType = (string) get_setting('referral_default_commission_type', 'percentage');
        if (!in_array($defaultType, ['percentage', 'fixed'], true)) {
            $defaultType = 'percentage';
        }

        return ReferralAgent::create([
            'user_id' => $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'phone' => (string) ($user->phone ?? ''),
            'password' => Hash::make(Str::random(24)),
            'commission_type' => $defaultType,
            'commission_value' => (float) get_setting('referral_default_commission_value', 5),
            'is_active' => true,
        ]);
    }

    /**
     * @return array{title:string,body:string,hash:string}|null
     */
    private function resolveReferralPopup(Request $request): ?array
    {
        $enabled = filter_var(get_setting('referral_popup_enabled', false), FILTER_VALIDATE_BOOL);
        $title = trim((string) get_setting('referral_popup_title', ''));
        $body = trim((string) get_setting('referral_popup_body', ''));
        if (!$enabled || $title === '' || $body === '') {
            return null;
        }

        $startRaw = trim((string) get_setting('referral_popup_start_at', ''));
        $endRaw = trim((string) get_setting('referral_popup_end_at', ''));

        try {
            if ($startRaw !== '' && now()->lt(Carbon::parse($startRaw))) {
                return null;
            }
            if ($endRaw !== '' && now()->gt(Carbon::parse($endRaw))) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        $hash = sha1($title . '|' . $body . '|' . $startRaw . '|' . $endRaw);
        $dismissed = collect($request->session()->get('dismissed_referral_popup_hashes', []))
            ->map(fn ($value) => (string) $value)
            ->all();
        if (in_array($hash, $dismissed, true)) {
            return null;
        }

        return [
            'title' => $title,
            'body' => $body,
            'hash' => $hash,
        ];
    }
}
