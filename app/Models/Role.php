<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeClient($query)
    {
        return $this->getRole($query);
    }

    public function scopeMaster($query)
    {
        return $this->getRole($query, 'master');
    }

    public function getRole($query, $role = 'client')
    {
        return $query->where('role', '=', config('constants.db.roles.' . $role));
    }
}
