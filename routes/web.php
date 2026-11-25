<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$editor = fn (?string $id = null) => Inertia::render('Designs/Editor', [
    'designId' => $id,
    'limits' => config('crane_design'),
    'previewUrl' => route('designs.generate', [], false),
]);

Route::get('/', $editor)->name('home');
Route::inertia('/designs', 'Designs/Index')->name('studio.designs');
Route::get('/designs/{id}/edit', $editor)->whereNumber('id')->name('studio.edit');
