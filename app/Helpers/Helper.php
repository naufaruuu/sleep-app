<?php

namespace App\Helpers;

use App\Models\Exclude;
use App\Models\Projects;
use App\Models\Resources;
use App\Models\Subservice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class Helper

{
    static function refreshAll()
    {
        Log::info("Starting refreshAll to update projects and subservices");

        try {
            // Execute kubectl command to fetch all projects and subservices

            // Output Reference: Project - Subservice
            // infra exporter
            // infra infra-no-subservice
            // kestra kestra-no-subservice
            // temporal-cluster gfs-pricethroughgoogle
            // temporal-cluster wf-abandonedcart

            $command = <<<CMD
            kubectl get deployments,statefulsets --all-namespaces -o=jsonpath='{range .items[*]}{.metadata.labels.project}{" "} {.metadata.labels.subservice}{"\\n"}{end}' | \
            awk 'NF && \$1 {project=\$1; subservice=(NF>1 ? \$2 : project"-no-subservice"); if (!seen[project","subservice]++) print project, subservice}' | \
            sort
            CMD;     

            $outputString = Helper::execs($command, 'Kubectl', 'Get all projects & subservices lists');
            $output = explode("\n", trim($outputString));
            $output = array_filter($output, 'strlen');
            Log::info("Fetched " . count($output) . " project-subservice pairs.");

            // Get exclusions
            $excludes = Exclude::all()->toArray();
            $excludeProjectNames = array_column(array_filter($excludes, fn($exclude) => $exclude['type'] === 'Project'), 'name');
            $excludeSubserviceNames = array_column(array_filter($excludes, fn($exclude) => $exclude['type'] === 'Subservice'), 'name');

            // Get existing projects and subservices
            $existingProjects = Projects::all()->keyBy('name')->toArray();
            $existingSubservices = Subservice::all()->keyBy('name')->toArray();

            // Prepare data structures for batch operations
            $projectsToInsert = [];
            $subservicesToInsert = [];
            $projectSubservicePairs = [];
            
            // First pass: collect unique projects
            $uniqueProjects = [];
            foreach ($output as $line) {
                $fields = explode(" ", $line);
                if (count($fields) < 2 || empty($fields[0]) || empty($fields[1])) {
                    Log::warning("Skipping invalid line: $line - Insufficient or empty fields");
                    continue;
                }
                
                $project = trim($fields[0]);
                $subservice = trim($fields[1]);
                
                if (empty($project) || empty($subservice)) {
                    Log::warning("Skipping line with empty values after trimming: $line");
                    continue;
                }
                
                // Skip excluded projects
                if (in_array($project, $excludeProjectNames)) {
                    Log::info("Skipping excluded project: $project");
                    continue;
                }
                
                // Skip excluded subservices
                if (in_array($subservice, $excludeSubserviceNames)) {
                    Log::info("Skipping excluded subservice: $subservice");
                    continue;
                }

                // Store valid project-subservice pairs
                $projectSubservicePairs[] = [
                    'project' => $project,
                    'subservice' => $subservice
                ];
                
                // Collect unique projects that don't already exist in DB
                if (!isset($existingProjects[$project]) && !isset($uniqueProjects[$project])) {
                    $uniqueProjects[$project] = [
                        'name' => $project,
                        'description' => "",
                        'isFixed' => 0,
                        'isDuration' => 0,
                    ];
                    Log::info("Inserting new project: $project");
                }
            }
            
            // Batch insert new projects
            if (!empty($uniqueProjects)) {
                Log::info("Inserting " . count($uniqueProjects) . " new projects");
                Projects::insert(array_values($uniqueProjects));
                
                // Refresh projects to get newly assigned IDs
                $existingProjects = Projects::all()->keyBy('name')->toArray();
            }
            
            // Second pass: collect subservices with project IDs
            foreach ($projectSubservicePairs as $pair) {
                $project = $pair['project'];
                $subservice = $pair['subservice'];
                
                // Skip if project doesn't exist (shouldn't happen at this point)
                if (!isset($existingProjects[$project])) {
                    Log::warning("Project not found: $project");
                    continue;
                }
                
                $projectId = $existingProjects[$project]['id'];
                
                // Collect new subservices
                if (!isset($existingSubservices[$subservice])) {
                    $subservicesToInsert[] = [
                        'name' => $subservice,
                        'projectID' => $projectId,
                        'active_left' => -1,
                        'PIC' => ""
                    ];
                    Log::info("Inserting new subservice: $subservice");
                }
            }
            
            // Batch insert new subservices
            if (!empty($subservicesToInsert)) {
                Log::info("Inserting " . count($subservicesToInsert) . " new subservices");
                Subservice::insert($subservicesToInsert);
            }
            
            // Update resources for all projects
            foreach ($existingProjects as $project) {
                Helper::updateResources($project['name']);
            }
            
            Log::info("refreshAll completed successfully with batch processing.");
            return "success";
        } catch (\Exception $e) {
            Log::error("Error in refreshAll: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    static function updateResources($projectName)
    {
        Log::info("Updating resources for project: $projectName");
    
        // Fetch existing resources grouped by a unique key
        $existingResources = Resources::all()->groupBy(function ($item) {
            return $item['name'] . '|' . $item['namespace'] . '|' . $item['type'];
        })->toArray();
    
        try {
            $output = [];
            Log::info("Fetching All resources for project: $projectName");
    
            // Fetch excluded subservices
            $excludes = Exclude::all()->toArray();
            $excludeSubserviceNames = array_column(array_filter($excludes, fn($exclude) => $exclude['type'] === 'Subservice'), 'name');
    
            // Command to fetch resources using kubectl
            $command = <<<CMD
            kubectl get deployment,statefulset --all-namespaces -l project=$projectName -o json | jq -r '
            .items[] | 
            "\(.kind) \(.metadata.namespace) \(.metadata.name) \(.status.readyReplicas // 0)/\(.spec.replicas // 0) \(.metadata.labels.subservice // (.metadata.labels.project + "-no-subservice"))"
            '
            CMD;

            // output reference
            // Deployment team-behemoth kestra-executor 0/0 kestra-no-subservice
            // StatefulSet team-behemoth kestra-postgresql 0/0 kestra-no-subservice
            // Deployment kube-logging apz-exporter 1/1 infra-no-subservice
            // Deployment kube-logging es-exporter 1/1 exporter
    
            $outputString = Helper::execs($command, 'Kubectl', 'Get Project: ' . $projectName . ' resources');
            $output = array_filter(explode("\n", trim($outputString)), 'strlen');
    
            if (!empty($output)) {
                $resourcesToUpdate = [];
                $resourcesToInsert = [];
    
                foreach ($output as $line) {
                    // Data mapping
                    $data = explode(" ", $line);
                    $type = $data[0];
                    $namespace = $data[1];
                    $subject = $data[2];
                    $pod_info = explode("/", $data[3]);
                    $pod_run = $pod_info[0];
                    $replica_count = $pod_info[1];
                    $subservieName = $data[4];
                    $ready = "$pod_run/$replica_count";
    
                    if (in_array($subservieName, $excludeSubserviceNames)) {
                        Log::info("Skipping excluded subservice: $subservieName");
                        continue;
                    }
    
                    // Define pod run status
                    $status = ($pod_run == 0 && $replica_count == 0) ? "Sleeping" : "Running";
    
                    // Define pod health status
                    $health_status = ($pod_run == 0 && $replica_count == 0) ? "Inactive" : (($pod_run == $replica_count) ? "Healthy" : "Unhealthy");
    
                    $resourceKey = $subject . '|' . $namespace . '|' . $type;
                    if ($replica_count == 0) {
                        $replica_count = 1;
                    }
    
                    if (isset($existingResources[$resourceKey])) {
                        $resource = $existingResources[$resourceKey][0];
                        $resourceId = $resource['id'];
    
                        Log::info("Updating resource: $subject in namespace: $namespace (Type: $type)");
    
                        $resourcesToUpdate[] = [
                            'id' => $resourceId,
                            'name' => $subject, // Include the name field
                            'namespace' => $namespace,
                            'type' => $type,
                            'status' => $status,
                            'health_status' => $health_status,
                            'ready' => $ready,
                            'replica' => $replica_count,
                        ];
                    } else {
                        Log::info("Inserting new resource: $subject in namespace: $namespace (Type: $type)");
                        $subserviceId = Subservice::where('name', $subservieName)->first()?->id;
    
                        $resourcesToInsert[] = [
                            'name' => $subject,
                            'namespace' => $namespace,
                            'type' => $type,
                            'status' => $status,
                            'replica' => $replica_count,
                            'health_status' => $health_status,
                            'subserviceID' => $subserviceId,
                            'ready' => $ready,
                        ];
                    }
                }
    
                // Perform batch updates
                if (!empty($resourcesToUpdate)) {
                    Log::info("Updating Batchly ". count($resourcesToUpdate) ." Resources from Project: $projectName");
                    Resources::upsert($resourcesToUpdate, ['id'], ['name', 'namespace', 'type', 'status', 'health_status', 'ready', 'replica']);
                }
    
                // Perform batch inserts
                if (!empty($resourcesToInsert)) {
                    Log::info("Inserting Batchly ". count($resourcesToInsert) ." Resources from Project: $projectName");
                    Resources::insert($resourcesToInsert);
                }
            } else {
                Log::warning("No resources found for project: $projectName.");
            }
        } catch (\Exception $e) {
            Log::error("Error updating resources for project: $projectName - " . $e->getMessage());
        }
    }

    
    static function execs($command, $mode, $action)
    {
        $result = shell_exec($command);
        Log::info('execs_run', [
            'mode' => $mode,
            'action' => $action,
            'command' => $command,
            'result' => $result
        ]);
        return $result;
    }

    static function sleepActive($subserviceId, $mode)
    {
        
        Log::info("Starting sleepActive for subservice ID: $subserviceId with mode: $mode");

        $resources = Resources::where('subserviceID', $subserviceId)->get();

        $projectName = Subservice::where('id', $subserviceId)->first()?->project?->name;

        if ($resources->isEmpty()) {
            Log::error("No resources found for subservice ID: $subserviceId");
            return "No resources found for subservice ID: $subserviceId"; 
        }

        foreach ($resources as $resource) {
            try {
                if ($mode == "sleep") {
                    Log::info("Putting resource {$resource->name} to sleep (namespace: {$resource->namespace}, type: {$resource->type})");
                    Resources::where('id', $resource->id)->update([
                        'replica' => $resource->replica
                    ]);

                    $command = "kubectl scale {$resource->type} {$resource->name} -n {$resource->namespace} --replicas=0";

                    Helper::execs($command, "Kubectl", "Set " . $resource->name . " 0");

                } else if ($mode == "active") {
                    Log::info("Activating resource {$resource->name} (namespace: {$resource->namespace}, type: {$resource->type})");

                    $command = "kubectl scale {$resource->type} {$resource->name} -n {$resource->namespace} --replicas={$resource->replica}";

                    Helper::execs($command, "Kubectl", "Set " . $resource->name . " " . $resource->replica);

                }
            } catch (\Exception $e) {
                Log::error("Error scaling resource {$resource->name}: " . $e->getMessage());
                return "Error on resource {$resource->name}: " . $e->getMessage();
            }
        }

        return "success"; 
    }

}
