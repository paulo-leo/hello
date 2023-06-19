<?php

namespace Kernel\Http;

class Storage
{
   public $name;
   public $type;
   public $error;
   public $tmp_name;
   public $file;
   public $upload_date;
   public $extension;
   public $new_name;
   public $validated;

   private $path = 'public';
   private $disk = 'local';

   public function __construct(array $file)
   {
     $this->type = $file['type'];
     $this->error = $file['error'];
     $this->name = $file['name'];
     $this->tmp_name = $file['tmp_name'];
     $this->file = $file['file'];
     $this->upload_date = $file['upload_date'];
     $this->extension = $file['extension'];
     $this->new_name = $file['new_name'];
     $this->validated = $file['validated'];
   }

   private function getPath($path)
   {
      $path = str_replace('\\','/',$path);
      $path = "/{$path}/";
      $path = str_replace('//','/',$path);
      $path = str_replace('/',DIRECTORY_SEPARATOR,$path);
      $path = storage_path().$path;

      if(!is_dir($path)) mkdir( $path, 0755, true); 

      return $path;
   }

   //Salva o arquivo dentro da pasta storage
   public function store($path=null,$disk=null)
   {
      return $this->saveFile($path,null,$disk);
   }

   public function storeAs($path=null,$file=null,$disk=null)
   {
      return $this->saveFile($path,$file,$disk);
   }
   
   /*Salva o arquivo*/
   private function saveFile($path=null,$file=null,$disk=null)
   {
     $path = is_null($path) ? $this->path : $path;
     $file = is_null($file) ? $this->new_name : $file.'.'.$this->extension;

     $file_path = $this->getFilePath($path.'/'.$file);

     $path = $this->getPath($path);
     $path = $path . $file;

     if (move_uploaded_file($this->tmp_name, $path))
     {
        return $file_path;
     }
   }

   private function getFilePath($path)
   {
      return str_replace('//','/',$path);
   }

}