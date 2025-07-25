<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ExcludeRequest;
use App\Models\Exclude;
use App\Models\Projects;
use App\Models\Subservice;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CRUDExcludeController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CRUDExcludeController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Exclude::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/exclude');
        CRUD::setEntityNameStrings('exclude', 'excludes');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.
        CRUD::orderBy('name', 'asc');


        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ExcludeRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
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
        $list = explode(',', $request['name']);
        // dd($list);   
        foreach ($list as $exclude) {
            $item = $this->crud->create(['name' => trim($exclude), 'type' => $request['type']]);
            $this->data['entry'] = $this->crud->entry = $item;
            if ($request['type'] == "Project") {
                $object = Projects::where('name', $item->name)->first();
            } else if ($request['type'] == "Subservice") {
                $object = Subservice::where('name', $item->name)->first();
            }

            if ($object) {
                $object->delete();
            }
        }


        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}

class ExcludeFunc
{
    static function exclude($id, $type)
    {
        if ($type == "Project") {
            $object = Projects::where('id', $id)->first();
        } else if ($type == "Subservice") {
            $object = Subservice::where('id', $id)->first();
        }
        if ($object) {
            Exclude::insert([
                'name' => $object->name,
                'type' => $type
            ]);
            $object->delete();
        }
    }
}
