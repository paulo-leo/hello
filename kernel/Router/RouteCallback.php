<?php
namespace Kernel\Router;

use Exception;
use Kernel\Router\Route;
use Kernel\FS\ReadArray;
use Kernel\Http\Path;
use Kernel\Router\Injection;
use Kernel\Router\RouteException;

class RouteCallback
{

   private $routes = [];
   private $route;

   public function __construct()
   {
      $route = new Route();
      $this->routes = $route->all();
      $this->route = filter_input(INPUT_GET, 'uri', FILTER_SANITIZE_STRING) ?? 'index';
   }


   private function getPath($params)
   {
      $path = explode('/', $this->route);
      $count = count($path);
      $arr = [];

      for ($i = 0; $i < $count; $i++) {
         $key = array_key_exists($i, $params) ? $params[$i] : $i;
         $arr[$key] = $path[$i];
      }

      $arr['path'] = $this->route;

      foreach ($arr as $k => $v) {
         if (is_null($v) || $k == '404') {
            unset($arr[$k]);
         }
      }

      return $arr;
   }


   private function getCallback($callback, $namespace): object
   {
      $controller = null;
      $method = null;

      if (is_array($callback)) {
         $controller = $callback[0];
         $method = $callback[1] ?? 'index';
      } else {
         if ($callback[0] == '@') {
            $namespace = 'Modules\\';
            $callback = substr($callback, 1);
         }

         $callback = str_ireplace('/', '\\', $callback);
         [$controller, $method] = explode('@', $callback);
         $controller = $namespace . $controller;
      }

      return (object) ['controller' => $controller, 'method' => $method];
   }

   private function callback($callback, $params, $namespace = "App\\Controllers\\")
   {
      $output = null;
      $injection = new Injection($this->route, $params);

      try {
         // código que pode lançar exceções
         if (is_callable($callback)) {
            $output = $injection->callbackFunction($callback);
         } else {

            $callback = $this->getCallback($callback, $namespace);

            if ($injection->checkNamespace($callback)) {
               $output = $injection->callbackFunctionForNamespace($callback);

            } else {
               throw new Exception("Erro ao executar callback: controller não pode ser instanciado, ou método não existe");
            }
         }
        
         http_response_code(200);

         if (is_array($output) || is_object($output))
          {
            header('Content-Type: application/json');
            echo json_encode((array) $output);
         } else {
            echo $output;
         }

      } catch (Exception $e) {
         
         http_response_code(500);
         $exception = new RouteException($e);
         $exception->view();

      }
   }


   private function callbackCheckMethod($search, $check_method = true)
   {
      $method = $_SERVER['REQUEST_METHOD'] == $search['method'] ? true : false;
      $method = $check_method ? $method : true;

      if ($method) {
         $callback = $search['callback'];
         $parans = $this->getPath($search['path']);
         $this->callback($callback, $parans);
      }
   }

   private function executeMiddleware($middlewares, $method, $route)
   {
      $file = new ReadArray('config/middlewares.php');
      $r = true;
      $message = [];

      $middlewares = is_array($middlewares) ? $middlewares : array($middlewares);
      for ($i = 0; $i < count($middlewares); $i++) {
         if ($file->has($middlewares[$i])) {
            $c = $file->get($middlewares[$i]);
            $c = new $c;
            $r = $c->handle($method, $route);
            if (!$r)
               $message[$i] = $c->message();
         }
      }
      return (object) array('result' => $r, 'message' => $message);
   }

   public function execute()
   {
      $method = strtolower($_SERVER['REQUEST_METHOD']);
      $search = $this->find($this->route, $method);

      if ($search) {
         if ($this->whereCall($search['where'])) {
            $mid = $this->executeMiddleware($search['middleware'], $method, $this->route);
            if ($mid->result)
               $this->callbackCheckMethod($search);
            else
               echo json_encode($mid->message);
         } else {
            $this->page404();
         }
      } else {
         $this->page404();
      }
   }


   private function whereCall($where)
   {
      $r = 1;
      foreach ($where as $path => $pattern) {
         if ($pattern == 'int')
            $pattern = "([0-9]+)";

         $search = Path::get($path);
         if (!preg_match("/^$pattern$/i", $search)) {
            $r *= 0;
            break;
         }
      }
      return $r;
   }

   private function page404()
   {
      http_response_code(404);
      $search = $this->find('404');
      if ($search) {
         $this->callbackCheckMethod($search, false);
      }
   }

   private function pattern($pattern)
   {
      $pattern = str_ireplace('/', '\/', $pattern);
      $pattern = preg_replace("/\{([0-9a-z]+)\}/", "([0-9a-z]+)", $pattern);

      return "{$pattern}\/?";
   }

   /*Busca a coleção de rota que foi armazenada*/
   public function find($search, $method = 'GET')
   {
      $result = null;
      for ($i = 0; $i < count($this->routes); $i++) {
         $pattern = $this->pattern($this->routes[$i]['route']);

         if (preg_match("/^$pattern$/i", $search)) {
            if (strcasecmp($this->routes[$i]['method'], $method) == 0) {
               $result = $this->routes[$i];
               break;
            }
         }
      }
      return $result;
   }

}