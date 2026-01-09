<?php

namespace App\Http\Requests\Plants;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantRequest extends FormRequest
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
            'plant_reference_id'      => 'sometimes|nullable|integer|exists:plant_references,id',
            'custom_name'             => 'sometimes|required|string|max:255',
            'image'                   => 'sometimes|nullable|image|max:4096',
            'watering_frequency_days' => 'sometimes|nullable|integer|min:1|max:365',
            'next_watering_date'      => 'sometimes|nullable|date',
            'notes'                   => 'sometimes|nullable|string',
        ];
    }
}
