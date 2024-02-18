<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        return response()->json($this->galleryService->getList($user));
    }

    public function show(string $user, string $gallery)
    {
        try {
            $response = $this->galleryService->getById($user, $gallery);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function store(Request $request, string $user)
    {
        try {
            $images = $request->file('images');
            $response = $this->galleryService->addPhoto($user, $images);

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function destroy(string $user, string $gallery)
    {
        try {
            $this->galleryService->delete($user, $gallery);

            return response()->json(['message' => 'Gallery photo successfully deleted']);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
