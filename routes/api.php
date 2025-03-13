<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');


Route::apiResource('product', ProductController::class)->middleware('auth:sanctum');

Route::patch('order/{order}/confirm', [OrderController::class, 'confirmOrder'])->middleware('auth:sanctum');
Route::patch('order/{order}/markAsShipped', [OrderController::class, 'markAsShipped'])->middleware('auth:sanctum');
Route::patch('order/{order}/markAsDelivered', [OrderController::class, 'markAsDelivered'])->middleware('auth:sanctum');

Route::apiResource('order', OrderController::class)->middleware('auth:sanctum');


