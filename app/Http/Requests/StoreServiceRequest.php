<?php

namespace App\Http\Requests;

use App\Http\Libs\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    { $serviceId = $this->route('service'); // Assuming your route parameter is 'service'

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('services', 'name')->ignore($serviceId)
            ],
            'price' => 'string|max:255',
            'description' => 'string|max:255',];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter your service name.',
            'name.unique' => 'This name is already registered.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'service name',
            'price' => 'price',
            'description' => 'description',
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
