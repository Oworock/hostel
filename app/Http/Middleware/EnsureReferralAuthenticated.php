<?php

namespace App\Http\Middleware;

use App\Models\ReferralAgent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReferralAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $agentId = (int) session('referral_agent_id');
        $agent = $agentId > 0 ? ReferralAgent::find($agentId) : null;

        if (!$agent || !$agent->is_active) {
            session()->forget('referral_agent_id');
            return redirect()->route('referral.login');
        }

        $request->attributes->set('referral_agent', $agent);

        return $next($request);
    }
}

