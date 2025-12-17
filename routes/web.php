<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MqttScannerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SensorDataController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Public MQTT Scanner Routes (no authentication required)
Route::get('/mqtt/scan', [MqttScannerController::class, 'showScanForm'])->name('mqtt.scan.form');
Route::post('/mqtt/scan', [MqttScannerController::class, 'scan'])->name('mqtt.scan');
Route::get('/mqtt/results', [MqttScannerController::class, 'results'])->name('mqtt.results.endpoint');

// Dashboard Routes (No Authentication Required)
Route::get('/dashboard', [MqttScannerController::class, 'index'])->name('dashboard');

// Scan routes - Use Flask scanner (MqttScannerController) instead of direct PHP
Route::post('/scan', [MqttScannerController::class, 'scan'])->name('scan');
Route::get('/results', [MqttScannerController::class, 'results'])->name('results');

// Sensor data routes
Route::get('/sensors', [SensorDataController::class, 'index'])->name('sensors.index');
Route::get('/sensors/{id}', [SensorDataController::class, 'show'])->name('sensors.show');

// Dashboard with Authentication (Optional - for future use)
Route::middleware(['auth', 'verified'])->group(function () {
    // Protected routes can go here if needed in future
});

require __DIR__ . '/auth.php';

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {

    // Profile routes (provided by Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
