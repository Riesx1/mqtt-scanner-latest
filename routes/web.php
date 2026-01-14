<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MqttScannerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SensorDataController;

// Welcome page (Public)
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

require __DIR__ . '/auth.php';

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {

    // Dashboard - Protected
    Route::get('/dashboard', [MqttScannerController::class, 'index'])->name('dashboard');

    // MQTT Scanner Routes - Protected
    Route::get('/mqtt/scan', [MqttScannerController::class, 'showScanForm'])->name('mqtt.scan.form');
    Route::post('/mqtt/scan', [MqttScannerController::class, 'scan'])->name('mqtt.scan');
    Route::get('/mqtt/results', [MqttScannerController::class, 'results'])->name('mqtt.results.endpoint');

    // Scan routes - Protected
    Route::post('/scan', [MqttScannerController::class, 'scan'])->name('scan');
    Route::get('/results', [MqttScannerController::class, 'results'])->name('results');

    // Scan History API routes - Protected
    Route::get('/api/scan-history', [MqttScannerController::class, 'scanHistory'])->name('api.scan-history');
    Route::get('/api/scan-history/{id}', [MqttScannerController::class, 'scanHistoryResults'])->name('api.scan-history.results');

    // Sensor data routes - Protected
    Route::get('/sensors', [SensorDataController::class, 'index'])->name('sensors.index');
    Route::get('/sensors/{id}', [SensorDataController::class, 'show'])->name('sensors.show');

    // Profile routes (provided by Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
