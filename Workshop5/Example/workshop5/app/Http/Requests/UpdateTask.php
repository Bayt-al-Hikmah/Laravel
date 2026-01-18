<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTask extends FormRequest{

    public function authorize(): bool{
        return $request->user();
    }

    public function rules(): array{
        return [
            'state' => 'required|string',
        ];
    }
}
