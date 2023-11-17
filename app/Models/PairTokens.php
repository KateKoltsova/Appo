<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PairTokens extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token_id',
        'refresh_token_id',
    ];
}
