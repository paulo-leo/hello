<?php
namespace Kernel\HelloCLI;

use Kernel\Support\CLI;
use Kernel\Support\DB;


class Select extends CLI
{
    public function main()
    {
        $table = $this->first();
        
        if($table != 'sql')
        {
        $limit =  $this->key('limit');
        $limit = $limit ? " LIMIT {$limit}" : '';

        $id =  $this->key('id');
        $id = $id ? " WHERE id = {$id}" : '';

        $sql = "SELECT * FROM {$table}{$id}{$limit}";
       
       }else{
        
        $sql = str_ireplace('.','*',$this->line(false));
       }
       $sql = DB::query()->fetch($sql);
        if (count($sql) > 0) {
            $keys = array_keys($sql[0]);
        
            // Imprime o cabeçalho da tabela
            echo "\e[35m";
            foreach ($keys as $key) {
                printf("%-15s", $key); 
            }
            echo "\e[0m";
            echo "\n";
        
         
            foreach ($sql as $row) {
                foreach ($keys as $key) {


                    if(!is_null($row[$key]))
                    {
                        $row[$key] = strlen(trim($row[$key])) < 1 ? "\e[31mEMPTY\e[0m" : $row[$key];
                    }

                    $value = $row[$key] ?? "\e[34mNULL\e[0m";
                    
                    printf("%-15s", $value); 
                }
                echo "\n";
            }
        }
    }
}