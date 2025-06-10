<?php

namespace App\Http\Requests\TaskRequest;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        $group = $this->attributes->get('group');
        if ($user && $group) {
            // Check if the current user is part of the group
            $check1 = $user->groups()->where('group_id', $group->id)->exists();

            $assignedUserId = $this->input('assigned_to');
            if($assignedUserId === null ) {
                return $check1;
            }
            $assignedUser = User::findOrFail($assignedUserId);
            // Check if the assigned user is part of the group
            $check2 = $assignedUser->groups()->where('group_id', $group->id)->exists();

            return $check1 && $check2;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,completed,in_progress',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'date|after_or_equal:now',
            'deadline_date' => 'date|after_or_equal:start_date',
            'group_id' => 'exists:groups,id'
        ];
    }

    public function messages():array
    {
        return [
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'status.in' => 'The status must be one of the following: pending, completed, in_progress.',
            'assigned_to.exists' => 'The assigned user must exist in the users table.',
            'start_date.required' => 'The start date is required.',
            'deadline_date.required' => 'The deadline date is required.',
            'deadline_date.after_or_equal' => 'The deadline date must be after or equal to the start date.',
            'group_id.required' => 'The group ID is required.',
            'group_id.exists' => 'The selected group does not exist.',
            'author_id.required' => 'The author ID is required.',
            'author_id.exists' => 'The selected author does not exist.',
        ];

    }
}
