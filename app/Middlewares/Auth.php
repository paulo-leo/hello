<?php
namespace App\Middlewares;

use Kernel\Http\Middleware;
use Kernel\Http\Auth as Login;


class Auth extends Middleware
{
   public function handle($method,$route)
   {
      if(Login::check()) return true;
   }

   public function message()
   {
      return 'Filtro não localizado';
   }
}