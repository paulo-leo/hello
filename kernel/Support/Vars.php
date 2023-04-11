<?php
namespace Kernel\Support;

 class Vars
 {
   private static $vars = [];

   public static function set($key,$val)
   {
      self::$vars[$key] = $val;
   }

   public static function get($key)
   {
      return self::$vars[$key] ?? '';
   }
 }