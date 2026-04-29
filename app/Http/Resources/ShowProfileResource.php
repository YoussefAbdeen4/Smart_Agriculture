<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'first_name'             => $this->first_name,
            'last_name'              => $this->last_name,
            'email'                  => $this->email,
            'handle'                 => $this->handle,
            'email_verified_at'      => $this->email_verified_at,
            'phone'                  => $this->phone,
            'img'                    => url('/img/Profile/'.$this->img),
            'role'                   => $this->role,
            'google_id'              => $this->google_id,
            'registration_completed' => $this->registration_completed,

            // عرض الـ Staff بتحديد الحقول المطلوبة بس
            'staff' => $this->whenLoaded('staff', function () {
                return $this->staff->map(function ($member) {
                    return [
                        'id'          => $member->id,
                        'first_name'  => $member->first_name,
                        'last_name'   => $member->last_name,
                        'handle'      => $member->handle,
                        'role'        => $member->role,
                        'engineer_id' => $member->engineer_id,
                    ];
                });
            }),

            // عرض الـ Farms اللي تبع اليوزر
            'farms' => $this->whenLoaded('farms', function () {
                return $this->farms->map(function ($farm) {
                    return [
                        'id'        => $farm->id,
                        'name'      => $farm->name,
                        'location'  => $farm->location,
                        'area'      => $farm->area,
                        'soil_type' => $farm->soil_type,
                        'img'       => $farm->img ?? null,
                    ];
                });
            }),
        ];
    }
}
