<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

//use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $birthdate
 * @property string $email
 * @property string $phone_number
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBirthdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @property int $role_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read \App\Models\Role $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRoleId($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'firstname',
        'lastname',
        'birthdate',
        'email',
        'phone_number',
        'password',
        'image_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class, 'master_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'master_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'client_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }

    public function scopeWithSchedules($query, $date)
    {
        return $query
            ->with([
                'schedules' => function ($query) use ($date) {
                    $query
                        ->where('schedules.status', config('constants.db.status.available'))
                        ->where(function ($query) {
                            $query
                                ->where('schedules.blocked_until', '<', now()->setTimezone('Europe/Kiev'))
                                ->orWhereNull('schedules.blocked_until');
                        })
                        ->where('date_time', '>', now()->setTimezone('Europe/Kiev'))
                        ->when($date, function ($query) use ($date) {
                            $query->whereIn(DB::raw('DATE(date_time)'), $date);
                        })
                        ->select([
                            'id as schedule_id',
                            'master_id',
                            'date_time',
                            'status',
                        ]);
                },
            ]);
    }

    public function scopeWithPrices($query, $service, $category)
    {
        return $query
            ->with([
                'prices' => function ($query) use ($service, $category) {
                    $query
                        ->join('services', 'prices.service_id', '=', 'services.id')
                        ->when($service, function ($query) use ($service) {
                            $query->whereIn('service_id', $service);
                        })
                        ->when($category, function ($query) use ($category) {
                            $query->whereIn('category', $category);
                        })
                        ->select([
                            'prices.id as price_id',
                            'master_id',
                            'service_id',
                            'price',
                            'category',
                            'title',
                        ]);
                },
            ]);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class, 'master_id', 'id');
    }
}
