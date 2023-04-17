<?php

use Kernel\Router\Route;
use App\Validations\Forms\UserRequest;
use Kernel\Http\Auth;

Route::get('/',function(){
    return view('welcome');
});

Route::get('about',function(){
    return view('about');
});

Route::get('*',function(){
    return view('404');
});


Route::get('file',function(){

    $url = url('file');
    $token = csrf_token();

    return "<form method='POST' enctype='multipart/form-data' action='{$url}'>
         <input type='hidden' name='_token' value='{$token}'>
          <input type='file'  name='arquivo' multiple>
          <input type='submit' value='Enviar'>
      </form>";

});


Route::post('file',function(Kernel\Http\Request $request){


    $request->validate(['arquivo'=>'file|size:900000|extension:png']);

    $request->set('arquivo','xxxxxxxxx');

    if($request->success()) return $request->validated();
    else return $request->errors();
    
});

Route::get('teste1',function(){
     /*
    return Auth::register([
        'name'=>'Paulo Leonardo',
        'email'=>'xxxx.rixxxo@gmail.com',
        'password'=>'123456'
     ]);
     */
    //Auth::login(['email'=>'xxxx.rixxxo@gmail.com','password'=>'123456']);

    
    if(Auth::check()) redirect('teste/10');

});