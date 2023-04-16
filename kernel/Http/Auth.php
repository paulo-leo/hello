<?php

namespace Kernel\Http;

use Kernel\Http\Session;
use App\Models\UserModel as User;
use Kernel\Support\ObjectDefault;

class Auth 
{
   private static $name = 'user_login_array';
   private static $ignore = ['password'];
   private static $messages = [
      'password_invalid' => 'Senha incorreta'
   ];

   private static $session;

   private static function start()
   {
      if(!self::$session) self::$session = new Session;
   }

   /*
       Verifica se uma sessão de usuário existe.
    */
   public static function login($data)
   {
      self::start();
      $data = (object) $data;
      $login = User::filter(array(
         'email' => $data->email
      ))->collection()->first();

      if ($login)
      {
         if (secret($data->password) == $login->password) 
         {
            $login = (array) $login;
            $login['ip'] = get_ip();
            $login['login_at'] = date('Y-m-d H:m:s');
            self::$session->put(self::$name, $login);
         }
      }
   }


   /*
       Verifica se uma sessão de usuário existe.
    */
   public static function check()
   {
     self::start();
     return self::$session->has(self::$name) 
     ? self::$session->get(self::$name) : false;
   }

   /*
       Destroí uma sessão de usuário existente.
    */
   public static function destroy()
   {
      self::start();
      self::$session->remove(self::$name);
   }

   /*
       Retorna todos os dados da sessão de usário em formato de objeto.
    */
   public static function user()
   {
      $auth = self::check();
      $check = $auth ? true : false;
      $auth = $check ? $auth : array();
      $auth['check'] = $check;
      $auth['all'] = (array) $auth;
      $auth = new ObjectDefault($auth);
      return $auth;
   }

   /*
       Registra um usuário
    */
   public static function register($data)
   {
      $password = secret($data['password']);
      $name = trim($data['name']);
      $email = $data['email'];

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
      if (strlen($name) < 2) return false;
      if (!$password) return false;

      $user = new User;

      if ($user->where('email', $email)->count() > 0) return false;

      $data['name'] = $name;
      $data['password'] = $password;

      return $user->create($data);
   }
}
