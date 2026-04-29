<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowFarmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'location'   => $this->location,
            'area'       => $this->area,
            'soil_type'  => $this->soil_type,
            'img'        => url('/img/Farm/'.$this->img),
            'user_id'    => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // هندلة الـ Plants من غير ريسورس خارجي
            'plants' => $this->whenLoaded('plants', function () {
                return $this->plants->map(function ($plant) {
                    return [
                        'id'            => $plant->id,
                        'name'          => $plant->name,
                        'health_status' => $plant->health_status,
                        'growth_stage'  => $plant->growth_stage,
                        'plans'         => $plant->plans, // هيرجع array عادي زي ما هي
                    ];
                });
            }),

            // هندلة الـ Plans (لو موجودة مباشرة في المزرعة)
            'plans' => $this->whenLoaded('plans'),

            // هندلة الـ Users بالطلبات اللي حددتها (id, first, last, handle)
            'users' => $this->whenLoaded('users', function () {
                return $this->users->map(function ($user) {
                    return [
                        'id'         => $user->id,
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'handle'     => $user->handle,
                        // لو محتاج الـ pivot (الدور في المزرعة)
                        'role'       => $user->pivot ? $user->pivot->role : null, 
                    ];
                });
            }),
        ];
    }
}
