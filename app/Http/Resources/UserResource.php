<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'phone'            => $this->phone,
            'is_active'        => (bool) $this->is_active,
            'phone_verified_at' => $this->phone_verified_at,

            // role (اختياري حسب التحميل)
            'role' => $this->whenLoaded('role', function () {
                return [
                    'id'        => $this->role?->id,
                    'role_name' => $this->role?->role_name,
                ];
            }),

            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
