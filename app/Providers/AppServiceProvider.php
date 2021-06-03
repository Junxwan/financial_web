<?php

namespace App\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request, Factory $view, Dispatcher $events, Repository $config)
    {
        $this->registerMenu($request, $view);
    }

    /**
     * Register the package's view composers.
     *
     * @return void
     */
    private function registerMenu(Request $request, Factory $view)
    {
        $view->share([
            'menu' => config('menu'),
            'url' => $request->url(),
            'time' => time(),
        ]);
    }
}
