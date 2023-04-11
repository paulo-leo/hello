<?php

namespace Kernel\Http;

use Kernel\FS\ReadArray;

class Request
{
    private $__all;
	private $__check;
	private $__files;
	private $erros;
	private $erros_person;
	private $__values;
	private $allHeaders;
	private $request;
    private $_value;
    private $_validations;
    private $_validations_keys;

	public function __construct()
	{
	  $this->erros_person = array();
	  $this->erros = array();
	  $this->__values = array();
	  $this->__files = array();
	  $this->__check = 1;
      $this->_validations_keys = array();

      $this->_validations = new ReadArray('config/validations.php');
	  
	  /*Salva todos os headers dentro de um array associativo*/
	  $this->allHeaders = $this->setAllHeaders();
	  
	  if($this->getHeader('Content-type') == 'application/json')
	  {
		   $_NP_REQUEST = json_decode(file_get_contents('php://input'), true);
		   $this->__all = isset($_NP_REQUEST) ? $_NP_REQUEST : array();
	  }else{
	  
      $_SERVER['REQUEST_METHOD']  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'TERM';

      switch ($_SERVER['REQUEST_METHOD']) 
	  {
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
       return array_key_exists($key,$header) ? $header[$key] : null;
	}
	
	/*retorna todos os headers*/
	public function headers()
	{
	   return $this->allHeaders;
	}

    private function setAllHeaders()
	{
		$arr = array();
		foreach(getallheaders() as $key=>$val)
		{
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

    public function set($key,$value=null)
	{
        $this->__all[$key] = $value;
	}

    public function __get($key)
    {
       return $this->get($key);
    }

    public function __set($key,$value)
    {
        return $this->set($key,$value);
    }
    

    public function get($key)
	{
        $value = isset($this->__all[$key]) ? $this->__all[$key] : null;
        return $value;
	}

    private function checkSize($size,$value)
    {    
        if(is_null($value)) return true;

        $size = explode(',',$size);  
        $min = (int) $size[0];
        $max = (int) isset($size[1]) ? $size[1] : $min;

        $value = is_numeric($value) ? (float) $value : strlen($value);

        return ($value >= $min && $value <= $max); 
    }

    public function validation($validations,$value=null)
    {
        if(!is_array($validations))
        {
            $value = is_string($value) ? explode('|',$value) : $value;
            $this->_validations_keys[$validations] = $value; 
        }else{

        foreach($validations as $k=>$v)
        {
          if(!$this->has($k)) $this->set($k);
          $v = is_string($v) ? explode('|',$v) : $v;
        }
      }
    }

    private function checkInValue($key, $value)
    {
        $validations = isset($this->_validations_keys[$key]) ?
        $this->_validations_keys[$key] : null;

        if(is_null($validations)) return true;

        for($i=0;$i<count($validations);$i++)
        {
           $key = trim($validations[$i]);
           $key = str_ireplace(' ','',$key);

           if($this->_validations->has($key))
           {
              $p = $this->_validations->get($key);
              $value = preg_match("/^{$p}$/", $value);
              if(!$value) $this->__check *= 0;
           }

           if(substr($key,0,5) == 'size:')
           {
              $key = substr($key,5);
              if(!$this->checkSize($key,$value))
                $this->__check *= 0;
           }

           if($key == 'required' && is_null($value))
           {
              $this->__check *= 0;
           }
        }   
    }

    public function check()
    {
        foreach($this->all() as $key=>$value)
        {
           $this->checkInValue($key, $this->get($key));
        }
        return $this->__check;
    }
}