<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiTrait;
use App\Models\Farm;
use App\Models\Plant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    use ApiTrait, AuthorizesRequests;

    /**
     * Display a listing of plants for a farm.
     */
    public function index(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('view', $farm);

        $plants = $farm->plants()->with('plans')->get();

        return $this->dataResponse(
            compact('plants'),
            'Plants retrieved successfully'
        );
    }

    /**
     * Store a newly created plant in storage.
     */
    public function store(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('update', $farm);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'health_status' => ['required', 'string', 'max:255'],
            'growth_stage' => ['required', 'string', 'max:255'],
        ]);

        $plant = $farm->plants()->create([
            'name' => $validated['name'],
            'health_status' => $validated['health_status'],
            'growth_stage' => $validated['growth_stage'],
        ]);

        $plant->load('plans');

        return $this->dataResponse(
            compact('plant'),
            'Plant created successfully',
            201
        );
    }

    /**
     * Display the specified plant.
     */
    public function show(Request $request, Farm $farm, Plant $plant)
    {
        abort(404, 'page not found');
    }

    /**
     * Update the specified plant in storage.
     */
    public function update(Request $request, Farm $farm, Plant $plant): JsonResponse
    {
        $this->authorize('update', $farm);

        // Ensure plant belongs to the farm
        if ($plant->farm_id !== $farm->id) {
            return $this->errorResponse(
                ['plant' => ['Plant not found in this farm.']],
                'Not Found',
                404
            );
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'health_status' => ['sometimes', 'string', 'max:255'],
            'growth_stage' => ['sometimes', 'string', 'max:255'],
        ]);

        $plant->update($validated);
        $plant->load('plans');

        return $this->dataResponse(
            compact('plant'),
            'Plant updated successfully'
        );
    }

    /**
     * Remove the specified plant from storage.
     */
    public function destroy(Request $request, Farm $farm, Plant $plant): JsonResponse
    {
        $this->authorize('update', $farm);

        // Ensure plant belongs to the farm
        if ($plant->farm_id !== $farm->id) {
            return $this->errorResponse(
                ['plant' => ['Plant not found in this farm.']],
                'Not Found',
                404
            );
        }

        $plant->delete();

        return $this->successResponse('Plant deleted successfully');
    }
}
