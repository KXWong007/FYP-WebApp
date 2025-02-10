<?php

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ForecastingController;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/dashboard', function () {
//     return view('welcome');
// })->name('dashboard');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/', function () {
    return view('auth.login');
});

Route::controller(AuthController::class)->group(function () {
 
    Route::get('login', 'login')->name('login');
    Route::post('login', 'loginAction')->name('login.action');
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('auth');
    
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

});

// Customer Routes
Route::get('/customers/export/{type?}', [CustomerController::class, 'export'])->name('customers.export');
Route::get('/customers/template', [CustomerController::class, 'template'])->name('customers.template');
Route::get('/customers/{id}', [CustomerController::class, 'getCustomer'])->name('customers.get');
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
Route::post('/customers/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');
Route::post('/check-customer-id', [CustomerController::class, 'checkCustomerId'])->name('check.customer.id');
Route::post('/check-nric', [CustomerController::class, 'checkNric'])->name('check.nric');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');

// Staff Routes
Route::get('/staff/export/{type?}', [StaffController::class, 'export'])->name('staff.export');
Route::get('/staff/template', [StaffController::class, 'template'])->name('staff.template');
Route::get('/staff/{id}', [StaffController::class, 'getStaff'])->name('staff.get');
Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
Route::post('/staff/update/{id}', [StaffController::class, 'update'])->name('staff.update');
Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
Route::post('/staff/import', [StaffController::class, 'import'])->name('staff.import');
Route::post('/check-staff-id', [StaffController::class, 'checkStaffId'])->name('check.staff.id');
Route::post('/check-nric', [StaffController::class, 'checkNric'])->name('check.nric');
Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');

// Menu Management Routes
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::post('/menu', [MenuController::class, 'store']);
Route::get('/menu/{dishId}/edit', [MenuController::class, 'edit']);
Route::put('/menu/{dishId}', [MenuController::class, 'update']);
Route::delete('/menu/{dishId}', [MenuController::class, 'destroy']);
Route::get('/menu/export/{type}', [MenuController::class, 'export'])->name('menu.export');
Route::get('/menu/{id}/edit', [MenuController::class, 'edit'])->name('menu.edit');

Route::get('/check-dishid/{dishId}', [MenuController::class, 'checkDishId']);

// Orders
Route::get('/generate-qr/{tableNum}', function ($tableNum) {
    // Generate a QR code for the given tableNum
    return QrCode::size(300)->generate($tableNum);
});

Route::resource('orders', OrderController::class);
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

Route::get('/orders/edit/{orderId}', [OrderController::class, 'edit'])->name('orders.edit');
Route::put('/orders/update/{orderId}', [OrderController::class, 'update'])->name('orders.update');

Route::get('/orders/details/{orderId}', [OrderController::class, 'details'])->name('orders.details');

Route::delete('/orders/{orderId}', [OrderController::class, 'destroy'])->name('orders.destroy');

Route::post('/orders/import', [OrderController::class, 'import'])->name('orders.import');
Route::get('/orders/export/{type?}', [OrderController::class, 'export'])->name('orders.export');
Route::get('/orders/template', [OrderController::class, 'template'])->name('orders.template');

Route::post('/check-customer', [OrderController::class, 'checkCustomer']);
Route::post('/check-staff', [OrderController::class, 'checkStaff']);
Route::get('/get-customer-detail', [OrderController::class, 'getCustomerDetail']);
Route::get('/get-staff-detail', [OrderController::class, 'getStaffDetail']);
Route::get('/generate-order-id', [OrderController::class, 'generateOrderId']);
Route::get('/generate-order-date', [OrderController::class, 'generateOrderDate']);
Route::get('/get-tables', [OrderController::class, 'getTables']);
Route::get('/get-menu-items', [OrderController::class, 'getMenuItems']);

Route::get('/customers/{id}', [CustomerController::class, 'getCustomer'])->name('customers.get');
Route::get('/staffs/{id}', [StaffController::class, 'getStaff'])->name('staffs.get');

// Order Items
Route::post('/orders/details/{orderId}/store', [OrderController::class, 'itemStore'])->name('orders.store');

Route::get('/orders/details/{orderId}/edit/{orderItemId}', [OrderController::class, 'itemEdit'])->name('orderitems.edit');
Route::put('/orders/details/{orderId}/update/{orderItemId}', [OrderController::class, 'itemUpdate'])->name('orderitems.update');

Route::delete('/orders/details/{orderId}/{orderItemId}', [OrderController::class, 'itemDestroy'])->name('orderitems.destroy');;


Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
Route::post('/reservations/store', [ReservationController::class, 'store'])->name('reservations.store');
Route::post('/reservations/update/{id}', [ReservationController::class, 'update'])->name('reservations.update');

// Reservation Routes
Route::post('/check-customer', [ReservationController::class, 'checkCustomer']);
Route::get('/get-customer-detail', [ReservationController::class, 'getCustomerDetail']);

Route::get('/customers/{id}', [CustomerController::class, 'edit'])->name('customers.get');

Route::get('/reservations/edit/{id}', [ReservationController::class, 'edit'])->name('reservations.edit');

