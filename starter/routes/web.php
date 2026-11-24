<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Designs/Editor', [
    'limits' => config('crane_design'),
    'previewUrl' => route('designs.generate', [], false),
]))->name('home');
