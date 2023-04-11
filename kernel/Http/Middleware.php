<?php

namespace Kernel\Http;

class Middleware
{
 protected function handle($method,$route)
 {
   return true;
 }

 protected function message()
 {
    return 'HTTP route blocked by Middleware.';
 }
} 