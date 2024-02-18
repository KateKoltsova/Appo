<?php

namespace App\Services\Api;

use App\Models\Gallery;
use App\Models\User;
use App\Services\ImageService;
use Exception;
use Illuminate\Support\Facades\DB;

class GalleryService
{
    private $path = 'images/galleries';

    public function pathAddition(string $path)
    {
        $this->path .= '/' . $path;
    }

    public function __construct(
        private ImageService $imageService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function getList(string $userId)
    {
        $galleries = Gallery::select([
            'id',
            'image_url',
        ])
            ->where('master_id', $userId)
            ->get();

        return ['data' => $galleries];
    }

    /**
     * Display the specified resource.
     */
    public function getById(string $userId, string $galleryId)
    {
        $gallery = Gallery::select([
            'id',
            'image_url',
        ])
            ->where('master_id', $userId)
            ->where('id', $galleryId)
            ->first();

        if (!empty($gallery)) {
            return ['data' => $gallery];
        } else {
            throw new Exception('Gallery photo not found', 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addPhoto(string $userId, $images)
    {
        try {
            $user = User::findOrFail($userId);

            $this->pathAddition($userId);

            $this->imageService->path = $this->path;

            DB::beginTransaction();

            if (isset($images)) {
                foreach ($images as $image) {

                    $imageUrl = $this->imageService->upload($image);

                    $params['image_url'] = $imageUrl['data']['url'];

                    $user->galleries()->create($params);
                }
            }

            DB::commit();

            return $this->getList($userId);

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $userId, string $galleryId)
    {
        $gallery = Gallery::where('id', $galleryId)->where('master_id', $userId)->first();

        if (!empty($gallery)) {

            $this->pathAddition($userId);

            $this->imageService->path = $this->path;

            $this->imageService->delete($gallery->image_url);

            $gallery->delete();

            return true;

        } else {
            throw new Exception('Gallery photo not found', 404);
        }
    }
}
