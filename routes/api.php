<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-code', [AuthController::class, 'resendCode']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/reviews/recent', [ReviewController::class, 'recent']);
Route::get('/reviews/{rawg_id}', [ReviewController::class, 'index']);
Route::get('/reviews/{review_id}/comments', [CommentController::class, 'index']);
Route::get('/users/{id}/followers', [FollowController::class, 'followers']);
Route::get('/users/{id}/following', [FollowController::class, 'following']);
Route::get('/users/search', [UserController::class, 'search']);
Route::get('/users/{username}', [UserController::class, 'show']);
Route::get('/users/{username}/library', [LibraryController::class, 'publicIndex']);
Route::get('/users/{username}/reviews', [ReviewController::class, 'publicIndex']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/library', [LibraryController::class, 'index']);
    Route::post('/library', [LibraryController::class, 'store']);
    Route::put('/library/{rawg_id}', [LibraryController::class, 'update']);
    Route::delete('/library/{rawg_id}', [LibraryController::class, 'destroy']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::post('/reviews/{review_id}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/users/{id}/follow', [FollowController::class, 'follow']);
    Route::delete('/users/{id}/unfollow', [FollowController::class, 'unfollow']);
    Route::put('/users/profile/username', [UserController::class, 'updateUsername']);
    Route::post('/users/profile/picture', [UserController::class, 'updateProfilePicture']);
    Route::delete('/users/profile/picture', [UserController::class, 'removeProfilePicture']);
});
