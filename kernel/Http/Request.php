<?php

namespace Kernel\Http;

use Kernel\FS\ReadArray;

class Request
{

    /*
    required: O campo é obrigatório.
    email: O campo deve conter um endereço de e-mail válido.
    numeric: O campo deve ser um número.
    integer: O campo deve ser um número inteiro.
    digits: O campo deve ter um número específico de dígitos.
    min: O valor mínimo permitido para o campo.
    max: O valor máximo permitido para o campo.
    between: O valor do campo deve estar entre um intervalo específico.
    in: O campo deve corresponder a um valor específico de uma lista.
    not_in: O campo não deve corresponder a nenhum valor específico de uma lista.
    alpha: O campo deve conter somente caracteres alfabéticos.
    alpha_num: O campo deve conter somente caracteres alfanuméricos.
    alpha_dash: O campo deve conter somente caracteres alfanuméricos, sublinhados e hífens.
    regex: O campo deve corresponder a uma expressão regular específica.
    confirmed: O campo deve ser confirmado por outro campo com o mesmo nome seguido de "_confirmation".
    unique: O valor do campo deve ser único em uma determinada tabela de banco de dados.
    exists: O valor do campo deve existir em uma determinada tabela de banco de dados.
    */

    private $__all;
    private $__check;
    private $__files;
    private $erros;
    private $erros_person;
    private $__values;
    private $allHeaders;
    private $request;
    private $_value;
    private $_validations;
    private $_validations_keys;

    private $_patterns = array(
        'email' => '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/',
        'date' => '/^(0?[1-9]|1[0-2])\/(0?[1-9]|[12][0-9]|3[01])\/\d{4}$/',
        'time' => '/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',
        'phone' => '/^(\+?\d{1,2}\s?)?(\(\d{2}\)|\d{2})\s?\d{4,5}\-\d{4}$/',
        'url' => '/^https?:\/\/(?:www\.)?[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}(?:\.[a-zA-Z]{2,})?\/?.*$/',
        'datetime' => '/^\d{4}-\d{2}-\d{2}[T\s]([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'
    );

    private function getPattern($pattern)
    {
        $pattern = $this->_patterns[$pattern] ?? false;
    }

    protected function setPattern($pattern,$value)
    {
        $this->_patterns[$pattern] = $value;
    }


    public function __construct()
    {
        $this->erros_person = array();
        $this->erros = array();
        $this->__values = array();
        $this->__files = array();
        $this->__check = 1;
        $this->_validations_keys = array();

        $this->_validations = new ReadArray('config/validations.php');

        /*Salva todos os headers dentro de um array associativo*/
        $this->allHeaders = $this->setAllHeaders();

        if ($this->getHeader('Content-type') == 'application/json') {
            $_NP_REQUEST = json_decode(file_get_contents('php://input'), true);
            $this->__all = isset($_NP_REQUEST) ? $_NP_REQUEST : array();
        } else {

            $_SERVER['REQUEST_METHOD']  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'TERM';

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->__all = $_GET;
                    break;
                case 'POST':
                    $this->__all = $_POST;
                    $this->__files = isset($_FILES) ? $_FILES : array();
                    break;
                default:
                    $_NP_REQUEST = file_get_contents('php://input');
                    parse_str($_NP_REQUEST, $_NP_REQUEST);
                    $this->__all = isset($_NP_REQUEST) ? $_NP_REQUEST : array();
                    break;
            }
        }
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        $header = $this->allHeaders;
        return array_key_exists($key, $header) ? $header[$key] : null;
    }

    /*retorna todos os headers*/
    public function headers()
    {
        return $this->allHeaders;
    }

    private function setAllHeaders()
    {
        $arr = array();
        foreach (getallheaders() as $key => $val) {
            $arr[strtolower($key)] = $val;
        }
        return $arr;
    }

    public function all()
    {
        unset($this->__all['uri']);
        return $this->__all;
    }

    public function has($key)
    {
        return isset($this->__all[$key]);
    }

    public function set($key, $value = null)
    {
        $this->__all[$key] = $value;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }


    public function get($key)
    {
        $value = isset($this->__all[$key]) ? $this->__all[$key] : null;
        return $value;
    }

    private function checkSize($size, $value, $number = false)
    {
        if (is_null($value)) return true;
        $size = str_ireplace(['-', '&', 'and'], ',', $size);
        $size = explode(',', $size);
        $min = (float) $size[0];
        $max = (float) isset($size[1]) ? $size[1] : $min;

        if ($number && is_numeric($value)) {
            $value = (float) $value;
        } else {
            $value = strlen($value);
        }

        return ($value >= $min && $value <= $max);
    }

    public function validations(array $validations)
    {
        foreach ($validations as $key => $value) {
            if (!$this->has($key)) $this->set($key);

            $value = is_string($value) ? explode('|', $value) : $value;

            $this->_validations_keys[$key] = $value;
        }
    }

    private function checkInValue($key, $value)
    {
        $validations = isset($this->_validations_keys[$key]) ?
            $this->_validations_keys[$key] : null;

        if (is_null($validations)) return true;

        for ($i = 0; $i < count($validations); $i++) {
            $key = trim($validations[$i]);
            $key = str_ireplace(' ', '', $key);

            if ($this->_validations->has($key)) {
                $p = $this->_validations->get($key);
                $value = preg_match("/^{$p}$/", $value);
                if (!$value) $this->__check *= 0;
            }

            if (substr($key, 0, 7) == 'length:') {
                $key = substr($key, 7);
                if (!$this->checkSize($key, $value))
                    $this->__check *= 0;
            }

            if (substr($key, 0, 5) == 'size:') {
                $key = substr($key, 5);
                if (!$this->checkSize($key, $value, true))
                    $this->__check *= 0;
            }

            if (substr($key, 0, 8) == 'pattern:') {
                $er = substr($key, 8);
                $value = preg_match("/^{$er}$/", $value ?? '');
                if (!$value) $this->__check *= 0;
            }

            $pattern = $this->getPattern($key);
            if ($pattern)
            {
                $value = $value ?? '';
                $value = preg_match((string) $pattern, $value);
                if (!$value) $this->__check *= 0;
            }

            if ($key == 'required' && is_null($value)) {
                $this->__check *= 0;
            }
        }
    }

    public function check()
    {
        foreach ($this->all() as $key => $value) {
            $this->checkInValue($key, $this->get($key));
        }
        return $this->__check;
    }
}
