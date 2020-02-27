<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->post('/getReport', 'ReportConctroller@getReport');

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::group([
    'middleware' => 'auth:api'

], function () {
    Route::post('get_general_report', 'ReportController@getReport');
    Route::post('/get_users', 'ReportController@getUsers');
    Route::post('/add_user', 'ReportController@addUser');
    Route::post('/change_status', 'ReportController@changeUserStatus');
    Route::post('/delete_user', 'ReportController@deleteUser');
    Route::post('/change_user_data', 'ReportController@changeUserData');

    Route::post('get_age_chart_data', 'ReportController@getAgeCategoryChartData');
    Route::post('get_ages_data', 'ReportController@getTest');
    Route::post('get_saved_data', 'ReportController@getSavedData');

    Route::post('get_pivot_report', 'ReportController@getPivotReport');
    Route::post('get_comparative_report', 'ReportController@getComparativeReport');
    Route::post('get_chart_report', 'ReportController@getChartReport');

    Route::post('get_regions_report_data', 'ReportController@getRegionsReport');

    Route::post('get_sale_centers_report_data', 'ReportController@getSaleCentersReport');

    Route::get('get_regions', function () {
        return response()->json(\App\Region::select('id', 'name')->orderBy('name')->get());
    });

    Route::get('get_filtersets', function (Request $request) {
        return response()->json(App\FilterSet::where('name', 'default')->get()->merge($request->user()->filter_sets));
    });

    Route::get('get_vehicle_filters', function (Request $request) {
        return response()->json([
            'year_categories' => \App\VehicleYearCategory::select('id','category')->get(),
            'brands' => \App\VehicleBrand::select('id', 'name')->orderBy('name')->get(),
            'models' => \App\VehicleModel::select('id', 'name')->orderBy('name')->get()
        ]);
    });

    Route::get('get_seller_filters', function (Request $request) {
       return response()->json([
//            'agents' => \App\Agent::all(),
            'referrers' => \App\Referrer::select('id', 'name')->get(),
            'departments' => \App\Department::select('id', 'name')->orderBy('name')->get(),
            'sale_channels' => \App\SaleChannel::select('id', 'name')->orderBy('name')->get(),
            'sale_centers' => \App\SaleCenter::select('id', 'name')->orderBy('name')->get()
       ]);
    });

    Route::get('get_clients_status', function() {
       return response()->json(\App\Status::select('id', 'name')->get());
    });

    Route::get('get_ages_category', function(){
       return response()->json(\App\Age::select('id', 'name')->get());
    });

    Route::post('/set_filterset', 'ReportController@setFilterSet');

    //Route::post('/create_summary_table', 'ReportController@createSummaryTable');
    Route::post('/create_summary_table', 'ReportController@getReport');


});
