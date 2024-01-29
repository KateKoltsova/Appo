<?php

namespace App\Services;

use DateTime;
use Exception;

class ScheduleService
{

    static function scheduleValidation($schedules, $userId, $appointmentSchedules, $addingSchedule = null)
    {
        if (is_null($addingSchedule)) {

            return $schedules->every(function ($schedule, $key) use ($userId, $schedules, $appointmentSchedules) {
                if (!self::isAvailable($schedule, $userId)) {
                    return false;
                }
                $otherSchedules = $schedules->filter(function ($item, $otherKey) use ($key) {
                    return $otherKey !== $key;
                });

                $otherSchedulesValidation = $otherSchedules->every(function ($otherSchedule) use ($userId, $schedule) {
                    return
                        self::isAvailable($otherSchedule, $userId) &&
                        self::isValidDateTime($schedule, $otherSchedule);
                });

                $appointmentSchedulesValidation = $appointmentSchedules->every(function ($appointmentSchedule) use ($userId, $schedule) {
                    return self::isValidDateTime($schedule, $appointmentSchedule);
                });

                return $otherSchedulesValidation && $appointmentSchedulesValidation;
            });
        } else {

            if (!self::isAvailable($addingSchedule, $userId)) {
                return false;
            }

            $schedulesValidation = $schedules->every(function ($schedule) use ($userId, $addingSchedule) {
                if (self::isAvailable($schedule, $userId)) {
                    return self::isValidDateTime($addingSchedule, $schedule);
                }

                return true;
            });

            $appointmentSchedulesValidation = $appointmentSchedules->every(function ($appointmentSchedules) use ($userId, $addingSchedule) {
                return self::isValidDateTime($addingSchedule, $appointmentSchedules);
            });

            return $schedulesValidation && $appointmentSchedulesValidation;
        }

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
