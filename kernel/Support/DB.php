<?php

namespace Kernel\Support;

use PDO;
use Exception;
use Kernel\Http\URI;
use Kernel\Support\Collection;
use Kernel\Support\ObjectDefault;

class DB
{
  private $sql;
  private $filters = [];
  private $selects = [];
  private $table;
  private $connection = null;
  private $primaryKey = 'id';
  private $collection;
  private $limit = 0;
  private $order_by;
  public $ids = [];
  public $namespace = "";
  private $with = [];
  private $use_fillable = false;
  protected $fillable = [];
  private $timestamps = false;
  protected $attributes = [];
  protected $model = false;
  protected $trash = false;
  private $get_trash = 0;
  private $data = [];
  private $joins = null;


  public function setConnection($connection)
  {
     $this->connection = $connection;
     return $this;
  }

  public function setUseFillable($value)
  {
     $this->use_fillable = $value;
     return $this;
  }

  public function setFillable($value)
  {
     $this->fillable = $value;
     return $this;
  }

  public function getFillable()
  {
     return $this->fillable;
  }

  public function setTrash(bool $trash)
  {
    $this->trash = $trash;
    return $this;
  }

  public function withTrash()
  {
    $this->get_trash = 1;
    return $this;
  }

  public function onlyTrash()
  {
    $this->get_trash = 2;
    return $this;
  }

  public function setAttributes(array $attributes)
  {
    $this->attributes = $attributes;
    return $this;
  }

  public function setTimestamps(bool $timestamps)
  {
    $this->timestamps = $timestamps;
    return $this;
  }

  public function __construct($connection=null)
  {
    $this->connection = $connection;
    $this->namespace = get_class($this);
  }

  public function setNamespace($namespace)
  {
    $this->namespace = $namespace;
    return $this;
  }

  public static function execute($sql)
  {
    $exec = new DB;
    return $exec->connect()->exec($sql);
  }

  public static function schema($table=null)
  {

     $table = $table ? "AND table_name = '{$table}'" : "";
     $schema = env('DB_DATABASE');

     $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '{$schema}' {$table}";


     $query = new DB;
     $query =  $query->fetch($sql);


     return $query;
  
  }

  public function fetch($sql)
  {
    return $this->connect()->query($sql,PDO::FETCH_ASSOC)->fetchAll();
  }

  private function connect()
  {
    return Connection::getConn($this->connection);
  }
  /*
    Faz a construção de uma query
  */
  public static function query($sql=null)
  {
     $db = new DB;
     if($sql) $db->fetch($sql);
     return $db;
  }

