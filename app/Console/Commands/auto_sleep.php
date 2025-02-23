<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\Projects;
use App\Models\Subservice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class auto_sleep extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_sleep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Sleep All Pods Every 1 Hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting auto_sleep process');
        $projects = Projects::where('isActive', 1)->get();
        Log::info('Found ' . $projects->count() . ' active projects');

        // Exit early if no active projects found
        if ($projects->count() == 0) {
            Log::info('No active projects found. Exiting auto_sleep process.');
            return;
        }

        $subservices = Subservice::all()->groupBy('projectID');
        Log::info('Fetched all subservices and grouped by projectID');

        $subservicesToUpdate = [];
        $subservicesToSleep = [];

        foreach ($projects as $project) {
            Log::info("Processing project: {$project->name} (ID: {$project->id})");
            Helper::updateResources($project->name);

            if (isset($subservices[$project->id])) {
                Log::info("Found " . count($subservices[$project->id]) . " subservices for project {$project->name}");

                foreach ($subservices[$project->id] as $subservice) {
                    $active = $subservice->active_left;
                    Log::info("Processing subservice: {$subservice->name}, active_left: {$active}");

                    if ($active > 0 && $active !== -1) {
                        // Store original ID for sleep operations if needed
                        if ($active - 1 == 0) {
                            $subservicesToSleep[] = $subservice->id;
                            Log::info("Marked subservice {$subservice->name} (ID: {$subservice->id}) for sleep");
                        }

                        // Prepare data for batch update - include all required fields
                        $subservicesToUpdate[] = [
                            'id' => $subservice->id,
                            'name' => $subservice->name, // Include required name field
                            'active_left' => $active - 1,
                            'updated_at' => now(),
                            'created_at' => $subservice->created_at, // Preserve original creation date
                            // Add any other required fields that don't have default values
                        ];
                        Log::info("Added subservice {$subservice->name} to update batch, new active_left: " . ($active - 1));
                    }
                }
            } else {
                Log::info("No subservices found for project {$project->name}");
            }
        }

        // Batch update all modified subservices using upsert
        if (!empty($subservicesToUpdate)) {
            Log::info("Performing batch update for " . count($subservicesToUpdate) . " subservices");
            try {
                Subservice::upsert(
                    $subservicesToUpdate,
                    ['id'], // Unique key(s)
                    ['active_left', 'updated_at', 'name'] // Include name in columns to update
                );
                Log::info("Batch update completed successfully");
            } catch (\Exception $e) {
                Log::error("Failed to batch update subservices: " . $e->getMessage());
            }
        } else {
            Log::info("No subservices to update");
        }

        // Process sleep operations after all database updates
        if (!empty($subservicesToSleep)) {
            Log::info("Processing sleep operations for " . count($subservicesToSleep) . " subservices");
            foreach ($subservicesToSleep as $subserviceId) {
                Log::info("Putting subservice ID: {$subserviceId} to sleep");
                $response = Helper::sleepActive($subserviceId, "sleep");
                if ($response != "success") {
                    Log::error("$response on subservice id: $subserviceId");
                } else {
                    Log::info("Successfully put subservice ID: {$subserviceId} to sleep");
                }
            }
        } else {
            Log::info("No subservices to put to sleep");
        }

        Log::info('Completed auto_sleep process');
    }
}