Route::get('/reservations/export/{type?}', [ReservationController::class, 'export'])->name('reservations.export');

Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

Route::get('/reservations/template', [ReservationController::class, 'template'])->name('reservations.template');
Route::post('/check-customerstatus', [ReservationController::class, 'checkCustomerStatus'])->name('check.customer.status');

// Add this route for notifications
Route::get('/api/new-reservations', [ReservationController::class, 'getNewReservations'])
    ->name('reservations.new');

// Then add this single route for the main payment page
Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
Route::get('/payment/data', [PaymentController::class, 'getData']);

// Your other payment routes can be grouped
Route::prefix('payments')->group(function () {
    Route::get('/data', [PaymentController::class, 'getPayments'])->name('payments.data');
    Route::post('/', [PaymentController::class, 'store'])->name('payments.store');
    Route::put('/{id}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/export/{type}', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('/template', [PaymentController::class, 'downloadTemplate'])->name('payments.template');
});

Route::get('/payments/{id}/edit', [PaymentController::class, 'edit']);
Route::put('/payments/{id}', [PaymentController::class, 'update']);

Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
Route::get('/kitchen/dish-status/{id}', [KitchenController::class, 'getDishStatus']);
Route::post('/kitchen/start-cooking', [KitchenController::class, 'startCooking']);
Route::post('/kitchen/finish-cooking', [KitchenController::class, 'finishCooking']);
Route::post('/kitchen/update-status', [KitchenController::class, 'updateStatus'])->name('kitchen.update-status');
Route::post('/kitchen/cancel-order', [KitchenController::class, 'cancelOrder'])->name('kitchen.cancel-order');
Route::post('/kitchen/cancel-item', [KitchenController::class, 'cancelItem'])->name('kitchen.cancel-item');
Route::get('/kitchen/check-staff/{staffId}', [KitchenController::class, 'checkStaff']);

// POS (Dine Side) Routes
Route::get('/dine-side', [PosController::class, 'index'])->name('dine-side.index');
Route::get('/dine-side/dish-status/{id}', [PosController::class, 'getDishStatus']);
Route::post('/dine-side/serve-dish', [PosController::class, 'serveDish']);

// Handle OPTIONS requests for storage
Route::options('storage/{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', '*')
        ->header('Access-Control-Allow-Origin', '*');
})->where('any', '.*');

// Add this new route for upcoming reservations API
Route::get('/api/reservations/upcoming', [DashboardController::class, 'getUpcomingReservations'])
    ->name('reservations.upcoming');

Route::post('/kitchen/copy-order-id', [KitchenController::class, 'copyOrderId']);

Route::get('/api/orders/{orderId}/details', [DashboardController::class, 'getOrderDetails']);

//tables
Route::get('/table', [TablesController::class, 'index'])->name('table.index');
Route::post('/table/store', [TablesController::class, 'store'])->name('table.store');
Route::put('/table/update/{tableNum}', [TablesController::class, 'update'])->name('table.update');
Route::get('/table/edit/{tableNum}', [TablesController::class, 'edit'])->name('table.edit');
Route::delete('/table/{tableNum}', [TablesController::class, 'destroy'])->name('table.destroy');
Route::post('/table/generate-qrcode/{tableNum}', [TablesController::class, 'generateQRCode'])->name('table.generate-qrcode');
Route::get('/table/view-qrcode/{tableNum}', [TablesController::class, 'showQRCode'])->name('table.view-qrcode');

//inventory
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/inventory/edit/{inventoryId}', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::put('/inventory/update/{inventoryId}', [InventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{inventoryId}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
Route::get('/inventory/export/{type?}', [InventoryController::class, 'export'])->name('inventory.export');
Route::get('/inventory/template', [InventoryController::class, 'template'])->name('inventory.template');

//Forecasting stock Management
Route::get('/forecast', [ForecastingController::class, 'index'])->name('forecast.index');
Route::post('/forecast/{inventoryId}', [ForecastingController::class, 'store'])->name('forecast.store');
Route::get('/forecast/{id}/edit', [ForecastingController::class, 'edit'])->name('forecast.edit');
Route::put('/forecast/{id}', [ForecastingController::class, 'update'])->name('forecast.update');
Route::delete('/forecast/{id}', [ForecastingController::class, 'destroy'])->name('forecast.destroy');
Route::get('/forecast/{inventoryId}/next30days', [ForecastingController::class, 'forecastNext30Days'])->name('forecast.next30days');
Route::post('/forecast/store/{inventoryId}', [ForecastingController::class, 'store'])->name('forecast.store');
Route::put('/forecast/update/{id}', [ForecastingController::class, 'update'])->name('forecast.update');

Route::get('/api/low-stock-items', [ForecastingController::class, 'getLowStockItems']);

Route::get('/get-available-tables', [ReservationController::class, 'getAvailableTables']);

Route::get('/check-table-availability', [ReservationController::class, 'checkTableAvailability']);
Route::post('/process-waiting-list', [ReservationController::class, 'processWaitingList']);

Route::get('/check-edit-table-availability', [ReservationController::class, 'checkEditTableAvailability']);


