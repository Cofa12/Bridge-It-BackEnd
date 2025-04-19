<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name'=>'required|string|max:40',
            'email' => 'required|email|unique:users,email',
            'device_token'=>'required|string',
            'password'=>'required|string',
            'type' => 'string',
            'phone' => 'string'
        ];
    }

    public function messages(): array{
        return [
            'name.required'=>'Name is required',
            'name.string'=>'Name must be string',
            'name.max'=>'Name is too long',
            'email.required'=>'Email is required',
            'email.email'=>'Email is invalid',
            'email.unique'=>'Email already exists',
            'device_token.required'=>'Device token is required',
            'device_token.string'=>'Device token must be string',
            'password.required'=>'Password is required'
        ];
    }
}
