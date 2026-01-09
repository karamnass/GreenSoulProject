<?php

namespace App\Http\Requests\Admin\PlantReferences;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantReferenceRequest extends FormRequest
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
            'description' => 'sometimes|required|string',
            'image'       => 'sometimes|nullable|image|max:4096',
        ];
    }
}
