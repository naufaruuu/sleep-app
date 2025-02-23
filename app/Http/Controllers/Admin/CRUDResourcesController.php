<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Requests\ResourcesRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Models\Projects;
use App\Models\Resources;
use App\Models\Subservice;
use Illuminate\Http\Request;


class CRUDResourcesController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $subservice = \Route::current()->parameter('subservice');
        CRUD::setModel(\App\Models\Resources::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . "/resources/$subservice");
        CRUD::setEntityNameStrings('resources', $subservice . ' resources');
    }

    protected function setupListOperation()
    {

        // First, remove the default name column if it exists
        CRUD::removeColumn('name');

        // Get subservice information once
        $subservice = \Route::current()->parameter('subservice');
        $subserviceModel = Subservice::where('name', $subservice)->first();
        $activeLeftValue = '-';

        if ($subserviceModel) {
            // Add "Hours" after the integer, but not for "Auto Sleep is Disabled"
            $activeLeftValue = $subserviceModel->active_left == -1 ?
                'Auto Sleep is Disabled' : $subserviceModel->active_left . ' Hours';
        }

        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'limit' => 999999999,
        ]);

        // Add the active_left column first
        CRUD::addColumn([
            'name' => 'active_left',
            'label' => 'Active Left',
            'type' => 'text',
            'value' => $activeLeftValue,
            'wrapper' => [
                'element' => 'span',
                'class' => $subserviceModel && $subserviceModel->active_left == -1 ?
                    'badge badge-secondary' : ''
            ],
            'orderByClause' => false, // Disable sorting if needed
            'searchLogic' => false,   // Disable searching if needed
        ]);


        CRUD::setListView('vendor.backpack.crud.list_details_res');

        CRUD::setFromDb();
        // CRUD::column('name')->remove();
        CRUD::orderBy('name', 'asc');


        // Remove other unwanted columns
        CRUD::column('subserviceID')->remove();

        CRUD::addButtonFromView('top', 'activateAll', 'activateSubfromRes', 'end');
        CRUD::addButtonFromView('top', 'sleepAll', 'sleepSubfromRes', 'end');
        CRUD::addButtonFromView('top', 'excludeAll', 'excludeSubfromRes', 'end');
        CRUD::addButtonFromView('top', 'refreshAll', 'refreshSubfromRes', 'end');

        $subserviceId = $subserviceModel->id;

        $projectName = Subservice::where('id', $subserviceId)->first()?->project?->name;

        $this->crud->addClause('where', 'subserviceID', $subserviceId);
        // Helper::updateResources($projectName);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // update the row in the db
        $request->merge(['subserviceID' => \Route::current()->parameter('id_subservice')]);
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $request->all()
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }


    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ResourcesRequest::class);
        CRUD::setFromDb(); // set fields from db columns.
        CRUD::field('subserviceID')->remove();
        CRUD::field('namespace')->remove();
        CRUD::field('name')->remove();
        CRUD::field('type')->remove();
        CRUD::field('status')->remove();
        CRUD::field('health_status')->remove();
        CRUD::field('subserviceID')->remove();
        CRUD::field('ready')->remove();
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // insert item in the db
        $request->merge(['subserviceID' => \Route::current()->parameter('id_subservice')]);
        $item = $this->crud->create($request->all());
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function getDescribe(Request $request)
    {
        $resource = Resources::where('name', $request->name)->first();

        return Helper::execs(
            "kubectl describe $resource->type -n $resource->namespace $resource->name",
            'Kubectl',
            'Describe ' . $resource->name
        );
    }

    public function getLogs(Request $request)
    {
        $resource = Resources::where('name', $request->name)->first();
        $podName = $request->podName;
        $namespace = $resource->namespace;
        $tailLines = $request->tailLines ?? 20; // Default to 20 lines

        // Build logs command
        $logsCommand = "kubectl logs -n $namespace $podName";

        // If tailLines is -1, don't limit the logs (get all available logs)
        // Otherwise, add the --tail parameter with the specified number
        if ($tailLines != -1) {
            $logsCommand .= " --tail=$tailLines";
        }

        return Helper::execs(
            $logsCommand,
            'Kubectl',
            $tailLines == -1 ? "All logs for $podName" : "Logs for $podName (last $tailLines lines)"
        );
    }

    public function getPods(Request $request)
    {
        $resource = Resources::where('name', $request->name)->first();
        $namespace = $resource->namespace;
        $name = $resource->name;
        $type = $resource->type;

        // First, get the selector from the resource
        $selectorCommand = "kubectl get $type $name -n $namespace -o jsonpath='{.spec.selector.matchLabels}'";
        $selectorOutput = Helper::execs($selectorCommand, 'Kubectl', "Getting selector for $name");

        // Parse the selector from the output
        $selector = "";
        if ($selectorOutput && $selectorOutput !== "null") {
            try {
                // The output could be in JSON format, like {"app":"es-exporter"}
                $selectorData = json_decode($selectorOutput, true);

                if (is_array($selectorData)) {
                    $selectorParts = [];
                    foreach ($selectorData as $key => $value) {
                        $selectorParts[] = "$key=$value";
                    }
                    $selector = "--selector=" . implode(',', $selectorParts);
                } else {
                    // Fallback if not valid JSON
                    $selector = "--selector=app=$name";
                }
            } catch (\Exception $e) {
                // Fallback if any parsing error occurs
                $selector = "--selector=app=$name";
            }
        } else {
            // Fallback to app=$name if we couldn't get the selector
            $selector = "--selector=app=$name";
        }

        // Get pod list using the selector
        $podListCommand = "kubectl get pods -n $namespace $selector -o jsonpath='{.items[*].metadata.name}'";
        $podList = Helper::execs($podListCommand, 'Kubectl', "Getting pods for $name with selector: $selector");

        // Convert string of pod names to array
        $pods = [];
        if ($podList) {
            $pods = explode(' ', trim($podList));
        }

        return json_encode([
            'pods' => $pods,
            'resourceType' => $type,
            'resourceName' => $name,
            'defaultTailLines' => 20 // Include default value for UI
        ]);
    }

    public function sleep()
    {
        exec('echo "hello world"');
        return back();
    }

    public function activate()
    {
        exec('echo "hello world"');
        return back();
    }
}
