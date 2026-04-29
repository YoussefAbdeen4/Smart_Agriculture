<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Protected routes (require authentication)
Route::middleware(['auth:sanctum', 'isVerified'])->group(function () {
    // Profile Management
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    Route::delete('profile/delete', [ProfileController::class, 'destroy']);
    Route::get('profile/farms', [ProfileController::class, 'myFarms']);
    Route::get('profile/{id}', [ProfileController::class, 'showPublicProfile']);

    // Farm Management
    Route::get('farms', [FarmController::class, 'index']);
    Route::post('farms', [FarmController::class, 'store']);
    Route::get('farms/{farm}', [FarmController::class, 'show']);
    Route::post('farms/{farm}', [FarmController::class, 'update']);
    Route::delete('farms/{farm}', [FarmController::class, 'destroy']);
    Route::post('farms/{farm}/grant-access', [FarmController::class, 'grantAccess']);
    Route::post('farms/{farm}/revoke-access', [FarmController::class, 'revokeAccess']);
    Route::get('farms/{farm}/access-list', [FarmController::class, 'getAccessList']);

    // Farm Plants Management
    Route::get('farms/{farm}/plants', [PlantController::class, 'index']);
    Route::post('farms/{farm}/plants', [PlantController::class, 'store']);
    Route::post('farms/{farm}/plants/{plant}', [PlantController::class, 'update']);
    Route::delete('farms/{farm}/plants/{plant}', [PlantController::class, 'destroy']);


    // Farm Plans Management
    Route::get('farms/{farm}/plans', [PlanController::class, 'index']);
    Route::post('farms/{farm}/plans', [PlanController::class, 'store']);
    Route::get('farms/{farm}/plans/{plan}', [PlanController::class, 'show']);
    Route::post('farms/{farm}/plans/{plan}', [PlanController::class, 'update']);
    Route::delete('farms/{farm}/plans/{plan}', [PlanController::class, 'destroy']);

    // Blog & Community
    Route::get('blogs', [BlogController::class, 'index']);
    Route::post('blogs', [BlogController::class, 'store']);
    Route::get('blogs/{blog}', [BlogController::class, 'show']);
    Route::post('blogs/{blog}', [BlogController::class, 'update']);
    Route::delete('blogs/{blog}', [BlogController::class, 'destroy']);
    Route::post('blogs/{blog}/comments', [BlogController::class, 'addComment']);
    Route::post('blogs/{blog}/reactions', [BlogController::class, 'toggleReaction']);

    // Chat System
    Route::post('chats/get-or-create', [ChatController::class, 'getOrCreateChat']);
    Route::apiResource('chats', ChatController::class, ['only' => ['index']]);
    Route::post('chats/{chat}/send-message', [ChatController::class, 'sendMessage']);

    // ChatBot
    Route::post('chatbot', [ChatBotController::class, 'request']);

    // weather
    Route::post('/weather', [WeatherController::class, 'getForecast']);
});

require __DIR__ . '/auth.php';
