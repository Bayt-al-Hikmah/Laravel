<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfile extends FormRequest{

    public function authorize(): bool{
        return $request->user();
    }

    public function rules(): array{
        return [
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'avatar' => 'nullable|image'
        ];
    }
}
