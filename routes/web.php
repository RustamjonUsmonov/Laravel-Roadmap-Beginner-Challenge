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

Route::post('/upload',[\App\Http\Controllers\ArticleController::class,'upload']);
Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/About', 'about')->name('about-me');
    Route::resource('tag', \App\Http\Controllers\TagController::class);
    Route::resource('article', \App\Http\Controllers\ArticleController::class);
    Route::resource('category', \App\Http\Controllers\CategoryController::class);
});

require __DIR__ . '/auth.php';
