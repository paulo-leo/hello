<?php

namespace Modules\Panel;

use Kernel\Http\Auth;
use Kernel\Router\Route;
use Kernel\Support\Module;

class Panel extends Module
{
    public function main()
    {
        Route::get('login',function(){
             return Auth::check() ? 
             view('panel') : view('@panel.forms.login');
        }); 

        Route::get('logout',function(){
             Auth::destroy();
             return redirect('login');
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
