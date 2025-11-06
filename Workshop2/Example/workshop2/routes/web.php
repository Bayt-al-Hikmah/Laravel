<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App1Controller;
use App\Http\Controllers\App2Controller;
use App\Http\Controllers\App3Controller;
use App\Http\Controllers\App4Controller;
use App\Http\Controllers\FeedbackController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/app1', [App1Controller::class, 'index'])->name('app1_index');
Route::get('/app2/{role?}', [App2Controller::class, 'index'])->name('app2_index');
Route::get('/app3', [App3Controller::class, 'index'])->name('app3_index');
Route::get('/app4', [App4Controller::class, 'index'])->name('app4_index');


Route::get('/feedback', [FeedbackController::class, 'showForm'])
     ->name('submit_feedback');

Route::post('/feedback', [FeedbackController::class, 'storeFeedback']);

Route::get('/feedback/list', [FeedbackController::class, 'feedbackList'])
     ->name('feedbacks');