<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    // deny
    private const MESSAGE = 'You don\'t have permissions to make this action!';
    private const DENY_CODE = 403;

    private const INSTANCE = 'services.%s-other';

    private function checkPermission($user, $permission)
    {
        $permission = sprintf(self::INSTANCE, $permission);
        return $user->hasPermissionTo($permission)
            ? Response::allow()
            : Response::denyAsNotFound(self::MESSAGE, self::DENY_CODE);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user)
    {
        return $this->checkPermission($user, 'read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $this->checkPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user)
    {
        return $this->checkPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user)
    {
        return $this->checkPermission($user, 'delete');
    }
}
