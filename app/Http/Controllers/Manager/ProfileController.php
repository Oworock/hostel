<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('manager.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($user->is_admin_uploaded && $user->must_change_password && empty($validated['new_password'])) {
            return redirect()->back()->withErrors([
                'new_password' => 'You must set a new password before continuing.',
            ])->withInput();
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        if (!empty($validated['new_password'])) {
            $validated['password'] = Hash::make($validated['new_password']);
            $validated['must_change_password'] = false;
        }
        unset($validated['new_password'], $validated['new_password_confirmation']);
        $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $user->update($validated);

        return redirect()->route('manager.profile.edit')->with('success', 'Profile updated successfully!');
    }
}
