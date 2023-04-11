<?php

namespace App\Services;

use Kernel\Http\ServiceProvider;

class CSRF extends ServiceProvider
{
   private static $token;
   private $token_name = 'np_csrf_token';

   public function boot()
   {
      $this->register('csrf');
      $this->generateToken();
      
      $is_method = array('get','term');
      $is_method = in_array($this->method,$is_method);

      if (!$is_method  && !$this->isPrefix('api'))
      {
         $token = getallheaders()['X-CSRF-TOKEN'] ?? false;

         if (!$token) {
            $token = request('_token');
         }

         if ($token != self::$token) {
            $this->stop('Token CSRF inválido!');
         }
      }
   }

   public function token()
   {
      return self::$token;
   }

   private function generateToken()
   {
      if(!$this->isMethod('term'))
      {
        if (!session()->has($this->token_name)) {
             session([$this->token_name => secret(date('YmdHs'))]);
          }
         self::$token = session()->get($this->token_name);
     }
   }
}
