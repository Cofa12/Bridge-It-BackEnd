<?php

namespace App\Http\Requests\landingRequest;

use Illuminate\Foundation\Http\FormRequest;

class GetSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            //
            'email'=>'required|email|unique:subscriptors,email'
        ];
    }
    public function messages(): array
    {
        return [
            'email.required'=>'Email is required',
            'email.email'=>'Email is invalid',
            'email.unique'=>'Email already exists',
        ];
    }
}
