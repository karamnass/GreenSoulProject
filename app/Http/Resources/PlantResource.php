<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //   return [
        //       'id'                     => $this->id,
        //       'user_id'                => $this->user_id,
        //
        //       'plant_reference_id'     => $this->plant_reference_id,
        //       'plant_reference'        => $this->whenLoaded('plantReference', function () {
        //           return new PlantReferenceResource($this->plantReference);
        //       }),
        //
        //       'custom_name'            => $this->custom_name,
        //       'image'                  => $this->image, // لو عندك accessor يرجّع URL فهو ممتاز
        //
        //       'watering_frequency_days' => $this->watering_frequency_days,
        //       'next_watering_date'     => $this->next_watering_date,
        //       'notes'                  => $this->notes,
        //
        //       'created_at'             => $this->created_at,
        //       'updated_at'             => $this->updated_at,
        //   ];

        return [
            'id'                     => $this->id,
            'user_id'                => $this->user_id,

            'plant_reference_id'     => $this->plant_reference_id,
            'plant_reference'        => $this->whenLoaded('plantReference', function () {
                return new PlantReferenceResource($this->plantReference);
            }),

            'custom_name'            => $this->custom_name,
            'image'                  => $this->image,

            'watering_frequency_days' => $this->watering_frequency_days,
            'next_watering_date'     => $this->next_watering_date,
            'notes'                  => $this->notes,

            'ai_result'              => $this->relationLoaded('latestAiResult')
                ? ($this->latestAiResult ? new AiResultResource($this->latestAiResult) : null)
                : null,

            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
