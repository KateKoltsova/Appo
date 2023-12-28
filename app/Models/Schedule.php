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
        'date',
        'time',
        'status',
        'blocked_until',
        'blocked_by'
    ];

    protected $timezone = 'Europe/Kiev';

    protected $dates = [
        'date',
        'time',
    ];

    private $enableDateTimeAttribute = true;

    protected $trackedFields = ['date', 'time'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->disableDateTimeAttribute();
            $model->setDateTimeAttributes($model->date, $model->time);
            $model->enableDateTimeAttribute();
        });

        static::updating(function ($model) {
            $changedFields = array_keys($model->getDirty());

            if (count(array_intersect($changedFields, $model->trackedFields)) > 0) {
                $model->disableDateTimeAttribute();
                $model->setDateTimeAttributes($model->date, $model->time);
                $model->enableDateTimeAttribute();
            }
        });
    }

    public function setDateTimeAttributes($date, $time)
    {
        $dateTimeString = $date . ' ' . $time;
        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString, $this->timezone);

        $this->attributes['date'] = $dateTime->setTimezone('UTC')->format('Y-m-d');
        $this->attributes['time'] = $dateTime->setTimezone('UTC')->format('H:i:s');
    }

    public function getDateTimeAttribute()
    {
        if ($this->enableDateTimeAttribute) {
            $combinedDateTime = $this->attributes['date'] . ' ' . $this->attributes['time'];
            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $combinedDateTime, 'UTC');

            return [
                'date' => $dateTime->setTimezone($this->timezone)->format('Y-m-d'),
                'time' => $dateTime->setTimezone($this->timezone)->format('H:i:s')
            ];
        } else {
            return [
                'date' => $this->attributes['date'],
                'time' => $this->attributes['time']
            ];
        }
    }

    private function disableDateTimeAttribute()
    {
        $this->enableDateTimeAttribute = false;
    }

    private function enableDateTimeAttribute()
    {
        $this->enableDateTimeAttribute = true;
    }

    public function getDateAttribute()
    {
        return $this->getDateTimeAttribute()['date'];
    }

    public function getTimeAttribute()
    {
        return $this->getDateTimeAttribute()['time'];
    }

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
