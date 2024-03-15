<?php

namespace App\Http\Resources\UserResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'city' => $this->city,
            'description' => $this->description,
            'position' => $this->position,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'date_of_birth' => $this->date_of_birth,
            'photo' => $this->photo,
            'two_factor_secret' => $this->two_factor_secret,
            'two_factor_recovery_codes' => $this->two_factor_recovery_codes,
            'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
