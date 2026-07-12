<?php

use Illuminate\Support\Facades\Route;
use Pest\Plugins\Tia\Storage;

Route::get('/', function () {
    return view('welcome');
});


