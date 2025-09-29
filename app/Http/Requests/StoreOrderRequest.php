<?php

namespace App\Http\Requests;

use App\Http\Libs\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'business_id' => 'required|string|max:255|exists:businesses,id',
            'services' => 'required|string|max:255',
            'description' => 'string|max:65535',
            'status' => 'string|max:255',
            'full_price' => 'required|string|max:255',
            'fee_price' => 'string|max:255',
            'profit_price' => 'string|max:255',
            'discount' => 'string|max:255',
            'service_user_id' => 'integer|exists:users,id',

        ];
        $orderId = $this->route('orders'); // Adjust 'orders' to match your route parameter name

        if (!$orderId) {
            // This is a create request, make fields required
            $rules['business_id'] = 'required|' . $rules['business_id'];
            $rules['services'] = 'required|' . $rules['services'];
            $rules['full_price'] = 'required|' . $rules['full_price'];
        } else {
            // This is an update request, make fields optional
            $rules['business_id'] = 'sometimes|' . $rules['business_id'];
            $rules['services'] = 'sometimes|' . $rules['services'];
            $rules['full_price'] = 'sometimes|' . $rules['full_price'];
        }
        return $rules;

    }

    public function messages()
    {
        return [
            'business_id.required' => 'Please enter your business.',
            'services.required' => 'Please enter services.',
            'full_price.required' => 'Please enter full_price.',

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
