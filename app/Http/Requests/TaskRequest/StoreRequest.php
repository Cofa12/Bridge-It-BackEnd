<?php

namespace App\Http\Requests\TaskRequest;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $group= $this->attributes->get('group');
        if ($user && $group) {
            $assignedUserId = $this->input('assigned_to');
            $assignedUser = User::findOrFail($assignedUserId);
            // Check if the assigned user is part of the group
           $check1 = $assignedUser->groups()->where('group_id', $group->id)->exists();
            // Check if the current user is part of the group
           $check2 = $user->groups()->where('group_id', $group->id)->exists();

            return $check1 && $check2;
        }else{
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
            'Urgency' => 'string|in:Later,Normal,Urgent',
            'assigned_to' => 'required|exists:users,id',
            'deadline_date' => 'required|date|after_or_equal:now',
            'group_id' => 'required|exists:groups,id'
        ];
    }

    public function messages():array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'Urgency.string' => 'The urgency must be a string.',
            'Urgency.in' => 'The urgency must be one of the following: Later, Normal, Urgent.',
            'assigned_to.exists' => 'The selected assigned user does not exist.',
            'deadline_date.required' => 'The deadline date field is required.',
            'deadline_date.date' => 'The deadline date must be a valid date.',
            'deadline_date.after_or_equal' => 'The deadline date must be a date after or equal to today.',
            'group_id.required' => 'The group ID field is required.',
            'group_id.exists' => 'The selected group does not exist.'
        ];

    }

}
