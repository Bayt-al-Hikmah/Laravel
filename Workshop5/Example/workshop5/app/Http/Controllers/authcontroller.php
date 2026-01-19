<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class authcontroller extends Controller {
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('uploads', 'public');
            $validated['avatar'] = $path;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $validated['avatar'] ?? null,
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(LoginRequest $request){
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
            'message' => 'Invalid credentials',
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
        ], 200);
    }
    public function logout(Request $request){
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Successfully logged out. Token revoked.'
    ], 200);
}


}