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


Route::get('teste/{id}',function(UserRequest $request){

     return $request->fails() ? $request->errors() : $request->validated(); 

});

Route::get('teste1',function(){
     /*
    return Auth::register([
        'name'=>'Paulo Leonardo',
        'email'=>'pauloleonardo.rio@gmail.com',
        'password'=>'123456'
     ]);
     */
    //Auth::login(['email'=>'pauloleonardo.rio@gmail.com','password'=>'123456']);

    
    if(Auth::check()) redirect('teste/10');

});