<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plan';

    protected $fillable = [
        'name',
        'irrigation_date',
        'fertilization_date',
        'note',
        'farm_id',
    ];

    protected function casts(): array
    {
        return [
            'irrigation_date' => 'date',
            'fertilization_date' => 'date',
        ];
    }

    /**
     * Get the farm this plan belongs to.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get the plants associated with this plan.
     */
    public function plants(): BelongsToMany
    {
        return $this->belongsToMany(Plant::class, 'plan_plants');
    }
}
