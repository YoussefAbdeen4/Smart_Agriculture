<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllBlogsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'content' => $this->content,

            // بيانات صاحب البلوج
            'user' => [
                'first_name' => $this->user->first_name,
                'last_name'  => $this->user->last_name,
                'img'        => $this->user->img ? url('/img/profile/' . $this->user->img) : null,
            ],

            // المرفقات (صور البلوج)
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'url' => url('/img/blogs/attachments/' . $attachment->name),
                ];
            }),

            // الكومنتات واسم المستخدم وصورته
            'comments' => $this->comments->map(function ($comment) {
                return [
                    'id'      => $comment->id,
                    'comment' => $comment->content, // تأكد من اسم الكولوم في الداتابيز
                    'user'    => [
                        'first_name' => $comment->user->first_name,
                        'last_name'  => $comment->user->last_name,
                        'img'        => $comment->user->img ? url('/img/profile/' . $comment->user->img) : null,
                    ],
                ];
            }),

            // الرياكتات واسم المستخدم وصورته
            'reactions' => $this->reactions->map(function ($reaction) {
                return [
                    'is_like' => (bool) $reaction->is_like, // هيرجع true لو لايك و false لو عكس كدة
                    'user'    => [
                        'first_name' => $reaction->user->first_name,
                        'last_name'  => $reaction->user->last_name,
                        'img'        => $reaction->user->img ? url('/img/profile/' . $reaction->user->img) : null,
                    ],
                ];
            }),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];;
    }
}
