<?php

namespace App\Validations\Rules;

use Kernel\Http\RuleRequest;

class emails extends RuleRequest
{
    public function passes($name, $value)
    {
        #Sua lógica de validação aqui.
        return true;
    }
    public function message()
    {
        return 'Sua mensagem de erro aqui.';
    }
}
