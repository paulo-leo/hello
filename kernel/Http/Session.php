<?php

namespace Kernel\Http;

class Session
{
    private string $name = 'np_session';

    private function start(): void
    {
        $dir = '/../../storage/sessions';
        if (!is_dir(__DIR__.$dir)){
            mkdir(__DIR__.$dir, 0700,true);
        }
        
        if (!isset($_SESSION)) {
            session_save_path(__DIR__.$dir);
            session_start();
        }
    }

    private function createArray(): void
    {
        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }
    }
    
    /*Salva a chave e valor de uma sessão*/
    public function put(string $name, mixed $value): void
    {
        $this->start();
        $_SESSION[$name] = $value;
    }
     
    /*Recupera o valor de uma sessão especifica*/
    public function get(string $name): mixed
    {
        $this->start();
        return $_SESSION[$name] ?? null;
    }

    public function pull(string $name): mixed
    {
        $this->start();
        $value = $_SESSION[$name] ?? null;
        $this->remove($name);
        return $value;
    }
    /*Retorna um array com todas as sessões*/ 
    public function all(): array
    {
        $this->start();
        return $_SESSION;
    }
    /*Verifica se uma chave de sessão existe*/
    public function exists(string $name): bool
    {
        $this->start();
        return isset($_SESSION[$name]);
    }
    /*Alias para o método "exists"*/
    public function has(string $name): bool
    {
        return $this->exists($name);
    }

    /*Verifica se uma key de sessão não existe*/
    public function missing(string $name): bool
    {
        $this->start();
        return !isset($_SESSION[$name]);
    }
     /*Remove a chave de uma sessão especifica*/
    public function remove(string $name): void
    {
        if ($this->exists($name))
        {
            unset($_SESSION[$name]);
        }
    }
    /*Elimina todas as sessões*/
    public function destroy(): void
    {
        $this->start();
        session_destroy();
    }
}