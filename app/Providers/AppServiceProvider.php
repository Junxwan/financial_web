<?php

namespace App\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Http\ViewComposers\AdminLteComposer;

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
    public function boot(Factory $view, Dispatcher $events, Repository $config)
    {
        $this->registerViewComposers($view);
    }

    /**
     * Register the package's view composers.
     *
     * @return void
     */
    private function registerViewComposers(Factory $view)
    {
        $view->composer('page', AdminLteComposer::class);
    }
}
