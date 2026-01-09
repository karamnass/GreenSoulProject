<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,

            // بيانات المستخدم (للأدمن فقط عادةً)
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),

            'subject'        => $this->subject,
            'message'        => $this->message,
            'status'         => $this->status,
            'admin_response' => $this->admin_response,

            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
