<?php

namespace App\Services;

use App\Services\Contracts\BlockModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BlockService implements BlockModel
{
    public function block(int $minutes, string $user_id, Model $blockedModel): bool
    {
        $blockedUntil = now()->setTimezone('Europe/Kiev')->addMinutes($minutes);
        $params = [
            'blocked_until' => $blockedUntil,
            'blocked_by' => $user_id
        ];

        try {
            $blocked = $blockedModel->fill($params);
            $blocked->save();
            return true;
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Model not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to block the model'], 400);
        }
    }

    public function unblock(Model $blockedModel): bool
    {
        $params = [
            'blocked_until' => null,
            'blocked_by' => null
        ];
        try {
            $unblocked = $blockedModel->fill($params);
            $unblocked->save();
            return true;
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Model not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to unblock the model'], 400);
        }
    }
}
