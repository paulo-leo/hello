<?php

use Kernel\Router\Route;
use App\Forms\Requests\UserRequest;

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


   if($request->fails())
   { 
      
      var_dump($request->errors());
    
   }else
   {
     return 'Dados validados:'.implode('|',$request->validated()); 
   }

});