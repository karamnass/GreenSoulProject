<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Traits\ApiResponse;
use App\Http\Resources\TipResource;

class TipController extends Controller
{
   use ApiResponse;

   // قائمة النصائح (للموبايل)
   public function index()
   {
      $tips = Tip::query()
         ->orderByDesc('created_at')
         ->paginate(10);

      return $this->success(TipResource::collection($tips), 'Tips retrieved successfully.');
   }

   public function show(Tip $tip)
   {
      return $this->success(new TipResource($tip), 'Tip retrieved successfully.');
   }
}
