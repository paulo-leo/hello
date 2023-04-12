<?php

namespace Kernel\Http;

use Kernel\Http\Request;

class FormRequest extends Request
{

    final protected function init()
    {
       foreach($this->rules() as $key=>$value)
       {
         $this->setPattern($key,$value);
       }
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
