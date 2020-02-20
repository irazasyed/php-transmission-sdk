<?php

namespace Transmission\Laravel;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Transmission\Client;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Boot the Service Provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/transmission.php' => config_path('transmission.php'),
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('transmission');
        }
    }

    /**
     * Register the Service.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/transmission.php', 'transmission');

        $this->app->singleton('transmission', function () {
            $client = new Client(
                config('transmission.host'),
                config('transmission.port'),
                config('transmission.username'),
                config('transmission.password')
            );

            if (config('transmission.enableTLS')) {
                $client = $client->enableTLS();
            }

            return $client;
        });

        $this->app->alias('transmission', Client::class);
    }
}
