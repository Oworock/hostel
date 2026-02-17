<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetRequestController extends Controller
{
    public function create()
    {
        return view('livewire.auth.forgot-password');
    }

    public function store(Request $request, SmsGatewayService $sms): RedirectResponse
    {
        $identifier = trim((string) (
            $request->input('identifier')
            ?? $request->input('email')
            ?? $request->input('phone')
            ?? ''
        ));

        $request->merge(['identifier' => $identifier]);
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        $user = $isEmail
            ? User::where('email', strtolower($identifier))->first()
            : $this->findUserByPhone($identifier);

        if (!$user) {
            throw ValidationException::withMessages([
                'identifier' => __('We could not find a user with that email or phone number.'),
            ]);
        }

        if ($isEmail) {
            $status = Password::broker()->sendResetLink(['email' => $user->email]);

            if ($status !== Password::RESET_LINK_SENT) {
                throw ValidationException::withMessages([
                    'identifier' => __($status),
                ]);
            }

            return back()->with('status', 'Password reset link sent to your email.');
        }

        $token = Password::broker()->createToken($user);
        $link = route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email);
        $sent = $sms->send((string) $user->phone, "Reset your password: {$link}");

        if (!$sent) {
            throw ValidationException::withMessages([
                'identifier' => __('Unable to send reset SMS right now. Please try email instead or contact support.'),
            ]);
        }

        return back()->with('status', 'Password reset link sent to your phone by SMS.');
    }

    private function findUserByPhone(string $rawPhone): ?User
    {
        $phone = trim($rawPhone);
        if ($phone === '') {
            return null;
        }

        $normalizedDigits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($normalizedDigits === '') {
            return User::where('phone', $phone)->first();
        }

        return User::query()
            ->where('phone', $phone)
            ->orWhereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                [$normalizedDigits]
            )
            ->first();
    }
}
