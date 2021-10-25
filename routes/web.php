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
Route::group(['prefix' => 'news', 'as' => 'news.'], function () {
    Route::get('/', [\App\Http\Controllers\News\IndexController::class, 'index'])->name('index');
    Route::get('/list', [\App\Http\Controllers\News\IndexController::class, 'list'])->name('list');
    Route::put('/{id}', [\App\Http\Controllers\News\IndexController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\News\IndexController::class, 'delete'])->name('delete');
    Route::post('/clear', [\App\Http\Controllers\News\IndexController::class, 'clear'])->name('clear');

    // 關鍵字
    Route::get('/keyWord',
        [\App\Http\Controllers\News\KeyWordController::class, 'index'])->name('keyWord.index');
    Route::get('/keyWord/list',
        [\App\Http\Controllers\News\KeyWordController::class, 'list'])->name('keyWord.list');
    Route::post('/keyWord',
        [\App\Http\Controllers\News\KeyWordController::class, 'create'])->name('keyWord.create');
    Route::put('/keyWord/{id}',
        [\App\Http\Controllers\News\KeyWordController::class, 'update'])->name('keyWord.update');
    Route::delete('/keyWord/{id}',
        [\App\Http\Controllers\News\KeyWordController::class, 'delete'])->name('keyWord.delete');
});

// 個股
Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {
    Route::get('/', [\App\Http\Controllers\Stock\IndexController::class, 'index'])->name('index');
    Route::get('/{code}/info', [\App\Http\Controllers\Stock\IndexController::class, 'search'])->name('search');
    Route::get('/list', [\App\Http\Controllers\Stock\IndexController::class, 'list'])->name('list');
    Route::post('/', [\App\Http\Controllers\Stock\IndexController::class, 'create'])->name('create');
    Route::delete('/{id}', [\App\Http\Controllers\Stock\IndexController::class, 'delete'])->name('delete');
    Route::put('/{id}', [\App\Http\Controllers\Stock\IndexController::class, 'update'])->name('update');
    Route::get('/{code}/code', [\App\Http\Controllers\Stock\IndexController::class, 'name'])->name('name');
    Route::get('/{tag}/tag',
        [\App\Http\Controllers\Stock\IndexController::class, 'namesByTag'])->name('names.tag');

    // 投信持股(for 個股)
    Route::get('/fund', [\App\Http\Controllers\Stock\FundControllers::class, 'index'])->name('fund.index');
    Route::get('/fund/{code}/code/{year}/year',
        [\App\Http\Controllers\Stock\FundControllers::class, 'list'])->name('fund.list');

    # 個股股價
    Route::get('/price', [\App\Http\Controllers\Stock\PriceController::class, 'index'])->name('price.index');
    Route::get('/price/list',
        [\App\Http\Controllers\Stock\PriceController::class, 'list'])->name('price.list');
    Route::get('/last/date',
        [\App\Http\Controllers\Stock\PriceController::class, 'date'])->name('price.last.date');
});

// 標籤
Route::group(['prefix' => 'tag', 'as' => 'tag.'], function () {
    Route::get('/', [\App\Http\Controllers\Stock\TagController::class, 'index'])->name('index');
    Route::get('/list', [\App\Http\Controllers\Stock\TagController::class, 'list'])->name('list');
    Route::post('/', [\App\Http\Controllers\Stock\TagController::class, 'create'])->name('create');
    Route::delete('/{id}', [\App\Http\Controllers\Stock\TagController::class, 'delete'])->name('delete');
    Route::put('/{id}', [\App\Http\Controllers\Stock\TagController::class, 'update'])->name('update');
});

// 投資報告
Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
    Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/{id}/edit', [\App\Http\Controllers\ReportController::class, 'edit'])->name('edit');
    Route::get('/create', [\App\Http\Controllers\ReportController::class, 'createView'])->name('create.view');
    Route::get('/list', [\App\Http\Controllers\ReportController::class, 'list'])->name('list');
    Route::post('', [\App\Http\Controllers\ReportController::class, 'create'])->name('create');
    Route::put('/{id}', [\App\Http\Controllers\ReportController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\ReportController::class, 'delete'])->name('delete');
});

// 綜合損益表
Route::group(['prefix' => 'profit', 'as' => 'profit.'], function () {
    Route::get('/', [\App\Http\Controllers\Financial\ProfitController::class, 'index'])->name('index');
    Route::get('/{code}/code/{year}/year/{season}/season',
        [\App\Http\Controllers\Financial\ProfitController::class, 'get'])->name('get');
    Route::get('/{code}/code/{year}/year',
        [\App\Http\Controllers\Financial\ProfitController::class, 'year'])->name('year');
    Route::get('/recent/{code}/code/{year}/year/{quarterly}/quarterly',
        [\App\Http\Controllers\Financial\ProfitController::class, 'recent'])->name('recent');
    Route::get('/eps/{code}/code',
        [\App\Http\Controllers\Financial\ProfitController::class, 'eps'])->name('eps');
    Route::get('/dividend/{code}/code',
        [\App\Http\Controllers\Financial\ProfitController::class, 'dividend'])->name('dividend');
    Route::get('/rank',
        [\App\Http\Controllers\Financial\ProfitController::class, 'rankIndex'])->name('rank.index');
    Route::get('/rank/{year}/year/{season}/season/{name}',
        [\App\Http\Controllers\Financial\ProfitController::class, 'rank'])->name('rank');
    Route::get('download/{year}/year/{quarterly}/quarterly',
        [\App\Http\Controllers\Financial\ProfitController::class, 'download'])->name('download');
    Route::get('code/{year}/year/{quarterly}/quarterly',
        [\App\Http\Controllers\Financial\ProfitsController::class, 'quarterly'])->name('codes');
});

