<?php

use Kernel\Router\Route;
use Kernel\Http\Request;

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

   $request = new Request;
   $request->get("nome");

   var_dump($request->get("nome"));

   $request->validations([
    'nome'=>'length:3&100|email'
   ]);

  var_dump($request->check());


});