<?php
namespace Kernel\Support;

 class CLI
 {
   private $line;
   private $args;
   private $keys = array();
   private $first;

   public function __construct($line)
   {
     $this->line = $line;
     $args = $line;
     unset($args[0]);
     $this->first = $args[1];
     unset($args[1]);
     $this->args = array_values($args);
     $this->loopKeys();

   }

   final protected function dir($path=null,$root=false)
   {
     return !$root ? dirname(__DIR__) . "/../{$path}" : 
     dirname(__DIR__) . "/{$path}";
   }

   final protected function hasFile(string $file)
   {
      $file = $this->dir($file);
      return file_exists($file);
   }

   final protected function first() : mixed
   {
      $value = explode(':',$this->first);
      return isset($value[1]) ? $value[1] : $value[0];
   }
  
    final protected function print($value, $color = null): void
    {
        switch ($color) {
            case 'black':
                $color = "\033[30m";
                break;
            case 'red':
                $color = "\033[31m";
                break;
            case 'green':
                $color = "\033[32m";
                break;
            case 'yellow':
                $color = "\033[33m";
                break;
            case 'blue':
                $color = "\033[34m";
                break;
            case 'magenta':
                $color = "\033[35m";
                break;
            case 'cyan':
                $color = "\033[36m";
                break;
            case 'white':
                $color = "\033[37m";
                break;
            case 'pink':
                $color = "\033[38;5;206m";
                break;
            default:
                $color = "\033[0m";
        }
        echo "{$color}{$value}\033[0m\n";
    }
    

   final protected function position(int $index) : mixed
   {
     if($index < 1) $index = 1;
     $index--;
     return $this->args[$index] ?? null;
   }

   private function loopKeys()
   {
      foreach($this->args as $value)
      {
         $value = explode('=',$value);
         $key = $value[0];
         $value = isset($value[1]) ? $value[1] : $key;
         $this->keys[$key] = $value;
      }
   }

   final protected function line($header=true)
   {
     $line = $this->line;
     if(!$header)
     {
       unset($line[0]);
       unset($line[1]);
     }
     return implode(' ',$line);
   }

   final protected function key($key)
   {
     $value = $this->keys[$key] ?? null;
     return ($value != $key) ? $value : null;
   }

   public function main()
   {
      
   }
 }
