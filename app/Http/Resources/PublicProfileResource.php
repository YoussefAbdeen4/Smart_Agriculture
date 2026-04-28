<?php

namespace App\Http\Resources;

use App\Http\Resources\AllBlogsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'email' => $this->email,
        'phone'=>$this->phone,
        'handle'=>$this->handle,
        'role'=>$this->role,
        'farms' => $this->whenLoaded('farms'),
        'blogs' => AllBlogsResource::collection($this->whenLoaded('blogs')),
        'created_at' => $this->created_at->format('Y-m-d'),
    ];
}
}
