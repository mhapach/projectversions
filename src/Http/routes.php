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


Route::group(
    [
        'middleware' => env('VCS_USE_AUTH_MIDDLEWARE') ? ['web', 'auth'] : ['web'],
        'namespace' => 'mhapach\ProjectVersions\Http\Controllers',
        'prefix' => 'project_versions'
    ],
    function () {

        Route::get('/login', 'LoginController@index')->name('project_versions.login');
        Route::post('/login', 'LoginController@login')->name('project_versions.login');
        Route::get('/', 'ProjectVersionsController@index')->name('project_versions.index');
        Route::get('/checkout/{revision}', 'ProjectVersionsController@checkout')->name('project_versions.checkout');
        Route::get('/update', 'ProjectVersionsController@update')->name('project_versions.update');
        Route::get('/info', 'ProjectVersionsController@info');
        Route::get('/version', 'ProjectVersionsController@version');
        Route::get('/new', 'ProjectVersionsController@new')->name('project_versions.new');

    }
);
