<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(public User $model)
    {
        parent::__construct();
        $this->model = new User;
    }

    public function index()
    {
        return $this->abstractIndex($this->model);
    }

    public function show(User $user)
    {
        return $this->abstractShow($user);
    }

    public function getTrashedUsers()
    {
        return $this->abstractGetTrashed($this->model);
    }

    public function getAllUsers()
    {
        return $this->abstractGetAll($this->model);
    }

    public function getAllUserDetails()
    {
        return $this->abstractGetAllRegistersWithDetails($this->model);
    }

    public function store(Request $request)
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

    public function update(User $user, Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email,except,' . $user->id,
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

    public function updateStatus(User $user, Request $request)
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

    public function updateRole(User $user, Request $request)
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

    public function delete(User $user)
    {
        return $this->abstractDelete($user);
    }

    public function restoreUser(User $user)
    {
        return $this->abstractRestore($user);
    }

    public function restoreAllUsers()
    {
        return $this->abstractRestoreAll($this->model);
    }

    public function forceDelete(User $user)
    {
        return $this->abstractForceDelete($user);
    }
}
