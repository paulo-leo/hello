<?php
if ($_SERVER['SERVER_PORT'] === '8080' || $_SERVER['SERVER_PORT'] === '8000')
{
    $request_uri = $_SERVER['REQUEST_URI'];
    if ($request_uri !== '/')
    {
        $uri = ltrim($request_uri, '/');
        $_GET['uri'] = $uri;
    }
}

require __DIR__ . '/../bootstrap.php';