<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AttachmentController;

Route::get('/', fn () => view('landing'));

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
	// Enable ticket creation for authenticated end-users with cooldown (staff exempt)
	Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
	Route::get('tickets/create', [TicketController::class, 'create'])->name('tickets.create');
	Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store');
	// Keep show after create to avoid conflict and restrict param to numeric id
	Route::get('tickets/{ticket}', [TicketController::class, 'show'])->whereNumber('ticket')->name('tickets.show');
	// Cooldown for messaging per user (skip for staff)
	Route::post('messages', [MessageController::class, 'store'])->middleware('cooldown.message')->name('messages.store');
	Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
	Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->middleware('cooldown.download')->name('attachments.download');

	// User profile (normal users)
	Route::get('user/profile', [\App\Http\Controllers\User\ProfileController::class, 'edit'])->name('user.profile.edit');
	Route::patch('user/profile', [\App\Http\Controllers\User\ProfileController::class, 'update'])->name('user.profile.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_agent'])->group(function () {
	Route::get('/', function(){ return redirect()->route('admin.dashboard'); })->name('home');
	Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->middleware('security.headers')->name('dashboard');
	// Reports
	Route::get('/reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
	Route::get('/reports/managers', [\App\Http\Controllers\Admin\ReportsController::class, 'managers'])->name('reports.managers');
	// Profile (agents and superadmins)
	Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
	Route::patch('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
	// Admin-scoped tickets
	Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
	Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
	Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
	Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
	Route::post('/tickets/bulk', [TicketController::class, 'bulk'])->name('tickets.bulk');
	Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
	// Cooldown for creating tickets per user (only applies to end-user store route outside admin)
	Route::post('/tickets/{ticket}/print', [TicketController::class, 'print'])->name('tickets.print');

	// User's tickets listing for admin
	Route::get('/users/{user}/tickets', [\App\Http\Controllers\Admin\UserController::class, 'tickets'])->name('users.tickets');


	// Users index visible to agents and superadmins
	Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');

	// Departments CRUD
	Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['show']);

	// Messages moderation
	Route::get('/messages', [\App\Http\Controllers\Admin\MessageAdminController::class, 'index'])->name('messages.index');
	Route::get('/messages/{message}/edit', [\App\Http\Controllers\Admin\MessageAdminController::class, 'edit'])->name('messages.edit');
	Route::patch('/messages/{message}', [\App\Http\Controllers\Admin\MessageAdminController::class, 'update'])->name('messages.update');
	Route::delete('/messages/{message}', [\App\Http\Controllers\Admin\MessageAdminController::class, 'destroy'])->name('messages.destroy');

	// Internal messages (staff-only)
	Route::get('/internal-messages', [\App\Http\Controllers\Admin\InternalMessageController::class, 'index'])->name('internal_messages.index');
	Route::get('/internal-messages/create', [\App\Http\Controllers\Admin\InternalMessageController::class, 'create'])->name('internal_messages.create');
	Route::post('/internal-messages', [\App\Http\Controllers\Admin\InternalMessageController::class, 'store'])->name('internal_messages.store');
	Route::get('/internal-messages/{internalMessage}', [\App\Http\Controllers\Admin\InternalMessageController::class, 'show'])->name('internal_messages.show');
	Route::post('/internal-messages/{internalMessage}/reply', [\App\Http\Controllers\Admin\InternalMessageController::class, 'reply'])->name('internal_messages.reply');
	Route::delete('/internal-messages/bulk-destroy', [\App\Http\Controllers\Admin\InternalMessageController::class, 'destroyMany'])->name('internal_messages.destroy_many');
	
	// Server section
	Route::get('/server', [\App\Http\Controllers\Admin\ServerController::class, 'index'])->name('server.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_superadmin'])->group(function () {
	Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'edit'])->name('settings.edit');
	Route::patch('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
	// Settings - Security
	Route::get('/settings/security', [\App\Http\Controllers\Admin\SettingsController::class, 'editSecurity'])->name('settings.security.edit');
	Route::patch('/settings/security', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSecurity'])->name('settings.security.update');
	// Settings - Retention
	Route::get('/settings/retention', [\App\Http\Controllers\Admin\SettingsController::class, 'editRetention'])->name('settings.retention.edit');
	Route::patch('/settings/retention', [\App\Http\Controllers\Admin\SettingsController::class, 'updateRetention'])->name('settings.retention.update');
	// Settings - Email
	Route::get('/settings/email', [\App\Http\Controllers\Admin\SettingsController::class, 'editEmail'])->name('settings.email.edit');
	Route::patch('/settings/email', [\App\Http\Controllers\Admin\SettingsController::class, 'updateEmail'])->name('settings.email.update');
	Route::post('/settings/email/test', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('settings.email.test');
	Route::post('/settings/email/ping', [\App\Http\Controllers\Admin\SettingsController::class, 'pingEmail'])->name('settings.email.ping');
	// Email reports
	Route::get('/settings/email/report', [\App\Http\Controllers\Admin\EmailReportController::class, 'index'])->name('settings.email.report');
	Route::get('/settings/retention/run', function(){
		\Artisan::call('tickets:retention');
		return back()->with('success', 'عملیات نگهداری اجرا شد.');
	})->name('settings.retention.run');
	// Settings - FAQ dedicated page
	Route::get('/settings/faq', [\App\Http\Controllers\Admin\SettingsController::class, 'editFaq'])->name('settings.faq.edit');
	Route::patch('/settings/faq', [\App\Http\Controllers\Admin\SettingsController::class, 'updateFaq'])->name('settings.faq.update');
	// Settings - Uploads & Limits dedicated page
	Route::get('/settings/uploads', [\App\Http\Controllers\Admin\SettingsController::class, 'editUploads'])->name('settings.uploads.edit');
	Route::patch('/settings/uploads', [\App\Http\Controllers\Admin\SettingsController::class, 'updateUploads'])->name('settings.uploads.update');
	// Settings - Work Hours dedicated page
	Route::get('/settings/workhours', [\App\Http\Controllers\Admin\SettingsController::class, 'editWorkhours'])->name('settings.workhours.edit');
	Route::patch('/settings/workhours', [\App\Http\Controllers\Admin\SettingsController::class, 'updateWorkhours'])->name('settings.workhours.update');
	// Canned replies (all endpoints including index restricted to superadmin)
	Route::get('/canned', [\App\Http\Controllers\Admin\CannedReplyController::class, 'index'])->name('canned.index');
	// Canned replies write operations - superadmin only
	Route::post('/canned/groups', [\App\Http\Controllers\Admin\CannedReplyController::class, 'storeGroup'])->name('canned.groups.store');
	Route::patch('/canned/groups/{group}', [\App\Http\Controllers\Admin\CannedReplyController::class, 'updateGroup'])->name('canned.groups.update');
	Route::delete('/canned/groups/{group}', [\App\Http\Controllers\Admin\CannedReplyController::class, 'destroyGroup'])->name('canned.groups.destroy');
	Route::post('/canned/replies', [\App\Http\Controllers\Admin\CannedReplyController::class, 'storeReply'])->name('canned.replies.store');
	Route::patch('/canned/replies/{reply}', [\App\Http\Controllers\Admin\CannedReplyController::class, 'updateReply'])->name('canned.replies.update');
	Route::delete('/canned/replies/{reply}', [\App\Http\Controllers\Admin\CannedReplyController::class, 'destroyReply'])->name('canned.replies.destroy');
	Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['create', 'store', 'destroy']);
});

// Users edit/update available to both agents and superadmins; controller enforces fine-grained permissions
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
});

// Separate admin/staff login form and handler

Route::middleware('guest')->group(function(){
	// Derive admin login throttle from settings (same policy style as user login)
	$loginCooldown = max(1, (int) (settings('cooldown_login_minutes') ?? 10));
	$adminLoginThrottle = "throttle:5,{$loginCooldown}";
	Route::get('/admin/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'createAdmin'])->name('admin.login');
	Route::post('/admin/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'storeAdmin'])
		->middleware($adminLoginThrottle)
		->name('admin.login.store');
});


