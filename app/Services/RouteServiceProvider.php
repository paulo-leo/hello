<?php

namespace App\Services;

use Kernel\Router\Route;
use Kernel\Support\Module;
use Kernel\Http\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
   public function boot()
   {
      Module::load();
      Route::loadFile("web");
   }
}
