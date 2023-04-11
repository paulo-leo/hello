<?php

namespace Kernel\Support;

use Kernel\Http\URI;
use Kernel\FS\ReadArray;
use PDO;
use Exception;
//Autor: Paulo Leonardo da Silva Cassimiro
//Classe para criar um objeto único de instancia PDO
class Connection
{

    private static $connection = null;
    private static $Host;
    private static $DBname;
    private static $User;
    private static $Password;
    private static $Driver;
    private static $hasConnect = false;
    //@var PDO
    private static $Connect = null;

    private static function Connect($conn = null)
    {

        self::jsonConfig();

        if (!is_null($conn) && is_array($conn)) {

            if (isset($conn['host'])) {
                self::$Host = $conn['host'];
            }
            if (isset($conn['base'])) {
                self::$DBname = $conn['base'];
            }
            if (isset($conn['user'])) {
                self::$User = $conn['user'];
            }
            if (isset($conn['pass'])) {
                self::$Password = $conn['pass'];
            }
            if (isset($conn['sgbd'])) {
                self::$Driver = $conn['sgbd'];
            }
        }

        $DBname = self::$DBname;

        try {
            if (self::$Connect == null) :
                switch (strtolower(self::$Driver)) {
                    case "mysql":
                        $dsn = 'mysql:host=' . self::$Host . ';dbname=' . $DBname;
                        break;
                    case "sqlite":
                        $dsn = 'sqlite:' . self::$Host . ':' . $DBname;
                        break;
                }
                $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];
                self::$Connect = new PDO($dsn, self::$User, self::$Password, $options);
            endif;
        } catch (PDOExeption $e) {
            PHPErro($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            die;
        }
        self::$Connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$Connect;
    }

    public static function setConnection($connection)
    {
        self::$connection = $connection;
    }
    public static function getConn($connection = null)
    {
         self::setConnection($connection);
         return self::Connect(null);
    }
    /*Metodo para acessar o arquivo de configuração*/
    private static function jsonConfig()
    {
       if(!self::$hasConnect)
       {
        $connection = (is_null(self::$connection)) ? 
        env('DB_CONNECTION') : self::$connection;

         $file = new ReadArray('config/connections.php');

         if($file->has($connection))
         {
          self::$Host = $file->get($connection)['host'];
          self::$DBname = $file->get($connection)['database'];
          self::$User = $file->get($connection)['username'];
          self::$Password = $file->get($connection)['password'];
          self::$Driver = $file->get($connection)['driver'];
          self::$hasConnect = true;
        }else{
            throw new Exception('O nome da conexão informada não existe em seu arquivo de conexão. Verifique o seu arquivo de conexão ou o valor da variável de ambiente "DB_CONNECTION" no arquivo “.env”.');
        } 
      }
    }
}