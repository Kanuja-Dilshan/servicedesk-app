<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('tickets', TicketController::class)->except(['edit', 'destroy']);
    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->except(['show', 'destroy']);
    Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'destroy']);
});

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';