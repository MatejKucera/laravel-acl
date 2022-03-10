<?php

namespace MatejKucera\LaravelAcl;

use Illuminate\Support\ServiceProvider;

class AclServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/acl.php' => config_path('acl.php'),
        ], 'config');

    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/acl.php', 'acl'
        );
    }
}