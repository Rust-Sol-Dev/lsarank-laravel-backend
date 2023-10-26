<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('sync/zipcode/radius', [\App\Http\Controllers\SyncController::class, 'syncRadius'])->name('sync.radius');
Route::get('test', [\App\Http\Controllers\ReportController::class, 'generatePdf']);
Route::get('test/mail', [\App\Http\Controllers\ReportController::class, 'sendPDFoverEmail']);
