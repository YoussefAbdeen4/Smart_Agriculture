<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'=>$this->name,
            'area'=>$this->area,
            'location'=>$this->location,
            'soil_type'=>$this->soil_type,
            'img'=>url('img/Farm/'.$this->img),
        ];
    }
}
