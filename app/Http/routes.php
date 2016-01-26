<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// REST API Routes
Route::group(['middleware' => 'cors'], function()
{   
    // vendors
    Route::resource('api/vendor', 'Api\VendorController');

    // transactions
    Route::resource('api/vendor.transaction', 'Api\TransactionController');

    // reports
    Route::resource('api/report', 'Api\ReportController');

});

// Misc API routes
Route::post('/api/generate/report', 'Api\ReportController@generate');
Route::post('/api/send/report', 'Api\ReportController@send');
Route::get('/api/sync/google', 'AdminController@syncGoogleContacts');
Route::get('/api/disconnect/google', 'AdminController@disconnectGoogle');
Route::get('/api/key', 'AdminController@apiKey');

// Auth controllers
Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController'
]);

// Main routes
Route::get('/', 'Auth\AuthController@getLogin');
Route::get('/admin', 'AdminController@index');
Route::get('/admin/download/vendors', 'AdminController@downloadVendors');
Route::get('/admin/download/{reportId}/{vendorId?}', 'AdminController@downloadReport');
Route::get('/oauth/google', 'AdminController@connectGoogle');

// Vendors (eventually?)
Route::get('/profile', 'ProfileController@index');
Route::get('/home', 'ProfileController@index');