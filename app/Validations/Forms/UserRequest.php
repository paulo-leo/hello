<?php

namespace App\Validations\Forms;

use Kernel\Http\FormRequest;
use App\Validations\Rules\CpfRule;

class UserRequest extends FormRequest
{
    protected function rules()
    {
      return [
        'cpf'=>['required',new CpfRule]
      ];
    }

    protected function messages()
    {
      return [
      ];
   }
}