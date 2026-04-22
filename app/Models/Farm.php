<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'area',
        'soil_type',
        'img',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns this farm.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plants on this farm.
     */
    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }

    /**
     * Get the plans for this farm.
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Get the users with access to this farm.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'farm_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}
