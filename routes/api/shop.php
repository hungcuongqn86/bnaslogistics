<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'shop', 'namespace' => 'Modules\Shop\Http\Controllers'], function () {
        Route::get('/search', 'ShopController@search');
        Route::get('/detail/{id}', 'ShopController@detail');
    });
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'mshop', 'namespace' => 'Modules\Shop\Http\Controllers'], function () {
            Route::group(['prefix' => 'myshop'], function () {
                Route::get('/search', 'ShopController@myshop');
                Route::get('/detail/{id}', 'ShopController@detail');
                Route::post('/update/{id}', 'ShopController@update');
                Route::post('/delete/{id}', 'ShopController@delete');
            });
        });
    });
});

Route::group(['prefix' => 'shop', 'namespace' => 'Modules\Shop\Http\Controllers'], function () {
    Route::post('/create', 'ShopController@create');
});

