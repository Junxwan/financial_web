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
Route::get('/stock', [\App\Http\Controllers\Stock\IndexController::class, 'index'])->name('stock.index');
Route::get('/stock/{code}/info', [\App\Http\Controllers\Stock\IndexController::class, 'search'])->name('stock.search');
Route::get('/stock/list', [\App\Http\Controllers\Stock\IndexController::class, 'list'])->name('stock.list');
Route::post('/stock', [\App\Http\Controllers\Stock\IndexController::class, 'create'])->name('stock.create');
Route::delete('/stock/{id}', [\App\Http\Controllers\Stock\IndexController::class, 'delete'])->name('stock.delete');
Route::put('/stock/{id}', [\App\Http\Controllers\Stock\IndexController::class, 'update'])->name('stock.update');

# 標籤
Route::get('/tag', [\App\Http\Controllers\TagController::class, 'index'])->name('tag.index');
Route::get('/tag/list', [\App\Http\Controllers\TagController::class, 'list'])->name('tag.list');
Route::post('/tag', [\App\Http\Controllers\TagController::class, 'create'])->name('tag.create');
Route::delete('/tag/{id}', [\App\Http\Controllers\TagController::class, 'delete'])->name('tag.delete');
Route::put('/tag/{id}', [\App\Http\Controllers\TagController::class, 'update'])->name('tag.update');

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
Route::get('/profit/recent/{code}/code/{year}/year/{quarterly}/quarterly',
    [\App\Http\Controllers\ProfitController::class, 'recent'])->name('profit.recent');
Route::get('/profit/eps/{code}/code',
    [\App\Http\Controllers\ProfitController::class, 'eps'])->name('profit.eps');
Route::get('/profit/dividend/{code}/code',
    [\App\Http\Controllers\ProfitController::class, 'dividend'])->name('profit.dividend');
Route::get('/profit/rank', [\App\Http\Controllers\ProfitController::class, 'rankIndex'])->name('profit.rank.index');
Route::get('/profit/rank/{year}/year/{season}/season/{name}',
    [\App\Http\Controllers\ProfitController::class, 'rank'])->name('profit.rank');
Route::get('/profit/{year}/year/{quarterly}/quarterly/download',
    [\App\Http\Controllers\ProfitController::class, 'download'])->name('profit.download');

// 現金流量表
Route::get('/cash/recent/{code}/code/{year}/year/{quarterly}/quarterly',
    [\App\Http\Controllers\CashController::class, 'recent'])->name('cash.recent');

// 月營收
Route::get('/revenue/{code}/code/{year}/year',
    [\App\Http\Controllers\RevenueController::class, 'year'])->name('revenue.year');
Route::get('/revenue/recent/{code}/code/{year}/year/{month}/month',
    [\App\Http\Controllers\RevenueController::class, 'recent'])->name('revenue.recent');

// 投信持股
Route::get('/fund', [\App\Http\Controllers\FundController::class, 'index'])->name('fund.index');
Route::get('/fund/list/{id}', [\App\Http\Controllers\FundController::class, 'funds'])->name('fund.list');
Route::get('/fund/stocks/{year}/year/{fundId}/fund',
    [\App\Http\Controllers\FundController::class, 'stocks'])->name('fund.stocks');

// 投信持股(for 個股)
Route::get('/stock/fund', [\App\Http\Controllers\Stock\FundControllers::class, 'index'])->name('stock.fund.index');
Route::get('/stock/fund/{code}/code/{year}/year',
    [\App\Http\Controllers\Stock\FundControllers::class, 'list'])->name('stock.fund.list');

// 類股
Route::get('/category', [\App\Http\Controllers\CategoryController::class, 'index'])->name('category.index');
Route::get('/category/list',
    [\App\Http\Controllers\CategoryController::class, 'list'])->name('category.list');

// 產業
Route::get('/industry', [\App\Http\Controllers\IndustryController::class, 'index'])->name('industry.index');
Route::get('/industry/list',
    [\App\Http\Controllers\IndustryController::class, 'list'])->name('industry.list');
Route::get('/industry/last/date',
    [\App\Http\Controllers\IndustryController::class, 'date'])->name('industry.last.date');

// 產業指數
Route::get('/exponent', [\App\Http\Controllers\ExponentController::class, 'index'])->name('exponent.index');
Route::get('/exponent/tag/{id}/year/{year}',
    [\App\Http\Controllers\ExponentController::class, 'tag'])->name('exponent.tag.k');
Route::get('/exponent/profit/tag/{id}/year/{year}/quarterly/{quarterly}',
    [\App\Http\Controllers\ExponentController::class, 'tagProfit'])->name('exponent.tag.profit');
Route::get('/exponent/k/tag/{id}/year/{year}',
    [\App\Http\Controllers\ExponentController::class, 'stockK'])->name('exponent.tag.stock.k');

// 股價
Route::get('/price', [\App\Http\Controllers\PriceController::class, 'index'])->name('price.index');
Route::get('/price/list',
    [\App\Http\Controllers\PriceController::class, 'list'])->name('price.list');
Route::get('/price/{code}/code',
    [\App\Http\Controllers\PriceController::class, 'price'])->name('price');
Route::get('/price/last/date',
    [\App\Http\Controllers\PriceController::class, 'date'])->name('price.last.date');

# 個股股價
Route::get('/stock/price', [\App\Http\Controllers\Stock\PriceController::class, 'index'])->name('stock.price.index');
Route::get('/stock/price/list',
    [\App\Http\Controllers\Stock\PriceController::class, 'list'])->name('stock.price.list');

// 月營收排行
Route::get('/revenues/month',
    [\App\Http\Controllers\MonthRevenuesController::class, 'index'])->name('revenues.month.index');
Route::get('/revenues/month/list',
    [\App\Http\Controllers\MonthRevenuesController::class, 'list'])->name('revenues.month.list');
Route::get('/revenues/{year}/year/{month}/month/download',
    [\App\Http\Controllers\MonthRevenuesController::class, 'download'])->name('revenues.download');


// 可轉債
Route::group(['prefix' => 'cb', 'as' => 'cb.'], function () {
    # 個股
    Route::get('/', [\App\Http\Controllers\Cb\IndexController::class, 'index'])->name('index');
    Route::get('/list', [\App\Http\Controllers\Cb\IndexController::class, 'list'])->name('list');

    # 餘額變化
    Route::get('/stock/balance',
        [\App\Http\Controllers\Cb\BalanceController::class, 'index'])->name('balance.index');
    Route::get('/stock/balance/list',
        [\App\Http\Controllers\Cb\BalanceController::class, 'list'])->name('balance.list');
    Route::get('/stock/{code}/balance',
        [\App\Http\Controllers\Cb\BalanceController::class, 'get'])->name('balance');

    # 價格
    Route::get('/price', [\App\Http\Controllers\Cb\PriceController::class, 'index'])->name('price.index');
    Route::get('/price/{code}', [\App\Http\Controllers\Cb\PriceController::class, 'get'])->name('price');
    Route::get('/price/{code}/month', [\App\Http\Controllers\Cb\PriceController::class, 'month'])->name('price.month');
    Route::get('/price/last/date', [\App\Http\Controllers\Cb\PriceController::class, 'date'])->name('price.last.date');
    Route::get('/price/{code}/premium',
        [\App\Http\Controllers\Cb\PriceController::class, 'premium'])->name('price.premium');
});

// 匯出
Route::get('/export', [\App\Http\Controllers\ExportController::class, 'index'])->name('export.index');

