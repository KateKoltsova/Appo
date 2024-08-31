<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    // deny
    private const MESSAGE = 'You don\'t have permissions to make this action!';
    private const DENY_CODE = 403;

    private const INSTANCE = 'users.%s-';

    private function checkPermission($user, $permission)
    {
        return $user->hasPermissionTo($permission)
            ? Response::allow()
            : Response::denyAsNotFound(self::MESSAGE, self::DENY_CODE);
    }

    private function checkId(User $user, $permission, int $id = null)
    {
        $permission = sprintf(self::INSTANCE, $permission);

        if ($id === null) {
            return $this->checkPermission($user, $permission . 'all');
        } elseif ($user->id === $id) {
            return $this->checkPermission($user, $permission . 'own');
        } else {
            return $this->checkPermission($user, $permission . 'other');
        }
    }

    public function view(User $user, int $id = null)
    {
        return $this->checkId($user, 'read', $id);
    }

    public function create(User $user)
    {
        $permission = sprintf(self::INSTANCE, 'create');
        return $this->checkPermission($user, $permission . 'other');
    }

    public function update(User $user, int $id)
    {
        return $this->checkId($user, 'update', $id);
    }

    public function delete(User $user, int $id)
    {
        return $this->checkId($user, 'delete', $id);
    }
}
