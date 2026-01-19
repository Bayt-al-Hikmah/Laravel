<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UpdateProfile;
use Illuminate\Support\Facades\Hash;

class usercontroller extends Controller{
    public function index(Request $request){
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return new UserResource($request->user());
    }

    public function update_profile(UpdateProfile $request){
        $validated = $request->validated();
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('uploads', 'public');
            $validated['avatar'] = $path;
        }
        $request->user()->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $request->user()->avatar,
        ]);
        return response()->json(['message' => 'Profile Updated'], 201);
    }

    public function update_password(UpdatePassword $request){
    $validated = $request->validated();
    $password = Hash::make($validated['password']);

    $request->user()->update([
        'password' => Hash::make($password),
    ]);

    return response()->json(['message' => 'Password updated successfully']);
    }

    
}
