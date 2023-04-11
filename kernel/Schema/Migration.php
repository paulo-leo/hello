<?php 
namespace Kernel\Schema;

use Kernel\Schema\Table;
use Kernel\Support\DB;


class Migration
{
    protected $table;
    protected $column;
    
    public function __construct()
    {
        $this->column = new Table($this->table);
    }
    
    public function up()
    {
        // implementar aqui a lógica de migração para criar a tabela
    }
    
    public function down()
    {
        // implementar aqui a lógica de migração para excluir a tabela
    }

    public function dropIfExists($table)
    {
        DB::execute("DROP TABLE IF EXISTS {$table};");
    }

    final public function executeUp()
    {
         $this->up();
         DB::execute($this->column->toSQL());
    }

    final public function executeDown()
    {
         $this->down();
         //DB::execute($this->column->toSQL());
    }
}
