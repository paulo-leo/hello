<?php

use Kernel\Router\Route;

Route::get('/',function(){
    return view('welcome');
});

Route::get('*',function(){
    return view('404');
});

Route::get('blog',function(){
    return view('@blog.home');
});