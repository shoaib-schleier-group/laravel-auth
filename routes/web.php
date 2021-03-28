<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Auth::routes(['register' => false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/users', 'UsersController@index')->name('users');
Route::get('/users/invite', 'UsersController@invite_view')->name('invite_view');
Route::post('/users/invite', 'UsersController@process_invites')->name('process_invite');
Route::get('/registration/{token}', 'UsersController@registration_view')->name('registration');
Route::POST('/registration', 'Auth\RegisterController@register')->name('accept');
