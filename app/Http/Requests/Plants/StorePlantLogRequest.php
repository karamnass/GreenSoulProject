<?php

namespace App\Http\Requests\Plants;

use Illuminate\Foundation\Http\FormRequest;

class StorePlantLogRequest extends FormRequest
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
             'action_type' => 'required|string|max:50', // لاحقاً ممكن تحصرها in:watering,fertilizing,...
            'details'     => 'nullable|string',
            'logged_at'   => 'nullable|date',
        ];
    }
}
