<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\StaffPerformanceController; // Updated Controller Import
use App\Http\Controllers\AppraisalController;
use App\Http\Controllers\TaskLogController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\AppraisalCommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HrNotificationController;
use App\Http\Controllers\KingsChatSenderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// Offline fallback page for PWA
Route::get('/offline', fn() => view('offline'))->name('offline');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
// KingsChat sign-in: only for accounts whose username is the user's KingsChat handle
Route::post('/login/kingschat', [AuthController::class, 'kingsChatLogin'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('login.kingschat');

// KingsChat OAuth landing page. KingsChat redirects here after sign-in.
// Must stay public (no auth/guest middleware): the user is not logged in yet at this point.
// DIAGNOSTIC VERSION: shows only the NAMES of the values received (never their contents).
Route::get('/auth/kingschat/callback', function () {
    return response(<<<'HTML'
<!doctype html><meta charset="utf-8"><title>KingsChat</title>
<p id="m" style="font-family:sans-serif;text-align:center;margin-top:20vh">Signing in…</p>
<script>
const p = new URLSearchParams(location.hash.slice(1) + "&" + location.search.slice(1));
const names = [...p.keys()].filter(Boolean).join(", ") || "nothing";
document.getElementById("m").textContent =
  "Received: " + names + " | opener: " + (window.opener ? "yes" : "no");
</script>
HTML);
})->name('kingschat.callback');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Forgot / reset password (guests, via emailed link)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    // NOTE: must be named "password.reset" - Laravel's reset email links to it
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Change password from inside the app (all authenticated roles)
Route::middleware('auth')->group(function () {
    Route::get('/account/password', [ChangePasswordController::class, 'edit'])->name('password.change');
    Route::put('/account/password', [ChangePasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.change.update');
});

// In-app notifications (the bell) — all authenticated roles
Route::middleware('auth')->group(function () {
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
});

// Web Push subscription endpoint
Route::post('/push/subscribe', function (Request $request) {
    $request->user()->updatePushSubscription(
        $request->endpoint,
        $request->keys['p256dh'] ?? null,
        $request->keys['auth'] ?? null
    );
    return response()->json(['success' => true]);
})->middleware('auth')->name('push.subscribe');

// Staff
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    // Tasks
    Route::get('/tasks', [TaskLogController::class, 'staffIndex'])->name('tasks');
    Route::post('/tasks', [TaskLogController::class, 'staffStore'])->name('tasks.store');
    Route::put('/tasks/{taskLog}', [TaskLogController::class, 'staffUpdate'])->name('tasks.update');
    Route::delete('/tasks/{taskLog}', [TaskLogController::class, 'staffDestroy'])->name('tasks.destroy');
    // Appraisal
    Route::get('/appraisal', [AppraisalController::class, 'staffShow'])->name('appraisal');
    Route::post('/appraisal/{appraisal}/save', [AppraisalController::class, 'staffSave'])->name('appraisal.save');
    Route::post('/appraisal/{appraisal}/submit', [AppraisalController::class, 'staffSubmit'])->name('appraisal.submit');
    Route::post('/appraisal/{appraisal}/competencies', [AppraisalController::class, 'staffSaveCompetencies'])->name('appraisal.competencies.save');
    // Appraisal history (past cycles, view-only once no longer drafting)
    Route::get('/appraisal/history', [AppraisalController::class, 'staffHistory'])->name('appraisal.history');
    Route::get('/appraisal/{appraisal}/view', [AppraisalController::class, 'staffShowAny'])->name('appraisal.view');
});

// Supervisor group (corrected)
Route::middleware(['auth'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/pipeline', [SupervisorController::class, 'pipeline'])->name('pipeline');
    Route::get('/supervisors', [SupervisorController::class, 'supervisors'])->name('supervisors');
    // Tasks
    Route::get('/tasks', [TaskLogController::class, 'supervisorIndex'])->name('tasks');
    Route::get('/tasks/staff/{staff}', [TaskLogController::class, 'supervisorStaff'])->name('tasks.staff');
    Route::post('/tasks/{taskLog}/grade', [TaskLogController::class, 'supervisorGrade'])->name('tasks.grade');
    // Appraisal history (all staff I supervise, across every cycle) — must come before {appraisal} below
    Route::get('/appraisal/history', [AppraisalController::class, 'supervisorHistory'])->name('appraisal.history');
    // Appraisal
    Route::get('/appraisal/{appraisal}', [AppraisalController::class, 'supervisorShow'])->name('appraisal.show');
    Route::post('/appraisal/{appraisal}/save', [AppraisalController::class, 'supervisorSave'])->name('appraisal.save');
    Route::post('/appraisal/{appraisal}/forward', [AppraisalController::class, 'supervisorForward'])->name('appraisal.forward');
    // Per-row comment (KRA / task / idea) saved from the comment drawer
    Route::post('/appraisal/{appraisal}/comment', [AppraisalCommentController::class, 'store'])->name('appraisal.comment');
});

// Staff Performance (Maintains prefix and named routes as 'hr' to match your layouts)
Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', [StaffPerformanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [StaffPerformanceController::class, 'users'])->name('users');
    Route::put('/users/{user}', [StaffPerformanceController::class, 'updateUser'])->name('users.update');
    Route::post('/users', [StaffPerformanceController::class, 'storeUser'])->name('users.store');
    Route::delete('/users/{user}', [StaffPerformanceController::class, 'deleteUser'])->name('users.delete');
    Route::get('/assignments', [StaffPerformanceController::class, 'assignments'])->name('assignments');
    Route::post('/assignments/{user}', [StaffPerformanceController::class, 'updateAssignment'])->name('assignments.update');
    Route::get('/cycles', [StaffPerformanceController::class, 'cycles'])->name('cycles');
    Route::post('/cycles', [StaffPerformanceController::class, 'storeCycle'])->name('cycles.store');
    Route::patch('/cycles/{cycle}/extend', [StaffPerformanceController::class, 'extendCycle'])->name('cycles.extend');
    Route::delete('/cycles/{cycle}', [StaffPerformanceController::class, 'destroyCycle'])->name('cycles.destroy');

    // KingsChat notification sender: HR connects the KingsChat account that messages are sent from
    Route::get('/kingschat', [KingsChatSenderController::class, 'show'])->name('kingschat');
    Route::post('/kingschat', [KingsChatSenderController::class, 'store'])->name('kingschat.store');

    // HR bell: live feed of submitted appraisals (full name: hr.notifications.appraisals)
    Route::get('/notifications/appraisals', [HrNotificationController::class, 'appraisals'])
        ->name('notifications.appraisals');

    // Task logs: supervisor list, then one supervisor's staff and their logs ("none" = staff with no supervisor)
    Route::get('/tasks', [TaskLogController::class, 'hrIndex'])->name('tasks');
    Route::get('/tasks/supervisor/{supervisor}', [TaskLogController::class, 'hrSupervisor'])
        ->where('supervisor', '[0-9]+|none')
        ->name('tasks.supervisor');

    // Appraisal history (every appraisal, across every cycle) — must come before {appraisal} below
    Route::get('/appraisal/history', [AppraisalController::class, 'hrHistory'])->name('appraisal.history');
    Route::get('/appraisal/{appraisal}', [AppraisalController::class, 'hrShow'])->name('appraisal.show');
    Route::post('/appraisal/{appraisal}/auto-calculate', [AppraisalController::class, 'hrAutoCalculate'])->name('appraisal.calculate');
    Route::post('/appraisal/{appraisal}/approve', [AppraisalController::class, 'hrApprove'])->name('appraisal.approve');
    Route::post('/appraisal/{appraisal}/ai-comment', [AppraisalController::class, 'aiComment'])->name('appraisal.ai-comment');
});

// PDF export (all authenticated roles)
Route::middleware(['auth'])->get('/appraisal/{appraisal}/pdf', [AppraisalController::class, 'exportPdf'])->name('appraisal.pdf');
