<?php

namespace Kernel\HelloCLI;

use Kernel\Support\CLI;
use Kernel\FS\ReadArray;
use Kernel\Support\Module as Mod;
use  ZipArchive;

class Module extends CLI
{
  public function main()
  {
    $action = $this->first();
    $name = $this->position(1) ?? false;
    $name = ucfirst(trim($name));

    if ($action == 'create' && $name) {
      $this->confirm();
      $this->createModule($name);
    } elseif ($action == 'on' && $name) {
      $this->confirm();
      $this->onModule($name);
    } elseif ($action == 'off' && $name) {
      $this->confirm();
      $this->offModule($name);
    } elseif ($action == 'add' && $name) {
      $this->confirm();
      $this->registerModule($name);
    } elseif ($action == 'remove' && $name) {
      $this->confirm();
      $this->removeModule($name);
    } elseif ($action == 'status' && $name) {
      $this->statusModule($name);
    } elseif ($action == 'pull' && $name) {
      $this->confirm();
      $this->pullModule($name);
    } elseif ($action == 'info' && $name) {
      $this->getInfo($name);
    }elseif ($action == 'list') {
      $this->listModules();
    }elseif ($action == 'clear') {
      $this->clearModule();
    } else {
      $this->print("Sintaxe de comando inválido!", "red");
      $this->print("Siga o padrão:\033[34m php hello module:[comando] [comando]", "yellow");
    }
  }
  
  private function createModuleFile($name)
  {
    $codigo = "<?php\n\n";
    $codigo .= "namespace Modules\\{$name};\n\n";
    $codigo .= "#use Kernel\Router\Route;\n";
    $codigo .= "use Kernel\Support\Module;\n\n";
    $codigo .= "class {$name} extends Module\n";
    $codigo .= "{\n";
    $codigo .= "    public function main()\n";
    $codigo .= "    {\n";
    $codigo .= "     #Seu código aqui...\n";
    $codigo .= "    }\n\n";
    $codigo .= "    protected function on()\n";
    $codigo .= "    {\n";
    $codigo .= "     #Esse método será executado uma vez na ativação automática do seu módulo.\n";
    $codigo .= "    }\n\n";
    $codigo .= "    protected function off()\n";
    $codigo .= "    {\n";
    $codigo .= "     #Esse método será executado uma vez na desativação automática do seu módulo.\n";
    $codigo .= "    }\n";
    $codigo .= "}\n";
    return $codigo;
  }

  private function createModule($name)
  {
    if (strlen($name) < 2) {
      $this->print("O nome do módulo é muito curto, deve ter no mínimo 2 caracteres.", "yellow");
      exit;
    }

    $namespace = "modules/{$name}";

    $dir = $this->dir($namespace);

    if (!is_dir($dir)) {

      mkdir($dir, 0777, true);
      mkdir("$dir/Controllers", 0777, true);
      mkdir("$dir/Models", 0777, true);
      mkdir("$dir/Views", 0777, true);
    } else {
      $this->alertLine("Já existe um diretório de módulo com o nome \"{$name}\".", "danger");
      exit;
    }

    $class = $this->dir("$namespace/{$name}.php");

    if (file_put_contents($class, $this->createModuleFile($name)) !== false) {
      $this->alertLine("Arquivo de classe \"{$name}.php\" criado com sucesso.", "success");
    }


    $file = $this->dir("$namespace/mod.xml");
    if (file_put_contents($file, $this->xml($name)) !== false) {
      $this->alertLine("Arquivo descritivo \"mod.xml\" criado com sucesso.", "success");
    }

    $this->alertLine("Módulo \"{$name}\" criado com sucesso.", "success");
  }

  private function xml($dir)
  {

    $name = $this->input('Nome do módulo:') ?? $dir;
    $version = $this->input('Versão:') ?? '1.0.0';
    $author = $this->input('Seu nome:') ?? 'Seu nome aqui.';

    $codigo = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $codigo .= "<module>\n";
    $codigo .= "  <directory>{$dir}</directory>\n";
    $codigo .= "  <name>{$name}</name>\n";
    $codigo .= "  <uri></uri>\n";
    $codigo .= "  <description></description>\n";
    $codigo .= "  <version>{$version}</version>\n";
    $codigo .= "  <author>{$author}</author>\n";
    $codigo .= "  <image></image>\n";
    $codigo .= "  <screen_shots></screen_shots>\n";
    $codigo .= "  <keyworks></keyworks>\n";
    $codigo .= "  <category></category>\n";
    $codigo .= "  <requires_at_least>3</requires_at_least>\n";
    $codigo .= "  <requires></requires>\n";
    $codigo .= "</module>";
    return $codigo;
  }

