<?php

namespace App\Http\Resources;

use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{
    public $collects = CartResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $collection = parent::toArray($request);
        $result = collect($collection)->map(function ($item, $key) use ($collection) {
            if (!isset($item['message'])) {
                foreach ($collection as $otherKey => $otherItem) {
                    if ($otherKey == $key || isset($otherItem['message'])) {
                        continue;
                    }

                    $isValid = ScheduleService::isValidDateTime($item, $otherItem);

                    if (!$isValid) {
                        $item['message'] = 'You selected the same date time schedule';
                    }
                }
            }

            return $item;
        });

        return $result->all();
    }
}
