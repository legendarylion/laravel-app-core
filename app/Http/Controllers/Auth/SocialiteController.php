<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $user = User::where([
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
            ])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider_name' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                    'password' => '',  // Since we're using social auth, no password needed
                ]);
            }

            Auth::login($user);

            return redirect()->route('dashboard');
            
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Something went wrong or you have rejected the app.']);
        }
    }
}