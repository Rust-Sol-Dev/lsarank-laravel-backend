<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\AuthController;
use App\Http\Livewire\UserManagement;
use App\Http\Livewire\KeywordMetrics;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\BillingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('keyword/{keyword}/destroy', [KeywordController::class, 'destroy'])->name('keyword.destroy');
    Route::get('keyword/list/download', [KeywordController::class, 'downloadList'])->name('keyword.download.list');
    Route::get('billing', [BillingController::class, 'index'])->name('billing');
    Route::get('reports', [ReportController::class, 'list'])->name('reports');
    Route::get('reports/{hash}/download', [ReportController::class, 'download'])->name('download');
    Route::get('auth/logout', [AuthController::class, 'destroy'])->name('auth.logout');
    Route::get('admin', UserManagement::class)->name('admin')->middleware('role:Admin');;
    Route::get('keyword/{keyword}', KeywordMetrics::class)->name('keyword.metrics');
});

//Route::get('map/{heatMap}', [ReportController::class, 'renderMap'])->name('map-report');
Route::get('map/{heatMap}', [ReportController::class, 'renderMap'])->name('map-report')->middleware('mapGeneration');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
