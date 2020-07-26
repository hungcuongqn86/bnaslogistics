<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        // API Routes Sim
        Route::group(['prefix' => 'muser', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::group(['prefix' => 'user'], function () {
                Route::get('/search', 'UserController@search');
                Route::get('/handles', 'UserController@handleGetAll');
                Route::get('/custumers', 'UserController@custumers');
                Route::get('/detail/{id}', 'UserController@detail');
                Route::post('/create', 'UserController@create');
                Route::post('/update', 'UserController@update');
                Route::delete('/delete', 'UserController@delete');
            });
            Route::group(['prefix' => 'role'], function () {
                Route::get('/search', 'RoleController@search');
            });
            Route::group(['prefix' => 'transaction'], function () {
                Route::get('/search', 'TransactionController@search');
                Route::get('/types', 'TransactionController@types');
                Route::post('/create', 'TransactionController@create');
                Route::post('/withdrawalrequest', 'TransactionController@withdrawalRequest');
                Route::post('/updatewithdrawalrequest', 'TransactionController@updatewithdrawalrequest');
                Route::get('/withdrawalrequests', 'TransactionController@withdrawalRequests');
                Route::get('/withdrawalrequestsstatus', 'TransactionController@withdrawalrequestsstatus');
                Route::get('/withdrawalrequestcount', 'TransactionController@withdrawalrequestcount');
            });
        });
    });
});
