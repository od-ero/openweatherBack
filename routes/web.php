<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrentWeatherDataController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/current-weather-data', [CurrentWeatherDataController::class, 'index']) ->name('index');
