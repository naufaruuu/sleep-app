<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subservice;
use App\Models\Projects;
use App\Http\Requests\SubserviceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class SubserviceAllController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Subservice::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/subservice-all');
        CRUD::setEntityNameStrings('subservice', 'all subservices');
        $this->crud->query = $this->crud->query->with('project');
    }

    protected function setupListOperation()
    {
        // Add Subservice column first
        CRUD::addColumn([
            'name' => 'name',
            'label' => 'Subservice',
            'type' => 'text',
            'limit' => 999999999,
        ]);

        // Add Project column second
        CRUD::addColumn([
            'name' => 'project.name',
            'label' => 'Project',
            'type' => 'relationship',
            'entity' => 'project',
            'attribute' => 'name',
            'model' => Projects::class,
        ]);

        CRUD::addColumn([
            'name' => 'active_left',
            'label' => 'Active Left',
            'type' => 'text',
            'limit' => 999999999,
        ]);

        CRUD::setListView('vendor.backpack.crud.list_details_sub');
        CRUD::setFromDb();
        CRUD::column('projectID')->remove();

        // Add buttons
        CRUD::addButtonFromView('top', 'refreshAll', 'refreshAllSwal', 'end');
        CRUD::addButtonFromView('line', 'sleep_now', 'sleepSubSwal', 'beginning');
        CRUD::addButtonFromView('line', 'activate_now', 'activateSubSwal', 'beginning');
        CRUD::addButtonFromView('line', 'refresh_now', 'refreshSubSwal', 'beginning');
        CRUD::addButtonFromView('line', 'exclude', 'excludeSubSwal', 'end');
        $this->crud->addButtonFromModelFunction('line', 'preview', 'previewButton', 'beginning');

        // Keep the same sorting (by project, then subservice)
        $this->crud->query->join('projects', 'subservices.projectID', '=', 'projects.id')
            ->orderBy('projects.name', 'asc')
            ->orderBy('subservices.name', 'asc')
            ->select('subservices.*');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(SubserviceRequest::class);
        CRUD::setFromDb();
        CRUD::field('projectID')->remove();
        CRUD::field('name')->remove();
        CRUD::field('active_left')->remove();
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $start = (int) request()->input('start');
        $length = (int) request()->input('length');
        $search = request()->input('search');

        if ($search && $search['value'] ?? false) {
            $this->crud->applySearchTerm($search['value']);
        }
        if ($start) {
            $this->crud->skip($start);
        }
        if ($length) {
            $this->crud->take($length);
        }
        $this->crud->applyDatatableOrder();

        $entries = $this->crud->getEntries();

        foreach ($entries as $entry) {
            if ($entry->active_left == -1) {
                $entry->active_left = "Auto Sleep is Disabled";
            } else if ($entry->active_left == 0) {
                $entry->active_left = "0 Hours";
            } else {
                $entry->active_left = $entry->active_left . " Hours";
            }
        }

        if ($this->crud->getOperationSetting('showEntryCount')) {
            $totalEntryCount = (int) (request()->get('totalEntryCount') ?: $this->crud->getTotalQueryCount());
            $filteredEntryCount = $this->crud->getFilteredQueryCount() ?? $totalEntryCount;
        } else {
            $totalEntryCount = $length;
            $filteredEntryCount = $entries->count() < $length ? 0 : $length + $start + 1;
        }

        $this->crud->setOperationSetting('totalEntryCount', $totalEntryCount);

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalEntryCount, $filteredEntryCount, $start);
    }
}
