<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIRequest extends Model
{
    use HasFactory;

    protected $table = 'ai_requests';

    protected $fillable = [
        'request_type',
        'request_data',
        'response_data',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'json',
            'response_data' => 'json',
        ];
    }

    /**
     * Get the user that made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
