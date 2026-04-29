<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Http\Resources\PublicProfileResource;
use App\Http\Resources\ShowProfileResource;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\media;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiTrait, AuthorizesRequests, media;

    /**
     * Display the authenticated user's profile data.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->load(['supervisor', 'staff', 'farms']);

        return $this->dataResponse(
          new ShowProfileResource($user),
            'Profile retrieved successfully'
        );
    }

public function showPublicProfile($id): JsonResponse
{
    $user = User::with([
        'supervisor', 
        'staff', 
        'farms', 
        'blogs.reactions', 
        'blogs.comments'
    ])->find($id);

    if (!$user) {
        return $this->errorResponse('','User not found',404);     
        
    }

    return $this->dataResponse(
        new PublicProfileResource($user), 
        'User profile retrieved successfully'
    );
}

    /**
     * Update the authenticated user's profile information.
     * Note: 'handle' is read-only and cannot be updated after registration.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        //dd($user);
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'string', 'regex:/^[\d\+\-\s\(\).]*$/', 'max:20'],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        // Handle image upload if provided
        if ($request->has('img')) {
            $oldName = User::find($user->id)->img;
            if ($oldName) {
                $path = public_path('img/Profile/' . $oldName);
                $this->deletePhoto($path);
            }
            $img = $this->uploadPhoto($request->img, 'Profile');
            $validated['img'] = $img;
        }


        $user->update($validated);

        return $this->dataResponse(
            new ProfileResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete user's profile image if exists
        $oldName = User::find($user->id)->img;
        if ($oldName) {
            $path = public_path('img/Profile/' . $oldName);
            $this->deletePhoto($path);
        }

        // Revoke all tokens for the user
        $user->tokens()->delete();

        // Delete the user
        $user->delete();

        return $this->successResponse('Account deleted successfully', 200);
    }

    /**
     * Get farms owned by or accessible to the authenticated user.
     * Returns data structured for table presentation.
     */
    public function myFarms(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get farms owned by user
        $ownedFarms = Farm::where('user_id', $user->id)
            ->with(['user', 'plants', 'users' => function ($query) {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'users.role');
            }])
            ->get();

        // Get farms where user has access via the pivot table
        $accessibleFarms = $user->assignedFarms()
            ->with(['user', 'plants', 'users' => function ($query) {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'users.role');
            }])
            ->get();

        // Merge and remove duplicates
        $allFarms = $ownedFarms->merge($accessibleFarms)->unique('id')->values();

        // Format farms for table presentation
        $farms = $allFarms->map(function ($farm) {
            return [
                'id' => $farm->id,
                'name' => $farm->name,
                'location' => $farm->location,
                'area' => $farm->area,
                'soil_type' => $farm->soil_type,
                'plants_count' => $farm->plants()->count(),
                'owner' => [
                    'id' => $farm->user->id,
                    'name' => $farm->user->first_name . ' ' . $farm->user->last_name,
                ],
                'assigned_engineers' => $farm->users->map(function ($engineer) {
                    return [
                        'id' => $engineer->id,
                        'name' => $engineer->first_name . ' ' . $engineer->last_name,
                        'role' => $engineer->pivot->role ?? $engineer->role,
                    ];
                }),
            ];
        });

        return $this->dataResponse(
            ['farms' => $farms],
            'Farms retrieved successfully'
        );
    }
}
