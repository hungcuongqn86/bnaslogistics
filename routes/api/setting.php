<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {

        Route::group(['prefix' => 'setting', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'SettingController@search');
            Route::get('/detail/{id}', 'SettingController@detail');
            Route::post('/create', 'SettingController@create');
            Route::post('/update', 'SettingController@update');
        });

        Route::group(['prefix' => 'service_fee', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'ServiceFeeController@search');
            Route::get('/detail/{id}', 'ServiceFeeController@detail');
            Route::post('/create', 'ServiceFeeController@create');
            Route::post('/update/{id}', 'ServiceFeeController@update');
            Route::post('/delete/{id}', 'ServiceFeeController@delete');
        });

        Route::group(['prefix' => 'transport_fees', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'TransportFeeController@search');
            Route::get('/detail/{id}', 'TransportFeeController@detail');
            Route::post('/create', 'TransportFeeController@create');
            Route::post('/update/{id}', 'TransportFeeController@update');
            Route::post('/delete/{id}', 'TransportFeeController@delete');
        });

        Route::group(['prefix' => 'inspection_fees', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'InspectionFeeController@search');
            Route::get('/detail/{id}', 'InspectionFeeController@detail');
            Route::post('/create', 'InspectionFeeController@create');
            Route::post('/update/{id}', 'InspectionFeeController@update');
            Route::post('/delete/{id}', 'InspectionFeeController@delete');
        });

        Route::group(['prefix' => 'warehouses', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'WarehouseController@search');
            Route::get('/detail/{id}', 'WarehouseController@detail');
            Route::post('/create', 'WarehouseController@create');
            Route::post('/update/{id}', 'WarehouseController@update');
            Route::post('/delete/{id}', 'WarehouseController@delete');
        });

        Route::group(['prefix' => 'china_warehouses', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'WarehouseController@search');
            Route::get('/detail/{id}', 'WarehouseController@detail');
            Route::post('/create', 'WarehouseController@create');
            Route::post('/update/{id}', 'WarehouseController@update');
            Route::post('/delete/{id}', 'WarehouseController@delete');
        });

        Route::group(['prefix' => 'vip', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'VipController@search');
            Route::get('/detail/{id}', 'VipController@detail');
            Route::post('/create', 'VipController@create');
            Route::post('/update/{id}', 'VipController@update');
            Route::post('/delete/{id}', 'VipController@delete');
        });
    });
});
