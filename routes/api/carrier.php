<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'carrier', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
            Route::get('/search', 'CarrierController@search');
            Route::get('/myshipping', 'CarrierController@myshipping');
            Route::get('/status', 'CarrierController@status');
            Route::get('/count', 'CarrierController@countByStatus');
            Route::post('/create', 'CarrierController@create');
            Route::post('/update/{id}', 'CarrierController@update');
            Route::post('/delete/{id}', 'CarrierController@delete');
            Route::post('/approve', 'CarrierController@approve');
            Route::get('/detail/{id}', 'CarrierController@detail');
        });
    });
});

