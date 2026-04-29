<?php

namespace App\Http\Controllers;

use App\Http\Resources\FarmResource;
use App\Http\Resources\ShowFarmResource;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\media;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    use ApiTrait, AuthorizesRequests,media;

    /**
     * Display a listing of farms accessible to the user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get farms owned by user OR farms they have access to
        $farms = Farm::where('user_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['plants', 'plans'])
            ->get();

        return $this->dataResponse(
            FarmResource::collection($farms),
            'Farms retrieved successfully'
        );
     }

    /**
     * Store a newly created farm in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Farm::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'area' => ['required', 'numeric', 'min:0.01'],
            'soil_type' => ['required', 'string', 'max:255'],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        
        // Handle image upload if provided
        if ($request->has('img')) {
            $img = $this->uploadPhoto($request->img, 'Farm');
            $validated['img'] = $img; 
        }

        $farm = Farm::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'area' => $validated['area'],
            'soil_type' => $validated['soil_type'],
            'img' => $validated['img'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return $this->dataResponse(
            new FarmResource($farm),
            'Farm created successfully',
            201
        );
    }

    /**
     * Display the specified farm.
     */
    public function show(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('view', $farm);

        $farm->load(['plants' => function ($query) {
            $query->with('plans');
        }, 'plans', 'users']);
        $farm->load(['plants.plans', 'plans', 'users']);
        return $this->dataResponse(
            new ShowFarmResource($farm),
            'Farm retrieved successfully'
        );
    }

    /**
     * Update the specified farm in storage.
     */
    public function update(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('update', $farm);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'area' => ['sometimes', 'numeric', 'min:0.01'],
            'soil_type' => ['sometimes', 'string', 'max:255'],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $farm->update($validated);

        return $this->dataResponse(
             new FarmResource($farm),
            'Farm updated successfully'
        );
    }

    /**
     * Remove the specified farm from storage.
     */
    public function destroy(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('delete', $farm);

        $farm->delete();

        return $this->successResponse('Farm deleted successfully');
    }

    /**
     * Grant access to a farm for a specific user.
     *
     * Farm Owner can add anyone.
     * Engineer with 'editor' access can add ONLY their supervised farmers.
     *
     * When an Engineer adds a Farmer, automatically set the Farmer's engineer_id.
     */
    public function grantAccess(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('grantAccess', $farm);

        $validated = $request->validate([
            'handle' => ['required', 'string', 'exists:users,handle'],
            'role' => ['required', 'in:editor,viewer'],
        ]);

        $userToAdd = User::where('handle', $validated['handle'])->firstOrFail();

        // Additional validation for engineers with editor access
        if ($request->user()->id !== $farm->user_id && $request->user()->role === 'engineer') {
            // Engineer can only add farmers they supervise or who have no engineer yet
            if ($userToAdd->engineer_id !== null && $userToAdd->engineer_id !== $request->user()->id) {
                return $this->errorResponse(
                    ['handle' => ['You can only add farmers under your supervision']],
                    'Unauthorized',
                    403
                );
            }
        }

        // Auto-assign engineer_id if engineer is adding a farmer with no engineer
        if ($request->user()->role === 'engineer' && $userToAdd->engineer_id === null && $userToAdd->role === 'farmer') {
            $userToAdd->update(['engineer_id' => $request->user()->id]);
        }

        // Attach user to farm with specified role
        $farm->users()->syncWithoutDetaching([
            $userToAdd->id => ['role' => $validated['role']],
        ]);

        return $this->dataResponse(compact('farm'), 'Access granted successfully');
    }

    /**
     * Revoke access to a farm for a specific user.
     */
    public function revokeAccess(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('grantAccess', $farm);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $farm->users()->detach($validated['user_id']);

        return $this->successResponse('Access revoked successfully');
    }

    /**
     * Get users with access to this farm.
     */
    public function getAccessList(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('view', $farm);

        $users = $farm->users()
            ->get(['users.handle', 'users.first_name', 'users.last_name', 'users.email', 'farm_user.role']);

        return $this->dataResponse(
            compact('users'),
            'Access list retrieved successfully'
        );
    }
}
