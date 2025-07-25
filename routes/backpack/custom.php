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
    Route::get('project/refresh/{name}', 'CRUDProjectsController@hardRefresh');
    Route::get('project/sleep/{name}', 'CRUDProjectsController@sleep');
    Route::post('project/fixed_sleep','CRUDProjectsController@switchFixedSleep');
    Route::post('project/duration_sleep','CRUDProjectsController@switchDurationSleep');
    Route::get('project/refreshAll','CRUDProjectsController@refreshAll');
    Route::get('project/exclude/{name}','CRUDProjectsController@exclude');
    Route::post('project/popupProject', 'CRUDProjectsController@popup');

    Route::crud('/resources/{subservice}','CRUDResourcesController');
    Route::crud('resources', 'CRUDResourcesController');
    Route::post('resource/getDescribe', 'CRUDResourcesController@getPodDescribe');
    Route::post('resource/getLogs', 'CRUDResourcesController@getPodLogs');
    Route::post('resource/getPods', 'CRUDResourcesController@getPods');

    Route::crud('/subservice/{project}','CRUDSubserviceController');
    Route::get('subservice/activate/{name}', 'CRUDSubserviceController@activate');
    Route::get('subservice/refresh/{name}', 'CRUDSubserviceController@hardRefresh');
    Route::get('subservice/sleep/{name}', 'CRUDSubserviceController@sleep');
    Route::post('subservice/popupSubservice', 'CRUDSubserviceController@popup');
    Route::get('subservice/exclude/{name}','CRUDSubserviceController@exclude');

    Route::crud('subservice-all', 'SubserviceAllController');

    // User management - only for superadmins
    Route::group(['middleware' => 'superadmin'], function () {
        Route::crud('user', 'CRUDUserController');
    });

    Route::get('/healthz', function () {
        return response('ok', 200);
    });
    Route::crud('exclude', 'CRUDExcludeController');
    Route::get('application-lists', 'ApplicationListsController@applicationlists');
}); // this should be the absolute last line of this file