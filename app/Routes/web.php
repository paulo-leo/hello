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


});

