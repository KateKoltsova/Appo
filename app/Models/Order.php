<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'payment_status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'order_id', 'id');
    }
}
