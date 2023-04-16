<?php

namespace Kernel\HelloCLI;

use Kernel\Support\CLI;

class Make extends CLI
{
    public function main()
    {
        $action = $this->first();
        $name = $this->position(1) ?? false;

        $table = $this->key('table') ?? null;
        $file = $this->key('copy') ?? false;
        $mod = $this->key('mod') ?? false;

       
        if ($action == 'controller' && $name)
        {
            $this->createFile($name,$this->controller($name,$mod),'c',$mod);
            $this->alertLine('Controle de regra de negócio criado com sucesso.','success');
        }
        elseif ($action == 'request' && $name)
        {
            $this->createFile($name,$this->formRequest($name,$mod),'f',$mod);
            $this->alertLine('Classe de requisição de formulário criada com sucesso.','success');
        }
        elseif ($action == 'rule' && $name)
        {
            $this->createFile($name,$this->ruleRequest($name,$mod),'r',$mod);
            $this->alertLine('Classe de validação personalizada criada com sucesso.','success');
        }
        elseif($action == 'model' && $name)
        {
            $this->createFile($name,$this->model($name,$table,$mod),'m',$mod);
            $this->alertLine('Modelo de entidade de banco de dados criado com sucesso.','success');
        }
        elseif($action == 'service' && $name)
        {
            $this->createFile($name,$this->service($name,$mod),'s',$mod);
            $this->alertLine('Provedor de serviços criado com sucesso.','success');
        }
        elseif($action == 'middleware' && $name)
        {
            $this->createFile($name,$this->middleware($name,$mod),'mi',$mod);
            $this->alertLine('Middleware para rotas criado com sucesso.','success');
        }
        elseif($action == 'migration' && $name)
        {
            if($table)
            {
               system("php hello migrate:create {$name} table={$table}");
            }else{
                $this->alertLine("Você deve informar o nome da tabela da sua migração conforme o exemplo abaixo:","warning");
                $this->print("php hello make:migration [name_migration] table=[name_table]","yellow");
            }
        }
        elseif($action == 'view' && $name)
        {
           $file = $file ? $this->getFile($file) : "";
           $this->createFile($name, $file,'v',$mod);
        }
        elseif($action == 'file' && $name)
        {
           $file = $file ? $this->getFile($file) : "";
           $this->createFileLivre($name, $file);
        }else{
            $this->alertLine("Você deve informar um comando válido!","warning");
            $this->print("Exemplo: php hello make:[comando] [name]","yellow");  
        }
    }

    private function getFile($file)
    {
        $file = $this->dir($file);
        if(!file_exists($file))
        {
           $this->print("Não foi possível criar a view com a flag \"file\", pois o arquivo não foi localizado em seu projeto.",'red');
           exit;
        }
        $file = file_get_contents($file);
        return $file;
    }

    private function getClassNamespace($class,$type='c',$mod=false)
    {
    
     $mod = (is_string($mod) && strlen($mod) >= 2) ? ucfirst($mod) : false;

      $namespace = match($type) {
        'c' => !$mod ? "App\\Controllers" : "Modules\\{$mod}\\Controllers",
        'm' => !$mod ? "App\\Models" : "Modules\\{$mod}\\Models",
        's' => !$mod ? "App\\Services" : "Modules\\{$mod}\\Services",
        'mi'=> !$mod ? "App\\Middlewares" : "Modules\\{$mod}\\Middlewares",
        'f'=> !$mod ? "App\\Validations\\Forms" : "Modules\\{$mod}\\Validations\\Forms",
        'r'=> !$mod ? "App\\Validations\\Rules" : "Modules\\{$mod}\\Validations\\Rules"
      };

      $index = explode('/', $class);
      $class = end($index);

      if(count($index) > 1)
      {
       $index = array_slice($index, 0, -1);
       $namespace .= '\\' . ucwords(implode('\\', $index));
      }

      return (object) ['class' => $class, 'namespace' => $namespace];
     }


