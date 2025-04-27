<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrentWeatherDataController;
Route::get('/weather-data', [CurrentWeatherDataController::class, 'index']) ->name('index');
