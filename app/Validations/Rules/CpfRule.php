<?php

namespace App\Validations\Rules;

use Kernel\Http\RuleRequest;

class CpfRule extends RuleRequest
{
  public function passes($name, $value)
  {
    return false;
  }

  public function message()
  {
    return 'O campo "{name}" não é um CPF válido!';
  }
}