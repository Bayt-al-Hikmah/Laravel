<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoController;

// The root path (/) will show the gallery
Route::get('/', [PhotoController::class, 'gallery'])->name('gallery');

// The /upload path will show the upload form
Route::get('/upload', [PhotoController::class, 'create'])->name('upload.create');

// Posting to /upload will handle the file storage
Route::post('/upload', [PhotoController::class, 'store'])->name('upload.store');