  private function registerModule($name)
  {
    $module = new ReadArray('config/modules.php');

    if ($module->has($name)) {
      $this->alertLine("Esse módulo já está registrado.", "danger");
      exit;
    }

    if (!$this->checkModule($name)) {
      $this->alertLine("Não foi possível registrar este módulo, pois seus arquivos estão inválidos.", "danger");
      exit;
    }

    $module->set($name, 'off');
    $module->save();

    $date = date('Y-m-d H:i:s');
    $this->print("{$date}", "blue");
    $this->alertLine("Módulo '{$name}' registrado com sucesso.", "success");
  }

  private function removeModule($name)
  {
    $module = new ReadArray('config/modules.php');

    if ($module->has($name)) {
      $module->del($name);
      $module->save();

      $date = date('Y-m-d H:i:s');
      $this->print("[REMOVE {$name} | {$date}]", "yellow");
      $this->alertLine("Módulo '{$name}' removido com sucesso.", "success");
    } else {
      $this->alertLine("Esse módulo já foi removido.", "danger");
    }
  }

  private function onModule($name)
  {
    $module = new ReadArray('config/modules.php');

    if (!$module->has($name)) {
      $this->alertLine("Não há nenhum módulo registrado com o nome '{$name}' para ativação.", "waning");
      exit;
    }

    if ($module->get($name) == 'on') {
      $this->alertLine("Esse módulo já está ativo.", "success");
      exit;
    }

    if (!$this->checkModule($name)) {
      $this->alertLine("Não foi possível ativar este módulo, pois seus arquivos estão inválidos.", "danger");
      exit;
    }

    $class = "Modules\\{$name}\\{$name}";
    call_user_func(array(new $class, 'eventOn'));

    $module->set($name, 'on');
    $module->save();
    $date = date('Y-m-d H:i:s');
    $this->print("[ON {$name} | {$date}]", "green");
    $this->alertLine("Módulo '{$name}' ativado com sucesso.", "success");
  }

  private function offModule($name)
  {

    $module = new ReadArray('config/modules.php');

    if (!$module->has($name)) {
      $this->print("Não há nenhum módulo registrado com o nome '{$name}' para desabilitação.", "red");
      exit;
    }

    if ($module->get($name) == 'off') {
      $this->print("Esse módulo já está desabilitado.", "red");
      exit;
    }

    if (!$this->checkModule($name)) {
      $this->print("Não foi possível desabilitar este módulo, pois seus arquivos estão inválidos.", "red");
      exit;
    }

    $class = "Modules\\{$name}\\{$name}";
    call_user_func(array(new $class, 'eventOff'));

    $module->set($name, 'off');
    $module->save();
    $date = date('Y-m-d H:i:s');
    $this->print("[OFF {$name} | {$date}]", "red");
    $this->print("Módulo '{$name}' desativado com sucesso.", "green");
  }

  private function checkModule($module)
  {
    $r = false;
    $config = __DIR__ . "/../../modules/{$module}/mod.xml";
    if (file_exists($config)) {
      $class = "Modules\\{$module}\\{$module}";
      if (class_exists($class) && is_subclass_of($class, "Kernel\\Support\\Module")) {
        $r = true;
      }
    }
    return $r;
  }

  private function statusModule($name)
  {
    $module = new ReadArray('config/modules.php');
    $date = date('Y-m-d H:i:s');
    $check = false;
    if ($this->checkModule($name)) {
      $this->print("[Módulo '{$name}' válido  | {$date}]", "green");
      $check = true;
    } else {
      $this->print("[Módulo '{$name}' inválido | {$date}]", "red");
    }

    if ($module->has($name)) {
      $status = $module->get($name);
      $style = $status == 'on' ? 'green' : 'red';
      $status = $check && $status == 'on' ? 'Módulo em execução' : 'Módulo parado';
      $this->print("[{$status}  - {$name} | {$date}]", $style);
    } else {
      $this->print("[Módulo '{$name}' não registrado  | {$date}]", "yellow");
    }
  }

