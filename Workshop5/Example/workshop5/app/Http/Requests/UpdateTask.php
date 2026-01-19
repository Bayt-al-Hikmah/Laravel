<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTask extends FormRequest{

    public function authorize(): bool{
       return $this->user() !== null;
    }

    public function rules(): array{
        return [
            'state' => 'required|string',
        ];
    }
}
