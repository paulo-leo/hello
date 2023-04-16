<?php

namespace Modules\Panel;

use Kernel\Router\Route;
use Kernel\Support\Module;

class Panel extends Module
{
    public function main()
    {
     
      Route::get('login',function(){
        
      });


    }

    protected function on()
    {
     #Esse método será executado uma vez na ativação automática do seu módulo.
    }

    protected function off()
    {
     #Esse método será executado uma vez na desativação automática do seu módulo.
    }
}