  private function listModules()
  {
    $dir_path = __DIR__ . "/../../modules";

    // Verifica se o diretório existe
    if (is_dir($dir_path)) {
      $module = new ReadArray('config/modules.php');
      // Obtém a lista de arquivos e diretórios no diretório
      $files = scandir($dir_path);

      if(count($files) <= 2)
      {
        $this->alertLine('Até o momento, não existem módulos a serem listados.','warning');
        exit;
      }

      foreach ($files as $file) {
        // Ignora os diretórios "." e ".."
        if ($file == '.' || $file == '..') {
          continue;
        }

        // Verifica se é um diretório
        if (is_dir($dir_path . '/' . $file))
        {
          $status = $this->checkModule($file);
          $style = $status ? "\033[32m" : "\033[31m";
          $msg = $status ? "\033[34m[OK]\033[0m" : "\033[31m[INVALID]\033[0m";

          $register = $module->has($file);

          if ($register) {
            $type = $module->get($file);
            $typea = strtoupper($type);
            $register =  $type == 'on' ? "\033[32m{$typea}" : "\033[31m$typea";
          } else {
            $register = "\033[33m(Não registrado)\033[0m";
          }

          $this->print("   ______{$msg}  ____{$register}_______________________");
          $this->print("  | [ {$file} ]");
          $this->print("  | " . date('Y-m-d H:i:s'));
          echo "\n";
        }
      }
    } else {
      echo 'O diretório não existe.';
    }
  }

  private function pullModule($zipContent)
  {
    $headers = get_headers($zipContent);
    if (strpos($headers[0], '200') === false) {
      $this->print("Arquivo não encontrado ou não pode ser baixado.", "red");
      exit;
    }

    $zipContent = file_get_contents($zipContent);

    $tmpFile = tmpfile();
    fwrite($tmpFile, $zipContent);

    $zip = new ZipArchive;
    $zip->open(stream_get_meta_data($tmpFile)['uri']);
    $zip->extractTo(__DIR__ . "/../../modules");
    $zip->close();

    $this->print("[OK] - Arquivos baixados para o seu projeto.", "green");

    $zip = $this->getLastDir();

    if ($this->checkModule($zip)) {
      $this->print("[OK] - Validação do módulo.", "green");
      $this->print("operação realizada com sucesso.", "green");
      $this->print("-----------DICAS-------------", "yellow");
      $this->print("Use o seguinte comando para verificar a situação do módulo baixado ou atualizado.");
      $this->print("php hello module:status {$zip}", "blue");
      $this->print("----------------------------", "blue");
      $this->print("Use o seguinte comando para registrar o módulo baixado ou atualizado.");
      $this->print("php hello module:status {$zip}", "blue");
    } else {
      dir_delete(__DIR__ . "/../../modules/{$zip}");
      $this->print("[ERROR] - A operação foi cancelada e o projeto foi removido dos arquivos do módulo baixado porque esses arquivos não atendem à estrutura mínima necessária para um módulo.", "red");
    }
  }

  private function getLastDir()
  {
    $dir = __DIR__ . "/../../modules";
    $files = scandir($dir);
    usort($files, function ($a, $b) use ($dir) {
      return filemtime($dir . '/' . $b) - filemtime($dir . '/' . $a);
    });

    $lastDirectory = false;
    foreach ($files as $file) {
      if ($file !== '.' && $file !== '..' && is_dir($dir . '/' . $file)) {
        $lastDirectory = $file;
        break;
      }
    }
    return  $lastDirectory;
  }

  private function getInfo($name)
  {
     $info = Mod::getInfo($name);
     if(count($info) == 0)
     {
      $this->print("Diretório ou arquivo descritivo ‘mod.xml’ não foi localizado para o módulo informado.","red");   
     }
     $this->print("[ID] {$name}","green");
     foreach($info as $name=>$value)
     {
        $name = ucfirst($name);
        if(is_array($value))
        {
          $count = count($value) > 0 ? '' : '***';
          $this->print("\033[33m{$name}: \033[0m{$count}","blue");
          foreach($value as $key)
          {
            $this->print("  {$key}");
          }
        }else{
          $value = strlen(trim($value)) < 1 ? "***" : "{$value}"; 
          $this->print("\033[33m{$name}: \033[0m{$value}","blue");
        }
     }
  }

  private function clearModule()
  {
    $modules = new ReadArray('config/modules.php');
    $total = 0;
    foreach($modules->all() as $key=>$value)
    {
      if (!$this->checkModule($key))
      {
        $total++;
        $modules->del($key);
        $this->print("[{$total}] Módulo '{$key}' removido do registro.", "yellow");
      }
    }

    if($total > 0)
    {
      $modules->save();
      $this->print("[TOTAL {$total}] - Registro de módulos limpo com sucesso.", "green");
    }else{
      $this->print("O registro de módulos já está limpo.", "red");
    }
  }
}
