<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

class BlogPolicy
{
    /**
     * Determine if the user can view any blogs.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the blog.
     */
    public function view(User $user, Blog $blog): bool
    {
        return true;
    }

    /**
     * Determine if the user can create blogs.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create blogs
        return true;
    }

    /**
     * Determine if the user can update the blog.
     */
    public function update(User $user, Blog $blog): bool
    {
        // Only the author can update their blog
        return $blog->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the blog.
     */
    public function delete(User $user, Blog $blog): bool
    {
        // Only the author can delete their blog
        return $blog->user_id === $user->id;
    }

    /**
     * Determine if the user can comment on the blog.
     */
    public function comment(User $user, Blog $blog): bool
    {
        // Both engineers and farmers can comment
        return true;
    }

    /**
     * Determine if the user can react to the blog.
     */
    public function react(User $user, Blog $blog): bool
    {
        // Both engineers and farmers can react
        return true;
    }
}
