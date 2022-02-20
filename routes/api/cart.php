<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'cart', 'namespace' => 'Modules\Cart\Http\Controllers'], function () {
        Route::get('/search', 'CartController@search');
        Route::post('/update/{id}', 'CartController@update');
        Route::post('/delete', 'CartController@delete');
        Route::group(['prefix' => 'cart_item'], function () {
            Route::post('/update', 'CartController@itemUpdate');
            Route::post('/delete', 'CartController@itemDelete');
        });
    });
});

Route::group(['prefix' => 'cart', 'namespace' => 'Modules\Cart\Http\Controllers'], function () {
    Route::post('/create', 'CartController@create');
});
