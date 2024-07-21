<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client([
                'base_uri' => 'https://www.googleapis.com', // Set your base URI here
                'timeout'  => 2.0, // Set your timeout here
            ]);
        });
        // Explicitly bind GuzzleHttp\ClientInterface to GuzzleHttp\Client
        $this->app->bind(ClientInterface::class, function ($app) {
            return new Client();
        });
        // Bind Firebase Auth to its factory implementation
        $this->app->singleton(Auth::class, function ($app) {
            $factory = (new Factory)->withServiceAccount(config('firebase.projects.app.credentials.file'));

            return $factory->createAuth();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
