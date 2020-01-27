<?php

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

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');

Route::resource('users', 'UserController');

Route::post('/importReports', 'HomeController@feedFromFile')->name('feed_from_file');
Route::get('/test', 'ReportController@getComparativeReport');
Route::get('/test2', 'ReportController@getTest2');


//Route::get('getkbms', function () {
//    foreach (App\Client::select('insurance_class')->groupBy('insurance_class')->get() as $kbm) {
//        echo $kbm->insurance_class . "\n";
//    }
//});
