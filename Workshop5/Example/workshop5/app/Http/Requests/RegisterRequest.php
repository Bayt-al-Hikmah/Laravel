<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest{

    public function authorize(): bool{
        return true;
    }
    
    public function rules(): array{
        return [
            'name' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image'
        ];
    }
}
