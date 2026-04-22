<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Chat;
use App\Models\Farm;
use App\Policies\BlogPolicy;
use App\Policies\ChatPolicy;
use App\Policies\FarmPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Farm::class => FarmPolicy::class,
        Blog::class => BlogPolicy::class,
        Chat::class => ChatPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
