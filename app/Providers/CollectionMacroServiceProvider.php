<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class CollectionMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('dot', function($key, $default = null) {
            $value = Arr::get($this->all(), $key);
            return $value ?? $default;
        });

        Collection::macro('dotc', function($key, $default = null) {
            $value = Arr::get($this->all(), $key);
            return collect($value) ?? collect($default ?? []);
        });
    }
}
