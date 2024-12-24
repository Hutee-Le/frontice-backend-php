<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VNPAYController;

Route::get('/', function () {
    return view('welcome');
});
