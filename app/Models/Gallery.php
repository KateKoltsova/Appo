<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'image_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'master_id', 'id');
    }

    public function master()
    {
        return $this->user();
    }
}
