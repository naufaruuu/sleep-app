<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Hash;

class CRUDUserController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');
        
        // Only superadmin can access this
        if (backpack_user()->role !== 'superadmin') {
            abort(403, 'Unauthorized access');
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('email');
        CRUD::column('role');
        CRUD::column('created_at');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::field('name');
        CRUD::field('email');
        CRUD::field([
            'name' => 'password',
            'type' => 'password',
            'label' => 'Password',
        ]);
        CRUD::field([
            'name' => 'password_confirmation',
            'type' => 'password',
            'label' => 'Password Confirmation',
        ]);
        CRUD::field([
            'name' => 'role',
            'type' => 'select_from_array',
            'options' => ['admin' => 'Admin', 'superadmin' => 'Super Admin'],
            'default' => 'admin',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        
        // Make password optional in update
        CRUD::field('password')->attributes(['required' => false]);
        CRUD::field('password_confirmation')->attributes(['required' => false]);
    }

    // Store operation with password hashing
    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation();

        return $this->traitStore();
    }

    // Update operation with password hashing
    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation();

        return $this->traitUpdate();
    }

    // Handle password hashing
    protected function handlePasswordInput($request)
    {
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        return $request;
    }
}