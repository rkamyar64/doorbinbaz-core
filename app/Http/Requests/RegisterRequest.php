<?php

namespace App\Http\Requests;

use App\Http\Libs\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter your name.',
            'family.required' => 'Please enter your family.',
            'phone.required' => 'Please enter your phone.',
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Please enter your password.',
            'password.min' => 'Password must be at least 8 characters.',
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
