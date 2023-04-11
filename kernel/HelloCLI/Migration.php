<?php
namespace Kernel\HelloCLI;

use Kernel\Support\CLI;
use Kernel\Support\DB;
use Kernel\Schema\Table;



class Migration extends CLI
{
    private $table = 'np_migrations';
    private $migrations;
    private $path_migrations = 'app/Migrations';

    private function init()
    {

       try {
             $this->migrations = DB::schema($this->table);
            if (count($this->migrations) < 1) {
            $this->createTableMigrationForController();
        }
      } catch (\Exception $e) {
          $this->print("Para realizar qualquer operação no banco de dados, é necessário configurar corretamente a conexão no arquivo .env, inserindo as informações de host, porta, nome do banco de dados, usuário e senha. Somente após as configurações corretas serem realizadas, será possível modificar as migrações com segurança. Certifique-se de preencher corretamente o arquivo .env antes de realizar qualquer alteração nas migrações.","red");
          exit;
      }
    }

    public function main()
    {
        $this->init();
        $name = $this->position(1) ?? '';
        $table = $this->key('table') ?? '';

        switch($this->first())
        {
            case 'create' : 
            
            if(strlen($name) >= 2 && strlen($table) >= 2)
            {
                if(!$this->migrationExists($name))
                {
                    $name = 'c'.date('Y_m_d_His').'_'.$name;
                    $file = $this->dir("{$this->path_migrations}/{$name}.php");

                    $content = $this->createMigrationFile($name,$table);
                    $this->createFile($file,$content);

                    $this->print("Migração \"{$name}\" criada com sucesso.","green");
                    $this->print("[root]/".$this->rootDir($file),"magenta");

                }else
                {
                    $this->print("Já existe um arquivo de migração de nome \"{$name}\".","red");
                }
            }else{
               $this->print("Você deve informar um nome de migração e um nome de tabela(table) válidos!","red");
               $this->print("php hello migration:create [nome_migration] table=[nome_tabela]","magenta"); 
            }

            break;

            case 'status' : 

            
            $data = DB::table($this->table)->collection();

            $migrations = $data->pluck('migration');
           

            $batchs = [];
            foreach($data->all() as $k)
            {
                $batchs[$k['migration']] = $k['batch'];
            }


            $classes = $this->listMigrations();

            $list = $classes;
    
            foreach($migrations as $migration)
            {
              if(!in_array($migration,$list)) $list[] = $migration;
            }
            arsort($list);

            printf("+----------------------+-----------------------------+-------------------------------+\n");
            printf("| %-62s | %-5s | %-10s |\n", "Migração", "Batch ", "Status");
            printf("+------------------------------------------------------------------------------------+\n");
            
            foreach($list as $name)
            {
                $status = in_array($name,$migrations) ? "\033[32m Executado" : "\033[31m Pendente";
                $batch = $batchs[$name] ?? '*';

                printf("| %-60s | %-6s | %-19s |\n", $name, $batch, $status."\033[0m");
                printf("+------------------------------------------------------------------------------------+\n");
            }

            break;

            case 'rollback' : 
                $this->migrationRollback();
            break;

            case 'reset' : 
                $this->migrationRollback(true);
            break;

            case 'fresh' : 

                $line = readline();
                if(trim($line) != 'sim'){
                    echo "Ação cancelada.\n";
                    exit;
                }
                echo "\nContinuando...\n";
         
                
                system('php hello migrate:reset; php hello migrate:status; php hello migrate; php hello migrate:status;');
            break;

            default : 
              
            $this->executeMigration();

        }
       /*
        echo "Tem certeza de que deseja excluir o diretório? Digite 'sim' para confirmar: ";
          confirmacao = readline();
       */
 
    }

    private function migrationRollback($reset=false)
    {
        $last = DB::table($this->table)->collection()->max('batch');

        if($last > 0 || $reset)
        {
            $migrations = DB::table($this->table);
           
             if(!$reset)
             {
                $migrations = $migrations->where('batch',$last);
             }

             $migrations = $migrations->collection()
            ->pluck('migration'); 
             arsort($migrations);
            $order = 0;
            foreach($migrations as $migration)
            {
                $order++;
                require_once $this->dir("app/Migrations/{$migration}.php");
                $class = "App\\Migrations\\{$migration}";
                call_user_func(array(new $class, 'executeDown'));
    
                $this->print("[{$order}] - Método down() executado em: \"{$class}\".","red");
                
            }
             if($order > 0)
             {
                if($reset)
                {
                     DB::table($this->table)->delete();
                     $this->print("A transação foi revertida com sucesso para o estado anterior, e todas as alterações feitas foram desfeitas.","green");
                }else{
                    DB::table($this->table)->where('batch',$last)->delete();
                    $this->print("A transação foi revertida com sucesso para o estado anterior, e todas as alterações feitas no último lote {$last} de {$order} registro(s) foram desfeita(s).","green");
                }
                $this->print("Os registros afetados foram restaurados para o estado anterior à transação que os modificou.","green");
             }else{
                $this->print("Nenhum lote para ser executado.","red");
             }
        }else{
            $this->print("Nenhum lote para ser executado.","red");
        }

    }

