<?php

namespace App\Forms\Requests;

use Kernel\Http\FormRequest;

class UserRequest extends FormRequest
{
    protected function rules()
    {
      return [
        'nome'=>'min:2|max:5|regex:[paulo]{5}|email|required'
      ];
    }

    protected function messages()
    {
      return [
        'nome.email'=>'E-mail inválido.',
        'nome.max'=>'O máximo permitido é 5',
        'nome.min'=>'O mínimo permitido é 2',
        'nome.regex'=>'O valor "{value}" não bate com o padrão."'
      ];
   }
}