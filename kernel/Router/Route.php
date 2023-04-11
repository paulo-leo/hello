<?php
namespace Kernel\Router;
use Kernel\Router\RouteStorage;
use Kernel\Http\Path;

class Route
{
    private $last = null;
    public static $routes = [];
    public static $group = null;
    public static $init = null;

    /*
      Cria a rota
    */
    public function create($create)
    {
        $index = $create['route'];
        $method = $create['method'] ?? 'GET';
        $middleware = $create['middleware'] ?? null;

        $index = isset(self::$group['prefix']) ? self::$group['prefix'].'/'.$index : $index; 
        $middleware = isset(self::$group['middleware']) ? self::$group['middleware'] : $middleware;

        Path::register($index);
        $data = array(
            'method'=>$method,
            'route'=>$index,
            'callback'=>$create['callback'],
            'middleware'=>$middleware
        );
       
       $index = md5("{$index}{$method}");
       self::$routes[$index] = $data;
       $this->last = $index;
    }

     public static function all()
     {
        $route = new RouteStorage;
        foreach(self::$routes as $set)
        {
           $set = (object) $set;

           $route->set([
            'method'=>$set->method,
            'route'=>$set->route,
            'callback'=>$set->callback,
            'middleware'=>$set->middleware,
            'where'=>$set->where ?? array(),
            'name'=>$set->name ?? ''
           ]);
        }
        return $route->all();
     }

     public function setGroup($v=[])
     {
        self::$group = $v;
     }
     public static function group($config,$callback)
     {
        self::init()->setGroup($config);
        call_user_func($callback);
        self::init()->setGroup();
     }
    
     public function name($name)
     {
      self::$routes[$this->last]['name'] = $name; 
      return $this;
     }

     public function middleware($middleware)
     {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        self::$routes[$this->last]['middleware'] = $middleware; 
        return $this;
     }

     public function where($where)
     {
        self::$routes[$this->last]['where'] = $where; 
        return $this;
     }

     /*Cria uma rota com o verbo GET*/
     public function createget($route,$callback)
     {
        $this->create([
         'route'=>$route,
         'callback'=>$callback
        ]);

        return $this;
     }
     /*Cria uma rota com o verbo POST*/
     public function createpost($route,$callback)
     {
        $this->create([
         'method'=>'POST',
         'route'=>$route,
         'callback'=>$callback
        ]);

        return $this;
     }
     /*Cria uma rota com o verbo PUT*/
     public function createput($route,$callback)
     {
        $this->create([
         'method'=>'PUT',
         'route'=>$route,
         'callback'=>$callback
        ]);

        return $this;
     }
      /*Cria uma rota com o verbo DELETE*/
      public function createdelete($route,$callback)
      {
         $this->create([
          'method'=>'DELETE',
          'route'=>$route,
          'callback'=>$callback
         ]);
 
         return $this;
      }
       /*Cria uma rota com o verbo PATCH*/
       public function createpatch($route,$callback)
       {
          $this->create([
           'method'=>'PATCH',
           'route'=>$route,
           'callback'=>$callback
          ]);
  
          return $this;
       }
       
        /*Cria uma rota com todos os verbos*/
        public static function any($route,$callback)
        {
            self::init()->createget($route,$callback);
            self::init()->createpost($route,$callback);
            self::init()->createput($route,$callback);
            self::init()->createdelete($route,$callback);
            self::init()->createpatch($route,$callback);
        }

        public static function match($verbs,$route,$callback)
        {
           foreach($verbs as $verb)
           {
              $verb = strtolower($verb);
              switch($verb)
              {
               case 'get' : self::init()->createget($route,$callback); break;
               case 'post': self::init()->createpost($route,$callback); break;
               case 'put' : self::init()->createput($route,$callback); break;
               case 'delete' : self::init()->createdelete($route,$callback); break;
               case 'patch' : self::init()->createpatch($route,$callback); break;
             }
           }
           return self::init();
        }

        /*
          Faz a junção de recurso e libera a sua entrega com verbos diferentes
        */
        public static function resources($route,$callback)
        {
            $id = "([0-9]+)";
            self::init()->createget($route,"{$callback}@index")
            ->name("{$route}.index");

            self::init()->createget("{$route}/create","{$callback}@create")
            ->name("{$route}.create");

            self::init()->createpost($route,"{$callback}@store")
            ->name("{$route}.store");

            self::init()->createget("{$route}/{id}","{$callback}@show")
            ->name("{$route}.show")->where(['id'=>$id]);

            self::init()->createget("{$route}/{id}/edit","{$callback}@edit")
            ->name("{$route}.edit")->where(['id'=>$id ]);

            self::init()->createput("{$route}/{id}","{$callback}@update")
            ->name("{$route}.update")->where(['id'=>$id ]);

            self::init()->createdelete("{$route}/{id}","{$callback}@destroy")
            ->name("{$route}.destroy");
        }

        public static function singleton($route,$callback)
        {
            self::init()->createget($route,"{$callback}@show")->name("{$route}.show");
            self::init()->createget("{$route}/edit","{$callback}@edit")->name("{$route}.edit");
            self::init()->createput($route,"{$callback}@update")->name("{$route}.update");
        }

    /*
      Define um contexto de controladores em rotas personalizadas
    */
    public static function controllers($class,$routes)
    {
           foreach($routes as $v => $callback)
           {
              $v = explode(':',$v);
              $route = isset($v[1]) ? $v[1] : $v[0];
              $method = isset($v[1]) ? $v[0] : 'GET';

              self::init()->create([
                'method'=>$method,
                'route'=>$route,
                'callback'=>"{$class}@{$callback}"
               ]);
           }
           return self::init();
     }

    public static function init()
    {
       if(is_null(self::$init))
       {
          self::$init = new Route;
       }
       return self::$init;
    }

    public static function get($route,$callback)
    {
        return self::init()->createget($route,$callback); 
    }

    public static function post($route,$callback)
    {
        return self::init()->createpost($route,$callback); 
    }

    public static function put($route,$callback)
    {
        return self::init()->createput($route,$callback); 
    }

    public static function delete($route,$callback)
    {
        return self::init()->createdelete($route,$callback); 
    }

    public static function patch($route,$callback)
    {
        return self::init()->createpatch($route,$callback); 
    }

    public static function loadFile($file)
    {
      $file = substr($file,0,1) == '/' ? substr($file,1) : $file; 
      $file = substr($file,-4) == '.php' ? substr($file,0,-4) : $file;

      $file = __DIR__ . "/../../app/Routes/{$file}.php";
      if(!file_exists($file))
      {
         echo "Arquivo de rota não existe!";
         exit;
      }
      require $file;
    }
}