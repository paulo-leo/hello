<?php

use Kernel\Router\Route;
use App\Validations\Forms\UserRequest;

Route::get('/',function(){
    return view('welcome');
});

Route::get('about',function(){
    return view('about');
});

Route::get('*',function(){
    return view('404');
});


Route::get('teste',function(){

   $request = new UserRequest;

   return $request->fails() ? 
          $request->errors() : 
          $request->validated(); 

});