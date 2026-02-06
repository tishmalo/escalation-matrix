<?php

use Illuminate\Support\Facades\Route;
use Tishmalo\EscalationMatrix\Http\Controllers\TicketController;

Route::middleware(['web'])->prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('escalation.tickets.index');
    Route::get('/{id}', [TicketController::class, 'show'])->name('escalation.tickets.show');
});
