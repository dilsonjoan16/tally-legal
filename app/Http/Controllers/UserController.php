<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Mail\WelcomeMail;
use App\Services\SaveRemoteAssets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Constructor for the UserController class.
     *
     * @param \App\Models\User $model The Eloquent model instance to use.
     * @param \App\Services\SaveRemoteAssets $saveRemoteAssets The service for saving remote assets.
     */
    public function __construct(
        public User $model,
        public SaveRemoteAssets $saveRemoteAssets
    )
    {
        parent::__construct($saveRemoteAssets);
        $this->model = new User;
    }

/**
 * Retrieves a JSON response of all non-trashed users.
 *
 * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of users.
 */
    public function index(): JsonResponse
    {
        return $this->abstractIndex($this->model);
    }

    /**
     * Retrieves a JSON response of the given user and its relationships.
     *
     * @param \App\Models\User $user The Eloquent model instance to retrieve.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the user and its relationships.
     */
    public function show(User $user): JsonResponse
    {
        return $this->abstractShow($user);
    }

    /**
     * Retrieves a JSON response of all trashed users.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of trashed users.
     */
    public function getTrashedUsers(): JsonResponse
    {
        return $this->abstractGetTrashed($this->model);
    }

    /**
     * Retrieves a JSON response of all records, including trashed records, of the given model.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of records.
     */
    public function getAllUsers(): JsonResponse
    {
        return $this->abstractGetAll($this->model);
    }

    /**
     * Retrieves a JSON response of all records, including trashed records, of the given model
     * and its relationships.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the collection of records.
     */
    public function getAllUserDetails(): JsonResponse
    {
        return $this->abstractGetAllRegistersWithDetails($this->model);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:12|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,12}$/',
            'role_id' => 'required|in:' . RoleEnum::USER->value . ',' . RoleEnum::ADMIN->value,
            'status' => 'required|in:' . StatusEnum::ACTIVE->value . ',' . StatusEnum::INACTIVE->value,
        ], [
            'email.unique' => 'Email already exists',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
        ]);

        try {
            $user = User::create($request->all());

            // Send welcome email.
            Mail::to($user->email)->queue(new WelcomeMail($user->username));

            return response()->json([
                'message' => 'User created',
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'User not created',
                'error' => $th
            ], 500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * Validates the input request for username, email, and optionally password.
     * Updates the user's information in the database and returns a JSON response.
     * Logs any errors encountered during the update process.
     *
     * @param \App\Models\User $user The user instance to update.
     * @param \Illuminate\Http\Request $request The request instance containing updated user data.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the updated user or an error message.
     */
    public function update(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $user->getKey(),
            'password' => 'nullable|string|min:8|max:12|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,12}$/',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
        ]);

        try {
            $hasPassword = $request->has('password') && $request->input('password') !== null && $request->input('password') !== '';

            $user->update($hasPassword ? $request->only('username', 'email', 'password') : $request->only('username', 'email'));

            return response()->json([
                'message' => 'User updated',
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error updating user', ['error' => $th]);
            return response()->json([
                'message' => 'User not updated',
                'error' => $th
            ], 500);
        }
    }

    /**
     * Update the status of the specified user in storage.
     *
     * Validates the input request for the status field, ensuring it matches the predefined active or inactive states.
     * Updates the user's status in the database and returns a JSON response.
     *
     * @param \App\Models\User $user The user instance whose status is to be updated.
     * @param \Illuminate\Http\Request $request The request instance containing the updated status.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the updated user status or an error message.
     */
    public function updateStatus(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . StatusEnum::ACTIVE->value . ',' . StatusEnum::INACTIVE->value,
        ]);

        $user->update($request->all());
        if (!$user) {
            return response()->json([
                'message' => 'User not updated',
            ], 500);
        }

        return response()->json([
            'message' => 'Status updated',
            'user' => $user,
            'new_status' => $user->status,
        ]);
    }

    /**
     * Update the role of the specified user in storage.
     *
     * Validates the input request for the role_id field, ensuring it matches the predefined user or admin roles.
     * Updates the user's role in the database and returns a JSON response.
     *
     * @param \App\Models\User $user The user instance whose role is to be updated.
     * @param \Illuminate\Http\Request $request The request instance containing the updated role.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the updated user role or an error message.
     */
    public function updateRole(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|in:' . RoleEnum::USER->value . ',' . RoleEnum::ADMIN->value,
        ]);

        $user->update($request->all());
        if (!$user) {
            return response()->json([
                'message' => 'User not updated',
            ], 500);
        }

        return response()->json([
            'message' => 'Role updated',
            'user' => $user,
            'new_role' => $user->role
        ]);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param \App\Models\User $user The user instance to delete.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function delete(User $user): JsonResponse
    {
        return $this->abstractDelete($user);
    }

    /**
     * Restore a user from the database.
     *
     * @param \App\Models\User $user The user instance to restore.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function restoreUser(User $user): JsonResponse
    {
        return $this->abstractRestore($user);
    }

    /**
     * Restores all trashed users from the database.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function restoreAllUsers(): JsonResponse
    {
        return $this->abstractRestoreAll($this->model);
    }

    /**
     * Permanently deletes a user from the database.
     *
     * @param \App\Models\User $user The user instance to permanently delete.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success or error message.
     */
    public function forceDelete(User $user)
    {
        return $this->abstractForceDelete($user);
    }
}
