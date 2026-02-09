<?php

use Illuminate\Support\Facades\Route;
use Tishmalo\EscalationMatrix\Http\Controllers\TicketAuthController;
use Tishmalo\EscalationMatrix\Http\Controllers\TicketController;

// Authentication routes (public)
Route::middleware(['web'])->group(function () {
    Route::get('escalation/login', [TicketAuthController::class, 'showLoginForm'])->name('escalation.login');
    Route::post('escalation/login', [TicketAuthController::class, 'login'])->name('escalation.login.attempt');
    Route::post('escalation/logout', [TicketAuthController::class, 'logout'])->name('escalation.logout');
});

// Ticket routes (protected)
Route::middleware(['web', \Tishmalo\EscalationMatrix\Http\Middleware\AuthenticateTickets::class])
    ->prefix('tickets')
    ->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('escalation.tickets.index');
        Route::get('/{id}', [TicketController::class, 'show'])->name('escalation.tickets.show');
        Route::post('/{id}/status', [TicketController::class, 'updateStatus'])->name('escalation.tickets.updateStatus');
    });
