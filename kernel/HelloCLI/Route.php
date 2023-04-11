<?php
namespace Kernel\HelloCLI;

use Kernel\Support\CLI;
use Kernel\Router\RouteStorage;

class Route extends CLI
{
    public function main()
    {
       // 
        /*
        $name = $this->key('env');
        $name = $name != 'env' ? $name : null;
        return env($name);
        */

        if($this->first() == 'list')
        {
          $this->printRouteTable();
        }
        elseif($this->first() == 'total')
        {
          $this->printRouteTableTotal();
        }
    }

    private function printRouteTable()
    {
        $r = new RouteStorage;
        $size = 133;
        printf("+%'-{$size}s+\n", '');
        printf("| %-7s | %-45s | %-53s | %-20s |\n", 'Verbo', 'Rota/URI', 'Ação/Método', 'Nome');
        printf("+%'-{$size}s+\n", '');
        foreach($r->all() as $route)
        {
            $callback = is_string($route['callback']) ? $route['callback'] : 'callback';
            $name = strlen($route['name']) >= 1 ? $route['name'] : '********';
            printf("| %-7s | %-45s | %-50s | %-20s |\n", $route['method'], $route['route'], $callback,$name);
            printf("+%'-{$size}s+\n", '');
        }
        
    }

    private function printRouteTableTotal()
    {
        $r = new RouteStorage;
        $size = 60;
        printf("+%'-{$size}s+\n", '');

        printf("| %-7s |  %-7s | %-7s | %-7s | %-7s | %-7s |\n", 'GET', 'POST', 'PUT', 'DELETE','PATCH','TOTAL');
        printf("+%'-{$size}s+\n", '');

        $get_total = 0;
        $post_total = 0;
        $put_total = 0;
        $delete_total = 0;
        $p_total = 0;
        foreach($r->all() as $route)
        {
            if($route['method'] == 'GET') $get_total++;
            if($route['method'] == 'POST') $post_total++;
            if($route['method'] == 'PUT') $put_total++;
            if($route['method'] == 'DELETE') $delete_total++;
            if($route['method'] == 'PATCH') $p_total++;
        }
        $total = ($get_total + $post_total + $put_total + $delete_total + $p_total);
        printf("| %-7s |  %-7s | %-7s | %-7s | %-7s | %-7s |\n", $get_total, $post_total, $put_total, $delete_total,$p_total,$total);
        printf("+%'-{$size}s+\n", '');
    }
}