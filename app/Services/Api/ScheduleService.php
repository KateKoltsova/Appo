<?php

namespace App\Services\Api;

use App\Http\Resources\AvailableScheduleCollection;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Repositories\ScheduleRepository;
use DateTime;
use Exception;

class ScheduleService
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function getList($filters, string $userId)
    {
        $schedule = $this->scheduleRepository->getAll($filters, $userId);

        $scheduleCollection = ScheduleResource::collection($schedule);

        return ['data' => $scheduleCollection];
    }

    public function getAvailable($filters)
    {
        $availableSchedules = $this->scheduleRepository->getAvailableWithFilters($filters);

        $availableScheduleCollection = new AvailableScheduleCollection($availableSchedules);

        return ['data' => $availableScheduleCollection];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create($params, string $userId)
    {
        $schedule = Schedule::create(
            [
                'master_id' => $userId,
                'date_time' => $params['date_time'],
                'status' => config('constants.db.status.available'),
            ],
        );

        if ($schedule) {
            return $this->getById($userId, $schedule->id);
        } else {
            throw new Exception('Bad request', 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getById(string $userId, string $scheduleId)
    {
        $schedule = $this->scheduleRepository->getByIdWithAppointments($userId, $scheduleId);

        if (!empty($schedule)) {
            $scheduleResource = ScheduleResource::make($schedule);

            return ['data' => $scheduleResource];
        } else {
            throw new Exception('Schedule not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($params, string $userId, string $scheduleId)
    {
        $schedule = Schedule::where('id', $scheduleId)->where('master_id', $userId)->first();

        if (!empty($schedule)) {
            $schedule->update($params);

            return $this->getById($userId, $scheduleId);
        } else {
            throw new Exception('Schedule not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $userId, string $scheduleId)
    {
        $schedule = Schedule::where('id', $scheduleId)->where('master_id', $userId)->first();

        if (!empty($schedule)) {
            if ($schedule->status == config('constants.db.status.unavailable')) {
                $schedule->appointment()->first()->delete();
            }

            $schedule->delete();

            return true;

        } else {
            throw new Exception('Schedule not found', 404);
        }
    }

    public function cancelAppointment(string $userId, string $scheduleId)
    {
        $schedule = Schedule::where('id', $scheduleId)->where('master_id', $userId)->first();

        if (!empty($schedule)) {
            if ($schedule->status == config('constants.db.status.unavailable')) {

                $schedule->appointment()->first()->delete();

                $schedule->update(['status' => config('constants.db.status.available')]);

                return true;
            } else {
                throw new Exception('This schedule is available', 400);
            }

        } else {
            throw new Exception('Schedule not found', 404);
        }

    }

    public function check(string $userId, Schedule $addingSchedule = null)
    {
        $schedulesInCart = $this->scheduleRepository->getSchedulesInCartByUserId($userId);

        $appointmentSchedules = $this->scheduleRepository->getAppointmentSchedulesByUserId($userId);

        $validSchedule = $this->scheduleValidation($schedulesInCart, $userId, $appointmentSchedules, $addingSchedule);

        if ($validSchedule) {
            return $schedulesInCart;
        }
    }

    static function scheduleValidation($schedules, $userId, $appointmentSchedules, $addingSchedule = null)
    {
        if (is_null($addingSchedule)) {

            return $schedules->every(function ($schedule, $key) use ($userId, $schedules, $appointmentSchedules) {
                $otherSchedules = $schedules->filter(function ($item, $otherKey) use ($key) {
                    return $otherKey !== $key;
                });

                return self::isValidScheduleComparedToOthers($schedule, $otherSchedules, $appointmentSchedules, $userId);
            });
        } else {

            return self::isValidScheduleComparedToOthers($addingSchedule, $schedules, $appointmentSchedules, $userId);
        }
    }

    static function isValidScheduleComparedToOthers($schedule, $otherSchedules, $appointmentSchedules, $userId): bool
    {
        if (!self::isAvailable($schedule, $userId)) {
            return false;
        }

        $otherSchedulesValidation = $otherSchedules->every(function ($otherSchedule) use ($userId, $schedule) {
            if (self::isAvailable($otherSchedule, $userId)) {
                return self::isValidDateTime($schedule, $otherSchedule);
            }

            return true;
        });

        $appointmentSchedulesValidation = $appointmentSchedules->every(function ($appointmentSchedules) use ($userId, $schedule) {
            return self::isValidDateTime($schedule, $appointmentSchedules);
        });

        return $otherSchedulesValidation && $appointmentSchedulesValidation;
    }

    static function isAvailable($schedule, $userId): bool
    {
        if ($schedule['status'] === config('constants.db.status.unavailable') ||
            (!is_null($schedule['blocked_by'])
                && $schedule['blocked_by'] != $userId
                && !is_null($schedule['blocked_until'])
                && ($schedule['blocked_until'] >= now()->setTimezone('Europe/Kiev'))) ||
            ($schedule['date_time'] < now()->setTimezone('Europe/Kiev'))) {
            return false;
        } else {
            return true;
        }
    }

    static function isValidDateTime($schedule, $otherSchedule): bool
    {
        $timeItem = new DateTime($schedule['date_time']);
        $timeOtherItem = new DateTime($otherSchedule['date_time']);
        $diff = $timeOtherItem->diff($timeItem);
        $diffMinutes = $diff->i + $diff->h * 60 + $diff->days * 24 * 60;

        if ($diffMinutes === 0 ||
            $diffMinutes < config('constants.db.diff_between_services.minutes')) {
            return false;
        }

        return true;
    }
}
