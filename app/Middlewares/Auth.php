<?php
namespace App\Middlewares;

use Kernel\Http\Middleware;

class Auth extends Middleware
{
   public function handle($method,$route)
   {
      return false;
   }

   public function message()
   {
      return 'Filtro não localizado';
   }
}