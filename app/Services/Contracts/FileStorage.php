<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface FileStorage
{
    public function upload(UploadedFile $file);

    public function delete(string $url);
}
