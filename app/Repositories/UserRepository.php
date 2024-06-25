<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(protected User $model)
    {
    }

    public function getAll($filters)
    {
        return $this->model->select([
            'users.*',
            'roles.role',
        ])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->when($filters['role'], function ($query) use ($filters) {
                $query->whereIn('users.role_id', $filters['role_id']);
            })
            ->get();
    }

    public function getById(string $userId)
    {
        return $this->model->select([
            'users.*',
            'roles.role',
        ])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $userId)
            ->first();
    }

}
