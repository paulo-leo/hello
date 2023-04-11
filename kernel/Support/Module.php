<?php
namespace Kernel\Support;

use Kernel\FS\ReadArray;
class Module
{
   public function main(){}
   protected function on(){}
   protected function off(){}
   final public function eventOn(){  $this->on(); }
   final public function eventOff(){ $this->off(); }
  
   public function loadModules()
   {
     
   }

   final public static function load()
   {
      $modules = new ReadArray('config/modules.php');
      $modules = $modules->all();

      foreach($modules as $module=>$status)
      {
         if($status == 'on')
         {
            $config = __DIR__."/../../modules/{$module}/mod.xml";
            if(file_exists($config))
            {
              $class = "Modules\\{$module}\\{$module}";
              if(class_exists($class) && is_subclass_of($class,"Kernel\\Support\\Module"))
              {
                 call_user_func(array(new $class, 'main'));
              }
           }
         }
      }
   }

   public static function getInfo($module)
   {
     $file = __DIR__."/../../modules/{$module}/mod.xml";
     $info = array();
     if(file_exists($file))
     {

     $xml = simplexml_load_file($file);
 
     $info = array(
       'name' =>$xml->name ?? '',
       'uri' =>$xml->uri ?? '',
       'description' => $xml->description ?? '',
       'version' => $xml->version ?? '',
       'author ' => $xml->author ?? '',
       'image' => $xml->image ?? '',
       'category' => $xml->category ?? '',
       'requires_at_least' => $xml->requires_at_least ?? ''
     );
 
     $keywords = array();
     $requires = array();
 
  
      if(isset($xml->keyworks->keywork))
      {
        foreach ($xml->keyworks->keywork as $keyword)
        {
           $keywords[] = (string) $keyword;
        }  
      }

      if(isset($xml->requires->require))
      {
       foreach ($xml->requires->require as $require)
       {
        $requires[] = (string) $require;
       }
     }
 
     $info['keywords'] = $keywords;
     $info['requires'] = $requires;

    }
 
     return (array) $info;
   }
}