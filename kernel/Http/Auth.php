<?php

namespace Kernel\Http;

use Kernel\Http\Session;
use App\Models\UserModel as User;
use Kernel\Support\ObjectDefault;
use Exception;

class Auth 
{
   private static $session;

   private static function start()
   {
      if(!self::$session) self::$session = new Session;
   }

   /*
       Verifica se uma sessão de usuário existe.
    */
   public static function login($data,$success=null)
   {
      self::start();
      $data = (object) $data;
      
      $login = User::find(['email' => $data->email]);
     
      if ($login->get('email'))
      {
         if (secret($data->password) == $login->get('password')) 
         {
            $login = $login->all();

            $first = explode(' ', $login['name']);
            $login['first_name'] = $first[0];
            $login['ip'] = get_ip();
            $login['login_at'] = date('Y-m-d H:m:s');

            foreach($login as $name=>$value)
            {
               self::$session->put($name, $value);
            }

            /*Executa uma função anônima no caso de sucesso, caso seja informado no segundo parâmetro.*/
            if(is_callable($success))
            {
               return $success();
            }

         }else{
           throw new Exception('A senha informada é inválida.',100);
         }
      }else{
         throw new Exception('Usuário não encontrado pelo endereço de e-mail informado.',200);
      } 
   }


   /*
       Verifica se uma sessão de usuário existe.
    */
   public static function check($role=null)
   {
     self::start();

     $_email = self::$session->has('email');
     $_name = self::$session->has('name');
     $_role = self::$session->has('role');

     $check = ($_email && $_name && $_role) 
     ? self::$session->all() :false;

     if($role && $check)
     {
       $check = ($check['role'] == $role) ?  $check : false;
     }

     return $check ? (object) $check : false;
   }

   /*
       Destroí uma sessão de usuário existente.
    */
   public static function destroy()
   {
      self::start();
      foreach(self::$session->all() as $key=>$val)
      {
         if($key != 'np_csrf_token') self::$session->remove($key);
      }
   }

   /*
       Retorna todos os dados da sessão de usário em formato de objeto.
    */
   public static function user()
   {
      self::start();
      $auth = new ObjectDefault(self::$session->all() ?? []);
      return $auth;
   }

   /*
       Registra um usuário
    */
   public static function register($data)
   {
      $user = new User; 
      $password = secret($data['password']);
      $name = trim($data['name']);
      $email = $data['email'];

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
         throw new Exception("O endereço de e-mail informado é inválido.",100);

      if (strlen($name) < 2) 
         throw new Exception("O nome não pode ter menos de dois caracteres.",200);

      if (!$password) 
         throw new Exception("A senha não atende ao padrão requerido.",300);

      if ($user->where('email', $email)->count() > 0) 
         throw new Exception("O endereço de e-mail informado já está em uso.",400);

      $data['name'] = $name;
      $data['password'] = $password;

      return $user->insertGetId($data);
   }
}