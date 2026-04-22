<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentBlog extends Model
{
    use HasFactory;

    protected $table = 'attachments_blog';

    protected $fillable = [
        'name',
        'blog_id',
    ];

    protected $appends = [
        'file_url',
    ];

    /**
     * Get the full URL for the file path.
     */
    protected function casts(): array
    {
        return [
            'file_path' => 'string',
        ];
    }

    /**
     * Get the blog this attachment belongs to.
     */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Get the full URL for the file.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return asset('storage/'.$this->file_path);
    }
}
