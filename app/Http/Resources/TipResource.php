<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'content'    => $this->content,
            'image'      => $this->image, // accessor يرجّع URL (حسب موديل Tip عندك)
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
