<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/admin/menu', 'panels.admin-menu')->name('admin.menu');
Route::view('/staff/orders', 'panels.staff-orders')->name('staff.orders');
Route::view('/customer/orders', 'panels.customer-orders')->name('customer.orders');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');