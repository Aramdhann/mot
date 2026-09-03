<?php

use Illuminate\Support\Facades\Route;

// PWA assets served via the framework — server-agnostic, correct Content-Type everywhere
Route::get('/manifest.webmanifest', fn () => response(file_get_contents(public_path('manifest.webmanifest')), 200, ['Content-Type' => 'application/manifest+json']));
Route::get('/sw.js', fn () => response(file_get_contents(public_path('sw.js')), 200, ['Content-Type' => 'application/javascript']));

Route::redirect('/', '/admin');
