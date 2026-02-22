<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\ReferralAgent;
use App\Models\ReferralPayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReferralPartnerController extends Controller
{
    public function showLogin(): View
    {
        abort_unless(Addon::isActive('referral-system'), 404);

        return view('referral.login');
    }

    public function login(Request $request): RedirectResponse
    {
        abort_unless(Addon::isActive('referral-system'), 404);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $agent = ReferralAgent::query()
            ->where('email', $data['email'])
            ->first();

        if (!$agent || !$agent->is_active || !Hash::check($data['password'], $agent->password)) {
            return back()->withErrors(['email' => 'Invalid referral login credentials.'])->withInput();
        }

        session(['referral_agent_id' => $agent->id]);

        return redirect()->route('referral.dashboard');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('referral_agent_id');

        return redirect()->route('referral.login');
    }

    public function dashboard(Request $request): View
    {
        abort_unless(Addon::isActive('referral-system'), 404);

        /** @var ReferralAgent $agent */
        $agent = $request->attributes->get('referral_agent');
        $agent->loadCount('referredStudents');
        $agent->load([
            'commissions' => fn ($q) => $q->latest()->limit(25),
            'payoutRequests' => fn ($q) => $q->latest()->limit(15),
        ]);

        return view('referral.dashboard', [
            'agent' => $agent,
            'referralPopup' => $this->resolveReferralPopup($request),
        ]);
    }

    public function storePayoutRequest(Request $request): RedirectResponse
    {
        abort_unless(Addon::isActive('referral-system'), 404);

        /** @var ReferralAgent $agent */
        $agent = $request->attributes->get('referral_agent');
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
            return back()->with('error', 'Requested amount exceeds your available balance.');
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

        return back()->with('success', 'Payout request submitted.');
    }

    public function dismissPopup(Request $request): RedirectResponse
    {
        abort_unless(Addon::isActive('referral-system'), 404);

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
