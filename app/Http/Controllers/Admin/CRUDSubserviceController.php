<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Requests\SubserviceRequest;
use App\Http\Controllers\Admin\ExcludeFunc as Exclude;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Models\Resources;
use App\Models\Projects;
use App\Models\Subservice;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class CRUDSubserviceController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CRUDSubserviceController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $project = \Route::current()->parameter('project');
        CRUD::setModel(\App\Models\Subservice::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . "/subservice/$project");
        CRUD::setEntityNameStrings('subservice', $project . ' Subservices');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'limit' => 999999999,
        ]);
        CRUD::addColumn([
            'name' => 'active_left',
            'label' => 'Active Left',
            'type' => 'text',
            'limit' => 999999999,
        ]);
        // CRUD::enableDetailsRow()
        CRUD::setListView('vendor.backpack.crud.list_details_sub');
        CRUD::setFromDb(); // set columns from db columns.
        CRUD::column('projectID')->remove();

        CRUD::addButtonFromView('top', 'activateAll', 'activateProfromSub', 'end');
        CRUD::addButtonFromView('top', 'sleepAll', 'sleepProfromSub', 'end');
        CRUD::addButtonFromView('top', 'excludeAll', 'excludeProfromSub', 'end');
        CRUD::addButtonFromView('top', 'refreshAll', 'refreshProfromSub', 'end');


        CRUD::addButtonFromView('line', 'sleep_now', 'sleepSubSwal', 'beginning');
        CRUD::addButtonFromView('line', 'activate_now', 'activateSubSwal', 'beginning');
        CRUD::addButtonFromView('line', 'refresh_now', 'refreshSubSwal', 'beginning');
        CRUD::addButtonFromView('line', 'exclude', 'excludeSubSwal', 'end');
        $project = \Route::current()->parameter('project');
        $id_project = Projects::where('name', $project)->first()?->id;
        $this->crud->addClause('where', 'projectID', $id_project);
        $this->crud->addButtonFromModelFunction('line', 'preview', 'previewButton', 'beginning');
        CRUD::orderBy('name', 'asc');
        // CRUD::enableDetailsRow();lia

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SubserviceRequest::class);
        CRUD::setFromDb(); // set fields from db columns.
        CRUD::field('projectID')->remove();
        // CRUD::field('active_left')->remove();

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $start = (int) request()->input('start');
        $length = (int) request()->input('length');
        $search = request()->input('search');

        // if a search term was present
        if ($search && $search['value'] ?? false) {
            // filter the results accordingly
            $this->crud->applySearchTerm($search['value']);
        }
        // start the results according to the datatables pagination
        if ($start) {
            $this->crud->skip($start);
        }
        // limit the number of results according to the datatables pagination
        if ($length) {
            $this->crud->take($length);
        }
        // overwrite any order set in the setup() method with the datatables order
        $this->crud->applyDatatableOrder();

        $entries = $this->crud->getEntries();

        foreach ($entries as $entry) {
            // dd($entry);
            if ($entry->active_left == -1) {
                $entry->active_left = "Auto Sleep is Disabled";
            } else if ($entry->active_left == 0) {
                $entry->active_left = "0 Hours";
            } else {
                $entry->active_left = $entry->active_left . " Hours";
            }
        }

        // if show entry count is disabled we use the "simplePagination" technique to move between pages.
        if ($this->crud->getOperationSetting('showEntryCount')) {
            $totalEntryCount = (int) (request()->get('totalEntryCount') ?: $this->crud->getTotalQueryCount());
            $filteredEntryCount = $this->crud->getFilteredQueryCount() ?? $totalEntryCount;
        } else {
            $totalEntryCount = $length;
            $filteredEntryCount = $entries->count() < $length ? 0 : $length + $start + 1;
        }

        // store the totalEntryCount in CrudPanel so that multiple blade files can access it
        $this->crud->setOperationSetting('totalEntryCount', $totalEntryCount);

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalEntryCount, $filteredEntryCount, $start);
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
        $request->merge(['projectID' => \Route::current()->parameter('id_project')]);
        $item = $this->crud->create($request->all());
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function sleep($name)
    {
        $subserviceID = Subservice::where('name', $name)->first()?->id;
        $subserviceName =  Subservice::where('id', $subserviceID)->first()->name;
        $projectName = Subservice::where('id', $subserviceID)->first()?->project?->name;

        Helper::updateResources($projectName);

        Subservice::find($subserviceID)->update(['active_left' => 0]);
        $response = Helper::sleepActive($subserviceID, "sleep");
        if ($response != "success") {
            Log::error("Error occured on subservice $subserviceName");
            return "$response on subservice $subserviceName";
        }
        return "success";
    }

    public function activate(Request $request)
    {
        $subserviceID = Subservice::where('name', $request->name)->first()?->id;
        $name =  Subservice::where('id', $subserviceID)->first()->name;

        $response = Helper::sleepActive($subserviceID, "active");
        Subservice::where('id', $subserviceID)->update(['active_left' => $request->hour]);

        if ($response != "success") {
            Log::error("Error occured on subservice $name");
            return "$response on subservice $name";
        }
        return "success";
    }

    public function popupSubservice(Request $request)
    {
        $subservice = Subservice::where('name', $request->name)->first();
        $subserviceID = $subservice->id;
        $projectName = Subservice::where('id', $subserviceID)->first()?->project?->name;
        Helper::updateResources($projectName);
        $resources = Resources::where('subserviceID', $subserviceID)->get();

        // Add active_left to each resource in the collection
        $resources = $resources->map(function ($resource) use ($subservice) {
            // Format active_left value
            if ($subservice->active_left == -1) {
                $resource->active_left = "No Auto Sleep";
            } else {
                $hours = $subservice->active_left;
                $resource->active_left = $hours . " " . ($hours == 1 ? "Hour" : "Hours");
            }

            return $resource;
        });

        return $resources->toArray();
    }

    public function refresh($name)
    {
        $subserviceID = Subservice::where('name', $name)->first()?->id;
        $projectName = Subservice::where('id', $subserviceID)->first()?->project?->name;
        Resources::where('subserviceID', $subserviceID)->delete();
        Helper::updateResources($projectName);
        return "success";
    }

    public function exclude($name)
    {
        $subserviceID = Subservice::where('name', $name)->first()?->id;
        Exclude::exclude($subserviceID, "Subservice");
        return "success";
    }


    public function preview($subserviceId)
    {
        $resources = Resources::where('subserviceID', $subserviceId)->get();
        return view('vendor.backpack.crud.preview', compact('resources'));
    }
}