// 損益比較
Route::group(['prefix' => 'profits', 'as' => 'profits.'], function () {
    Route::get('/', [\App\Http\Controllers\Financial\ProfitsController::class, 'index'])->name('index');
});

// 現金流量表
Route::get('/cash/recent/{code}/code/{year}/year/{quarterly}/quarterly',
    [\App\Http\Controllers\Financial\CashController::class, 'recent'])->name('cash.recent');

// 月營收
Route::group(['prefix' => 'revenue', 'as' => 'revenue.'], function () {
    Route::get('/{code}/code/{year}/year',
        [\App\Http\Controllers\RevenueController::class, 'year'])->name('year');
    Route::get('/recent/{code}/code/{year}/year/{month}/month',
        [\App\Http\Controllers\RevenueController::class, 'recent'])->name('recent');
    Route::get('/{year}/year/{month}/month/last',
        [\App\Http\Controllers\MonthRevenuesController::class, 'last'])->name('last');

    // 排行
    Route::get('/rank/month',
        [\App\Http\Controllers\MonthRevenuesController::class, 'index'])->name('rank.month.index');
    Route::get('/rank/month/list',
        [\App\Http\Controllers\MonthRevenuesController::class, 'list'])->name('rank.month.list');
    Route::get('/rank/{year}/year/{month}/month/download',
        [\App\Http\Controllers\MonthRevenuesController::class, 'download'])->name('rank.download');
});

// 投信持股
Route::group(['prefix' => 'fund', 'as' => 'fund.'], function () {
    Route::get('/', [\App\Http\Controllers\Fund\IndexController::class, 'index'])->name('index');
    Route::get('/list/{id}', [\App\Http\Controllers\Fund\IndexController::class, 'funds'])->name('list');
    Route::get('/stocks/{year}/year/{fundId}/fund',
        [\App\Http\Controllers\Fund\IndexController::class, 'stocks'])->name('stocks');

    Route::get('/detail', [\App\Http\Controllers\Fund\DetailController::class, 'index'])->name('detail.index');
    Route::get('/scale/{id}', [\App\Http\Controllers\Fund\DetailController::class, 'scale'])->name('detail.scale');
    Route::get('/value/{id}', [\App\Http\Controllers\Fund\DetailController::class, 'value'])->name('detail.value');
});

// 類股
Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
    Route::get('/', [\App\Http\Controllers\Stock\CategoryController::class, 'index'])->name('index');
    Route::get('/list',
        [\App\Http\Controllers\Stock\CategoryController::class, 'list'])->name('list');
});

// 產業
Route::group(['prefix' => 'industry', 'as' => 'industry.'], function () {
    Route::get('/', [\App\Http\Controllers\IndustryController::class, 'index'])->name('index');
    Route::get('/list',
        [\App\Http\Controllers\IndustryController::class, 'list'])->name('list');
    Route::get('/last/date',
        [\App\Http\Controllers\IndustryController::class, 'date'])->name('last.date');
});

// 產業指數
Route::group(['prefix' => 'exponent', 'as' => 'exponent.'], function () {
    Route::get('/', [\App\Http\Controllers\ExponentController::class, 'index'])->name('index');
    Route::get('/tag/{id}/year/{year}',
        [\App\Http\Controllers\ExponentController::class, 'tag'])->name('tag.k');
    Route::get('/profit/tag/{id}/year/{year}/quarterly/{quarterly}',
        [\App\Http\Controllers\ExponentController::class, 'tagProfit'])->name('tag.profit');
    Route::get('/k/tag/{id}/year/{year}',
        [\App\Http\Controllers\ExponentController::class, 'stockK'])->name('tag.stock.k');
});

// 股價
Route::group(['prefix' => 'price', 'as' => 'price.'], function () {
    Route::get('/', [\App\Http\Controllers\PriceController::class, 'index'])->name('index');
    Route::get('/list',
        [\App\Http\Controllers\PriceController::class, 'list'])->name('list');
    Route::get('/{code}/code',
        [\App\Http\Controllers\PriceController::class, 'price'])->name('code');
});

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
    Route::get('/price/{code}/conversion',
        [\App\Http\Controllers\Cb\PriceController::class, 'conversion'])->name('price.conversion');
});

// 匯出
Route::get('/export', [\App\Http\Controllers\ExportController::class, 'index'])->name('export.index');

