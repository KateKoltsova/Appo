<?php

namespace App\Services;

use DateTime;
use Exception;

class ScheduleService
{

    static function scheduleValidation($schedules, $userId, $addingSchedule = null)
    {
        if (is_null($addingSchedule)) {

            return $schedules->every(function ($schedule, $key) use ($userId, $schedules) {
                if (!self::isAvailable($schedule, $userId)) {
                    return false;
                }
                $otherSchedules = $schedules->filter(function ($item, $otherKey) use ($key) {
                    return $otherKey !== $key;
                });

                return $otherSchedules->every(function ($otherSchedule) use ($userId, $schedule) {
                    return
                        self::isAvailable($otherSchedule, $userId) &&
                        self::isValidDateTime($schedule, $otherSchedule);
                });
            });
        } else {

            if (!self::isAvailable($addingSchedule, $userId)) {
                return false;
            }

            return $schedules->every(function ($schedule) use ($userId, $addingSchedule) {
                if (self::isAvailable($schedule, $userId)) {
                    return self::isValidDateTime($addingSchedule, $schedule);
                }

                return true;
            });
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

    static function isValidDateTime($schedule, $otherSchedule)
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
