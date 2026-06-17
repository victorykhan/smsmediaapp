<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ChunkedUploadController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\PreUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAccountsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientsController::class);
    Route::get('/clients/{client}/onboarding', [ClientsController::class, 'onboarding'])->name('clients.onboarding');
    Route::post('/clients/{client}/onboarding/step', [ClientsController::class, 'onboardingStep'])->name('clients.onboarding.step');
    Route::post('/clients/{client}/invite', [ClientsController::class, 'invite'])->name('clients.invite');
    Route::put('/clients/{client}/members/{user}/role', [ClientsController::class, 'updateMemberRole'])->name('clients.members.role');
    Route::delete('/clients/{client}/members/{user}', [ClientsController::class, 'removeMember'])->name('clients.members.remove');

    Route::get('/clients/{client}/accounts', [SocialAccountsController::class, 'index'])->name('accounts.index');
    Route::get('/clients/{client}/accounts/create', [SocialAccountsController::class, 'create'])->name('accounts.create');
    Route::post('/clients/{client}/accounts', [SocialAccountsController::class, 'store'])->name('accounts.store');
    Route::get('/clients/{client}/accounts/{account}/edit', [SocialAccountsController::class, 'edit'])->name('accounts.edit');
    Route::put('/clients/{client}/accounts/{account}', [SocialAccountsController::class, 'update'])->name('accounts.update');
    Route::delete('/clients/{client}/accounts/{account}', [SocialAccountsController::class, 'destroy'])->name('accounts.destroy');

    Route::get('/clients/{client}/posts', [PostsController::class, 'index'])->name('posts.index');
    Route::get('/clients/{client}/posts/create', [PostsController::class, 'create'])->name('posts.create');
    Route::post('/clients/{client}/posts', [PostsController::class, 'store'])->name('posts.store');
    Route::get('/clients/{client}/posts/{post}', [PostsController::class, 'show'])->name('posts.show');
    Route::get('/clients/{client}/posts/{post}/edit', [PostsController::class, 'edit'])->name('posts.edit');
    Route::put('/clients/{client}/posts/{post}', [PostsController::class, 'update'])->name('posts.update');
    Route::delete('/clients/{client}/posts/{post}', [PostsController::class, 'destroy'])->name('posts.destroy');
    Route::post('/clients/{client}/posts/{post}/approve', [PostsController::class, 'approve'])->name('posts.approve');
    Route::post('/clients/{client}/posts/{post}/reject', [PostsController::class, 'reject'])->name('posts.reject');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');

    Route::get('/clients/{client}/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

    Route::post('/upload/chunk', [ChunkedUploadController::class, 'chunk'])->name('upload.chunk');
    Route::post('/upload/cancel', [ChunkedUploadController::class, 'cancel'])->name('upload.cancel');
    Route::get('/upload/chunks/{fileId}/status', [ChunkedUploadController::class, 'status'])->name('upload.chunks.status');

    Route::post('/clients/{client}/posts/{post}/pre-upload', [PreUploadController::class, 'upload'])->name('posts.pre-upload');
    Route::get('/clients/{client}/posts/{post}/pre-upload/status', [PreUploadController::class, 'status'])->name('posts.pre-upload.status');

    Route::get('/oauth/{platform}/redirect/{client?}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/oauth/{platform}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
});

require __DIR__.'/auth.php';

Route::get('/deploy/migrate', [\App\Http\Controllers\DeployController::class, 'migrate']);
