<?php
namespace Kernel\Router;

use Kernel\Router\Route;
use Kernel\FS\ReadArray;
use Kernel\Http\Path;

class RouteCallback
{
  
   private $routes = [];
   private $route;

   public function __construct()
   {
      $route = new Route;
      $this->routes = $route->all();
      $this->route = $_GET['uri'] ?? 'index'; 
   }

   private function getPath($params)
   {
      $path = $this->route;
      $path = explode('/',$path);
      $arr = [];

      for($i=0;$i<count($path);$i++)
      {   
          if(isset($params[$i]))
            $arr[$params[$i]] = $path[$i];
          else $arr[$i] = null;
      }

      $arr['path'] = $this->route;
      foreach($arr as $k=>$v)
      {
        if(is_null($v) || $k == '404') unset($arr[$k]);
      }
      return $arr;
   }

  private function getCallback($callback,$namespace) : object
  {
      $controller = null;  
      $method = null;
      if(is_array($callback))
      {
         $controller = $callback[0];
         $method = $callback[1] ?? 'index';
      }else{

         if(substr($callback,0,1) == '@')
         {
            $namespace = 'Modules\\';
            $callback = substr($callback,1);
         }

         $callback = str_ireplace('/','\\',$callback);
         $callback = explode('@',$callback);
         $controller = $namespace.$callback[0];
         $method = $callback[1] ?? 'index';
      }

      return (object) array('controller'=>$controller,'method'=>$method);
  }


   private function callback($callback, $params = [], $namespace = "App\\Controllers\\")
   {
      $output = null;
    if(is_callable($callback))
	 {
			$output = call_user_func($callback, $params);	
	  }else
     {

      $callback = $this->getCallback($callback,$namespace);
      $controller = $callback->controller;  
      $method = $callback->method;


      $rc = new \ReflectionClass($controller);

      if($rc->isInstantiable() && $rc->hasMethod($method))
      {
          
         $output = call_user_func_array(array(new $controller, $method), array_values($params));

      } else {

         throw new \Exception("Erro ao execultar callback: controller não pode ser instanciado, ou método não exite");				
      }
     }
     
    if(is_array($output) || is_object($output))
    {
       header('Content-Type: application/json');
       echo json_encode((array) $output);
     }else{
       echo $output;;
     }
   }

   private function callbackCheckMethod($search,$check_method=true)
   {
     $method = $_SERVER['REQUEST_METHOD'] == $search['method'] ? true : false;
     $method = $check_method ? $method : true;

     if($method)
     {
       $callback = $search['callback'];
       $parans = $this->getPath($search['path']);
       $this->callback($callback,$parans);
     }
   }

   private function executeMiddleware($middlewares,$method,$route)
   {
     $file= new ReadArray('config/middlewares.php');
     $r = true;
     $message = [];

     $middlewares = is_array($middlewares) ? $middlewares : array($middlewares);
     for($i=0;$i<count($middlewares);$i++)
     {
        if($file->has($middlewares[$i]))
        {
           $c = $file->get($middlewares[$i]);
           $c = new $c;
           $r = $c->handle($method,$route);
           if(!$r) $message[$i] = $c->message();
        }
     }
     return (object) array('result'=>$r,'message'=>$message);
   }

   public function execute()
   {
      $method = strtolower($_SERVER['REQUEST_METHOD']);
      $search = $this->find($this->route,$method);

      if($search)
      {    
          if($this->whereCall($search['where']))
          {
            $mid = $this->executeMiddleware($search['middleware'],$method,$this->route);
            if($mid->result) $this->callbackCheckMethod($search);
            else echo json_encode($mid->message);
          }else{
            $this->page404();
          }
      }else{
           $this->page404();
      }
   }


   private function whereCall($where)
   {
      $r = 1;
      foreach($where as $path=>$pattern)
      {
         if($pattern == 'int') $pattern = "([0-9]+)";
         
         $search = Path::get($path); 
         if(!preg_match("/^$pattern$/i", $search))
         {
            $r *= 0;
            break;
         }
      }
      return $r;
   } 

   private function page404()
   {
      header("HTTP/1.0 404 Not Found", true, 404);
      $search = $this->find('404');
      if($search)
      {
         $this->callbackCheckMethod($search,false);
      }
   }

   private function pattern($pattern)
   {
      $pattern = str_ireplace('/','\/',$pattern);
      $pattern = preg_replace("/\{([0-9a-z]+)\}/", "([0-9a-z]+)",$pattern);
      
      return "{$pattern}\/?";
   }

   /*Busca a coleção de rota que foi armazenada*/
   public function find($search, $method='GET')
   {
      $result = null;
      for($i=0;$i<count($this->routes);$i++)
      {
         $pattern = $this->pattern($this->routes[$i]['route']);
         
         if(preg_match("/^$pattern$/i", $search))
         {
            if(strcasecmp($this->routes[$i]['method'], $method) == 0)
            {
               $result = $this->routes[$i];
               break;
            }
         }
      }
      return $result;
   }

}