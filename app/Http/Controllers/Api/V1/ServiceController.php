<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceCreateRequest;
use App\Http\Requests\ServiceUpdateRequest;
use App\Models\Service;
use App\Services\ImageService;
use Exception;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private $path = 'images/services';

    public function __construct(
        private ImageService $imageService,)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::get(['id', 'title', 'description', 'image_url', 'category']);
        $categories = $services->pluck('category')->unique();
        return response()->json(['data' => [
            'categories' => array_values($categories->toArray()),
            'services' => $services->toArray()
        ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceCreateRequest $request)
    {
        try {
            $params = $request->validated();

            if (isset($params['image'])) {
                $this->imageService->path = $this->path;

                $imageUrl = $this->imageService->upload($params['image']);

                unset($params['image']);
            }

            $service = Service::create($params);

            if (!$service) {
                throw new Exception('Error creating service', 422);
            }

            $service->image_url = $imageUrl['data']['url'] ?? null;
            $service->save();

            return response()->json(['message' => 'Service successfully created'], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceUpdateRequest $request, Service $service)
    {
        try {
            $params = $request->validated();

            if (isset($params['image'])) {
                $this->imageService->path = 'images/services';

                if (!is_null($service->image_url)) {
                    $this->imageService->delete($service->image_url);
                }

                $imageUrl = $this->imageService->upload($params['image']);
                $service->image_url = $imageUrl['data']['url'];
                $service->save();

                unset($params['image']);
            }

            $service->update($params);

            if (!$service) {
                throw new Exception('Error creating service', 422);
            }

            return response()->json(['message' => 'Service successfully updated'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $service = Service::where('id', $id)->first();

            if (!is_null($service->image_url)) {
                $this->imageService->path = $this->path;
                $this->imageService->delete($service->image_url);
            }

            $service->delete();

            return response()->json(['message' => 'Service successfully deleted'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 400);
        }
    }
}
