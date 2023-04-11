<?php

namespace Kernel\Http;

use App\Models\UserModel as User;
use Kernel\Support\ObjectDefault;

class Auth
{
   private static $name = 'user_login_array';
   private static $ignore = ['password'];
   private static $messages = [
      'password_invalid' => 'Senha incorreta'
   ];

   /*
       Verifica se uma sessão de usuário existe.
    */
   public static function login($data)
   {
      $data = (object) $data;
      $login = User::filter(array(
         'email' => $data->email
      ))->collection()->first();

      if ($login) {
         if (secret($data->password) == $login->password) {
            self::createSession((array) $login);
         }
      }
   }

   /*
       Inicia uma sessão
    */
   private static function initSession()
   {
      if (!isset($_SESSION)) {
         session_start();
      }
   }

   /*
       Cria uma sessão de usuário.
    */
   private static function createSession($data)
   {
      self::initSession();
      $_SESSION[self::$name] = array();

      $data['login_at'] = date('Y-m-d H:m:s');
      foreach ($data as $name => $value) {
         if (!in_array($name, self::$ignore)) $_SESSION[self::$name][$name] = $value;
      }
   }

   /*
       Verifica se uma sessão de usuário existe.
    */
   public static function check()
   {
      self::initSession();
      if (isset($_SESSION[self::$name]))
         return $_SESSION[self::$name];
      else return false;
   }

   /*
       Destroí uma sessão de usuário existente.
    */
   public static function destroy()
   {
      self::initSession();
      if (isset($_SESSION[self::$name])) {
         unset($_SESSION[self::$name]);
         return true;
      } else return false;
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
