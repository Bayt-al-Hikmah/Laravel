<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfile extends FormRequest{

    public function authorize(): bool{
        return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'name' => 'required|string',
            'email' => 'required|email',
            'avatar' => 'nullable|image'
        ];
    }
}
