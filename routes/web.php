<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostmarkController;
use App\Http\Controllers\TwilioController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/postmark', [PostmarkController::class, 'index']);
Route::post('/smsWebhook', [TwilioController::class, 'index']);
