<?php

namespace App\Services;

use App\Models\Schedule;
use App\Repositories\ScheduleRepository;
use DateTime;

class ScheduleService
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
    )
    {
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

    static function isValidScheduleComparedToOthers($schedule, $otherSchedules, $appointmentSchedules, $userId)
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
        $diffMinutes = $diff->i + $diff->h * 60 + $diff->d * 24 * 60;

        if ($diffMinutes === 0 ||
            $diffMinutes < config('constants.db.diff_between_services.minutes')) {
            return false;
        }

        return true;
    }
}
