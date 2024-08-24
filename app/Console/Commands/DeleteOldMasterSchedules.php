<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldMasterSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-master-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete master schedules when it older than 2 month from 1 day current month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now();
        $firstDayOfCurrentMonth = $currentDate->startOfMonth();
        $twoMonthsAgo = $firstDayOfCurrentMonth->subMonths(2);

        $schedules = Schedule::where('date_time', '<', $twoMonthsAgo)->orderBy('date_time')->get();
        $schedules->delete();
    }
}
