<?php

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

Route::get('/','GoodAndNewController@home');
Route::post('/create','GoodAndNewController@teamCreate');
Route::post('/preview','GoodAndNewController@preview');
Route::get('/login','GoodAndNewController@showLoginForm');
Route::post('/login','GoodAndNewController@login');
Route::get('/logout','GoodAndNewController@logout');
Route::get('/edit','GoodAndNewController@showEditForm');
Route::post('/edit','GoodAndNewController@showEditForm');
Route::post('add','GoodAndNewController@addUser');
Route::post('select','GoodAndNewController@selectUser');
Route::post('delete','GoodAndNewController@deleteUser');
Route::post('replace','GoodAndNewController@replace');
Route::post('select2','GoodAndNewController@selectUser2');
Route::post('promote','GoodAndNewController@promote');
Route::post('replace2','GoodAndNewController@replace2');
Route::post('controlColumn','GoodAndNewController@countrolColumn');
