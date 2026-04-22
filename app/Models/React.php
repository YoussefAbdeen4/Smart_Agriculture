<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class React extends Model
{
    use HasFactory;

    protected $table = 'react';

    protected $fillable = [
        'is_like',
        'blog_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_like' => 'boolean',
        ];
    }

    /**
     * Get the blog this reaction is on.
     */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Get the user that made this reaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
