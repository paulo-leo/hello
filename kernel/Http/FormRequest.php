<?php

namespace Kernel\Http;

use Kernel\Http\Request;

abstract class FormRequest extends Request
{
    public function __construct()
    {
      foreach($this->rules() as $key=>$value)
       {
         $this->setPattern($key,$value);
       }

       foreach($this->message() as $key=>$message)
       {
         $this->setMessage($key,$message);
       }
       parent::__construct();
    }

    protected function rules() 
    {
      return array();
    }

    protected function message() : array
    {
        return array();
    }

}
