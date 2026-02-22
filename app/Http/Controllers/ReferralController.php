<?php

namespace App\Http\Controllers;

use App\Models\ReferralAgent;
use App\Models\Addon;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function capture(string $code): RedirectResponse
    {
        $agent = ReferralAgent::query()
            ->where('is_active', true)
            ->whereRaw('UPPER(referral_code) = ?', [strtoupper(trim($code))])
            ->first();

        if (!$agent) {
            return redirect()->route('register')->with('error', 'Referral link is invalid or inactive.');
        }

        session(['referral_code' => $agent->referral_code]);

        return redirect()->route('register', ['ref' => $agent->referral_code]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (!Addon::isActive('referral-system') || !filter_var(SystemSetting::getSetting('referral_enabled', true), FILTER_VALIDATE_BOOL)) {
            return redirect()->route('register')->with('error', 'Referral program is currently disabled.');
        }

        if (!$this->hasValidInviteToken($request)) {
            abort(404);
        }

        return view('public.referrals.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Addon::isActive('referral-system') || !filter_var(SystemSetting::getSetting('referral_enabled', true), FILTER_VALIDATE_BOOL)) {
            return redirect()->route('register')->with('error', 'Referral program is currently disabled.');
        }

        if (!$this->hasValidInviteToken($request)) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:referral_agents,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        do {
            $code = strtoupper(Str::random(8));
        } while (ReferralAgent::query()->where('referral_code', $code)->exists());

        $defaultType = (string) SystemSetting::getSetting('referral_default_commission_type', 'percentage');
        if (!in_array($defaultType, ['percentage', 'fixed'], true)) {
            $defaultType = 'percentage';
        }
        $defaultValue = (float) SystemSetting::getSetting('referral_default_commission_value', 5);

        $agent = ReferralAgent::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'referral_code' => $code,
            'commission_type' => $defaultType,
            'commission_value' => max(0, $defaultValue),
            'is_active' => true,
        ]);

        return redirect()
            ->route('referrals.register.create', ['invite' => (string) SystemSetting::getSetting('referral_partner_invite_token', '')])
            ->with('success', 'Referral account created. Your referral link is now active.')
            ->with('referral_link', $agent->referralUrl());
    }

    private function hasValidInviteToken(Request $request): bool
    {
        $token = trim((string) SystemSetting::getSetting('referral_partner_invite_token', ''));
        if ($token === '') {
            return true;
        }

        return hash_equals($token, trim((string) $request->query('invite', $request->input('invite', ''))));
    }
}
