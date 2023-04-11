<?php
namespace Kernel\Support;

 class Hello
 {
   private static $commands = [];
   private static $line;

   public static function setLine($line)
   {
      self::$line = $line;
   }

   public static function command($key,$val=null)
   {
      if(is_array($key))
      {
         foreach($key as $k=>$v)
         {
            self::$commands[$k] = $v;
         }
      }else{
         self::$commands[$key] = $val;
      }
   }

   public static function get($key)
   {
      self::$commands[$key] ?? "Comando \"{$key}\" não reconhecido.";
   }
  /*
    Confere o minimo de comandos
  */
  private static function checkMin()
  {
     return (count(self::$line) >= 2);
  }

   public static function execute()
   {
      $command = self::$line[1] ?? false;

      if($command)
      {
        $command = explode(':',$command);
        $command = $command[0];

        $command = self::$commands[$command] ?? false;

        if($command && self::checkMin())
        {
         if(class_exists($command))
         {
            $class = new $command(self::$line);
            $output = $class->main();
            echo (is_array($output) || is_object($output)) ? json_encode((array) $output) : $output;
         }
        }
      }else{
         echo "\n\n";
         echo "      \033[34mPHP\033[38;5;206m  _   _      _ _     \n"; 
         echo "          | | | | ___| | | ___  \n";
         echo "          | |_| |/ _ \\ | |/ _ \\\n";
         echo "          |  _  |  __/ | | (_) | \n";
         echo "          |_| |_|\\___|_|_|\\___/ \n\n";

         echo "\033[33m    Paulo Leonardo da Silva Cassimiro  \033[38;5;206m\n\n\n\n";
         echo "****************************************************\n";
         echo "********************  DICAS  ***********************\n";
         echo "****************************************************\033[35m\n";
         echo "Para executar um comando válido, escreva: \n php hello <nome_do_comando>\n";
         echo "Após o nome de um comando válido, você poderá declarar\n quantas flags forem necessárias para executar a sua\n tarefa. \n Exemplo: php hello [nome_do_comando] [flag1] \n [flag2]=[valor] ... \n";
         echo "Suas flags devem ser separadas por um espaço em branco\n conforme o exemplo acima.\033[38;5;206m\n";
         echo "****************************************************\n";
         echo "****************************************************\n";
         echo "****************************************************\n\033[0m";
      } 
   }
 }