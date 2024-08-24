<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MakeNewMasterSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-new-master-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy master schedules from last month to the next';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now();
        $firstDayOfLastMonth = $currentDate->copy()->startOfMonth();
        $lastDayOfLastMonth = $currentDate->copy()->endOfMonth();

        $schedulesLastMonth = Schedule::where('date_time', '>=', $firstDayOfLastMonth)
            ->where('date_time', '<=', $lastDayOfLastMonth)
            ->orderBy('date_time')
            ->get();

        foreach ($schedulesLastMonth as $schedule) {
            $scheduleDateOfLastMonth = Carbon::parse($schedule->date_time);
            $weekday = $scheduleDateOfLastMonth->dayOfWeek;

            $daysUntilNow = $scheduleDateOfLastMonth->day - 1;
            $fullWeeks = intdiv($daysUntilNow, 7);

            $firstDayOfNextMonth = $scheduleDateOfLastMonth->copy()->addMonth()->startOfMonth();

            if ($firstDayOfNextMonth->dayOfWeek == $weekday) {
                $firstWeekday = $firstDayOfNextMonth;
            } else {
                $firstWeekday = $firstDayOfNextMonth->copy()->next($weekday);
            }

            $newDate = $firstWeekday->copy()->addWeeks($fullWeeks);

            $newDate = $newDate->setTime($scheduleDateOfLastMonth->hour, $scheduleDateOfLastMonth->minute, $scheduleDateOfLastMonth->second);
            try {
                Schedule::firstOrCreate([
                    'master_id' => $schedule->master_id,
                    'date_time' => $newDate,
                    'status' => config('constants.db.status.available')
                ]);
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
