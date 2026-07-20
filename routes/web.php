<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TicketRequestController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/events/{event}', [LandingPageController::class, 'show'])->name('landing.show');
Route::get('/events/{event}/agenda', [AgendaController::class, 'show'])->name('agenda.show');
Route::get('/events/{event}/awards', [AwardsController::class, 'show'])->name('awards.show');
Route::get('/events/{event}/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/events/{event}/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');
Route::get('/events/{event}/request', [TicketRequestController::class, 'create'])->name('ticket-requests.create');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('events', EventController::class)->except('show');
    });
});
