<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'blog';

    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    /**
     * Get the user that created this blog.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attachments for this blog.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AttachmentBlog::class);
    }

    /**
     * Get the comments on this blog.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the reactions on this blog.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(React::class);
    }
}
