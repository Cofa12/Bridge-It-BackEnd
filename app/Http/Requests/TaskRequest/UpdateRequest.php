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
            'title' => 'required|string|max:255',
            'description' => 'required|nullable|string',
            'Urgency' => 'required|string|in:Later,Normal,Urgent',
            'assigned_to' => 'required|nullable|exists:users,id',
            'deadline_date' => 'required|date|after_or_equal:now',
        ];
    }

    public function messages():array
    {
        return [
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'Urgency.string' => 'The urgency must be a string.',
            'Urgency.in' => 'The urgency must be one of the following: Later, Normal, Urgent.',
            'assigned_to.exists' => 'The selected assigned user does not exist.',
            'deadline_date.date' => 'The deadline date must be a valid date.',
            'deadline_date.after_or_equal' => 'The deadline date must be a date after or equal to today.',
            'group_id.exists' => 'The selected group does not exist.'
        ];

    }
}
