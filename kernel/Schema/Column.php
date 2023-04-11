<?php 
namespace Kernel\Schema;

class Column
{
    protected $name;
    protected $type;
    protected $options = [];

    public function __construct($name, $type, $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    public function getName()
    {
       return $this->name;
    }
    
    public function getType()
    {
       $v = $this->type;
       $length = $this->options['length'] ?? false;
       if(is_numeric($length))
       {
         $v .= "({$length})";
       }
       return $v;
    }

    public function getOptions()
    {
       return $this->options;
    }
}