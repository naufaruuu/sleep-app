<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('projects', 'CRUDProjectsController');
    Route::get('preview', 'CRUDProjectsController@preview');
    Route::get('project/activate/{name}', 'CRUDProjectsController@activate');
    Route::get('project/refresh/{name}', 'CRUDProjectsController@refresh');
    Route::get('project/sleep/{name}', 'CRUDProjectsController@sleep');
    Route::post('project/active','CRUDProjectsController@active');
    Route::get('project/refreshAll','CRUDProjectsController@refreshAll');
    Route::get('project/exclude/{name}','CRUDProjectsController@exclude');
    Route::post('project/popupProject', 'CRUDProjectsController@popupProject');


    Route::crud('/resources/{subservice}','CRUDResourcesController');
    Route::crud('resources', 'CRUDResourcesController');
    // Route::post('resource/getDetailsA', 'CRUDResourcesController@getDetailsA');

    Route::post('resource/getDescribe', 'CRUDResourcesController@getDescribe');
    Route::post('resource/getLogs', 'CRUDResourcesController@getLogs');
    Route::post('resource/getPods', 'CRUDResourcesController@getPods');




    Route::crud('/subservice/{project}','CRUDSubserviceController');
    Route::get('subservice/activate/{name}', 'CRUDSubserviceController@activate');
    Route::get('subservice/refresh/{name}', 'CRUDSubserviceController@refresh');
    Route::get('subservice/sleep/{name}', 'CRUDSubserviceController@sleep');
    Route::post('subservice/popupSubservice', 'CRUDSubserviceController@popupSubservice');
    Route::get('subservice/exclude/{name}','CRUDSubserviceController@exclude');
    // Route::get('subservice-all','CRUDSubserviceController@showAll');

    Route::crud('subservice-all', 'SubserviceAllController');


    

    Route::get('/healthz', function () {
        return response('ok', 200);
    });
    Route::crud('exclude', 'CRUDExcludeController');
    Route::get('application-lists', 'ApplicationListsController@applicationlists');
}); // this should be the absolute last line of this file