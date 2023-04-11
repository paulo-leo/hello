<?php

namespace Kernel\MVC;

use Kernel\Support\DB;
use Kernel\Support\ObjectDefault;

class Model
{
   private $name;
   protected $table = null;
   protected $connection = null;
   protected $delete;
   protected $fillable = []; 
   protected $relationships = [];
   protected $primaryKey = 'id';
   protected $ids = [];
   protected $data = [];
   protected $timestamps = false;
   protected $attributes = [];
   protected $trash = false;
   protected $prefix = null;

   public function setIds($ids)
   {
      $this->ids = $ids;
   }

    /*
      Força o uso de assinatura de função sem aplicação de herança no modelo
    */
   private function getData()
   {
      return (object) array(
         'table'=>$this->table,
         'timestamps'=>$this->timestamps,
         'attributes'=>$this->attributes,
         'trash'=>$this->trash,
         'primaryKey'=>$this->primaryKey,
         'fillable'=>$this->fillable,
         'connection'=>$this->connection
      );
   }

   private function prefix()
   {
       if(!is_null($this->prefix))
       {
         $this->prefix = str_ireplace('_','',$this->prefix);
         $this->prefix = "{$this->prefix}_";
       }
   }

   /*
     Força uma assinatura de PDO e DB na construção do objeto
   */
   public function __construct()
   {
      $this->prefix(); 
      $this->name = get_class($this);
      $this->name = $this->ConvertClassName($this->name);
      $this->table = is_null($this->table) ?  
      $this->convertTableName($this->name) : $this->table;
      
      $this->table = $this->prefix.$this->table;

      $ref = DB::table($this->table,$this->connection)
      ->setUseFillable(true)
      ->setFillable($this->fillable)
      ->setPrimaryKey($this->primaryKey)
      ->setTrash($this->trash)
      ->setTimestamps($this->timestamps)
      ->setAttributes($this->attributes)
      ->setNamespace(get_called_class());

      $ref = new ObjectDefault(array(),$ref);
      return $ref;
   }

   public function __toString()
   {
      return DB::table($this->table);
   }

   private function ConvertClassName($name)
   {
     $name = explode('\\',$name);
     $name = $name[count($name) - 1];
     return $name;
   }

   public function getTableName()
   {
      return $this->table;
   }

   private function convertTableName($class)
   {
      $class = preg_replace('/([A-Z]{1})([a-z]{1,})/', '${1}${2}_', $class);
      $class = strtolower($class);
      $class = (substr($class,-1,1) == '_') ? substr($class,0,-1) : $class;
      $class = (substr($class,-6,6) == '_model') ? substr($class,0,-6) : $class;

      if(substr($class,-1,1) != 's')
      {
        $class = "{$class}s";
      }
      return $class;
   }

   public function getClassName()
   {
     return $this->name;
   }

   private static function classStatic()
   {
      $class = get_called_class(); 
      $class = new $class;
      return $class;
   }
 
   public static function withTrash()
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->withTrash();
   }

   public static function onlyTrash()
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->onlyTrash();
   }

   public static function force()
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->force();
   }

   public static function reverse()
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->reverse();
   }


   public static function where($column, $operator=null, $value=null,$value2=null)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->where($column,$operator,$value,$value2);
   }

   public static function all()
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->all();
   }

   public static function orWhere($column=null, $operator=null, $value=null,$value2=null)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->orWhere($column,$operator,$value,$value2);
   }

   public static function whereIn($column,$value)
   {
     $model = self::classStatic();
     $model->getData();
     return DB::table($model->table,$model->connection)
     ->setUseFillable(true)
     ->setFillable($model->fillable)
     ->setPrimaryKey($model->primaryKey)
     ->setTrash($model->trash)
     ->setTimestamps($model->timestamps)
     ->setAttributes($model->attributes)
     ->setNamespace(get_called_class())
     ->whereIn($column,$value);
   }

   public static function whereNotIn($column,$value)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->whereNotIn($column,$value);
   }

   public static function whereNull($column)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->whereNull($column);
   }

   public static function whereNotNull($column)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->whereNotNull($column);
   }

   public static function whereBetween($column,$value1,$value2)
   {
     $model = self::classStatic();
     $model->getData();
     return DB::table($model->table,$model->connection)
     ->setUseFillable(true)
     ->setFillable($model->fillable)
     ->setPrimaryKey($model->primaryKey)
     ->setTrash($model->trash)
     ->setTimestamps($model->timestamps)
     ->setAttributes($model->attributes)
     ->setNamespace(get_called_class())
     ->whereBetween($column,$value1,$value2);
   }

   public static function whereNotBetween($column,$value1,$value2)
   {
     $model = self::classStatic();
     $model->getData();
     return DB::table($model->table,$model->connection)
     ->setUseFillable(true)
     ->setFillable($model->fillable)
     ->setPrimaryKey($model->primaryKey)
     ->setTrash($model->trash)
     ->setTimestamps($model->timestamps)
     ->setAttributes($model->attributes)
     ->setNamespace(get_called_class())
     ->whereNotBetween($column,$value1,$value2);
   }

   public static function get()
   { 
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->get();
   }

   public static function find($id)
   { 
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->find($id);
   }

   public static function select($columns,$prefix=null)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->select($columns,$prefix);
   }

   public static function filter($columns)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->filter($columns);
   }

   public static function paginate($total=10)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->paginate($total);
   }

   public static function update($data)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->update($data);
   }

   public static function delete($data)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->delete($data);
   }

   public static function insert($data)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->insert($data);
   }

   public static function create($data)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->create($data);
   }

   public static function with($with)
   {
      $model = self::classStatic();
      $model->getData();
      return DB::table($model->table,$model->connection)
      ->setUseFillable(true)
      ->setFillable($model->fillable)
      ->setPrimaryKey($model->primaryKey)
      ->setTrash($model->trash)
      ->setTimestamps($model->timestamps)
      ->setAttributes($model->attributes)
      ->setNamespace(get_called_class())
      ->with($with);
   }


   public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}

   /*salva ou atualiza no banco de dados um registro*/
	/*OBS: a atualização acontece quando é informado um id no parametro ou no objeto da classe*/
	public function save()
	{
      $model = DB::table($this->table)
      ->setTimestamps($this->timestamps)
      ->setAttributes($this->attributes);
		if(array_key_exists($this->primaryKey,$this->data))
      {
         $data = $this->data;
         $id = $data[$this->primaryKey];
         unset($data[$this->primaryKey]);
         $model = $model->where($this->primaryKey,$id)
         ->update($data);
      }else{
         $model = $model->create($this->data);
      }
      return $model;
	}

   public function setData($data)
   {
      $this->data = $data;
   }

   public function hasMany($model,$fk_id)
   {
        $table = new $model;
        $table = $table->whereIn($fk_id,$this->ids)->getOnlyFetch();
        return array('key'=>$fk_id,'data'=>$table,'type'=>'array');
   }

   public function hasOne($model,$fk_id)
   {
        $table = new $model;
        $table = $table->whereIn($fk_id,$this->ids)->getOnlyFetch();

        $keys = [];

        for($i=0;$i<count($table);$i++)
        {
           if(in_array($table[$i][$fk_id],$keys)) 
               unset($table[$i]);
            else $keys[] = $table[$i][$fk_id];
        }
        return array('key'=>$fk_id,'data'=>$table,'type'=>'object');
   }

   public function hasManyTo(){}
}