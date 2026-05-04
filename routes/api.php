<?php

use App\Http\Controllers\Api\ClientIndexController;
use App\Http\Controllers\Api\ClientWebsiteIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/clients', ClientIndexController::class);
Route::get('/clients/{client}/websites', ClientWebsiteIndexController::class);
