<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Requests\ProjectsRequest;
use App\Http\Controllers\Admin\ExcludeFunc;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Resources;
use App\Models\Projects;
use App\Models\Subservice;
use App\Models\Exclude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class CRUDProjectsController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;    

    public function setup()
    {
        CRUD::setModel(Projects::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/projects');
        CRUD::setEntityNameStrings('projects', 'All Projects');
        // $this->crud->paginate(50);
    }

    protected function setupListOperation()
    {
        CRUD::setListView('vendor.backpack.crud.list_details_pro');
        CRUD::setFromDb(); // set columns from db columns.
        $this->crud->addColumns([
            [
                'type' => 'activity',
                'name' => 'activity',
                'label' => 'scheduled sleep'
            ]
        ]);

        $this->crud->addButtonFromModelFunction('line', 'preview', 'previewButton', 'end');
        CRUD::addButtonFromView('top', 'refreshAll', 'refreshAllSwal', 'end');
        CRUD::addButtonFromView('line', 'sleep_now', 'sleepProSwal', 'end');
        CRUD::addButtonFromView('line', 'activate_now', 'activateProSwal', 'end');
        CRUD::addButtonFromView('line', 'refresh_now', 'refreshProSwal', 'end');
        CRUD::addButtonFromView('line', 'exclude', 'excludeProSwal', 'end');
        CRUD::column('isActive')->remove();
        CRUD::orderBy('name', 'asc');
        // CRUD::enableDetailsRow();
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProjectsRequest::class);
        CRUD::setFromDb(); // set fields from db columns.
        CRUD::field('isActive')->remove();
        CRUD::field('name')->remove();
        // CRUD::field('description')->remove();
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCreateDefaults()
    {
        $this->crud->allowAccess('create');

        $this->crud->operation('create', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
            $this->crud->setupDefaultSaveActions();
        });
    }


    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function active(Request $request)
    {
        Projects::where('id', $request->id)->update(['isActive' => $request->status]);
        $projectName = Projects::where('id', $request->id)->first()->name;

        if ($request->status == 0) {
            return response()->json([
                'success' => true,
                'message' => "Project $projectName has become inactive, and cannot be automatically sleep!"
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Project $projectName has become active, and will be automatically sleep!"
            ]);
        }
    }

    public function popupProject(Request $request)
    {
        // Find the project by name
        $project = Projects::where('name', $request->name)->first();
        if (!$project) {
            return response()->json([], 404);
        }

        $projectID = $project->id;

        // Get all subservices for this project
        $subservices = Subservice::where('projectID', $projectID)->get();

        // Update resources for all subservices

        Helper::updateResources($request->name);


        // Get all resources for these subservices
        $resources = [];
        foreach ($subservices as $subservice) {
            $subserviceResources = Resources::where('subserviceID', $subservice->id)->get();
            foreach ($subserviceResources as $resource) {
                $resources[] = [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'subservice' => $subservice->name, // Include the subservice name
                    'namespace' => $resource->namespace,
                    'type' => $resource->type,
                    'status' => $resource->status,
                    'replica' => $resource->replica,
                    'ready' => $resource->ready,
                    'active_left' => ($subservice->active_left == -1) ? "No Auto Sleep" : $subservice->active_left . " Hours"
                ];
            }
        }

        return $resources;
    }

    public function sleep($name)
    {
        $projectID = Projects::where('name', $name)->first()?->id;
        if (!$projectID) {
            Log::error("Project with name $name not found");
            return "Project with name $name not found";
        }

        $subservices = Subservice::where('projectID', $projectID)->get();
        if ($subservices->isEmpty()) {
            Log::error("There is no Subservices in project $name!");
            return "There is no Subservices in project $name!";
        }

        $response = "No response from Sleep/Active funtion!";

        Helper::updateResources($name);

        foreach ($subservices as $subservice) {
            $subservice->active_left = 0;
            $subservice->save();

            $currentResponse = Helper::sleepActive($subservice->id, "sleep");

            if ($currentResponse != "success") {
                Log::error("Error occurred on subservice $subservice->name: $currentResponse");
                $response = "$currentResponse on subservice $subservice->name"; // Capture the last error
                break;
            } else {
                $response = "success";
            }
        }

        return $response;
    }


    public function activate(Request $request)
    {
        $projectName = $request->name;
        $projectID = Projects::where('name', $projectName)->first()?->id;
        if (!$projectID) {
            Log::error("Project with name $projectName not found");
            return "Project with name $projectName not found";
        }

        $subservices = Subservice::where('projectID', $projectID)->get();
        if ($subservices->isEmpty()) {
            Log::error("There is no Subservices in project $projectName!");
            return "There is no Subservices in project $projectName!";
        }

        $response = "No response from Sleep/Active funtion!";

        foreach ($subservices as $key => $subservice) {
            $currentResponse = Helper::sleepActive($subservice->id, "active");

            if ($currentResponse != "success") {
                Log::error("Error occurred on subservice $subservice->name: $currentResponse");
                $response = "Error occurred: $currentResponse on subservice $subservice->name";
                break;
            } else {
                $response = "success";
            }
        }

        Subservice::where('projectID', $projectID)->update(['active_left' => $request->hour]);

        return $response;
    }

    public function exclude($name)
    {
        $projectID = Projects::where('name', $name)->first()?->id;
        ExcludeFunc::exclude($projectID, "Project");
        return "success";
    }

    public function refresh($name)
    {
        $projectID = Projects::where('name', $name)->first()?->id;
        if (!$projectID) {
            Log::error("Project with name $name not found");
            return "Project with name $name not found";
        }

        Subservice::where('projectID', $projectID)->delete();

        $command = 'kubectl get deployments,statefulset --all-namespaces --selector=project="' . $name . '" -o=jsonpath=\'{range .items[*]}{.metadata.labels.subservice}{"\n"}{end}\' | uniq | awk \'{print length() ? $0 : "' . $name . '-no-subservice"}\'';
        $output = Helper::execs("$command", 'Kubectl', 'Get All Subservices from Project: ' . $name);
        $outputArray = explode("\n", trim($output));

        // Prepare batch insert data
        $subservicesToInsert = [];
        foreach ($outputArray as $line) {
            // Trim whitespace
            $line = trim($line);

            if (empty($line)) {
                Log::warning("Skipping empty line in output.");
                continue;
            }

            // Check if the subservice is in the Exclude table
            $isExcluded = Exclude::where('name', $line)->where('type', 'Subservice')->exists();

            if ($isExcluded) {
                Log::info("Skipping excluded subservice: $line");
            } else {
                $subservicesToInsert[] = [
                    'name' => $line,
                    'projectID' => $projectID,
                    'active_left' => -1,
                    'PIC' => ""
                ];
                Log::info("Inserting new subservice: $line");
            }
        }

        // Perform batch insert if there are records to insert
        if (!empty($subservicesToInsert)) {
            Log::info("Inserting " . count($subservicesToInsert) . " new subservices");
            Subservice::insert($subservicesToInsert);
        }

        Helper::updateResources($name);
        return "success";
    }

    public function refreshAll()
    {
        $response = Helper::refreshAll();
        return $response;
    }


    public function preview($projectId)
    {
        $resources = Resources::where('projectID', $projectId)->get();
        return view('vendor.backpack.crud.preview', compact('resources'));
    }
}
