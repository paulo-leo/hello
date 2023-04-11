<?php

namespace Kernel\Support;

class Collection
{
    private $array;
    private $count;
    private $tmp;
    private $all;
    private $storage;

    public function __construct($array=[])
    {
       $this->array = $array;
       $this->all = $this->array;
       $this->count = count($this->array);
       $this->storage = [];
    }
    public function setCollection($array)
    {
       $this->array = $array;
       $this->all = $this->array;
       $this->count = count($this->array);
       $this->storage = [];
    }

    public function all()
    {
        return $this->all;
    }

    public function ajust()
    {
        $arr = [];
        foreach($this->array as $value)
        {
           $arr[] = $value;
        }
        $this->array = $arr;  
    }

    public function get()
    {
        $arr = array();
        for($i=0;$i < $this->count;$i++)
        {
           $arr[] = (object) $this->array[$i];
        }
        return $arr;
    }

    public function count() : int
    {
        return $this->count;
    }

    public function removeIndex($index)
    {
        $index = is_array($index) ? $index : [$index];
        for($i=0;$i<count($index);$i++)
        {
            if(array_key_exists($index[$i],$this->array))
            {
              unset($this->array[$index[$i]]);
              $this->count--;
            }
        }
        $this->ajust();
    }

    public function removeKey($key)
    {
        for($i=0;$i<$this->count;$i++)
        {
            if(isset($this->array[$i][$key]))
                   unset($this->array[$i][$key]);
        }
        $this->ajust();
    }

    public function remove($filter)
    {
        $arr = [];
        for($i=0;$i<$this->count;$i++)
        {
            if(!call_user_func($filter,$this->array[$i]))
            {
               $arr[] = $this->array[$i];
            }
        }
        $this->array = $arr;
        $this->count = count($arr);
    }

    public function unshift($value)
    {
        array_unshift($this->array,$value);
        $this->count++;
    }

    public function push($value)
    {
        array_push($this->array,$value);
        $this->count++;
    }

    public function shift()
    {
        array_shift($this->array);
        $this->count--;
    }

    public function pop()
    {
        array_pop($this->array);
        $this->count--;
    }
    /*
      Retorna o valor mediano de uma determinada chave
    */
    public function median($key,$p=1)
    {
        $data = $this->pluck($key);
        $size = count($data);
        $total = 0;
        for($i=0;$i<$size;$i++)
        {
          $total += $data[$i];
        }
        return round($total/$size,$p);
    }
     /*
       Método alias para median 
     */
    public function avg($key,$p=1)
    {
        return $this->median($key,$p);
    }

    public function sum($key)
    {
        $data = $this->pluck($key);
        $size = count($data);
        $total = 0;
        for($i=0;$i<$size;$i++)
        {
          $total += $data[$i];
        }
        return $total;
    }

    public function min($key)
    {
        $data = $this->pluck($key);
        $size = count($data);

        $min = 0;
        for($i=0;$i<$size;$i++)
        {
           if($i == 0) $min = $data[$i]; 
           if($data[$i] < $min) $min = $data[$i]; 
        }
        return $min;
    }

    public function max($key)
    {
        $data = $this->pluck($key);
        $size = count($data);
        $min = $this->min($key);
        $max = $min;
        for($i=0;$i<$size;$i++)
        {
          if($data[$i] > $max) $max = $data[$i];
        }
        return $max;
    }

    
    public function mapWithKeys($fun)
    {
        return array_map($fun, $this->array);
    }

     /*
       Busca uma chave por meio de um valor fornecido
     */
    public function search($value)
    {
        $keyr = null;

        for($i=0;$i < $this->count;$i++)
        {
            foreach($this->array[$i] as $key=>$val)
            {
               if($val == $value){
                $keyr = $key;
                break;
             }
           }
        }
        return $keyr;
    }
    /*
      Retorna os itens da coleção com as chaves especificadas
    */
    public function only($keys)
    {
        $this->tmp = $keys;
        return $this->keyCallback(function($key){
             return in_array($key,$this->tmp);
        });
    }
   /*
      Retorna todos os itens da coleção, exceto aqueles com as chaves especificadas
    */
    public function except($keys)
    {
        $this->tmp = $keys;
        return $this->keyCallback(function($key){
             return !in_array($key,$this->tmp);
        });
    }
    
