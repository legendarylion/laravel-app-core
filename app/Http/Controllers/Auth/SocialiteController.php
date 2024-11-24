<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // First try to find user by provider_id
            $user = User::where('provider_id', $socialUser->getId())
                       ->where('provider_name', $provider)
                       ->first();
            
            // If not found, try to find by email
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if ($user) {
                    // User exists but used a different provider
                    // Update their provider credentials
                    $user->update([
                        'provider_name' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'provider_token' => $socialUser->token,
                        'provider_refresh_token' => $socialUser->refreshToken,
                    ]);
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'provider_name' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'provider_token' => $socialUser->token,
                        'provider_refresh_token' => $socialUser->refreshToken,
                        'email_verified_at' => now(),
                        'password' => Hash::make(Str::random(24))
                    ]);
                }
            } else {
                // Update the token for existing user
                $user->update([
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                ]);
            }

            Auth::login($user);
            return redirect()->route('dashboard');

        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }
}