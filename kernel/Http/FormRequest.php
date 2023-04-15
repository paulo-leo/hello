<?php

namespace Kernel\Http;

use Kernel\Http\Request;

abstract class FormRequest extends Request
{
    private $request;
    final public function __construct()
    {
      parent::__construct();
      $this->validate($this->rules(),$this->messages());
    }

    protected function rules() 
    {
      return array();
    }

    protected function messages() 
    {
      return array();
    }
}