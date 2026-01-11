<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegistrationController;

Route::post('/register', [RegistrationController::class, 'register'])
    ->middleware(['throttle:60,1']);

