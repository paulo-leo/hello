<?php

namespace Kernel\HelloCLI;

use Kernel\Support\CLI;

class Serve extends CLI
{
    public function main()
    {
        $port = $this->first();
        $port = $port == 'serve' ? '8080' : $port;
        $url = "localhost:{$port}";
        $command = "php -S {$url} public/index.php";
        $command = trim($command);
        system($command);
    }
}