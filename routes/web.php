<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

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

Route::get('/', [\App\Http\Controllers\IndexController::class, 'index'])->name('index');

// 新聞
Route::get('/news', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/news/list', [\App\Http\Controllers\NewsController::class, 'list'])->name('news.list');
Route::put('/news/{id}', [\App\Http\Controllers\NewsController::class, 'update'])->name('news.update');
Route::delete('/news/{id}', [\App\Http\Controllers\NewsController::class, 'delete'])->name('news.delete');
Route::post('/news/clear', [\App\Http\Controllers\NewsController::class, 'clear'])->name('news.clear');

// 個股
Route::get('/stock', [\App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
Route::get('/stock/{code}/info', [\App\Http\Controllers\StockController::class, 'search'])->name('stock.search');
Route::get('/stock/list', [\App\Http\Controllers\StockController::class, 'list'])->name('stock.list');
Route::post('/stock', [\App\Http\Controllers\StockController::class, 'create'])->name('stock.create');
Route::delete('/stock/{id}', [\App\Http\Controllers\StockController::class, 'delete'])->name('stock.delete');
Route::put('/stock/{id}', [\App\Http\Controllers\StockController::class, 'update'])->name('stock.update');

// 投資報告
Route::get('/report', [\App\Http\Controllers\ReportController::class, 'index'])->name('report.index');
Route::get('/report/{id}/edit', [\App\Http\Controllers\ReportController::class, 'edit'])->name('report.edit');
Route::get('/report/create', [\App\Http\Controllers\ReportController::class, 'createView'])->name('report.create.view');
Route::get('/report/list', [\App\Http\Controllers\ReportController::class, 'list'])->name('report.list');
Route::post('/report', [\App\Http\Controllers\ReportController::class, 'create'])->name('report.create');
Route::put('/report/{id}', [\App\Http\Controllers\ReportController::class, 'update'])->name('report.update');
Route::delete('/report/{id}', [\App\Http\Controllers\ReportController::class, 'delete'])->name('report.delete');

// 綜合損益表
Route::get('/profit', [\App\Http\Controllers\ProfitController::class, 'index'])->name('profit.index');
Route::get('/profit/{code}/code/{year}/year/{season}/season',
    [\App\Http\Controllers\ProfitController::class, 'get'])->name('profit.get');
Route::get('/profit/{code}/code/{year}/year',
    [\App\Http\Controllers\ProfitController::class, 'year'])->name('profit.year');

// 月營收
Route::get('/revenue/{code}/code/{year}/year',
    [\App\Http\Controllers\RevenueController::class, 'year'])->name('revenue.year');
Route::get('/revenue/recent/{code}/code/{year}/year/{month}/monyh',
    [\App\Http\Controllers\RevenueController::class, 'recent'])->name('revenue.recent');