    public function first() : object
    {
       $object =  $this->count > 0 ? $this->array[0] : []; 
       return (object) $object;
    }

    public function last() : object
    {
       if($this->count == 1)
          $object = $this->array[0];
      elseif($this->count > 1)
          $object = $this->array[$this->count-1];
      else $object = [];
      return (object) $object;
    }

    public function keyCallback($call)
    {
        $arr = [];
        for($i=0; $i < $this->count; $i++)
        {
           foreach($this->array[$i] as $key=>$val)
           {
              if(call_user_func_array($call,[$key,$val]))
              {
                $arr[$i][$key] =  $val; 
              }
           }
       } 
       return $arr; 
    }

    public function filter($call)
    {
        $arr = [];
        for($i=0; $i < $this->count; $i++)
        {
           if(call_user_func($call,$this->array[$i]))
           {
                $arr[] = $this->array[$i]; 
           }
       } 
       return $arr; 
    }
   /*
      Itera sobre os itens da coleção, passando cada valor de item aninhado para o retorno de chamada fornecido

      $this->each(function($item){ return $item['id']; })
   */
    public function each($call)
    {
        $string = '';
        for($i=0; $i < $this->count; $i++)
        {
            $string .= call_user_func($call,$this->array[$i]);
        } 
       return $string; 
    }

    public function alias($keys)
    {
        for($i=0; $i < $this->count; $i++)
        {
           foreach($this->array[$i] as $key=>$val)
           {
              if(array_key_exists($key,$keys))
              {
                $new_key = $keys[$key];
                $this->array[$i][$new_key] = $val;
                unset($this->array[$i][$key]);
              }
           }
        }
    }  

    public function default($keys)
    {
        for($i=0; $i < $this->count; $i++)
        {
            foreach($keys as $key=>$val)
            {
               if(!array_key_exists($key,$this->array[$i]))
               {
                 $val = ($val == '@index') ? $i : $val;
                 $val = ($val == '@hash') ? md5($i) : $val;
                 $val = ($val == '@datetime') ? date('Y-m-d h:m:s') : $val;
                 $this->array[$i][$key] = $val;
               }
            }
        }
    }  

    public function getKeys()
    {
        return $this->count > 0 ? array_keys($this->array[0]) : []; 
    }

   private function getLoopValues($key) : array 
   {
       $arr = [];
       for($i=0;$i<$this->count;$i++)
       {
          if(isset($this->array[$i][$key])) $arr[] = $this->array[$i][$key];
       }
       return $arr;
    }

   public function pluck($keys)
   {
     $id = false;
     if(is_string($keys))
     {
        $keys = [$keys];
     }

     if((count($keys) == 1))
     {
        $id = $keys[0]; 

     }
     
     
     $arr = [];
     for($i=0; $i < count($keys); $i++)
     {
        $arr[$keys[$i]] = $this->getLoopValues($keys[$i]);
     }
     return $id ? $arr[$id] : $arr;
   }

    public function sub($name,$call,$keys=[])
    {
        $keys = $this->pluck($keys);

        for($i=0; $i < $this->count; $i++)
        {
            foreach($this->array[$i] as $key=>$val)
            {
                if($key == $name)
                  $this->array[$i][$key] = call_user_func_array($call,[$val,$keys]);
            }
        }
    }  

    private function getByKey($key_storage,$key,$value,$type='array')
    {
        $arr = [];
        for($i=0; $i < count($this->storage[$key_storage]); $i++)
        {
            if(isset($this->storage[$key_storage][$i][$key]))
            {
                if($this->storage[$key_storage][$i][$key] == $value)
                {
                    $arr[] = (object) $this->storage[$key_storage][$i];
                }
            }
        }
        $count = count($arr);
        $arr = $type == 'object' ? $arr[0] : $arr;
        return $count > 0 ? $arr : null;
    } 

    public function storage($key,$storage)
    {
        $this->storage[$key] = $storage;
        $this->default([$key=>null]);
    }

    public function join($key_storage,$key_pk,$key_fk,$type='array')
    {
       for($i=0; $i < $this->count; $i++)
       {
        if(isset($this->array[$i][$key_pk]))
        {
            $value = $this->array[$i][$key_pk];
            $this->array[$i][$key_storage] = $this->getByKey($key_storage,$key_fk,$value,$type);
        }
      }
    }
}