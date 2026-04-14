<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SavingsTargetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\UserMissionController;

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
    Route::get('/transactions/streak', [TransactionController::class, 'streak']);
    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    // Savings Target
    Route::get('/savings-targets', [SavingsTargetController::class, 'index']);
    Route::get('/savings-targets/active', [SavingsTargetController::class, 'active']);
    Route::post('/savings-targets', [SavingsTargetController::class, 'store']);
    Route::put('/savings-targets/{id}', [SavingsTargetController::class, 'update']);
    Route::delete('/savings-targets/{id}', [SavingsTargetController::class, 'destroy']);
    Route::post('/savings-targets/{id}/add-progress', [SavingsTargetController::class, 'addProgress']);

    // Habits
    Route::get('/habits', [HabitController::class, 'index']);
    Route::get('/habits/{habit}', [HabitController::class, 'show']);

    // Reminders
    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::post('/reminders', [ReminderController::class, 'store']);
    Route::put('/reminders/{id}', [ReminderController::class, 'update']);
    Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']);
    Route::patch('/reminders/{id}/toggle', [ReminderController::class, 'toggle']);

    // Insights
    Route::get('/insights/spending-by-category', [InsightController::class, 'spendingByCategory']);
    Route::get('/insights/spending-by-time', [InsightController::class, 'spendingByTime']);
    Route::get('/insights/top-spends', [InsightController::class, 'topSpends']);
    Route::get('/insights/savings-tip', [InsightController::class, 'savingsTip']);

    // Missions
    Route::get('/missions', [MissionController::class, 'index']);
    Route::get('/missions/featured', [MissionController::class, 'featured']);
    Route::get('/missions/recommended', [MissionController::class, 'recommended']);
    Route::get('/missions/active', [MissionController::class, 'active']);
    Route::get('/missions/summary', [MissionController::class, 'summary']);
    Route::get('/missions/{id}', [MissionController::class, 'show']);
    Route::post('/missions/start', [MissionController::class, 'start']);
    Route::post('/missions/claim', [MissionController::class, 'claim']);
    Route::post('/missions/give-up', [MissionController::class, 'giveUp']);

    // User Missions
    Route::get('/user-missions/active', [UserMissionController::class, 'active']);
    Route::get('/user-missions', [UserMissionController::class, 'index']);
    Route::post('/user-missions/start', [UserMissionController::class, 'start']);
    Route::patch('/user-missions/{id}/progress', [UserMissionController::class, 'updateProgress']);
    Route::patch('/user-missions/{id}/give-up', [UserMissionController::class, 'giveUp']);
    Route::post('/user-missions/{id}/claim', [UserMissionController::class, 'claim']);
});
