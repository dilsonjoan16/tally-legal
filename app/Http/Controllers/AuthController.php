<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Mail\WelcomeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Controller for user authentication and management.
 *
 * @see https://codersfree.com/posts/guia-paso-a-paso-implementacion-de-autenticacion-jwt-en-laravel-y-lumen
 * @see https://www.codalas.com/es/1905/using-json-web-tokens-with-laravel-to-create-apis
 * @see https://www.nigmacode.com/laravel/jwt-en-laravel
 */
class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request The request instance containing user credentials.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the authentication token or an error message.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Retrieve the authenticated user's information.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the authenticated user's details.
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Logout the authenticated user and revoke their access token.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing a success message.
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['mensaje' => 'Cierre de sesión exitoso']);
    }

    /**
     * Refresh the authenticated user's access token.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the refreshed authentication token.
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Respond with a JSON response containing the authentication token.
     *
     * @param string $token The authentication token.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the authentication token.
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Handle a registration request to the application.
     *
     * @param \Illuminate\Http\Request $request The request instance containing user data.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the registered user or an error message.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:12|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!#%*?&]).{8,12}$/',
        ], [
            'email.unique' => 'Email already exists',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
        ]);

        try {
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'role_id' => RoleEnum::USER->value,
                'status' => StatusEnum::ACTIVE,
            ]);

            // Send welcome email.
            Mail::to($user->email)->queue(new WelcomeMail($user->username));

            return response()->json([
                'message' => 'User registered',
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'User not registered',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
