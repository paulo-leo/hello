<?php

namespace Kernel\Http;

use Kernel\Http\Request;

abstract class FormRequest
{
    private $request;
    final public function __construct()
    {
      $this->request = new Request;
      $this->request()->validate($this->rules(),$this->messages());
    }

    final public function request()
    {
      return $this->request;
    }

    protected function rules() 
    {
      return array();
    }

    protected function messages() 
    {
      return array();
    }

    final public function fails()
    {
      return $this->request()->fails();
    }

    final public function success()
    {
      return $this->request->success();
    }

    final public function errors()
    {
      return $this->request()->errors();
    }

    final public function validated()
    {
      return $this->request()->validated();
    }

    final public function all()
    {
      return $this->request()->all();
    }

    final public function get($key)
    {
      return $this->request()->get($key);
    }

    final public function set($key,$value)
    {
      return $this->request()->set($key,$value);
    }
}
