<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductSearchController;


Route::get('/products/search', [ProductSearchController::class, 'search']);
