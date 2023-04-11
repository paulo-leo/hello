<?php

use Kernel\Support\Hello;
use Kernel\FS\ReadArray;

$com = new ReadArray('config/commands.php');
Hello::command($com->all());

Hello::command([
    'route'=>Kernel\HelloCLI\Route::class,
    'serve'=>Kernel\HelloCLI\Serve::class,
    'migrate'=>Kernel\HelloCLI\Migration::class,
    'select'=>Kernel\HelloCLI\Select::class,
    'view'=>Kernel\HelloCLI\View::class,
    'make'=>Kernel\HelloCLI\Make::class,
    'module'=>Kernel\HelloCLI\Module::class,
    'echo'=>Kernel\HelloCLI\Prints::class
]);




