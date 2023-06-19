<?php

namespace Kernel\Http;

use Kernel\Http\Storage;

class FileRequest
{
  private $files;
  

  public function __construct()
  {
     $this->files = $_FILES ?? [];
  }

  public function file($name,$one=true)
  {
     $files = $this->getFile($name);
     if($one){
        return $files[0] ?? [];
     }else return $files;
  }


  public function getFile($name)
  {
     $files = $this->files[$name] ?? [];
     if(count($files) > 0 && is_array($files['name']))
     {
       $files = $this->dividirArray($files);
     }else
     {
        $files = count($files) > 0 ? array($files) : $files;
     }

     $files = $this->atributesFiles($files);

     return $files;
  }

  private function dividirArray($array) {
    $novoArray = array();
    foreach($array as $chave => $valores) {
      foreach($valores as $indice => $valor) {
        $novoArray[$indice][$chave] = $valor;
      }
    }
    return $novoArray;
  }

  private function atributesFiles(array $files)
  {
      for($i=0; $i < count($files);$i++)
      {
         $name = $files[$i]['name'];
         $tpm = $files[$i]['tmp_name'];
         $files[$i]['file'] = $tpm;
         $files[$i]['upload_date'] = date('Y-m-d H:i:s');
         $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
         $files[$i]['extension'] = $extension; 
         $files[$i]['new_name'] = md5('YmdHis'.$name).'.'.$extension;
         $files[$i]['validated'] = (is_uploaded_file($tpm) 
         && getimagesize($tpm) !== false);
         
         $files[$i] = new Storage($files[$i]);
       
      }
      return $files;
  }

  public function hasFile($name)
  {
     return isset($this->files[$name]);
  }
  public function fileSize($name,$one=true)
  {
     $files = $one ? [$this->file($name,$one)] 
     : $this->file($name,$one);
     $total = 0;
     foreach($files as $file){ $total += (float) $file->size; }
     return $total;
  }
  public function fileExtension($name,$one=true)
  {
     $files = $one ? [$this->file($name,$one)] 
     : $this->file($name,$one);
     $data = array();
     foreach($files as $file){ $data[$file->extension] = $file->extension; }
     return $data;
  }

  public function fileCheckExtension(array $extensions_files,array $extensions)
  {
     foreach($extensions_files as $ext)
     { 
        if(!in_array($ext,$extensions))
        return false;
     }
     return true;
  }
}