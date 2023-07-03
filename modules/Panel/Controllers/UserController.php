<?php

namespace Modules\Panel\Controllers;

use Kernel\Http\Request;
use Kernel\MVC\Controller;
use App\Models\UserModel;
use Kernel\Http\Auth;

class UserController extends Controller
{
    public function index()
    {
         return view('@panel.pages.users.index');
    }

    public function create()
    {
        return view('@panel.pages.users.create');
    }

    public function store(Request $request)
    {

        try{
           Auth::register($request->except(['_token']));
           return ['type'=>'success','msg'=>"Usuário de e-mail: '{$request->get('email')}' adcionado com sucesso."];
        }catch(\Exception $e){
            return ['type'=>'error','msg'=>"[{$e->getCode()}] {$e->getMessage()}"];
        }

    }

    public function records(UserModel $user)
    {
      return $user->paginate(2);
    }

    public function show(UserModel $user, $id)
    {
      $record = $user->where('id',$id)->get();  
      return $record ? $record[0] : [];
    }
}

    
