<?php

namespace Kernel\Http;

use Kernel\FS\ReadArray;
use Kernel\Http\RuleRequest;

class Request
{
    private $__all;
    private $__check;
    private $__files;
    private $errors;
    private $errors_person;
    private $__values;
    private $allHeaders;
    private $request;
    private $_value;
    private $_validations = array();
    private $validated = array();
    private $_validations_keys = array();

    private $_patterns = array();

    private $error_keys = array();

    private function getPattern($pattern, $argument = null)
    {
        $pattern = $this->_patterns[$pattern] ?? false;
        $pattern = $pattern ? str_replace(['#i', '{argument}'], $argument, $pattern) : $pattern;
        return $pattern;
    }

    protected function setPattern($pattern, $value)
    {
        $this->_patterns[$pattern] = $value;
    }
    
    protected function setMessage($key,$message)
    {
        $this->errors[$key] = $message;
    }


    public function __construct()
    {
        $this->_patterns = RuleRequest::patterns();
        $this->errors_person = array();
        $this->errors = array();
        $this->__values = array();
        $this->__files = array();
        $this->__check = 1;

        /*Salva todos os headers dentro de um array associativo*/
        $this->allHeaders = $this->setAllHeaders();

        if ($this->getHeader('Content-type') == 'application/json') {
            $_NP_REQUEST = json_decode(file_get_contents('php://input'), true);
            $this->__all = isset($_NP_REQUEST) ? $_NP_REQUEST : array();
        } else {

            $_SERVER['REQUEST_METHOD'] = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'TERM';

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->__all = $_GET;
                    break;
                case 'POST':
                    $this->__all = $_POST;
                    $this->__files = isset($_FILES) ? $_FILES : array();
                    break;
                default:
                    $_NP_REQUEST = file_get_contents('php://input');
                    parse_str($_NP_REQUEST, $_NP_REQUEST);
                    $this->__all = isset($_NP_REQUEST) ? $_NP_REQUEST : array();
                    break;
            }
        }
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        $header = $this->allHeaders;
        return array_key_exists($key, $header) ? $header[$key] : null;
    }

    /*retorna todos os headers*/
    public function headers()
    {
        return $this->allHeaders;
    }

    private function setAllHeaders()
    {
        $arr = array();
        foreach (getallheaders() as $key => $val) {
            $arr[strtolower($key)] = $val;
        }
        return $arr;
    }

    public function all()
    {
        unset($this->__all['uri']);
        return $this->__all;
    }

    public function has($key)
    {
        return isset($this->__all[$key]);
    }

    public function set($key, $value = null)
    {
        $this->__all[$key] = $value;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }


    public function get($key)
    {
        $value = isset($this->__all[$key]) ? $this->__all[$key] : null;
        return $value;
    }

    public function validate($validations, $messages = array())
    {
        foreach ($validations as $key => $value) {
            if (!$this->has($key))
                $this->set($key);

            array_push($this->validated, $key);
            $value = is_string($value) ? explode('|', $value) : $value;

            $this->_validations[$key] = $value;
        }

        $this->errors = $messages;
    }

    private function checkInValue($key, $value)
    {
        $name = $key;
        $validations = $this->_validations[$key] ?? false;

        if (!$validations)
            return true;

        foreach ($validations as $key)
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
                    $this->__check *= 0;
                }
            }

            if($key == 'required' && !$value)
            {
                  $key_v = "{$name}.{$key}";
                  $this->error_keys[$key_v] = $key_v;
                  $this->__check *= 0;
            }
        }
    }

    public function check()
    {
        foreach ($this->all() as $key => $value)
        {
            $this->checkInValue($key, $this->get($key));
        }
        return $this->__check;
    }

    public function errors()
    {
        $this->check();
        $messages = array();
        foreach ($this->error_keys as $error) {
            $name = explode('.', $error)[0];
            $value = $this->errors[$error] ?? false;

            if ($value)
            {
                $messages[] = str_replace('{value}', $this->get($name), $value);
            }
        }
        return $messages;
    }

    public function fails()
    {
        return !$this->check();
    }

    public function success()
    {
        return !$this->fails();
    }

    public function validated()
    {
        $validated = array();
        if ($this->success()) {
            foreach ($this->validated as $key) {
                $validated[$key] = $this->get($key);
            }
        }
        return $validated;
    }
}