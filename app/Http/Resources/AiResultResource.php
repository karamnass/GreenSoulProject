<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'plant_name' => $this->plant_name,
            'confidence' => $this->confidence,
            'recommendation' => $this->recommendation,
        ];
    }
}