    private function controller($class,$mod)
    {
        $class = $this->getClassNamespace($class,'c',$mod);

        $namespace = $class->namespace;
        $class = $class->class;

        $codigo = "<?php\n\n";
        $codigo .= "namespace {$namespace};\n\n";
        $codigo .= "#use Kernel\Http\Request;\n";
        $codigo .= "use Kernel\MVC\Controller;\n\n";
        $codigo .= "class {$class} extends Controller\n";
        $codigo .= "{\n";
        $codigo .= "    public function index()\n";
        $codigo .= "    {\n";
        $codigo .= "        // Seu código aqui...\n";
        $codigo .= "    }\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function formRequest($class,$mod)
    {
        $class = $this->getClassNamespace($class,'f',$mod);

        $namespace = $class->namespace;
        $class = $class->class;

        $codigo = "<?php\n\n";
        $codigo .= "namespace {$namespace};\n\n";
        $codigo .= "use Kernel\Http\FormRequest;\n\n";
        $codigo .= "class {$class} extends FormRequest\n";
        $codigo .= "{\n";
        $codigo .= "    public function rules()\n";
        $codigo .= "    {\n";
        $codigo .= "        return array();\n";
        $codigo .= "    }\n";
        $codigo .= "    public function messages()\n";
        $codigo .= "    {\n";
        $codigo .= "        return array();\n";
        $codigo .= "    }\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function ruleRequest($class,$mod)
    {
        $class = $this->getClassNamespace($class,'r',$mod);

        $namespace = $class->namespace;
        $class = $class->class;

        $codigo = "<?php\n\n";
        $codigo .= "namespace {$namespace};\n\n";
        $codigo .= "use Kernel\Http\RuleRequest;\n\n";
        $codigo .= "class {$class} extends RuleRequest\n";
        $codigo .= "{\n";
        $codigo .= "    public function passes(\$name, \$value)\n";
        $codigo .= "    {\n";
        $codigo .= "        #Sua lógica de validação aqui.\n";
        $codigo .= "        return true;\n";
        $codigo .= "    }\n";
        $codigo .= "    public function message()\n";
        $codigo .= "    {\n";
        $codigo .= "        return 'Sua mensagem de erro aqui.';\n";
        $codigo .= "    }\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function service($class,$mod)
    {
        $class = $this->getClassNamespace($class,'s',$mod);

        $namespace = $class->namespace;
        $class = $class->class;

        $codigo = "<?php\n\n";
        $codigo .= "namespace {$namespace};\n\n";
        $codigo .= "use Kernel\Http\ServiceProvider;\n\n";
        $codigo .= "class {$class} extends ServiceProvider\n";
        $codigo .= "{\n";
        $codigo .= "    public function boot()\n";
        $codigo .= "    {\n";
        $codigo .= "        // Seu código aqui...\n";
        $codigo .= "        // \$this->register('nome_do_seu_registro');\n";
        $codigo .= "    }\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function middleware($class,$mod)
    {
        $class = $this->getClassNamespace($class,'mi',$mod);

        $namespace = $class->namespace;
        $class = $class->class;

        $codigo = "<?php\n\n";
        $codigo .= "namespace {$namespace};\n\n";
        $codigo .= "use Kernel\Http\Middleware;\n\n";
        $codigo .= "class {$class} extends Middleware\n";
        $codigo .= "{\n";
        $codigo .= "    public function handle(\$method,\$route)\n";
        $codigo .= "    {\n";
        $codigo .= "        // Seu código aqui...\n";
        $codigo .= "        // A lógica do seu Middleware deverá retorna 'true' ou 'false' para filtrar a rota.\n";
        $codigo .= "    }\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function model($class,$table=null,$mod=false)
    {
        $class = $this->getClassNamespace($class,'m',$mod);

        $namespace = $class->namespace;
        $class = $class->class;

        $codigo = "<?php\n\n";
        $codigo .= "namespace {$namespace};\n\n";
        $codigo .= "use Kernel\MVC\Model;\n\n";
        $codigo .= "class {$class} extends Model\n";
        $codigo .= "{\n";
        if($table) $codigo .= "    protected \$table = '{$table}';\n";   
        $codigo .= "    protected \$timestamps = true;\n";  
        $codigo .= "    protected \$trash = false;\n"; 
        $codigo .= "    protected \$fillable = array();\n"; 
        $codigo .= "    protected \$attributes = array();\n";
        $codigo .= "}\n";
        return $codigo;
    }

    private function createFile($file, $texto,$type='c',$mod=false)
    { 
       $dir = $file;
       $dir = explode('/', $dir);
       $file = array_pop($dir);

       $dir = implode('/', $dir);

       $mod = (is_string($mod) && strlen($mod) >= 2) ? ucfirst($mod) : false;

        $namespace = match($type) {
            'c' => !$mod ? "app/Controllers" : "modules/{$mod}/Controllers",
            'm' => !$mod ? "app/Models": "modules/{$mod}/Models",
            's' => !$mod ? "app/Services": "modules/{$mod}/Services",
            'v' => !$mod ? "resources/views": "modules/{$mod}/Views",
            'mi'=> !$mod ? "app/Middlewares": "modules/{$mod}/Middlewares",
            'f'=> !$mod ? "app/Validations/Forms": "modules/{$mod}/Validations/Forms",
            'r'=> !$mod ? "app/Validations/Rules": "modules/{$mod}/Validations/Rules"
        };

        $dir = $this->dir("{$namespace}/{$dir}");

       if (!is_dir($dir))
       {
           mkdir($dir, 0777, true);
       }

       $file = ($type == 'v') ? "{$dir}{$file}.view.php" : "{$dir}{$file}.php";

       if(file_exists($file))
       {
          $dir = $this->rootDir($dir);
          $this-> alertLine("O arquivo especificado já existe no diretório informado.",'danger');
          $this->print("[root]/{$dir}");
          echo "\033[38;5;206m";
          system("cd {$dir};ls;");
          echo "\033[0m";
          exit;
       }

       $handle = fopen($file, 'w');
       if ($handle === false) {
        // lidar com o erro de abertura do arquivo
        return false;
       }

       if (fwrite($handle, $texto) === false) {
         // lidar com o erro de gravação do arquivo
          fclose($handle);
          return false;
       }

      fclose($handle);
      $file = $this->rootDir($file);
      if($type == 'v'){  $this->alertLine("Visualizador criado com sucesso.","info"); }
      $this->alertLine("Arquivo criado com sucesso em: {$file}","info");

      // Definir permissões do arquivo recém-criado
      chmod($file, 0777);

      return true;
 }

  private function rootDir($dir)
  {
     $dir = explode('../',$dir);
     return $dir[1];
  }

  private function createFileLivre($file, $texto)
    { 
       $file = substr($file,0,1) == '/' ? substr($file,1) : $file;

       $root = explode('/',$file);
       
       if(count($root) == 1)
       {
         $this->print("Você não pode criar um arquivo no diretório raiz.","red");
         exit;
       }

       if(substr($file,0,6) == 'kernel')
       {
         $this->print("Você não pode criar um arquivo no diretório kernel.","red");
         exit;
       }

       if(substr($file,0,6) == 'config')
       {
         $this->print("Você não criar um arquivo no diretório config.","red");
         exit;
       }

       $file = $this->dir($file);

       if(file_exists($file))
       {
          $this->print("O arquivo especificado já existe no diretório informado.","red");
          exit;
       }

       $handle = fopen($file, 'w');
       if ($handle === false) {
        // lidar com o erro de abertura do arquivo
        return false;
       }

       if (fwrite($handle, $texto) === false) {
         // lidar com o erro de gravação do arquivo
          fclose($handle);
          return false;
       }

      fclose($handle);
      $file = $this->rootDir($file);
      $this->print("Arquivo criado com sucesso em:\"{$file}\".",'green');
      // Definir permissões do arquivo recém-criado
      chmod($file, 0777);

      return true;
 }

}