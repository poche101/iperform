<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\StaffPerformanceController; // Updated Controller Import
use App\Http\Controllers\AppraisalController;
use App\Http\Controllers\TaskLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// Offline fallback page for PWA
Route::get('/offline', fn() => view('offline'))->name('offline');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

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
    Route::delete('/tasks/{taskLog}', [TaskLogController::class, 'staffDestroy'])->name('tasks.destroy');
    // Appraisal
    Route::get('/appraisal', [AppraisalController::class, 'staffShow'])->name('appraisal');
    Route::post('/appraisal/{appraisal}/save', [AppraisalController::class, 'staffSave'])->name('appraisal.save');
    Route::post('/appraisal/{appraisal}/submit', [AppraisalController::class, 'staffSubmit'])->name('appraisal.submit');
});

// Supervisor
Route::middleware(['auth'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/pipeline', [SupervisorController::class, 'pipeline'])->name('pipeline');
    Route::get('/supervisors', [SupervisorController::class, 'supervisors'])->name('supervisors');
    // Tasks
    Route::get('/tasks', [TaskLogController::class, 'supervisorIndex'])->name('tasks');
    Route::post('/tasks/{taskLog}/grade', [TaskLogController::class, 'supervisorGrade'])->name('tasks.grade');
    // Appraisal
    Route::get('/appraisal/{appraisal}', [AppraisalController::class, 'supervisorShow'])->name('appraisal.show');
    Route::post('/appraisal/{appraisal}/save', [AppraisalController::class, 'supervisorSave'])->name('appraisal.save');
    Route::post('/appraisal/{appraisal}/forward', [AppraisalController::class, 'supervisorForward'])->name('appraisal.forward');
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
    Route::get('/tasks', [TaskLogController::class, 'hrIndex'])->name('tasks');
    Route::get('/appraisal/{appraisal}', [AppraisalController::class, 'hrShow'])->name('appraisal.show');
    Route::post('/appraisal/{appraisal}/auto-calculate', [AppraisalController::class, 'hrAutoCalculate'])->name('appraisal.calculate');
    Route::post('/appraisal/{appraisal}/approve', [AppraisalController::class, 'hrApprove'])->name('appraisal.approve');
    Route::post('/appraisal/{appraisal}/ai-comment', [AppraisalController::class, 'aiComment'])->name('appraisal.ai-comment');
});

// PDF export (all authenticated roles)
Route::middleware(['auth'])->get('/appraisal/{appraisal}/pdf', [AppraisalController::class, 'exportPdf'])->name('appraisal.pdf');
