<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['namespace' => 'mhapach\ProjectVersions\Http\Controllers', 'prefix' => 'project_versions'], function () {

    Route::get('/', 'ProjectVersionsController@index');
    Route::get('/checkout/{revision}', 'ProjectVersionsController@checkout')->name('project_version.checkout');
    Route::get('/info', 'ProjectVersionsController@info');
    Route::get('/new', 'ProjectVersionsController@isNew')->name('project_version.new');

});
