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

    public function put(string $name, mixed $value): void
    {
        $this->start();
        $this->createArray();
        $_SESSION[$this->name][$name] = $value;
    }

    public function get(string $name): mixed
    {
        $this->start();
        $this->createArray();

        
        if(substr($name,0,5) == 'user.')
        {
             $name = str_replace('user.','',$name);
             $key = $_SESSION[$this->name]['user'] ?? null;
             return (array) $key;
        }else{
            return $_SESSION[$this->name][$name] ?? null;
        }

        
    }

    public function pull(string $name): mixed
    {
        $this->start();
        $this->createArray();
        $value = $_SESSION[$this->name][$name] ?? null;
        $this->remove($name);
        return $value;
    }

    public function all(): array
    {
        $this->start();
        $this->createArray();
        return $_SESSION[$this->name];
    }

    public function exists(string $name): bool
    {
        $this->start();
        $this->createArray();
        return isset($_SESSION[$this->name][$name]);
    }

    public function has(string $name): bool
    {
        return $this->exists($name);
    }

    public function missing(string $name): bool
    {
        $this->start();
        $this->createArray();
        return !isset($_SESSION[$this->name][$name]);
    }

    public function remove(string $name): void
    {
        if ($this->exists($name)) {
            unset($_SESSION[$this->name][$name]);
        }
    }

    public function destroy(): void
    {
        $this->start();
        session_destroy();
    }
}