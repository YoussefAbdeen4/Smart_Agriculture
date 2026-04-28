<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'title'=> $this->title,
            'content'=> $this->content,
            'user'=>[
                'id'=>$this->user->id,
                'first_name'=> $this->user->first_name,
                'last_name'=> $this->user->last_name,
                'img'=>$this->user->img? url('/img/profile/'.$this->user->img):null,
            ],
           'attachments' => $this->attachments->map(function ($attachment) {
            return [
                'attachment' => url('/img/blogs/attachments/'.$attachment->name),
            ];
        }),
        ];
    }
}
