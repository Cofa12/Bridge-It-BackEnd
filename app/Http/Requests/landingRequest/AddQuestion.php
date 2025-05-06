<?php

namespace App\Http\Requests\landingRequest;

use Illuminate\Foundation\Http\FormRequest;

class AddQuestion extends FormRequest
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
            'email'=>'required|email',
            'name'=>'required|string',
            'question'=>'required',
            'subject'=>'required|String'
        ];
    }
    public function messages(): array
    {
        return [
            'email.required'=>'Email is required',
            'email.email'=>'Email is invalid',
            'name.required'=>'Name is required',
            'question.required'=>'Question is required',
            'subject.required'=>'Subject is required',
        ];
    }
}
