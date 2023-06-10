<?php

namespace Kernel\Support;

//Autor: Paulo Leonardo da Silva Cassimiro

class ObjectDefault
{
   private $data = array();
   private $ref;

   public function __construct(array $data=[],$ref=null)
   {
      if(count($data) > 0) $this->data = $data;
      $this->ref = $ref;
   }

   public function all()
   {
      return $this->data;
   }

   public function get($name)
   {
      return $this->data[$name] ?? false;
   }
    
   public function __set($name, $value)
   {
      $this->data[$name] = $value;
   }

   public function __get($name)
   {
      return (array_key_exists($name,$this->data)) ? $this->data[$name] : null;
   }

   public function __toString()
   {
     return $this->ref;
   }

   public function __call($name,$args)
   {
      if($name == 'save')
      {
        $key = $this->ref->getPrimaryKey();
        $id = array_key_exists($key,$this->data) ? $this->data[$key] : false;
        $data = $this->data;
        if($id)
        {  
           unset($data[$key]);
           return $this->ref->where($key,$id)->update((array) $data);
        }else{
           return $this->ref->create((array) $data);
        }
      }
   }
}