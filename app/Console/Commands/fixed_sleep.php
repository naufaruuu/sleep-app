<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\Projects;
use App\Models\Subservice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class fixed_sleep extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixed_sleep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Sleep All Fixed-Based Applications Every 1 Hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting fixed_sleep process');

        $sleepTime = env('FIXED_SLEEP_TIME', '23:00');
        $activateTime = env('FIXED_ACTIVATE_TIME', '08:00');
        list($sleepHour, $sleepMinute) = explode(':', $sleepTime);
        list($activateHour, $activateMinute) = explode(':', $activateTime);

        Log::info('Configured sleep time: ' . $sleepTime);
        Log::info('Configured activate time: ' . $activateTime);

        $projects = Projects::where('isFixed', 1)->get();
        $subservices = Subservice::all()->groupBy('projectID');

        Log::info('Found ' . $projects->count() . ' projects with fixed_sleep');

        // Exit early if no active projects found
        if ($projects->count() == 0) {
            Log::info('No active projects found. Exiting duration_sleep process.');
            return;
        }

        // Get current time in UTC+7
        $currentTime = now()->timezone('Asia/Bangkok'); 
        $currentHour = $currentTime->hour;
        $currentMinute = $currentTime->minute;

        foreach ($projects as $project) {
            if (isset($subservices[$project->id])) {
                Helper::updateResources($project->name);
                foreach ($subservices[$project->id] as $subservice) {

                    if ($currentHour == (int)$sleepHour && $currentMinute == (int)$sleepMinute) {

                        $subservice->active_left = 0;
                        $subservice->save();
                        $response = Helper::sleepActive($subservice->id, "sleep");
                        if ($response != "success") {
                            Log::error("$response on subservice $subservice->name");
                        }
                        Log::info("Sleeping $subservice->name");
                    }

                    else if ($currentHour == (int)$activateHour && $currentMinute == (int)$activateMinute) {
                        $subservice->active_left = -1;
                        $subservice->save();
                        $response = Helper::sleepActive($subservice->id, "active");
                        if ($response != "success") {
                            Log::error("$response on subservice $subservice->name");
                        }
                        Log::info("Activating $subservice->name");
                    }

                    else {
                        Log::info("Not in sleep or activate time");
                    }
                }
            }
        }
        Log::info('Finish fixed_sleep process');
    }
}
