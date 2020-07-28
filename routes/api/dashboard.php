<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'dashboard', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/newlinks', 'DashboardController@newlinks');
            Route::get('/neworders', 'DashboardController@neworders');
            Route::get('/newusers', 'DashboardController@newusers');
            Route::get('/newcomplains', 'DashboardController@newcomplains');

            Route::get('/statisticbytaobao', 'DashboardController@statisticbytaobao');
            Route::get('/statisticbytmall', 'DashboardController@statisticbytmall');
            Route::get('/statisticby1688', 'DashboardController@statisticby1688');

            Route::get('/orderstatisticbyday', 'DashboardController@orderStatisticByDay');
        });
    });
});

