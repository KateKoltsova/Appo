<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Appointment
 *
 * @property int $id
 * @property int $schedule_id
 * @property int $service_id
 * @property int $client_id
 * @property int $sum
 * @property string $payment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Schedule $schedule
 * @property-read \App\Models\Service $service
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\AppointmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment wherePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Appointment extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