    private function migrationExists($string)
    {
    $pattern = '/^c(\d{4}_\d{2}_\d{2}_\d{6})?_(.+)$/';
    $r = false;
    foreach($this->listMigrations() as $migration)
    {
       if (preg_match($pattern, $migration, $matches))
       {
           $migrationName = $matches[2];
           if ($migrationName === $string)
           {
               $r = true;
               break;
           }
       }
     }
    return $r;
   }



    private function listMigrations()
    {
        $files = glob("app/Migrations/*.php");
        $main = "Kernel\\Schema\\Migration";
        $migrations = [];

        foreach ($files as $file) 
        {
         
             $class_name = pathinfo($file, PATHINFO_FILENAME);
             $class = "App\\Migrations\\{$class_name}";

             if(class_exists($class) && is_subclass_of($class, $main))
             {
                $migrations[] = $class_name;
             }
        }   

        return $migrations;
    }

    /*Executa as migrações*/
    private function executeMigration()
    {
        $data = DB::table($this->table)->collection();
        $migrations = $data->pluck('migration');
        $batch = $data->max('batch') + 1;
        $files = $this->listMigrations();
        sort($files); $order = 1; $total = 0;

        foreach($files as $migration)
        {
         if(!in_array($migration,$migrations))
         {
            require_once $this->dir("app/Migrations/{$migration}.php");
            $class = "App\\Migrations\\{$migration}";
            call_user_func(array(new $class, 'executeUp'));

            $this->registerMigration($migration,$batch);

            $this->print("[{$order}] - Migração: \"{$class}\" executada com sucesso.","green");
            $order++;
            $total++;
          }
        }

        $msg = $total > 0 ? "TOTAL de {$total} executada(s) com sucesso." : "Nenhuma migração foi executada.";
        $this->print($msg, $total > 0 ? "green" : "red");
    }


    private function registerMigration($name,$batch)
    {
        DB::table($this->table)->insert([
            'migration'=>$name,
            'batch'=>$batch
        ]);
    }

    /*Cria a tabela SQL para controle das migrações*/
    private function createTableMigrationForController()
    {
       $table = new Table($this->table);
       
       $table->increments('id');
       $table->string('migration')->null(false);
       $table->integer('batch');
       $table->createdAt();

       DB::execute($table->toSQL());

    }

    private function createMigrationFile($name,$table)
    {
        $codigo = "<?php\n\n";
        $codigo .= "namespace App\Migrations;\n\n";
        $codigo .= "use Kernel\Schema\Migration;\n\n";
        $codigo .= "class {$name} extends Migration\n";
        $codigo .= "{\n";
        $codigo .= "    protected \$table = '{$table}'; \n\n";
        $codigo .= "    /************************\n";
        $codigo .= "     **  Cria a migração   **\n";
        $codigo .= "     **                    **\n";   
        $codigo .= "     **  @return void      **\n";   
        $codigo .= "     ************************/\n";
        $codigo .= "    public function up()\n";
        $codigo .= "    {\n";
        $codigo .= "        \$this->column->increments();\n\n";
        $codigo .= "        //Sugestão no cometário de campo \"name\":\n";
        $codigo .= "        //\$this->column->string('name');\n\n";
        $codigo .= "        \$this->column->createdAt();\n";
        $codigo .= "    }\n\n";
        $codigo .= "    /************************\n";
        $codigo .= "     ** Reverte a migração **\n";
        $codigo .= "     **                    **\n";   
        $codigo .= "     ** @return void       **\n";   
        $codigo .= "     ************************/\n";
        $codigo .= "    public function down()\n";
        $codigo .= "    {\n";
        $codigo .= "        \$this->dropIfExists(\$this->table);\n";
        $codigo .= "    }\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function createFile($nome_arquivo,$texto)
    {
         $arquivo = fopen($nome_arquivo, 'w');
        // Escrever um texto no arquivo
        fwrite($arquivo, $texto);
        // Fechar o arquivo
        fclose($arquivo);
    }

    private function rootDir($dir)
    {
       $dir = explode('../',$dir);
       return $dir[1];
    }
}
