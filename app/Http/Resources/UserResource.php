<?php

namespace App\Http\Resources;

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
        if (!empty($this->image_url)) {
            $user = [
                'id' => $this->id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'birthdate' => $this->birthdate,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'role' => $this->role,
                'image_url' => $this->image_url
            ];
        } else {
            $user = [
                'id' => $this->id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'birthdate' => $this->birthdate,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'role' => $this->role
            ];
        }
        return $user;
    }
}
