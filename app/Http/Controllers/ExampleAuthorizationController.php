<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiTrait;
use App\Models\Blog;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Example Controller to demonstrate correct authorization usage
 * This is a reference implementation showing best practices
 */
class ExampleAuthorizationController extends Controller
{
    use ApiTrait;

    /**
     * Example 1: Check role directly on user
     * CORRECT: Using role checking method from User model
     */
    public function exampleCheckRole(Request $request): JsonResponse
    {
        $user = $request->user();

        // Using the role checking methods added to User model
        if ($user->isEngineer()) {
            return $this->dataResponse(
                compact('user'),
                'User is an engineer'
            );
        }

        if ($user->isFarmer()) {
            return $this->dataResponse(
                compact('user'),
                'User is a farmer'
            );
        }

        return $this->unauthorizedResponse('User role not recognized');
    }

    /**
     * Example 2: Authorize action on model using Policy
     * CORRECT: Using authorize() with Policy defined in AuthServiceProvider
     */
    public function exampleAuthorizeFarm(Request $request, Farm $farm): JsonResponse
    {
        // This will check FarmPolicy::view() method
        // If unauthorized, AuthorizationException is caught by exception handler
        // and returns JSON response with 403 status
        $this->authorize('view', $farm);

        $farm->load(['user', 'plants', 'plans', 'users']);

        return $this->dataResponse(
            compact('farm'),
            'Farm retrieved successfully'
        );
    }

    /**
     * Example 3: Authorize class-level action
     * CORRECT: Used for general create/store actions where no specific model exists
     */
    public function exampleAuthorizeCreate(Request $request): JsonResponse
    {
        // This will check BlogPolicy::create() method
        // Useful for actions that don't have a specific model instance
        $this->authorize('create', Blog::class);

        // Your creation logic here
        return $this->dataResponse(
            [],
            'Blog creation authorized'
        );
    }

    /**
     * Example 4: Manual authorization check with custom error handling
     * CORRECT: For complex authorization logic
     */
    public function exampleManualCheck(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();

        // Check if user can view this farm
        if (! $user->can('view', $farm)) {
            return $this->unauthorizedResponse(
                'You do not have permission to view this farm'
            );
        }

        // Check specific condition
        if (! $user->isEngineer() && $farm->user_id !== $user->id) {
            return $this->unauthorizedResponse(
                'Only farm owner or engineers can perform this action'
            );
        }

        return $this->successResponse('Authorization check passed');
    }

    /**
     * Example 5: Authorize with custom error message
     * CORRECT: When using authorize() with catch block
     */
    public function exampleWithCustomMessage(Request $request, Farm $farm): JsonResponse
    {
        try {
            $this->authorize('delete', $farm);
        } catch (AuthorizationException $e) {
            return $this->unauthorizedResponse(
                'Only the farm owner can delete this farm'
            );
        }

        // Delete logic here
        return $this->successResponse('Farm deleted successfully');
    }
}
