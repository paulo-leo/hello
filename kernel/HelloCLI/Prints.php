<?php

namespace Kernel\HelloCLI;

use Kernel\Support\CLI;


class Prints extends CLI
{
    public function main()
    {
        //$value = $this->position(1);
        //$this->print($value,"blue");

        $this->confirm();

        $nome = $this->input();
        $this->alert("Seja bem vindo {$nome}");
        
    }
}