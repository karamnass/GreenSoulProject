<?php

namespace App\Http\Requests\Plants;

use Illuminate\Foundation\Http\FormRequest;

class StorePlantRequest extends FormRequest
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
            'plant_reference_id'      => 'nullable|integer|exists:plant_references,id',
            'custom_name'             => 'required|string|max:255',

            // صورة النبتة من المستخدم (اختياري)
            'image'                   => 'nullable|image|max:4096',

            'watering_frequency_days' => 'nullable|integer|min:1|max:365',
            'next_watering_date'      => 'nullable|date',
            'notes'                   => 'nullable|string',
        ];
    }
}
