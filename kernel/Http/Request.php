<?php

namespace Kernel\Http;

use Kernel\Http\URI;
use Kernel\FS\ReadArray;
use Kernel\Http\FileRequest;
use Kernel\Http\RuleRequest;
use Kernel\Http\Path;

class Request extends FileRequest
{
    private $all;
    private $check;
    private $errors;
    private $_validations = array();
    private $validated = array();
    private $_validations_keys = array();
    private $headers;
    private $patterns;
    private $error_keys = array();

    public function __construct()
    {
        parent::__construct();
        $this->check = 1;
        $this->patterns = RuleRequest::patterns();
        $this->all = $this->parseInput();
        $this->headers = $this->parseHeaders();
    }

    private function getPattern($pattern, $argument = null)
    {
        $pattern = $this->patterns[$pattern] ?? false;
        $pattern = $pattern ? str_replace(['#i', '{argument}'], $argument, $pattern) : $pattern;
        return $pattern;
    }

    private function parseInput(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'TERM';
        switch ($method) {
            case 'GET':
                return $_GET;
            case 'POST':
                if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                    return json_decode(file_get_contents('php://input'), true) ?? [];
                }
                return $_POST;
            default:
                $input = file_get_contents('php://input');
                parse_str($input, $result);
                return $result ?? [];
        }
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach (getallheaders() as $key => $val) {
            $headers[strtolower($key)] = $val;
        }
        return $headers;
    }

    public function getHeader(string $key)
    {
        return $this->headers[$key] ?? null;
    }

    public function getHeaderAll()
    {
        return $this->headers;
    }

    public function all(): array
    {
        $input = $this->all;
        unset($input['uri']);
        return $input;
    }

    public function has(string $key): bool
    {
        return isset($this->all[$key]);
    }

    public function set(string $key, $value = null): void
    {
        $this->all[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->all[$key] ?? null;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }


    public function validate(array $validations, $messages = array())
    {
        foreach ($validations as $key => $value) 
        {
          if(!$this->has($key)) $this->set($key);

          array_push($this->validated, $key);
          $value = is_string($value) ? explode('|', $value) : $value;
          $this->_validations[$key] = $value;
        }

        $this->errors = $messages;
    }

    public function except($excepts,$data=null)
    {
          $data = is_null($data) ? $this->all() : $data;
          foreach($excepts as $except)
          {
             if(isset($data[$except])) 
                    unset($data[$except]);
          }
          return $data;
    }

    public function only($onlys,$data=null)
    {
          $data = is_null($data) ? $this->all() : $data;
          $new_data = array();
          foreach($onlys as $only)
          {
             if(isset($data[$only]))
                  $new_data[$only] = $data[$only];
          }
          return $new_data;
    }

    private function checkInValue($key, $value)
    {
        $name = $key;
        $validations = $this->_validations[$key] ?? false;

        if (!$validations)
            return true;

        foreach ($validations as $key)
        {
           if($key instanceof RuleRequest){
              if(!$key->passes($name, $value))
              {
                $this->error_keys[$key->rule()] = "{$name}.{$key->rule()}";
                $this->check *= 0;
              }
           }else
           {
            $key = trim($key);
            $key = str_ireplace(' ', '', $key);
            $key = explode(':', $key);
            $argument = $key[1] ?? '';
            $key = $key[0];

            $value = $this->get($name);
            $pattern = $this->getPattern($key, $argument);
            
            if ($pattern && $value)
            {
                $value = preg_match($pattern, $this->get($name));
                if (!$value)
                {
                    $key_v = "{$name}.{$key}";
                    $this->error_keys[$key_v] = $key_v;
                    $this->check *= 0;
                }
            }

            if($key == 'file')
            {
              if(!$this->hasFile($name))
              {
                $key_v = "{$name}.{$key}";
                $this->error_keys[$key_v] = $key_v;
                $this->check *= 0; 
              }   
            }

           
            if($key == 'required' && !$value)
            {
                  $key_v = "{$name}.{$key}";
                  $this->error_keys[$key_v] = $key_v;
                  $this->check *= 0;
            }

            if($key == 'size')
            {
              if($this->fileSize($name) > ((int) $argument))
              {
                $key_v = "{$name}.{$key}";
                $this->error_keys[$key_v] = $key_v;
                $this->check *= 0; 
              }   
            }
            //fileExtension($name,$one=true)
            if($key == 'extension')
            {
              $extension = $this->fileExtension($name);
              $argument = explode(',',$argument);
              if(!$this->fileCheckExtension($extension,$argument))
              {
                $key_v = "{$name}.{$key}";
                $this->error_keys[$key_v] = $key_v;
                $this->check *= 0; 
              }   
            }
         }
        }
    }

    private function check()
    {
        foreach ($this->all() as $key => $value)
        {
            $this->checkInValue($key, $this->get($key));
        }
        return $this->check;
    }

    public function errors()
    {
        $this->check(); 
        $messages = array();
        foreach ($this->error_keys as $error) {

           // var_dump($this->error_keys);

            $errors = explode('.', $error);
            $name = $errors[0];
            $key = $errors[1] ?? '';

            $message = $this->errors[$error] ?? false;

            if ($message)
            {
                $messages[] = str_replace(['{name}','{value}'],[$name,$this->get($name)], $message);
            }else{
                 $key = RuleRequest::getMessageDefault($key);
                 if($key) 
                    $messages[] = str_replace(['{name}','{value}'],[$name,$this->get($name)], $key);
            }
        }
        return $messages;
    }

    public function fails()
    {
        return !$this->check();
    }

    public function validated($excepts=array())
    {
        $validated = array();
        if (!$this->fails()) {
            foreach ($this->validated as $key) {
                $validated[$key] = $this->get($key);
            }
        }
        return $this->except($excepts,$validated);
    }

    public function path()
    {
        return Path::uri();
    }

    public function query()
    {
        
    }

    public function isMethod($method)
    {
        return ($_SERVER['REQUEST_METHOD'] === strtoupper($method));
    }

}