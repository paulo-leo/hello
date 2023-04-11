<?php

namespace Kernel\HelloCLI;

use Kernel\Support\CLI;
use Kernel\Support\DB;


class View extends CLI
{
    public function main()
    {

        $action = $this->first();
        if ($action == 'clear') {
            $this->clear();
        }
    }

    private function clear()
    {
        $dir = dirname(__DIR__) . '/../storage/cache/views/*.php';
        $total = 0;
        $files = glob($dir); 
        foreach ($files as $file)
        { 
            if (is_file($file)) 
            { 
                unlink($file);
                $total++;
            }
        }
        if($total > 0)
        {
            $this->print("Cache de Views limpo com sucesso!","green");
            $this->print("Total de {$total} arquivo(s) de cache excluído(s).","blue");
        }else{
            $this->print("O Cache de Views já foi limpo.","yellow");
        }
    }
}
