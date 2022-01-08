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

Route::post('login', 'App\Http\Controllers\Api\Auth\AuthController@login');
Route::post('register', 'App\Http\Controllers\Api\Auth\AuthController@register');
//Route::post('get_profile', 'App\Http\Controllers\Api\Auth\AuthController@getProfile');

Route::group(['middleware'=>'auth:api'],function () {
	Route::post('get_profile', 'App\Http\Controllers\Api\Auth\AuthController@getProfile');

	Route::post('create_blog', 'App\Http\Controllers\Api\BlogController@createBlog');
	Route::post('update_blog', 'App\Http\Controllers\Api\BlogController@updateBlog');
	Route::post('delete_blog', 'App\Http\Controllers\Api\BlogController@deleteBlog');
	Route::post('blog_list', 'App\Http\Controllers\Api\BlogController@blogList');
	Route::post('all_blog_list', 'App\Http\Controllers\Api\BlogController@allBlogList');
	Route::post('add_to_favorite', 'App\Http\Controllers\Api\BlogController@favouriteBlog');
});

