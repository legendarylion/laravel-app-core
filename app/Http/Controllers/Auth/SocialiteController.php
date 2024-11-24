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
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Laravel\Fortify\Features;

class SocialiteController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function redirect(string $provider): RedirectResponse
    {
        Log::info('Social callback started', [
            'provider' => $provider,
            'url' => request()->url(),
            'session_id' => session()->getId()
        ]);
        
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            Log::info('Social callback started', [
                'provider' => $provider,
                'email' => $socialUser->getEmail(),
                'session_id' => session()->getId()
            ]);

            $user = User::where('provider_id', $socialUser->getId())
                       ->where('provider_name', $provider)
                       ->first();
        
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if ($user) {
                    $user->update([
                        'provider_name' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'provider_token' => $socialUser->token,
                        'provider_refresh_token' => $socialUser->refreshToken,
                    ]);
                } else {
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
                $user->update([
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                ]);
            }

            // Check if 2FA is enabled for this user
            if (Features::enabled(Features::twoFactorAuthentication()) && 
                !empty($user->two_factor_secret) && 
                !empty($user->two_factor_confirmed_at)) {
                
                Log::info('2FA is enabled for user', [
                    'user_id' => $user->id,
                    'session_id' => session()->getId()
                ]);

                session([
                    'login.id' => $user->id,
                    'login.remember' => true,
                    'url.intended' => url('/dashboard')
                ]);

                Log::info('Set session for 2FA', [
                    'session_data' => session()->all()
                ]);

                return redirect()->route('two-factor.login');
            }

            // If no 2FA required, log in normally
            Auth::login($user, true);
            
            Log::info('Standard login completed', [
                'user_id' => $user->id,
                'is_authenticated' => Auth::check()
            ]);

            return redirect()->intended('/dashboard');

        } catch (Exception $e) {
            Log::error('Social auth error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }
}