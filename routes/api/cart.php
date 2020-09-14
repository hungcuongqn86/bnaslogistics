<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'cart', 'namespace' => 'Modules\Cart\Http\Controllers'], function () {
        Route::get('/search', 'CartController@search');
        Route::post('/update', 'CartController@update');
        Route::post('/delete', 'CartController@delete');
    });
});

Route::group(['prefix' => 'cart', 'namespace' => 'Modules\Cart\Http\Controllers'], function () {
    Route::post('/create', 'CartController@create');
});
