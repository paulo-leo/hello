<?php
/*
*Classe para paramenização da rota
*Autor:Paulo Leonardo da Silva Cassimiro
*/

namespace Kernel\Http;

use Kernel\Http\URI;

class Path
{
	private static $paths = [];

	public static function register($path)
	{
		$index = self::indexs($path);
		$er1 = '{([A-Za-zÀ-ú0-9\.\-\_]+)}';
		$size = explode('/', $path);
		$path = preg_replace("/{$er1}/simU", '{string}', $path);
		self::$paths[$path] = $index;
	}

	/*Registra os índices*/
	private static function indexs($path)
	{
		$path = explode('/', $path);
		$index = array();
		for ($i = 0; $i < count($path); $i++) {
			$in = str_ireplace(['{', '}'], '', $path[$i]);
			$index[$in] = $i;
		}
		return $index;
	}

	private static function uri()
	{
		return $_GET['uri'];
	}

	private static function getIndexValue($name, $keys)
	{
		$uri = self::uri();
		$value = false;
		if (isset($keys[$name])) {
			$index = $keys[$name];
			$uri = explode('/', $uri);
			$value = $uri[$index];
		}
		return $value;
	}

	public static function get($name)
	{
		$uri = self::uri();
		$value = false;
		foreach (self::$paths as $key => $val) {
			$key = str_ireplace(
				['/', '{string}'],
				['\/', '([A-Za-zÀ-ú0-9\.\-\_]+)'],
				$key
			);

			if (preg_match("/^{$key}$/i", $uri)) {
				$value = self::getIndexValue($name, $val);
				break;
			}
		}
		return $value;
	}
}
