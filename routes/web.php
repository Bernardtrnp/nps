<?php

use Illuminate\Support\Facades\Route;

// Upload & Data Management
use App\Http\Controllers\RiskUploadController;
use App\Http\Controllers\RiskVarController;
use App\Http\Controllers\RiskOverviewController;

// Dashboard
use App\Http\Controllers\RiskAnalysisController;

// Statistical Modules (dipisah)
use App\Http\Controllers\StatisticalAnalysisController;
use App\Http\Controllers\RiskSummaryController;


// -----------------------------------------
// HOME
// -----------------------------------------
Route::get('/', fn() => view('risk.home'))->name('risk.home');


// -----------------------------------------
// DASHBOARD
// -----------------------------------------
Route::get('/risk/dashboard', [RiskAnalysisController::class, 'index'])
    ->name('risk.dashboard');


// -----------------------------------------
// VARIABLE LIST + EXPORT
// -----------------------------------------
Route::get('/risk/var', [RiskVarController::class, 'index'])->name('risk.var');
Route::get('/risk/var/export', [RiskVarController::class, 'export'])->name('risk.var.export');

Route::get('/risk/template/download', [RiskUploadController::class, 'downloadTemplate'])
    ->name('risk.template.download');


// -----------------------------------------
// IMPORT
// -----------------------------------------
Route::get('/risk/import', [RiskUploadController::class, 'form'])->name('risk.import.form');
Route::post('/risk/import', [RiskUploadController::class, 'store'])->name('risk.import.store');
Route::get('/risk/import/template', [RiskUploadController::class, 'downloadTemplate'])->name('risk.import.template');
Route::delete('/risk/import/wipe', [RiskUploadController::class, 'wipe'])->name('risk.import.wipe');


// -----------------------------------------
// OVERVIEW CACHE
// -----------------------------------------
Route::get('/risk/overview', [RiskOverviewController::class, 'index'])->name('risk.overview');
Route::post('/risk/overview/flush', [RiskOverviewController::class, 'flush'])->name('risk.overview.flush');


// -----------------------------------------
// STATISTICAL ANALYSIS
// -----------------------------------------
Route::get('/risk/analysis', [StatisticalAnalysisController::class, 'index'])
    ->name('risk.analysis');

Route::post('/risk/analysis/montecarlo', [StatisticalAnalysisController::class, 'monteCarlo'])
    ->name('risk.analysis.montecarlo');

Route::get('/risk/analysis/export/{riskTypeId}', [StatisticalAnalysisController::class, 'exportPdf'])
    ->name('risk.analysis.export');


// -----------------------------------------
// SUMMARY (CVaR Ranking)
// -----------------------------------------
Route::get('/risk/summary', [RiskSummaryController::class, 'index'])
    ->name('risk.summary');
