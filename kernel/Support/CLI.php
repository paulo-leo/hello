<?php

namespace Kernel\Support;

class CLI
{
  private $line;
  private $args;
  private $keys = array();
  private $first;

  final public function __construct($line)
  {
    $this->line = $line;
    $args = $line;
    unset($args[0]);
    $this->first = $args[1];
    unset($args[1]);
    $this->args = array_values($args);
    $this->loopKeys();
  }

  final protected function dir($path = null, $root = false)
  {
    return !$root ? dirname(__DIR__) . "/../{$path}" :
      dirname(__DIR__) . "/{$path}";
  }

  final protected function hasFile(string $file)
  {
    $file = $this->dir($file);
    return file_exists($file);
  }

  final protected function first(): mixed
  {
    $value = explode(':', $this->first);
    return isset($value[1]) ? $value[1] : $value[0];
  }

  final protected function print($value, $color = null): void
  {

    $color = str_ireplace([
      'success','danger','warning','info'],
      ['green','red','yellow','blue'],$color);

    switch (strtolower($color)) {
      case 'black':
        $color = "\033[30m";
        break;
      case 'red':
        $color = "\033[31m";
        break;
      case 'green':
        $color = "\033[32m";
        break;
      case 'yellow':
        $color = "\033[33m";
        break;
      case 'blue':
        $color = "\033[34m";
        break;
      case 'magenta':
        $color = "\033[35m";
        break;
      case 'cyan':
        $color = "\033[36m";
        break;
      case 'white':
        $color = "\033[37m";
        break;
      case 'pink':
        $color = "\033[38;5;206m";
        break;
      default:
        $color = "\033[0m";
    }

    if (ob_get_level() > 0)
    {
      ob_end_flush();
    }
    echo "{$color}{$value}\033[0m\n";
    ob_start();
  }

  final protected function position(int $index): mixed
  {
    if ($index < 1) $index = 1;
    $index--;
    return $this->args[$index] ?? null;
  }

  private function loopKeys()
  {
    foreach ($this->args as $value) {
      $value = explode('=', $value);
      $key = $value[0];
      $value = isset($value[1]) ? $value[1] : $key;
      $this->keys[$key] = $value;
    }
  }

  final protected function line($header = true)
  {
    $line = $this->line;
    if (!$header) {
      unset($line[0]);
      unset($line[1]);
    }
    return implode(' ', $line);
  }

  final protected function key($key)
  {
    $value = $this->keys[$key] ?? null;
    return ($value != $key) ? $value : null;
  }

  final protected function argument($key)
  {
    return $this->first();
  }

  final protected function parameter($key)
  {
    return $this->key($key);
  }

  public function main()
  {
  }

  final protected function confirm($msg=null,$cancel=null)
  {
    $msg = $msg ?? 'Tem certeza que deseja continuar?';
    $cancel = $cancel ?? 'Ação não confirmada.';

    if (ob_get_level() > 0) {
      ob_end_flush();
    }
    // exibe uma mensagem de confirmação
    echo "\033[33m".$msg . "\033[0m (y/n):";


    // solicita a confirmação do usuário
    $confirm = trim(fgets(STDIN));

    // verifica a resposta do usuário
    if ($confirm != 'y')
    {
      // o usuário cancelou a ação
      $this->print($cancel,"red");
      exit;
    }
    $this->print('Ação confirmada e executada.',"green");
    ob_start();
  }

  final protected function input($msg=null)
  {
    if(ob_get_level() > 0) ob_end_flush();
    $msg = $msg ?? 'Informe um valor para continuar:';
    echo "\n";
    echo "\033[34m".$msg;
    echo "\033[0m";
    $value = trim(fgets(STDIN));
    ob_start();
    return $value;
  }

  final protected function alert($message,$color=null)
  {
    switch($color)
    {
      case 'success' : $color = "\033[42m"; break;
      case 'danger' : $color = "\033[37;41m"; break;
      case 'warning' : $color = "\033[30;43m"; break;
      default : $color = "\033[44m";
    }
    $reset_color = "\033[0m";
    $padded_message = str_pad($message, strlen($message) + 20, " ", STR_PAD_BOTH); 

    echo $color . $padded_message . $reset_color;
  }

  final protected function alertLine($message,$color=null)
  {
    if (ob_get_level() > 0) ob_end_flush();
    echo "\n";
    $this->alert($message,$color);
    echo "\n";
    ob_start();
  }
}