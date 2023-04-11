<?php

use Kernel\Support\Vars;
use Kernel\Support\DotEnv;
use Kernel\Router\RouteCallback;
use Kernel\MVC\View;
use Kernel\Http\Path;
use Kernel\Support\Hello;

Vars::set('DotEnv', new DotEnv(__DIR__ . '/.env'));
Vars::set('URI', new Kernel\Http\URI);
Vars::set('Request', new Kernel\Http\Request);
Vars::set('Request', Vars::get('Request')->all());
Vars::set('RouteStorage', new Kernel\Router\RouteStorage);
Vars::set('ServiceProvider', new Kernel\Http\ServiceProvider);
Vars::set('Session', new Kernel\Http\Session);


function session(array $sessions = [])
{
  $session = Vars::get('Session');
  if(count($sessions) > 0)
  {
    foreach($sessions as $name=>$value)
    {
      $session->put($name,$value);
    }
  }
  return $session; 
}

function build($params)
{
  $params = !is_array($params) ? [$params] : $params;
  $arr = [];
  foreach ($params as $value1) {
    $value1 = explode(' as ', $value1);
    $class =  trim($value1[0]);
    $value = explode('\\', $class);
    $name = isset($value1[1]) ?  $value1[1] : $value[count($value) - 1];
    $name = strtolower(trim($name));
    $arr[$name] = new $class;
  }
  return (object) $arr;
}

function env($key=null, $value = null)
{
  return Vars::get('DotEnv')->getValueByKey($key, $value);
}

function view($view, $scope = [])
{
  View::render($view, $scope);
}

function set($var, $value = '')
{
  Vars::set($var, $value);
}

function has($var)
{
  return Vars::get($var) ? true : false;
}

function get($var)
{
  return Vars::get($var);
}

function asset($file)
{
  $base = Vars::get('URI')->base();
  return $base . $file;
}

function url($route = null)
{
  $base = Vars::get('URI')->base();
  return $base . $route;
}

function request($key = null)
{
  $request = Vars::get('Request');
  $value = null;
  if (is_null($key)) $value = $request;
  else $value = $request[$key] ?? $value;
  return $value;
}

function route($name, $params = [])
{
  $route = Vars::get('RouteStorage')->getRouteByName($name, $params);
  return $route ? url($route) : null;
}


function path($name)
{
  return Path::get($name);
}

function secret($secret)
{
  $app_key = env('APP_KEY');
  $secret = trim((string) $secret);
  return (strlen($secret) >= 4) ? md5("{$secret}-{$app_key}-{$secret}") : false;
}

function get_ip() {
  // Verifica se o endereço IP do usuário é passado através de um proxy reverso
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip_address = $_SERVER['HTTP_CLIENT_IP'];
  }
  // Verifica se o endereço IP do usuário é passado através de um proxy
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
  // Obtém o endereço IP do usuário
  else {
      $ip_address = $_SERVER['REMOTE_ADDR'];
  }
  
   $ipv4_address = filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  
   return $ipv4_address ? $ipv4_address : $ip_address;
}



function dir_delete($dir) {
  if (!file_exists($dir)) {
      return true;
  }

  if (!is_dir($dir)) {
      return unlink($dir);
  }

  foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
          continue;
      }

      if (!dir_delete($dir . DIRECTORY_SEPARATOR . $item)) {
          return false;
      }
  }

  return rmdir($dir);
}

function dump($value = null)
{
  $style = "background-color:black;color:white;font-family:arial;padding:20px;border-radius:20px";
  echo "<div style='{$style}'>
   [";
  if (gettype($value) == 'array' || gettype($value) == 'object') {
    $value = (array) $value;
    echo "[ array(<br>";
    foreach ($value as $key => $val) {
      echo "{$key} = ";
      if (gettype($val) != 'array' || gettype($val) != 'object') {
        echoValueType($val);
      } else {
        dump($val);
      }
    }
    echo ")";
  } else {
    echoValueType($value);
  }
  echo "]</div>";
}

function echoValueType($value)
{
  $type = gettype($value);
  echo "<p style='color:blue'><b style='color:green'>{$type}</b> : {$value}</p>";
}

if (!function_exists('getallheaders')) {
  function getallheaders()
  {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
      if (substr($name, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }
}

Vars::get('ServiceProvider')->execute();

function service($name)
{
  return Vars::get('ServiceProvider')->getService($name);
}

if (!Vars::get('ServiceProvider')->checkStop())
{

  function csrf_token()
  {
    return service('csrf')->token();
  }

  $callback = new RouteCallback;
  
  if($_SERVER['REQUEST_METHOD'] == 'TERM')
  {
    require __DIR__ . '/kernel/HelloCLI/commands.php';
    Hello::execute();
  }else
  {
    $callback->execute();
  }

} else {
  header("HTTP/1.0 502 Bad Gateway", true, 502);
  echo Vars::get('ServiceProvider')->getMessages();
}
