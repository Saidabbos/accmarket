<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TelegramAuthController extends Controller
{
    /**
     * Display the login view with Telegram button.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'telegramBotUsername' => config('services.telegram.bot_username'),
        ]);
    }

    /**
     * Handle Telegram authentication callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        $data = $request->only([
            'id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash'
        ]);

        // Verify Telegram authentication
        if (!$this->verifyTelegramAuth($data)) {
            return redirect()->route('login')->withErrors([
                'telegram' => 'Invalid Telegram authentication. Please try again.',
            ]);
        }

        // Check auth_date (must be within 24 hours)
        if (time() - $data['auth_date'] > 86400) {
            return redirect()->route('login')->withErrors([
                'telegram' => 'Authentication expired. Please try again.',
            ]);
        }

        // Find or create user
        $user = User::where('telegram_id', $data['id'])->first();

        if (!$user) {
            // Create new user from Telegram data
            $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (empty($name)) {
                $name = $data['username'] ?? 'Telegram User';
            }

            $user = User::create([
                'name' => $name,
                'telegram_id' => $data['id'],
                'telegram_username' => $data['username'] ?? null,
                'telegram_photo_url' => $data['photo_url'] ?? null,
                'email' => null, // Telegram users don't need email
                'password' => null, // No password for Telegram users
                'status' => 'active',
                'email_verified_at' => now(), // Auto-verify Telegram users
            ]);

            // Assign default buyer role
            $user->assignRole('buyer');
        } else {
            // Update existing user's Telegram info
            $user->update([
                'telegram_username' => $data['username'] ?? $user->telegram_username,
                'telegram_photo_url' => $data['photo_url'] ?? $user->telegram_photo_url,
            ]);
        }

        // Check if user is banned
        if ($user->isBanned()) {
            return redirect()->route('login')->withErrors([
                'telegram' => 'Your account has been suspended. Reason: ' . ($user->ban_reason ?? 'No reason provided'),
            ]);
        }

        // Login user
        Auth::login($user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('shop.index'));
    }

    /**
     * Verify Telegram authentication data.
     */
    private function verifyTelegramAuth(array $data): bool
    {
        $botToken = config('services.telegram.bot_token');

        if (!$botToken) {
            Log::error('Telegram bot token not configured');
            return false;
        }

        $checkHash = $data['hash'] ?? null;
        unset($data['hash']);

        // Sort and prepare data string
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                $dataCheckArr[] = $key . '=' . $value;
            }
        }
        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);

        // Generate hash
        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $checkHash);
    }
}
