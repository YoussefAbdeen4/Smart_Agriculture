<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'handle',
        'password',
        'phone',
        'img',
        'role',
        'engineer_id',
        'google_id',
        'registration_completed',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the engineer (supervisor) that supervises this farmer user.
     * Returns null if user is an independent Engineer.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }

    /**
     * Get the farmers/staff working under this engineer.
     * Only relevant if user role is 'engineer'.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'engineer_id');
    }

    /**
     * Alias for supervisor() - get the engineer that supervises this user.
     *
     * @deprecated Use supervisor() instead
     */
    public function engineer(): BelongsTo
    {
        return $this->supervisor();
    }

    /**
     * Alias for staff() - get the users supervised by this engineer.
     *
     * @deprecated Use staff() instead
     */
    public function subordinates(): HasMany
    {
        return $this->staff();
    }

    /**
     * Get the farms owned by this user.
     */
    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class);
    }

    /**
     * Get the farms where user has access via pivot table.
     */
    public function assignedFarms(): BelongsToMany
    {
        return $this->belongsToMany(Farm::class, 'farm_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the AI requests made by this user.
     */
    public function aiRequests(): HasMany
    {
        return $this->hasMany(AIRequest::class);
    }

    /**
     * Get the chats sent by this user.
     */
    public function sentChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    /**
     * Get the chats received by this user.
     */
    public function receivedChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    /**
     * Get the messages sent by this user.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the blogs created by this user.
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Get the comments made by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the reactions made by this user.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(React::class);
    }

    /**
     * Check if user is an engineer.
     */
    public function isEngineer(): bool
    {
        return $this->role === 'engineer';
    }

    /**
     * Check if user is a farmer.
     */
    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }
}
