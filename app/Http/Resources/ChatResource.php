<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'chat_id'  => $this->id,
            'sender' => [
                'first_name' => $this->sender->first_name,
                'last_name'  => $this->sender->last_name,
                'img'        => $this->sender->img ? url('img/profile/' . $this->sender->img) : null,
            ],
            'receiver' => [
                'first_name' => $this->receiver->first_name,
                'last_name'  => $this->receiver->last_name,
                'img'        => $this->receiver->img ? url('img/profile/' . $this->receiver->img) : null,
            ],
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
