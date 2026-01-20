<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find existing user by google_id
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                // Check if email already exists (different auth method)
                $existingUser = User::where('email', $googleUser->getEmail())->first();

                if ($existingUser) {
                    // Account linking is disabled - reject login
                    return redirect()->route('login')->withErrors([
                        'email' => __('An account with this email already exists. Please sign in using your original method.'),
                    ]);
                }

                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(), // Google users are auto-verified
                    'status' => 'active',
                    'password' => null, // No password for Google users
                ]);

                // Assign default buyer role
                $user->assignRole('buyer');
            } else {
                // Update avatar if changed
                $user->update([
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            // Check if user is banned
            if ($user->isBanned()) {
                return redirect()->route('login')->withErrors([
                    'email' => __('Your account has been suspended. Reason: :reason', [
                        'reason' => $user->ban_reason ?? 'No reason provided'
                    ]),
                ]);
            }

            // Login user
            Auth::login($user, true);

            request()->session()->regenerate();

            return redirect()->intended(route('shop.index'));

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => __('Failed to authenticate with Google. Please try again.'),
            ]);
        }
    }
}
