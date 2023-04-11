<?php
namespace Kernel\MVC;

class Controller
{

   protected $dep = array();

   public function __construct()
   {
      $this->dep = build($this->dep);
   }

   function __call($name,$params)
   {
      return "Método \"{$name}\" não implementado.";
   }
}