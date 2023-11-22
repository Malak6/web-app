<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Go\Core\AspectKernel;
use Go\Core\AspectContainer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    $this->app->singleton(AspectKernel::class, function () {
        return AspectKernel::getInstance();
    });

    $this->app->singleton(AspectContainer::class, function () {
        /*
        $container = $this->app->make(AspectKernel::class)->getContainer();
        $container->registerAspect(LoggingAspect::class);
        $container->registerAspect(MessageAspect::class); // تسجيل الجانب MessageAspect

        return $container;*/
    });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
