<?php

namespace App\Services;

use App\Services\Contracts\FileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService implements FileStorage
{
    private string $path = 'images/avatars';

    public function upload(UploadedFile $image)
    {
        if (!$image) {
            throw new \Exception('You didn\'t select image');
        }

        $path = $image->store($this->path, 's3');

        $url = Storage::disk('s3')->url($path);

        return ['data' => ['url' => $url]];
    }

    public function delete(string $url)
    {
        $fileName = basename($url);

        Storage::disk('s3')->delete($this->path . '/' . $fileName);

        return true;
    }
}
