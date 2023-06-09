<?php

namespace Modules\Panel;

use Kernel\Http\Auth;
use Kernel\Router\Route;
use Kernel\Http\Request;
use Kernel\Support\Module;


class Panel extends Module
{
    public function main()
    {
        #Exibe um formulário de login
        Route::get('login',function(){

             return Auth::check() ? 
             redirect('panel') : 
             view('@panel.forms.login');
             
        }); 
        
        #Faz o login do usuário
        Route::post('login',function(Request $request){

           $login = array('email','password');
           $login = $request->only($login);

           Auth::login($login,fn() => redirect('panel'));
        });
        
        #Destroí a sessão atual do usuário logado
        Route::get('logout',function(){
             Auth::destroy();
             return redirect('login');
        });
        
        
      Route::get('panel',function(){

        //return view('@panel.pages.index');
  
        return session()->get('user.name');

      })->middleware('auth');


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
