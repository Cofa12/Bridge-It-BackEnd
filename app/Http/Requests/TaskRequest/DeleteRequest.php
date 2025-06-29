<?php

namespace App\Http\Requests\TaskRequest;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $user = $this->user();
        $group = $this->attributes->get('group');
        if ($user && $group) {
            return $user->groups()->where('group_id', $group->id)->exists();
        } else {
            return false;
        }
    }


    public function rules():array
    {
        return [];

    }
}
