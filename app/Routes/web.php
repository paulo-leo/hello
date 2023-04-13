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

   $request->validate([
    'nome'=>'min:2|max:5|regex:[paulo]{5}|email'
   ],
   [
    'nome.email'=>'E-mail inválido.',
    'nome.max'=>'O máximo permitido é 5',
    'nome.min'=>'O mínimo permitido é 2',
    'nome.regex'=>'O valor "{value}" não bate com o padrão."'
   ]);

   var_dump($request->errors());

   var_dump($request->fails());

   var_dump($request->success());

   var_dump($request->validated());


});