<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SavingsTargetController;
use App\Http\Controllers\CategoryController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Transactions
    // Transactions
    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    // Savings Target
    Route::get('/savings-target', [SavingsTargetController::class, 'show']);
    Route::post('/savings-target', [SavingsTargetController::class, 'store']);
    Route::put('/savings-target', [SavingsTargetController::class, 'update']);
    Route::delete('/savings-target', [SavingsTargetController::class, 'destroy']);

    // Habits
    Route::get('/habits', [HabitController::class, 'index']);
    Route::get('/habits/{habit}', [HabitController::class, 'show']);

    // Reminders
    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::post('/reminders', [ReminderController::class, 'store']);
    Route::put('/reminders/{id}', [ReminderController::class, 'update']);
    Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']);
    Route::patch('/reminders/{id}/toggle', [ReminderController::class, 'toggle']);
});
