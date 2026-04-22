<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'message'    => $this->content,
            'sent_at'    => $this->created_at->diffForHumans(), 
            'timestamp'  => $this->created_at->format('Y-m-d H:i:s'),
            'sender' => [
                'first_name' => $this->sender->first_name,
                'last_name'  => $this->sender->last_name,
                'img'        => $this->sender->img ? url('img/profile/' . $this->sender->img) : null,
            ],
            'attachments' => $this->attachments->map(function ($file) {
                return [
                    'url' => url('/img/chats/attachments/' . $file->name),
                ];
            }),
        ];
    }
}
