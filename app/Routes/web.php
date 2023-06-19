<?php

use Kernel\Router\Route;
use Kernel\Http\Auth;
use App\Models\UserModel as User;
use App\Validations\Rules\CpfRule;

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

   return '<!DOCTYPE html>
     <html>
     <head>
         <title>Envio de Arquivo</title>
     </head>
    <body>
       <h1>Envio de Arquivo</h1>
   
         <form method="POST" action="'.url('teste').'" enctype="multipart/form-data">
           <input type="hidden" name="_token" value="'.csrf_token().'">
            <label for="file">Selecione um arquivo:</label>
            <input type="file" name="arquivo[]" multiple>
               <br>
            <input type="submit" value="Enviar">
          </form>
        
        </body>
   </html>';

});

Route::post('teste',function(Kernel\Http\Request $request){ 
      
        $request->validate(
        [
          'arquivo'=>'file'
        ]);

        if($request->fails())
        {
            return $request->errors();

        }else{

           $file = $request->file('arquivo');
           echo $file->name;
          

        }
});