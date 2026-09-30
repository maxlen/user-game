<?php

use App\Http\Controllers\AccessLinkController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RegistrationController;
use App\Http\Middleware\EnsureLinkIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', [RegistrationController::class, 'create'])->name('register.form');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

Route::prefix('l/{accessLink:token}')
    ->middleware(EnsureLinkIsActive::class)
    ->group(function () {
        Route::get('/', [AccessLinkController::class, 'show'])->name('link.show');
        Route::get('/history', [AccessLinkController::class, 'history'])->name('link.history');
        Route::post('/regenerate', [AccessLinkController::class, 'regenerate'])->name('link.regenerate');
        Route::post('/deactivate', [AccessLinkController::class, 'deactivate'])->name('link.deactivate');
        Route::post('/lucky', [GameController::class, 'play'])->name('link.lucky');
    });
