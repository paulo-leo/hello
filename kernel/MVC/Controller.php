<?php
namespace Kernel\MVC;

use Exception;

class Controller
{

   function __call($name,$params)
   {
      throw new Exception("Método \"{$name}\" não implementado.");
   }
}