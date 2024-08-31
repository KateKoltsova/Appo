<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    // deny
    private const MESSAGE = 'You don\'t have permissions to make this action!';
    private const DENY_CODE = 403;

    private const INSTANCE = 'appointments.%s-';

    private function checkPermission($user, $permission)
    {
        return $user->hasPermissionTo($permission)
            ? Response::allow()
            : Response::denyAsNotFound(self::MESSAGE, self::DENY_CODE);
    }

    private function checkId(User $user, $id, $permission)
    {
        $permission = sprintf(self::INSTANCE, $permission);

        if ($user->id === $id) {
            return $this->checkPermission($user, $permission . 'own');
        } else {
            return $this->checkPermission($user, $permission . 'other');
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int $id)
    {
        return $this->checkId($user, $id, 'read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $id)
    {
        return $this->checkId($user, $id, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $id)
    {
        return $this->checkId($user, $id, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int $id)
    {
        return $this->checkId($user, $id, 'delete');
    }
}
