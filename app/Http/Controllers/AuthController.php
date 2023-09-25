<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

    /**
     * Register a new user with some datas.
     *
     * @group Authentication
     * @bodyParam name string required The name of the user.
     * @bodyParam email email required The email address of the user (must be unique).
     * @bodyParam password string required|min:8|regex:/^(?=.*[A-Z])(?=.*\d).+$/ The user's password.
     * @bodyParam phone_number string optional The phone number of the user.
     * @bodyParam address string optional The address of the user.
     * @bodyParam position string optional The position of the user.
     *
     * @response 201 {
     *     "user": {
     *         "name": "John Doe",
     *         "email": "johndoe@example.com",
     *         "phone_number": "1234567890",
     *         "address": "123 Main St",
     *         "position": "Developer"
     *     },
     *     "msg": "User created successfully"
     * }
     * @response 400 {
     *     "error": {
     *         "email": ["The email field must be unique."],
     *         "password": ["The password field does not match the required format."]
     *     }
     * }
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/^(?=.*[A-Z])(?=.*\d).+$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'phone_number' => $request->input('phone_number'),
            'address' => $request->input('address'),
            'position' => $request->input('position')
        ]);

        return response()->json(['user' => $user, 'msg' => 'User created successfully'], 201);
    }

    /**
     * Log in a registered user with email and password.
     *
     * @group Authentication
     * @bodyParam email email required The email address of the user.
     * @bodyParam password string required The user's password.
     *
     * @response 200 {
     *     "token": "your-access-token"
     * }
     * @response 401 {
     *     "error": "Invalid credentials"
     * }
     * @response 500 {
     *     "error": "Could not create token"
     * }
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * Get the currently authenticated user's information.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {
     *     "user": {
     *         "name": "John Doe",
     *         "email": "johndoe@example.com",
     *         "phone_number": "1234567890",
     *         "address": "123 Main St",
     *         "position": "Developer"
     *     }
     * }
     */
    public function me()
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);
    }

    /**
     * Log out the currently authenticated user.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 201 {
     *     "message": "Logged out successfully"
     * }
     */
    public function logout()
    {
        $token = JWTAuth::parseToken();
        $token->invalidate();

        return response()->json(['message' => 'Logged out successfully'], 201);
    }
}
