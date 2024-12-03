<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;

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

Route::middleware(['cors'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']); // Create a new order

    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/user/{userId}', [ReservationController::class, 'getUserReservations']);
    Route::get('/reservations/calendar', [ReservationController::class, 'getCalendarData']);
    Route::get('/new-reservations', [ReservationController::class, 'getNewReservations']);
    Route::post('/mark-notification-read/{reservationId}', [ReservationController::class, 'markNotificationRead']);
    Route::get('/reservation-detail/{id}', [ReservationController::class, 'getReservationDetail']);
    Route::post('/confirm-reservation/{id}', [ReservationController::class, 'confirmReservation']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancelReservation']);
    Route::get('/reservations/upcoming', [DashboardController::class, 'getUpcomingReservations']);
    
    
    Route::get('/customer/menu', [MenuController::class, 'customerMenu']);
    Route::get('/customer/order', [OrderController::class, 'customerOrder']);
    Route::get('/customer/order/{orderId}', [OrderController::class, 'customerOrderItems']);
    Route::get('/customer/menu-options', [MenuController::class, 'getDistinctMenuOptions']);
    Route::put('/customer/order/edit/{orderId}', [OrderController::class, 'editOrder']);
    Route::put('/customer/order/cancel/{orderId}', [OrderController::class, 'cancelOrder']);
    Route::get('/validateTable/{tableNum}', [TablesController::class, 'validateTableNumber']); 

    Route::post('/customer/login', [CustomerAuthController::class, 'login']);
    Route::post('/customer/forgot-password', [CustomerAuthController::class, 'forgotPassword']);
    Route::put('/customer/update-password', [CustomerAuthController::class, 'updatePassword']);
});
// Add this OPTIONS route for preflight requests
Route::options('{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
})->where('any', '.*');

Route::post('/login', [AuthController::class, 'customerLogin']);

