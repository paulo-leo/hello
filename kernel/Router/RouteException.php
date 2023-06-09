<?php
namespace Kernel\Router;

use Exception;

class RouteException
{
   private $e;

   public function __construct($exception)
   {
     $this->e = $exception;
   }
   public function view()
   {
        echo $this->template();
   }
   
   private function template()
   {
    return "<!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Oops! Something went wrong.</title>
        <!-- Bootstrap CSS -->
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css'>
    </head>
    <body>
        <div class='container py-5'>
            <div class='row justify-content-center'>
                <div class='col-10'>
                    <div class='card shadow'>
                        <div class='card-header bg-danger text-white'>
                            <h5 class='card-title'>Oops! {$this->e->getMessage()}</h5>
                        </div>
                        <div class='card-body'>
                        <div class='alert alert-warning d-flex align-items-center' role='alert'>
                            Ocorreu um erro de processamento. Tente novamente mais tarde.</div>
                            <p class='border-bottom'>Arquivo: <strong>{$this->e->getFile()}</strong></p> 
                            <p class='border-bottom'>Código: <code>{$this->e->getCode()}</code></p>
                            <p class='border-bottom'>Linha: <strong>{$this->e->getLine()}</strong></p>
                            <p class='border-bottom'>Rateamento: <code>{$this->e->getTraceAsString()}</code></p>
                        </div>
                    </div>
                </div>
             </div>
          </div>
       </body>
    </html>";
   }

}
