<?php
namespace Kernel\Support;

class DotEnv
{
    private $env = [];
    private $file;
    public function __construct($file)
    {
        if (!file_exists($file)) {
            throw new \Exception("Arquivo .env não encontrado.");
        }
        $this->file = $file;
        $this->parseFile($file);
    }

    private function parseFile($file)
    {
        try {
            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new \Exception("Falha ao abrir arquivo .env.");
            }

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line) || substr($line, 0, 1) === '#') {
                    continue;
                }

                list($name, $value) = explode('=', $line, 2);
                $this->env[$name] = $this->replaceVariables($value);
            }
        } catch (\Throwable $e) {
            throw new \Exception("Erro ao ler arquivo .env: " . $e->getMessage());
        } finally {
            if ($handle) {
                fclose($handle);
            }
        }
    }

    private function replaceVariables($value)
    {
        if (preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $value, $matches)) {
            foreach ($matches[1] as $match) {
                $var = $this->getValueByKey($match);
                $value = str_replace('{{' . $match . '}}', $var, $value);
            }
        }

        return $value;
    }

    public function getValueByKey($key = null, $default = null)
    {
        if ($key === null) {
            return $this->env;
        }

        $value = $this->env[$key] ?? $default;

        if(substr($value,0,1) == '"' && substr($value,-1) == '"')
        {
          $value = substr($value,1,-1);
        }

        if(substr($value,0,1) == "'" && substr($value,-1) == "'")
        {
          $value = substr($value,1,-1);
        }
        return $value;
    }
}
