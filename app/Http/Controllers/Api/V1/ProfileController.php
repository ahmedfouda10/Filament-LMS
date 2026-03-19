<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->only(['name', 'phone']));

        // If instructor, update instructorProfile fields
        if ($user->role === 'instructor') {
            $profileFields = $request->only([
                'bio',
                'specialization',
                'years_of_experience',
                'qualifications',
                'education',
                'expertise',
                'social_links',
            ]);

            if (!empty($profileFields)) {
                $profile = $user->instructorProfile ?? $user->instructorProfile()->create([]);
                $profile->update($profileFields);
            }
        }

        $user->load('instructorProfile');

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            $oldPath = str_replace('/storage/', '', $user->avatar);
            Storage::disk('public')->delete($oldPath);
        }

        // Store new avatar in public/avatars directory
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update([
            'avatar' => '/storage/' . $path,
        ]);

        return response()->json([
            'data' => [
                'avatar' => $user->avatar,
            ],
            'message' => 'Avatar uploaded successfully.',
        ]);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            $oldPath = str_replace('/storage/', '', $user->avatar);
            Storage::disk('public')->delete($oldPath);

            $user->update(['avatar' => null]);
        }

        return response()->json([
            'message' => 'Avatar removed successfully.',
        ]);
    }
}
