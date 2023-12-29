<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Schedule
 *
 * @property int $id
 * @property int $master_id
 * @property string $date
 * @property string $time
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Appointment|null $appointment
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\ScheduleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'date_time',
        'status',
        'blocked_until',
        'blocked_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'master_id', 'id');
    }

    public function master()
    {
        return $this->user();
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'schedule_id', 'id');
    }
}
