<?php

namespace Fyi\Infinitum;

use Illuminate\Support\ServiceProvider;

class InfinitumServiceProvider extends ServiceProvider 
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Infinitum::class, function () {
            return new Infinitum();
        });
        
        $this->app->alias(Infinitum::class, 'infinitum');
    }
}
