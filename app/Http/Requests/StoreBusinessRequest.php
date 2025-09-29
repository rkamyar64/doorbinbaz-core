<?php

namespace App\Http\Requests;

use App\Http\Libs\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreBusinessRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $businessId = $this->route('businesses') ? $this->route('business')->id : null;

        return [
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('businesses', 'mobile')->ignore($businessId)
            ],
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'tell' => 'nullable|string|max:20',
            'zipcode' => 'nullable|string|max:10',
            'national_id' => 'nullable|string|max:20|unique:businesses,national_id,' . $businessId,
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter your name.',
            'family.required' => 'Please enter your family name.',
            'business_name.required' => 'Please enter your business name.',
            'address.required' => 'Please enter your address.',
            'mobile.required' => 'Please enter your mobile number.',
            'national_id.unique' => 'This national ID is already registered.',
            'mobile.unique' => 'This mobile is already registered.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'first name',
            'family' => 'last name',
            'business_name' => 'business name',
            'mobile' => 'mobile number',
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
