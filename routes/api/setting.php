<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {

        Route::group(['prefix' => 'setting', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'SettingController@search');
            Route::get('/detail/{id}', 'SettingController@detail');
            Route::post('/create', 'SettingController@create');
            Route::post('/update', 'SettingController@update');
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
