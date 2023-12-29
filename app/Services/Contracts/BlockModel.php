<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BlockModel
{
    public function block(int $minutes, string $user_id, Model $blockedModel): bool;

    public function unblock(Model $blockedModel): bool;
}
