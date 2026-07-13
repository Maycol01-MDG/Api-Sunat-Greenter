<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DespatchController;
use App\Http\Controllers\Api\InvoiceConroller;
use App\Http\Controllers\Api\NoteController;

Route::post('register', [RegisterController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('me', [AuthController::class, 'me']);

// Creación de rutas para el controlador CompanyController
Route::apiResource('companies',CompanyController::class)->middleware('auth:api');

//Invoices
Route::post('invoices/send', [InvoiceConroller::class, 'send'])->middleware('auth:api');
Route::post('invoices/xml', [InvoiceConroller::class, 'xml'])->middleware('auth:api');
Route::post('invoices/pdf', [InvoiceConroller::class, 'pdf'])->middleware('auth:api');

//Note
Route::post('notes/send', [NoteController::class, 'send'])->middleware('auth:api');
Route::post('notes/xml', [NoteController::class, 'xml'])->middleware('auth:api');
Route::post('notes/pdf', [NoteController::class, 'pdf'])->middleware('auth:api');

//Despaches
Route::post('despatches/send', [DespatchController::class, 'send'])->middleware('auth:api');
Route::post('despatches/xml', [DespatchController::class, 'xml'])->middleware('auth:api');
Route::post('despatches/pdf', [DespatchController::class, 'pdf'])->middleware('auth:api');

