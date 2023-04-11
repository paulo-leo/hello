<?php

namespace Kernel\HelloCLI;

use Kernel\Support\CLI;
use Kernel\Router\RouteStorage;

class Serve extends CLI
{
    public function main()
    {
        $this->executeServe();
    }

    private function executeServe()
    {


        $host = 'localhost';
        $port =  8000;

        $socket = stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);

        if (!$socket) {
            echo "Error: $errstr ($errno)" . PHP_EOL;
            exit(1);
        }

        echo "Server running on {$host}:{$port}" . PHP_EOL;

        while (true) {
            $client = stream_socket_accept($socket);

            if ($client) {
                $request = stream_get_line($client, 1024, PHP_EOL);

                if (empty($request)) {
                    fclose($client);
                    continue;
                }

                list($method, $url, $protocol) = explode(' ', $request);

                $headers = [];

                while ($header = stream_get_line($client, 1024, PHP_EOL)) {
                    if (empty(trim($header))) {
                        break;
                    }

                    list($name, $value) = explode(':', $header, 2);

                    $headers[$name] = trim($value);
                }

                $body = stream_get_contents($client);

                $response = "HTTP/1.1 200 OK\r\n";
                $response .= "Content-Type: text/plain\r\n";
                $response .= "Content-Length: " . strlen($body) . "\r\n";
                $response .= "\r\n";
                $response .= $body;

                fwrite($client, $response);

                fclose($client);
            }
        }
    }
}
