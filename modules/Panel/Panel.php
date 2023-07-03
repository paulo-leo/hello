<?php

namespace Modules\Panel;

use Kernel\Http\Auth;
use Kernel\Router\Route;
use Kernel\Http\Request;
use Kernel\Support\Module;
use App\Models\UserModel as User;


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
      
           try{

               return Auth::login(
               $request->only(['email','password']),
               fn() => ['type'=>'success']);

           }catch(\Exception $e){

              return ['type'=>'error',
                     'msg'=>"[{$e->getCode()}] - {$e->getMessage()}"];

           }
  
        });
        
        #Destroí a sessão atual do usuário logado
        Route::get('logout',function(){
             Auth::destroy();
             return redirect('login');
        });
        
      /*Página inicial do painel admistrativo*/ 
      Route::get('panel',fn() => view('@panel.pages.index'))
      ->middleware('auth');



      Route::group(array(
        'prefix'=>'panel'
      ),function(){

        Route::resources('users','@Panel/Controllers/UserController');

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
