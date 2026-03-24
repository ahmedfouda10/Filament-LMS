<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        abort_unless(Setting::get('feature_social_login') === 'true', 403, 'Social login is currently disabled.');
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(Setting::get('feature_social_login') === 'true', 403, 'Social login is currently disabled.');

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Social authentication failed.'], 422);
        }

        return $this->authenticateOrRegister($provider, $socialUser);
    }

    public function handleToken(Request $request, string $provider)
    {
        abort_unless(Setting::get('feature_social_login') === 'true', 403, 'Social login is currently disabled.');
        $request->validate(['access_token' => 'required|string']);

        try {
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->access_token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid social token.'], 422);
        }

        return $this->authenticateOrRegister($provider, $socialUser);
    }

    private function authenticateOrRegister(string $provider, $socialUser)
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            $socialAccount = SocialAccount::where('provider', $provider)->where('provider_id', $socialUser->getId())->first();

            if ($socialAccount) {
                $socialAccount->update(['provider_token' => $socialUser->token, 'provider_refresh_token' => $socialUser->refreshToken, 'avatar_url' => $socialUser->getAvatar()]);
                $user = $socialAccount->user;
                abort_unless($user->is_active, 403, 'Your account has been deactivated.');
                return response()->json(['data' => ['user' => new UserResource($user), 'token' => $user->createToken('social-auth')->plainTextToken, 'is_new_user' => false]]);
            }

            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                SocialAccount::create(['user_id' => $user->id, 'provider' => $provider, 'provider_id' => $socialUser->getId(), 'provider_token' => $socialUser->token, 'provider_refresh_token' => $socialUser->refreshToken, 'avatar_url' => $socialUser->getAvatar()]);
                if (!$user->avatar && $socialUser->getAvatar()) $user->update(['avatar' => $socialUser->getAvatar()]);
                if (!$user->email_verified_at) $user->update(['email_verified_at' => now()]);
                return response()->json(['data' => ['user' => new UserResource($user), 'token' => $user->createToken('social-auth')->plainTextToken, 'is_new_user' => false]]);
            }

            $user = User::create(['name' => $socialUser->getName() ?? 'User', 'email' => $socialUser->getEmail(), 'password' => bcrypt(Str::random(32)), 'avatar' => $socialUser->getAvatar(), 'role' => 'student', 'is_active' => true, 'email_verified_at' => now()]);
            SocialAccount::create(['user_id' => $user->id, 'provider' => $provider, 'provider_id' => $socialUser->getId(), 'provider_token' => $socialUser->token, 'provider_refresh_token' => $socialUser->refreshToken, 'avatar_url' => $socialUser->getAvatar()]);
            UserPreference::create(['user_id' => $user->id, 'theme' => 'light', 'language' => 'ar', 'direction' => 'rtl']);
            UserNotificationPreference::create(['user_id' => $user->id, 'course_updates' => true, 'marketing' => false, 'account_security' => true]);

            return response()->json(['data' => ['user' => new UserResource($user), 'token' => $user->createToken('social-auth')->plainTextToken, 'is_new_user' => true]], 201);
        });
    }
}
