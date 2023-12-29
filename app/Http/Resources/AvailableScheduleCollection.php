<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AvailableScheduleCollection extends ResourceCollection
{
    public $collects = AvailableScheduleResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filteredData = collect(parent::toArray($request))->filter(function ($value) {
            return !empty($value);
        })->all();
        return $filteredData;
    }
}
