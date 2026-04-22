<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiTrait;
use App\Models\Farm;
use App\Models\Plan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    use ApiTrait, AuthorizesRequests;

    /**
     * Display a listing of plans for a farm.
     */
    public function index(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('view', $farm);

        $plans = $farm->plans()->with('plants')->get();

        return $this->dataResponse(
            compact('plans'),
            'Plans retrieved successfully'
        );
    }

    /**
     * Store a newly created plan in storage.
     */
    public function store(Request $request, Farm $farm): JsonResponse
    {
        $this->authorize('update', $farm);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'irrigation_date' => ['required', 'string'],
            'fertilization_date' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
            'plant_ids' => ['nullable', 'array'],
            'plant_ids.*' => ['integer', 'exists:plants,id'],
        ]);

        $plan = $farm->plans()->create([
            'name' => $validated['name'],
            'irrigation_date' => $validated['irrigation_date'],
            'fertilization_date' => $validated['fertilization_date'],
            'note' => $validated['note'] ?? null,
        ]);

        // Attach plants to the plan using sync
        if (!empty($validated['plant_ids'])) {
            $plan->plants()->sync($validated['plant_ids']);
        }

        $plan->load('plants');

        return $this->dataResponse(
            compact('plan'),
            'Plan created successfully',
            201
        );
    }

    /**
     * Display the specified plan.
     */
    public function show(Request $request, Farm $farm, Plan $plan): JsonResponse
    {
        $this->authorize('view', $farm);

        // Ensure plan belongs to the farm
        if ($plan->farm_id !== $farm->id) {
            return $this->errorResponse(
                ['plan' => ['Plan not found in this farm.']],
                'Not Found',
                404
            );
        }

        $plan->load('plants');

        return $this->dataResponse(
            compact('plan'),
            'Plan retrieved successfully'
        );
    }

    /**
     * Update the specified plan in storage.
     */
    public function update(Request $request, Farm $farm, Plan $plan): JsonResponse
    {
        $this->authorize('update', $farm);

        // Ensure plan belongs to the farm
        if ($plan->farm_id !== $farm->id) {
            return $this->errorResponse(
                ['plan' => ['Plan not found in this farm.']],
                'Not Found',
                404
            );
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'irrigation_date' => ['sometimes', 'date'],
            'fertilization_date' => ['sometimes', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'plant_ids' => ['nullable', 'array'],
            'plant_ids.*' => ['integer', 'exists:plants,id'],
        ]);

        // Update plan details (exclude plant_ids from the update)
        $planData = array_filter($validated, fn($key) => $key !== 'plant_ids', ARRAY_FILTER_USE_KEY);
        $plan->update($planData);

        // Sync plants to the plan
        if (array_key_exists('plant_ids', $validated)) {
            $plan->plants()->sync($validated['plant_ids'] ?? []);
        }

        $plan->load('plants');

        return $this->dataResponse(
            compact('plan'),
            'Plan updated successfully'
        );
    }

    /**
     * Remove the specified plan from storage.
     */
    public function destroy(Request $request, Farm $farm, Plan $plan): JsonResponse
    {
        $this->authorize('update', $farm);

        // Ensure plan belongs to the farm
        if ($plan->farm_id !== $farm->id) {
            return $this->errorResponse(
                ['plan' => ['Plan not found in this farm.']],
                'Not Found',
                404
            );
        }

        $plan->delete();

        return $this->successResponse('Plan deleted successfully');
    }
}
