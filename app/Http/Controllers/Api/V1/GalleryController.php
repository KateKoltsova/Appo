<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoadGalleryRequest;
use App\Models\Gallery;
use App\Services\Api\GalleryService;
use Exception;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function __construct(
        private GalleryService $galleryService,
    )
    {
    }

    public function index(Request $request, string $user)
    {
        try {
            return response()->json($this->galleryService->getList($user));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }

    public function show(string $user, string $gallery)
    {
        try {
            $response = $this->galleryService->getById($user, $gallery);
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }

    public function store(LoadGalleryRequest $request, string $user)
    {
        try {
            $this->authorize('create', [Gallery::class, $user]);
            $params = $request->validated();
            $response = $this->galleryService->addPhoto($user, $params['images']);
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }

    public function destroy(string $user, string $gallery)
    {
        try {
            $this->authorize('delete', [Gallery::class, $user]);
            $this->galleryService->delete($user, $gallery);
            return response()->json(['message' => 'Gallery photo successfully deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }
}
