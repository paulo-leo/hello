<?php

use Kernel\Support\Vars;
use Kernel\Support\DotEnv;
use Kernel\Router\RouteCallback;
use Kernel\MVC\View;
use Kernel\Http\Path;
use Kernel\Support\Hello;
use Kernel\Http\Auth;

Vars::set('DotEnv', new DotEnv(__DIR__ . '/.env'));
Vars::set('URI', new Kernel\Http\URI);
Vars::set('Request', new Kernel\Http\Request);
Vars::set('Request', Vars::get('Request')->all());
Vars::set('RouteStorage', new Kernel\Router\RouteStorage);
Vars::set('ServiceProvider', new Kernel\Http\ServiceProvider);
Vars::set('Session', new Kernel\Http\Session);

function base_path()
{
    return __DIR__;
}

function app_path($path='/')
{
  $path = "/{$path}/";
  $path = str_replace('\\','/', $path);
  $path = str_replace('//','/', $path);
  $path = str_replace('/',DIRECTORY_SEPARATOR,$path); 
  $path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$path);
  return base_path().$path;
}

function public_path()
{
    $public = base_path().DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR;
    $public_html = base_path().DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR;

    return is_dir($public_html) ? $public_html : $public;
  
}

function storage_path()
{
   return base_path().DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR;;
}

function config_path()
{
  return base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
}

function resource_path()
{
  return base_path().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
}


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

function auth($role=null)
{
  return Auth::check($role);
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

/*Retorna um atributo de classes CSS de forma condicional*/
function rclass(array $classes)
{
   $r = array();
   foreach($classes as $value=>$bool)
   {
     if($bool) $r[] = $value;
     if(is_numeric($value)) $r[] = $bool;
   }
   $r = implode(' ',$r);
   return 'class="'.$r.'"';
}
/*Retorna um atributo de estilo CSS de forma condicional*/
function rstyle(array $styles)
{
   $r = array();
   foreach($styles as $value=>$bool)
   {
     if($bool) $r[] = $value;
     if(is_numeric($value)) $r[] = $bool;
   }
   $r = implode(';',$r);
   return 'style="'.$r.';"';
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
      $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
  }
  
   $ipv4_address = filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  
   return $ipv4_address ? $ipv4_address : $ip_address;
}

/*Redireciona o usuário para uma rota especifica*/
function redirect($url)
{
  $url = url($url);
  header("Location: {$url}");
  exit;
}

function dir_delete($dir) 
{
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

function dump(...$vars)
{
    $styles = [
        'string' => 'color: #006400;', // verde escuro
        'integer' => 'color: #0000FF;', // azul
        'float' => 'color: #8A2BE2;', // roxo
        'boolean' => 'color: #DC143C;', // vermelho escuro
        'array' => 'color: #FF8C00;', // laranja escuro
        'object' => 'color: #FF00FF;', // magenta
        'NULL' => 'color: #A9A9A9;', // cinza
    ];

    echo '<pre>';
    foreach ($vars as $var)
    {
        $type = gettype($var);
        $style = isset($styles[$type]) ? $styles[$type] : '';
        echo '<span style="' . $style . '">';
           var_dump($var);
        echo '</span>';
    }
    echo '</pre>';
    die;
}

function dd(...$variables)
{
    echo "<pre>";
    foreach ($variables as $variable)
    {
        var_dump($variable);
    }
    echo "</pre>";
    die;
}

function echoValueType($value)
{
  $type = gettype($value);
  echo "<span style='color:blue'><b style='color:green'>{$type}</b> : {$value}</span>|";
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