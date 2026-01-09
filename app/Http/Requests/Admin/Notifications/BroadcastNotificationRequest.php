<?php

namespace App\Http\Requests\Admin\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:150',
            'body'         => 'required|string',
            'type'         => 'nullable|string|in:system,watering,complaint',
            'scheduled_at' => 'nullable|date',

            // فلترة اختيارية
            'role'         => 'nullable|string|in:user,admin',
            'only_active'  => 'nullable|boolean',
        ];
    }
}
