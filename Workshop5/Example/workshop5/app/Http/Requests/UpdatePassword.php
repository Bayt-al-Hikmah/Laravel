<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePassword extends FormRequest{

    public function authorize(): bool{
        return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'password' => 'required|string|min:6',
        ];
    }
}
