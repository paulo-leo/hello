<?php
namespace Kernel\Router;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;

class Injection
{
    private $route;
    private $params;
    public function __construct($route,$params)
    {
        $this->route = $route;
        $this->params = $params;
    }
    public function callbackFunction($user_call)
    {
        $reflection = new ReflectionFunction($user_call);
        $params = $this->arguments($reflection->getParameters());

        return call_user_func_array($user_call, $params);
    }

    public function callbackFunctionForNamespace($callback)
    {
      $method = $callback->method;
      $namespace = $callback->controller;
      $reflection = new ReflectionMethod($namespace, $method);
      $params = $this->arguments($reflection->getParameters());
      return call_user_func_array(array(new $namespace, $method), array_values($params));
    }

    public function checkNamespace($callback)
    {
        $method = $callback->method;
        $controller = $callback->controller;
        $rc = new ReflectionClass($controller);
        return ($rc->isInstantiable() && $rc->hasMethod($method));
    }

    private function arguments($parametros)
    {
        $params = array();
        foreach ($parametros as $pars) 
        {
            $name = $pars->getName();
            $type = $pars->getType();
            $type_name = $type && !$type->isBuiltin() ? $type->getName() : null;
            $params[$name] = $type_name ? new $type_name() : $this->params[$name] ?? null;
        }
        return $params;
    }
}
