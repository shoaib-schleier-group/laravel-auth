<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");



//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::namespace('Auth')->group(function($request) {

    Route::group(['prefix' => 'user'], function () {
        Route::post('/login', 'ApiAuthController@login')->name('login.api');
        Route::post('/register','ApiAuthController@register')->name('register.api');
        Route::post('/createsuperlogin', 'ApiAuthController@createsuperlogin')->name('createsuperlogin.api');
        Route::post('/forgetpassword', 'ApiAuthController@forgot_password');
        Route::post('/social_login', 'ApiAuthController@sociallogin')->name('socialLogin.api');

    });

});









