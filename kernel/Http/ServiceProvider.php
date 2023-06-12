<?php

namespace Kernel\Http;

use Kernel\FS\ReadArray;

class ServiceProvider
{

    protected $method;
    protected $path;
    protected $prefix;
    private static $stop = false;
    private static $messages = array();
    private static $services = array();

    protected function register($name, $class = null)
    {
        $class = is_null($class) ? get_class($this) : $class;
        self::$services[$name] = new $class;
    }

    public function getService($name)
    {
        return self::$services[$name];
    }

    //Executa o boot de todos os services registrados
    public function bootRegisters()
    {
    }

    public function __construct()
    {
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->path = strtolower($_GET['uri'] ?? '');
        $this->prefix = explode('/', $this->path);
        $this->prefix = $this->prefix[0];
    }

    protected function isPath($path)
    {
        return (strtoupper($path) == strtoupper($this->path));
    }

    protected function isMethod($method)
    {
        return (strtoupper($method) == strtoupper($this->method));
    }

    protected function isPrefix($prefix)
    {
        return (strtoupper($prefix) == strtoupper($this->prefix));
    }

    protected function stop($message)
    {
        self::$messages[] = $message;
        return self::$stop = true;
    }

    protected function message($message)
    {
        self::$messages[] = $message;
    }

    final public function checkStop()
    {
        return self::$stop;
    }

    final public function getMessages()
    {
        return json_encode(self::$messages);
    }

    public function boot()
    {
    }

    public function __call($name, $arguments)
    {
        return false;
    }

    final public function execute()
    {
        $file = new ReadArray('config/services.php');
        foreach ($file->all() as $name => $value)
        {
            $service = new $value;
            $service->boot();
        }
    }
}
