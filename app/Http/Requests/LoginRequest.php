<?php

namespace App\Http\Requests;

use App\Http\Libs\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email.',
            'password.required' => 'Please enter your password.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {

        $allErrors = collect($validator->errors()->all())->flatten()->all();

        throw new HttpResponseException(
            Response::error(
                'Validation failed',
                $allErrors,
                422
            )
        );
    }
}
