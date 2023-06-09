<?php

namespace Kernel\Http;

use Kernel\Http\Request;

 /*
    required: O campo é obrigatório.
    email: O campo deve conter um endereço de e-mail válido.
    numeric: O campo deve ser um número.
    integer: O campo deve ser um número inteiro.
    digits: O campo deve ter um número específico de dígitos.
    min: O valor mínimo permitido para o campo.
    max: O valor máximo permitido para o campo.
    is: O campo deve corresponder a um valor específico.
    not_is: O campo não deve corresponder a nenhum valor específico.
    alpha: O campo deve conter somente caracteres alfabéticos.
    alpha_num: O campo deve conter somente caracteres alfanuméricos.
    alpha_dash: O campo deve conter somente caracteres alfanuméricos, sublinhados e hífens.
    regex: O campo deve corresponder a uma expressão regular específica.
    confirmed: O campo deve ser confirmado por outro campo com o mesmo nome seguido de "_confirmation".
    unique: O valor do campo deve ser único em uma determinada tabela de banco de dados.
    exists: O valor do campo deve existir em uma determinada tabela de banco de dados.
    */

class RuleRequest extends Request
{
  
  private static $patterns = array(
    'min' => '/^(.{#i,})$/',
    'max' => '/^(.{0,#i})$/',
    'email' => '/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/',
    'date' => '/^(\d{4})-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01])$/',
    'time' => '/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',
    'phone' => '/^(\+?\d{1,2}\s?)?(\(\d{2}\)|\d{2})\s?\d{4,5}\-\d{4}$/',
    'url' => '/^https?:\/\/(?:www\.)?[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}(?:\.[a-zA-Z]{2,})?\/?.*$/',
    'datetime' => '/^\d{4}-\d{2}-\d{2}[T\s]([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/',
    'numeric' => '/^\d+$/',
    'integer' => '/^[+-]?\d+$/',
    'digits' => '/^\d{#i}$/',
    'is' => '/^(#i)$/',
    'not_is' => '/^(?!.*(?:#i)).+$/',
    'alpha' => '/^[a-zA-Z]+$/',
    'alpha_num' => '/^[a-zA-Z0-9]+$/',
    'alpha_dash' => '/^[a-zA-Z0-9_-]+$/',
    'regex' => '/^(#i)$/',
    'confirmed' => '/^(?P<field>.+)_confirmation$/',
    'unique' => 'unique',
    'exists' => 'exists'
   );

   /*Mensagens de padrão do sistema de validação*/
   private static $messages_defaults = array(
        'required' => 'O campo "{name}" é obrigatório.',
        'email' => 'O campo "{name}" deve conter um endereço de e-mail válido.',
        'numeric' => 'O campo "{name}" deve ser um número.',
        'integer' => 'O campo "{name}" deve ser um número inteiro.',
        'digits' => 'O campo "{name}" deve ter um número específico de dígitos.',
        'min' => 'O valor mínimo permitido para o "{name}" campo.',
        'max' => 'O valor máximo permitido para o "{name}" campo.',
        'is' => 'O campo "{name}" deve corresponder a um valor específico.',
        'not_is' => 'O campo "{name}" não deve corresponder a nenhum valor específico.',
        'alpha' => 'O campo deve conter somente caracteres alfabéticos.',
        'alpha_num' => 'O campo "{name}" deve conter somente caracteres alfanuméricos.',
        'alpha_dash' => 'O campo "{name}" deve conter somente caracteres alfanuméricos, sublinhados e hífens.',
        'regex' => 'O campo "{name}" deve corresponder a uma expressão regular específica.',
        'confirmed' => 'O campo "{name}" deve ser confirmado por outro campo com o mesmo nome seguido de "_confirmation".',
        'unique' => 'O valor do "{name}" campo deve ser único em uma determinada tabela de banco de dados.',
        'exists' => 'O valor do "{name}" campo deve existir em uma determinada tabela de banco de dados.',
        'file'=>'Você deve fazer o upload de arquivo para o campo "{name}".',
        'size'=>'O tamanho de arquivo é muito grande.',
        'extension'=>'Extensão de arquivo não permitido para upload.'
   );

   final public static function patterns()
   {
     return self::$patterns;
   }
   
   final public static function messages()
   {
     
   }
   final public static function getMessageDefault($key)
   {
    return self::$messages_defaults[$key] ?? false;
   }

   public function passes($name, $value)
   {
   }

   public function message()
   {
     return '';
   }

   public function __construct()
   {
     self::$messages_defaults[$this->rule()] = $this->message();
   }

   final public function rule()
   {
     $class = get_called_class();
     $class = str_replace('\\','_',$class);
     $class = strtolower($class);
     return $class;
   }
}