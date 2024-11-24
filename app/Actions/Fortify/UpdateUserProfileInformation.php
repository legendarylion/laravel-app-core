<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(User $user, array $input): void
    {
        // Get the request instance
        $request = request();
        
        Log::info('Starting profile update', [
            'user_id' => $user->id,
            'has_file' => $request->hasFile('photo'),
            'file_valid' => $request->hasFile('photo') ? $request->file('photo')->isValid() : false,
            'input_keys' => array_keys($input),
            'files' => $request->allFiles(),
        ]);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2048'],
        ])->validateWithBag('updateProfileInformation');

        if ($request->hasFile('photo')) {
            try {
                $file = $request->file('photo');
                Log::info('Processing photo upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'error' => $file->getError(),
                ]);

                // Generate a unique filename
                $filename = time() . '_' . $file->getClientOriginalName();
                
                // Store the file
                $path = $file->storeAs(
                    'profile-photos',
                    $filename,
                    ['disk' => 'public']
                );

                Log::info('Photo stored at path', ['path' => $path]);

                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Update user's photo path
                $user->forceFill([
                    'profile_photo_path' => $path
                ])->save();

                Log::info('User profile updated', [
                    'user_id' => $user->id,
                    'photo_path' => $path
                ]);
            } catch (\Exception $e) {
                Log::error('Error in photo upload', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}