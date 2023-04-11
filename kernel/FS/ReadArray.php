<?php
/*
*
*Author: Paulo Leonardo da Silva Cassimiro
*
*/
namespace Kernel\FS;

use Kernel\Http\URI;

class ReadArray
{
  private $arr = array();
  private $filename;
  
  /*Ler o caminho do arquivo*/
  public function __construct($file){
	  
	   //$uri = new URI();
	  //$file = $uri->local($file);
	  $file = dirname(__DIR__) . "/../{$file}";
	  $this->filename = $file;
	  
	  if(file_exists($file)){
		  
		 $file = require($file); 

	  if(is_array($file)){
		 
		 $this->arr = $file;
		 
	  }
	    }
  }

  /*Faz um include de arquivo */
  public static function addFile($file){
	  
	  $uri = new URI();
	  $file = $uri->local($file);
	  
	  if(file_exists($file))
		        require($file); 
  }
  
  /*Retona todos os valores*/
  public function all(){
	return $this->arr;  
  }
  
  /*Mescla um array ao array novo*/
  public function merge($array)
  { 
     if(is_array($array)){
		$this->arr = array_merge($this->arr, $array);
		return true;
	 }else return false;   
  }
  
  public function mergeFile($file){
	  $file = new ReadArray($file);
	  $this->merge($file->all());
  }
  
  /*Adiciona um novo elemento ao nó de dados*/
  public function set($key,$val=null,$val2=null)
  {  
      if($val2){
		  $this->arr[$key][$val] = $val2; 
	  }else{
		  $this->arr[$key] = $val; 
	  }
  }
  
  
  public function getKeys()
  {
	  return array_keys($this->arr);
  }

  public function has($key)
  {
	  return isset($this->arr[$key]);
  }
  
  /*Retorna um valor por meio da chave especifica*/
  public function get($key,$default=null){
	  
	  return isset($this->arr[$key]) ? $this->arr[$key] : $default;
	  
  }
  
  /*Elimina um elemento do nó de dados*/
  public function del($key)
  {
	 if(isset($this->arr[$key])){
		  unset($this->arr[$key]);
		  return true;
	 }else return false;
  }
  
  /*Salva o arquivo com as alterações*/
  public function save($array_save=false)
  {  
      $data = $this->arr;
	  $filename = $this->filename; 
	  $array = null;
	  foreach($data as $key=>$val){
		  
		  
		  if(is_string($val)){
			  $val = $array_save ? "['{$val}']" : "'{$val}'";
		  }
		  
		  if(is_bool($val)){
			  $val = $val ? 'true' : 'false';
			  $val = $array_save ? "['{$val}']" : $val;
		  }
		  
		  if(is_array($val)){
			  $arr = null;
			  foreach($val as $value)
			  {
				  $value = is_numeric($value) ? $value : "'{$value}',";
				  $arr .= $value;
			  }
			  $arr = substr($arr,0,-1);
			  $val = "[{$arr}]";  
		  }
		  
		  $array .= "\n'{$key}'=>{$val},";
	  }
	  
	  $data =  "<?php\nreturn [".$array."];";
      $data = str_ireplace(',];','];',$data);  
	  return file_put_contents($filename,$data); 
  }
}