  public function setTable($table)
  {
     $this->table = $table;
     $this->collection = new Collection;
  }

  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }

  public function find($id)
  { 
      $id = !is_array($id) ? [$this->primaryKey=>$id] : $id;
      $id = $this->filter($id)->get()[0] ?? array();
      $object = new ObjectDefault((array) $id);
      return $object;
  }

  private function checkFillable($data)
  {
     $r = true;
     $keys = array();
     $data = array_keys($data);
     for($i=0;$i<count($data);$i++)
     {
       if(!in_array($data[$i],$this->fillable))
       {
         $r = false;
         $keys[] = $data[$i];
       }
     }
     if(!$r)
     {
      $keys = implode(',',$keys);
      throw new Exception("As seguintes chaves submetidas para criação do modelo não estão no atributo fillable: [{$keys}]");
     }
     return $r;
  }

  public function create($data)
  {
    $columns = [];
    $values = [];

    if($this->use_fillable)
    {
      if(!$this->checkFillable($data))
      {
         return true;
      }
    }

    foreach($this->attributes as $key=>$val)
    {
       if(array_key_exists($key,$data))
       {
         if(is_null($data[$key])) $data[$key] = $val;
       }else $data[$key] = $val;
    }

    if($this->timestamps)
    {
      $data['created_at'] = date('Y-m-d H:m:s');
    } 

    foreach($data as $column=>$value)
    {
      $columns[] = $column;
      $values[] = !is_null($value) ? "'{$value}'" : "NULL";
    }
    $columns = implode(',',$columns);
    $values = implode(',',$values);

    $sql = "INSERT INTO {$this->table} ({$columns})
    VALUES ({$values})";
    $sql = trim($sql);
    return $this->connect()->exec($sql);
  }

   /*Inseri um registo no banco de dados por meio do método "create"*/
  public function insert($data)
  {
    $check = implode('',array_keys($data));
    $check = is_numeric($check);
    if(!$check) $data = array($data);
    
   $total = 0;
   for($i=0;$i<count($data);$i++)
   {
     if($this->create($data[$i]))
       $total++;
   }
   return $total;
  }

   /*Inseri um registo no banco de dados por meio do método "create"*/
   public function insertGetId($data,$all=false)
   {
       $id = 0;
       if($this->create($data))
       {
         $get = (array) $this->filter($data)
         ->orderBy($this->primaryKey,'DESC')
         ->get()[0] ?? $id;
         if($get > 0) $id = !$all ? $get[$this->primaryKey] : $get;
       }
       return $id;
   }

  public static function table($table,$connection=null)
  {
     $db = new DB($connection);
     $db->setTable($table);
     return $db;
  }

  public static function connection($connection,$table=null)
  {
     $db = new DB($connection);
     if($table) $db->setTable($table);
     return $db;
  }

  private function addFilter($column, $operator, $value,$value2=null)
  {
     $this->filters[] = array(
       'column'=>$column,
       'operator'=>$operator,
       'value'=>$value,
       'value2'=>$value2
      );
  }

  public function where($column, $operator=null, $value=null,$value2=null)
  {
     if(is_null($value))
     {
       $value = $operator;
       $operator = "=";
     }
     if(!is_array($column))
     {
           $this->addFilter($column, $operator, $value,$value2);
     }else
     {
        for($i=0;$i<count($column);$i++)
        {
           $a_column = $column[$i][0];
           $a_operator = $column[$i][1];
           $a_value2 = isset($column[$i][3]) ? $column[$i][3] : null;
            if(!isset($column[$i][2]))
            {
              $a_value = $a_operator;
              $a_operator = "=";
            }else{ 
              $a_value = $column[$i][2];
            } 

            $this->addFilter($a_column, $a_operator, $a_value,$a_value2);
        }
     }
     return $this;
   }
   
   /*
     Set todas as PK's do modelo
   */
   public function setIds()
   {
     $this->ids = $this->collection->pluck($this->primaryKey);
   }

   public function getIds()
   {
      return $this->ids;
   }

   /*Define o nome da chave primária usada pelo modelo*/
   public function setPrimaryKey($name)
   {
      $this->primaryKey = $name;
      return $this;
   }

  public function save()
	{
		 if(array_key_exists($this->primaryKey,$this->data))
      {
         $data = $this->data;
         $id = $data[$this->primaryKey];
         unset($data[$this->primaryKey]);
         $model = $this->where($this->primaryKey,$id)
         ->update($data);
      }else{
        $model = $this->create($this->data);
      }
      return $model;
	}


  private function calcRow($row,$op) : float
  {

      if($this->trash && $this->get_trash == 0)
      {
        $this->whereNull('deleted_at');
      } 

      if($this->trash && $this->get_trash == 2)
      {
        $this->whereNotNull('deleted_at');
      } 

      $sql = "SELECT {$op}({$row}) as total FROM {$this->table}";
      $sql .= " {$this->mountedWhere()} {$this->order_by}";
      $sql = $this->fetch($sql);
      $sql = $sql[0]['total'];
      return (float) $sql;
  }

  public function sum($row) : float
  {
    return $this->calcRow($row,'SUM');
  }

  public function avg($row) : float
  {
    return $this->calcRow($row,'AVG');
  }

  public function count($row='*') : int
  {
    return (int) $this->calcRow($row,'COUNT');
  }

   /*Obtem todos os dados da montagem*/
   public function get()
   {  
     $remove_id = false;
     if(count($this->with) > 0 
            && !$this->hasSelect($this->primaryKey) 
                 && $this->mountedSelect() != '*'){
                      $this->select([$this->primaryKey]);
                      $remove_id = true; }
       

      $this->collection->setCollection($this->fetch($this->mountedQuery()));
      $this->setIds();

      foreach($this->with as $with)
      {
        $class = new $this->namespace;
        $class->setIds($this->getIds());
        $storage = $class->$with();
        $this->collection->storage($with,$storage['data']);
        $this->collection->join($with,$this->primaryKey,$storage['key'],$storage['type']);
      }

     if($remove_id) $this->collection->removeKey('id');
     return $this->collection->get();
   }

   public function results()
   {
     return $this->fetch($this->mountedQuery());
   }

   /*
     Verifica se o registro existe
   */
   public function exists() : bool
   {
     return (count($this->get()) > 0);
   }

   public function doesntExist() : bool
   {
     return (!$this->exists());
   }

   public function getOnlyFetch()
   {
     return $this->fetch($this->mountedQuery());
   }

   public function all()
   {
     return $this->get();
   }

   public function have($d)
   {
     $f = array();
     foreach($d as $k=>$v)
     {
       $f[] = array($k,$v);
     } 
     $db = DB::table($this->table)
     ->setTrash($this->trash)
     ->where($f)->get();
     return $db ? $db[0] : false;
   }

   public function filter($d)
   {
     $f = array();
     foreach($d as $k=>$v)
     {
       $f[] = array($k,$v);
     } 
     $this->where($f);
     return $this;
   }

   public function updateOrCreate($filter,$data)
   {
      $check = $this->have($filter);
      if($check)
      {
        $check = (array) $check;
        $id = $check[$this->primaryKey];
        return DB::table($this->table)->where($this->primaryKey,$id)->update($data);
      }else{
        return DB::table($this->table)->setAttributes($this->attributes)
        ->create($data);
      }
   }

   public function update($columns)
   {

    if($this->use_fillable)
    {
      if(!$this->checkFillable($columns))
      {
         return true;
      }
    }

     $set = [];
     if($this->timestamps)
     {
       $columns['updated_at'] = date('Y-m-d H:m:s');
     } 

     if($this->trash)
     {
      $columns['deleted_at'] = null;
     }
    
     foreach($columns as $column=>$value)
     {
        if(is_null($value))
        {
          $set[] = "{$column} = NULL";
        }else{
          $set[] = "{$column} = '{$value}'";
        }
     }

     $set = "SET ".implode(',',$set);

     $sql = "UPDATE {$this->table} {$set} {$this->mountedWhere()}";
     $sql = trim($sql);
     return $this->connect()->exec($sql);
   }

   public function delete()
   {
     if(!$this->trash)
     {
       $sql = "DELETE FROM {$this->table} {$this->mountedWhere()}";
     }else{
        $datetime = date('Y-m-d H:m:s');
        $sql = "UPDATE {$this->table} SET deleted_at = '{$datetime}' {$this->mountedWhere()}";
     }
     $sql = trim($sql);
     return $this->connect()->exec($sql);
   }

   public function force()
   {
    if($this->trash)
    {
     $this->whereNotNull('deleted_at');
     $sql = "DELETE FROM {$this->table} {$this->mountedWhere()}";
     $sql = trim($sql);
     return $this->connect()->exec($sql);
    }
   }

   public function reverse()
   {
    if($this->trash)
    {
     $this->whereNotNull('deleted_at');
     $sql = "UPDATE {$this->table} SET deleted_at = NULL {$this->mountedWhere()}";
     $sql = trim($sql);
     return $this->connect()->exec($sql);
    }
   }

   public function collection()
   {
     $this->collection->setCollection($this->results());
     return $this->collection;
   }

   public function col()
   {
     return $this->collection();
   }

   public function mountedQuery()
   {
     
     if($this->trash && $this->get_trash == 0)
     {
       $this->whereNull('deleted_at');
     } 

     if($this->trash && $this->get_trash == 2)
     {
        $this->whereNotNull('deleted_at');
     } 

     $sql = "SELECT {$this->mountedSelect()} FROM {$this->table} {$this->joins}";
     $sql .= " {$this->mountedWhere()} {$this->mountedLimit()} {$this->order_by}";
     return $sql;
   }

   public function orderBy($cols,$type=null)
   {
       $type = is_null($type) ? 'ASC' : $type;
       $cols = is_array($cols) ? implode(',',$cols) : $cols;
       $this->order_by = "ORDER BY {$cols} {$type}";
       return $this;
   }

   public function groupBy($cols)
   {
       $cols = is_array($cols) ? implode(',',$cols) : $cols;
       $this->order_by = "GROUP BY {$cols}";
   }

   public function limit($number=10) 
   {
      $this->limit = $number;
      return $this;
   }

   public function mountedLimit()
   {
      $string = str_ireplace('.',',', $this->limit);
      return $this->limit != 0 ? "LIMIT {$string}" : "";
   }

   public function mountedSelect()
   {
      return count($this->selects) > 0 ? 
      implode(',',$this->selects) : '*';
   }

   public function mountedWhere()
   {
      $size = count($this->filters);
      $where = $size > 0 ? 'WHERE' : '';

      $and = "{#{{#and#}}#}";
      $or = "{#{{#or#}}#}";
      $or_end = "{$or} {$and}";

      for($i=0;$i<$size;$i++)
      {
        $column = $this->filters[$i]['column'];
        $operator = $this->filters[$i]['operator'];
        $value = $this->filters[$i]['value'];
        $value2 = $this->filters[$i]['value2'];

        if($where == 'WHERE')
        {

          $where .= $this->setOperator($column,$operator,$value,$value2 );
        }else
        {
          if($column == "@or")
          {
            $where .= " {$or}";
          }else{
            $where .= " {$and}{$this->setOperator($column,$operator,$value,$value2)}";
          }
        } 
      }

      $where = substr($where,-strlen($or)) == $or ? substr($where,0,-strlen($or)) : $where;

      $where = str_replace([$or_end,$and,$or],['OR','AND','OR'],$where);
      return $where;
   }

   public function whereIn($column,$value)
   {
       $this->where($column,'in',$value);
       return $this;
   }

   public function whereNotIn($column,$value)
   {
       $this->where($column,'!in',$value);
       return $this;
   }

   public function whereNull($column)
   {
       $this->where($column,'null','null');
       return $this;
   }

   public function whereNotNull($column)
   {
       $this->where($column,'!null','null');
       return $this;
   }

   public function whereBetween($column,$value1,$value2)
   {
       $this->where($column,'bet',$value1,$value2);
       return $this;
   }

   public function whereNotBetween($column,$value1,$value2)
   {
       $this->where($column,'!bet',$value1,$value2);
       return $this;
   }

   public function orWhere($column=null, $operator=null, $value=null,$value2=null)
   {
       $this->where('@or','');
       if(!is_null($column)) $this->where($column, $operator, $value,$value2);
       return $this;
   }

   public function notWhere($column=null, $operator=null, $value=null)
   {
     $column = "NOT {$column}";
     $this->where($column, $operator, $value);
     return $this;
   }

   /*Faz a construção do operador*/
   private function setOperator($column,$operator,$value=null,$value2=null)
   {
       $filter = '';
       if(($operator == 'in') || ($operator == '!in'))
       {
         $value = '('.implode(',',$value).')';
         $operator = ($operator == 'in') ? 'IN' : 'NOT IN';
         $filter = "{$column} {$operator} {$value}";
       }elseif($operator == 'null' || $operator == '!null'){
         $operator = ($operator == 'null') ? 'IS NULL' : 'IS NOT NULL';
         $filter = "{$column} {$operator}";
       }elseif($operator == 'bet' || $operator == '!bet'){
         $operator ==  ($operator == 'bet') ? 'BETWEEN' : 'NOT BETWEEN';
         $filter = "{$column} {$operator} '{$value}' AND '{$value2}'";
      }else{
          $operator = strtoupper($operator);
          $filter = "{$column} {$operator} '{$value}'";
       }
       return ' '.$filter;
   }
   

   public function hasSelect($key)
   {
     return in_array($key,$this->selects);
   }

   public function select($columns,$prefix=null)
   {
      $prefix = is_null($prefix) ? "{$this->table}." : "{$prefix}.";
      $columns = !is_array($columns) ? [$columns] : $columns;
      for($i=0;$i<count($columns);$i++)
      {
         $this->selects[] = "{$prefix}{$columns[$i]}";
      }
      return $this;
   }

   public function with($calls)
   {
     $calls = is_string($calls) ? [$calls] : $calls; 
     for($i=0;$i<count($calls);$i++)
     {
       $this->with[] = $calls[$i];
     }
     return $this;
   }

   public function paginate(int $total_reg=10)
   {
     $page = $_GET['page'] ?? 1;
     $page = is_numeric($page) ? $page : 1;
     $page = $page < 1 ? 1 : $page;

     $pc = (int) $page;

     $init = $pc - 1;
     $init = $init * $total_reg;

     $this->limit("{$init},{$total_reg}");
    
     $tr = $this->count("*"); // verifica o número total de registros
     $tp = ceil($tr / $total_reg); // verifica o número total de páginas

    // agora vamos criar os botões "Anterior e próximo"
    $previous = $pc -1;
    $next = $pc +1;

    $uri = new URI;
    
    $btn_previous = ($pc>1) ?  $uri->uri(['page'=>$previous]) : null;
    $btn_next = ($pc<$tp) ? $uri->uri(['page'=>$next]) : null;
    $first = $uri->uri(['page'=>1]);
    $last = $uri->uri(['page'=>$tp]);


    $links = [];
    for($i=0;$i<$tp;$i++)
    {
      $num = ($i+1);
      $links[] = (object) array(
        'label'=>$num,
        'link'=>$uri->uri(['page'=>$num]),
        'current_page'=>($pc == $num)
      ); 
    }

     return (object) array(
       'total'=>$tr,
       'per_page'=>$total_reg,
       'check_page'=>$pc <= $tp,
       'total_page'=>$tp,
       'current_page'=>$pc,
       'first'=>$first,
       'next'=>$btn_next,
       'previous'=>$btn_previous,
       'last'=>$last,
       'links'=> $links,
       'items'=>$this->get()
    );
   }

   private function joins($table,$column,$op,$column2,$type)
   {
      if(is_null($column2))
      {
        $column2 = $op;
        $op = "=";
      }
      $this->joins = " {$type} {$table} ON {$table}.{$column} {$op} {$this->table}.{$column2}";
   }

   public function join($table,$column,$op,$column2=null)
   {
      $this->joins($table,$column,$op,$column2,"INNER JOIN");
      return $this;
   }

   public function leftJoin($table,$column,$op,$column2=null)
   {
      $this->joins($table,$column,$op,$column2,"LEFT JOIN");
      return $this;
   }

   public function rightJoin($table,$column,$op,$column2=null)
   {
      $this->joins($table,$column,$op,$column2,"RIGHT JOIN");
      return $this;
   }

    

}