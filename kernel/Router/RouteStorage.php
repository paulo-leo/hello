<?php

namespace Kernel\Router;

class RouteStorage
{
  private static $storage = array();

  private function routeDefault($route)
  {
    $route = trim($route);
    $size = strlen($route);

    $route = ($size == 2 && $route == '/*') ? '*' : $route;

    if ($size == 1) {
      if ($route == '*') $route = '404';
      if ($route == '/') $route = 'index';
    }

    $route = (($size > 1) && (substr($route, 0, 1) == '/'))
      ? substr($route, 1) : $route;

    return $route;
  }

  public function guardPath($path)
  {
    $path = str_ireplace(['{', '}'], '', $path);
    $path = explode('/', $path);
    $arr = [];
    for ($i = 0; $i < count($path); $i++) {
      $arr[$i] = $path[$i];
    }
    return $arr;
  }

  public function set($options)
  {
    $route = $this->routeDefault($options['route']);
    $method = $options['method'] ?? 'GET';
    $callback = $options['callback'];
    $middleware = $options['middleware'];
    $where = $options['where'];
    $name = $options['name'];

    self::$storage[] = array(
      'route' => $route,
      'method' => $method,
      'callback' => $callback,
      'middleware' => $middleware,
      'path' => $this->guardPath($route),
      'where' => $where,
      'name' => $name
    );

    return $this;
  }

  /*
      Faz a atualização de opções de uma rota
    */
  public function change($route, $data)
  {
    for ($i = 0; $i < count(self::$storage); $i++) {
      if (self::$storage[$i]['route'] == $route) {
        foreach ($data as $k => $v) {
          self::$storage[$i][$k] = $v;
        }
      }
    }
  }

  public function mount()
  {
    return $this;
  }

  public function mid()
  {
    return $this;
  }


  public function all()
  {
    return self::$storage;
  }

  public function getRouteByName($name, $params = [])
  {
    $route = false;

    foreach (self::$storage as $routex) 
    {
      if ($routex['name'] == $name)
      {
        $route = $routex['route'];
        break;
      }
    }
    return $route ? $this->params($route, $params) : null;
  }

  private function params($path, $params)
  {
    foreach ($params as $key => $val) {
      $path = str_ireplace("{{$key}}", $val, $path);
    }
    return $path;
  }
}
