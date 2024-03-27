<?php

use App\Http\Controllers\Api\AnnouncementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use Spatie\FlareClient\Api;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("registeer", [ApiController::class, "register"])->name("api.register");
Route::post("login", [ApiController::class, "login"]);

Route::group([
    "middleware" => ["auth:api"]
], function(){
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
});

// Announcements
Route::group([
    "middleware" => ["auth:api"]
], function (){
    Route::post('announcement', [AnnouncementController::class, 'store']);
    Route::get('announcements', [AnnouncementController::class, 'index']);
    Route::get('announcements/{id}', [AnnouncementController::class, 'show']);
    Route::get('announcements/{id}/edit', [AnnouncementController::class, 'edit']);
    Route::put('announcements/{id}/edit', [AnnouncementController::class, 'update']);
    Route::delete('announcements/{id}/delete', [AnnouncementController::class, 'destroy']);
    Route::post('announcements/{id}/apply', [AnnouncementController::class, 'apply']);
});
Route::middleware('auth:api')->group(function () {
    
    Route::put('/applications/{applicationId}/accept', [AnnouncementController::class, 'acceptApplication']);
    Route::put('/applications/{applicationId}/reject', [AnnouncementController::class, 'rejectApplication']);
    Route::get('/user/applications', [AnnouncementController::class, 'userApplications']);

});

// Route::get('/announcements', [AnnouncementController::class, 'index']);



