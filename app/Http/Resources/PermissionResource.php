<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissions = [];

        foreach ($this->resource as $item) {
            $item->name = explode('.', $item->name);
            $permissions[$item->name[0]][$item->name[1]] = $item->id;
        }

        return $permissions;
    }
}
