<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\SaveRemoteAssets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Constructor for the ProfileController class.
     *
     * @param \App\Models\Profile $model The Eloquent model instance to use.
     * @param \App\Services\SaveRemoteAssets $saveRemoteAssets The service for saving remote assets.
     */
    public function __construct(
        public Profile $model,
        public SaveRemoteAssets $saveRemoteAssets
    )
    {
        parent::__construct($saveRemoteAssets);
        $this->model = new Profile;
    }

    /**
     * Retrieve the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the authenticated user's profile.
     */
    public function showProfile(): JsonResponse
    {
        return response()->json([
            'profile' => auth()->user()->profile
        ]);
    }

    /**
     * Create a new profile in the database.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the profile data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function storeProfile(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string|min:7|max:15|unique:profiles,phone',
            'address' => 'required|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'zip' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();

            $request->merge([
                'user_id' => $user->id
            ]);

            $request->except(['avatar']);

            // Handle transaction.
            $user->profile()->create($request->all());

            return response()->json([
            'message' => 'Profile created',
            'profile' => $user->profile
        ], 201);
        } catch (\Throwable $th) {
            Log::error('Error creating profile', ['error' => $th]);
            return response()->json([
                'message' => 'Profile not created',
            ], 500);
        }
    }

    /**
     * Updates an existing profile in the database.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the profile data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the operation.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if (!$user->profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone' => 'nullable|string|unique:profiles,phone,' . $user->profile->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'zip' => 'nullable|string',
        ]);

        $request->except(['avatar']);

        try {
            // Handle transaction.
            $user->profile()->update($request->all());

            return response()->json([
                'message' => 'Profile updated',
                'profile' => $user->profile
            ], 201);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'message' => 'Profile not updated',
            ], 500);
        }
    }

    /**
     * Update the avatar of the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manageAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if (!$user->profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        try {
            $user->profile->avatar = $this->saveRemoteAssets->__invoke($request, $user->profile->avatar, $this->model->getTable(), 'avatar');
            $user->profile->save();

            return response()->json([
                'message' => 'Avatar updated',
                'profile' => $user->profile
            ], 201);
        } catch (\Throwable $th) {
            Log::error('Avatar not updated.', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Avatar not updated',
            ], 500);
        }
    }
}
