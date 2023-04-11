<?php 
namespace Kernel\Schema;

use Kernel\Schema\Column;


class Table
{
    protected $table;
    protected $columns = [];
    protected $options_column = [];
    protected $last_column;

    public function __construct($table)
    {
        $this->table = $table;
    }
    
    public function on($table)
    {
        $this->options_column[$this->last_column]['on'] = $table;
        return $this;
    }

    public function null($null=true)
    {
        $this->options_column[$this->last_column]['null'] = $null;
        return $this;
    }

    public function default($default)
    {
        $this->options_column[$this->last_column]['default'] = $default;
        return $this;
    }

    public function references($column)
    {
        $this->options_column[$this->last_column]['references'] = $column;
        return $this;
    }

    public function increments(string $column = 'id')
    {
        $this->columns[] = new Column($column, 'INTEGER', [
        'primary_key'=>true,
        'unsigned' => true, 
        'autoincrement' => true]);
        return $this;
    }
     
    public function string(string $column, int $length = 255)
    {
        $length = $length > 255 || $length < 1 ? 255 : $length;
        $this->last_column = $column;
        $this->columns[] = new Column($column, 'VARCHAR', ['length' => $length]);
        return $this;
    }

    public function timestamp(string $column)
    {
        $this->last_column = $column;
        $this->columns[] = new Column($column, 'TIMESTAMP', 
        ['default' => 'CURRENT_TIMESTAMP','null'=>true]);
        return $this;
    }

    public function createdAt()
    {
        $this->timestamp('created_at');
        return $this;
    }
     
    public function datetime(string $column)
    {
        $this->last_column = $column;
        $this->columns[] = new Column($column, 'DATETIME', []);
        return $this;
    }

    public function date(string $column)
    {
        $this->last_column = $column;
        $this->columns[] = new Column($column, 'DATE', []);
        return $this;
    }

    public function time(string $column)
    {
        $this->last_column = $column;
        $this->columns[] = new Column($column, 'TIME', []);
        return $this;
    }

    public function foreign(string $column,int $length = 11)
    {
        $this->last_column = $column;
        $this->columns[] = new Column($column, 'INTEGER', ['foreign' => true,'length' => $length]);
        return $this;
    }

    public function integer(string $column,int $length = 11)
    {
        $this->columns[] = new Column($column, 'INTEGER',['length' => $length]);
        return $this;
    }
    
    public function toSQL()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (";

        foreach ($this->columns as $column)
        {
            $options = $column->getOptions();

            $options_column = $this->options_column[$column->getName()] ?? [];
            $options = array_merge($options,$options_column);

            $primary_key = $options['primary_key'] ?? false;
            $autoincrement = $options['autoincrement'] ?? false;
            $foreign = $options['foreign'] ?? false;
            $default = $options['default'] ?? false;


            $defaults = array('NULL','CURRENT_TIMESTAMP','TRUE','FALSE');

            if($default)
            {
                $default = (in_array(strtoupper($default),$defaults)) ? 
                " DEFAULT {$default}" : " DEFAULT '{$default}'" ;
            }else $default = '';


            $null = $options['null'] ?? false;
            $null = $null ? ' NULL' : ' NOT NULL';

            if(strlen($default) > 1) $null = '';

            $sql .= "{$column->getName()} {$column->getType()}{$null}{$default}";
            $sql .= $primary_key ? ' PRIMARY KEY' : '';
            $sql .= $autoincrement ? ' AUTO_INCREMENT' : '';

            if($foreign)
            {
                $references = $options['references'] ?? '';
                $on = $options['on'] ?? '';

                $sql .= ",FOREIGN KEY ({$column->getName()}) REFERENCES {$on}({$references})";
            }
            
            $sql .= ",";
        }

        $sql = rtrim($sql, ',');
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        return $sql;
    }


}
