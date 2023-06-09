<?php
namespace Kernel\MVC;

use Kernel\Http\URI;
use Exception;

class View
{
   /*caminho dos templates*/
   protected $path;
   /*Caminho dos caches*/
   protected $cache = 'storage/cache/views';
   /*regra ER para trasnformação*/
   private $all = "([\[\]\t\n\r\f\v\-A-Za-zÀ-ú0-9\s\{\} &,\_\$\.\"\'\:\|\(\)\+\-\*\%\/\!\?\>\<\=@#;]+)";
   /*armazena os dados do scope*/
   private $scope;

   private $props = array();

   private $sections = [];

   public static function render($mfile, $scope = null, $vh = false)
   {
      $view = new View;
      $view->create($mfile, $scope, $vh);
   }

   /*retonar o caminho de raz do site*/
   final public function local($local)
   {
      $config = new URI();
      $config = $config->local($local);
      return $config;
   }

   /*Salva uma sessão div*/
   private function sectionSave($name, $content)
   {
      $this->sections[$name] = $content;
   }

   /*Recupera uma sessão div*/
   private function getSection($name)
   {
      $print = $this->sections[$name] ?? '';
      return $print;
   }

   /*Estrutura para exibir uma sessão*/
   private function show($file)
   {
      $x = "/{show {$this->all}\|?}/imU";
      $file = preg_replace($x, '<?php echo \$this->getSection($1); ?>', $file);
      return $file;
   }

   /*Estrutura para criação de sessão*/
   private function section($file)
   {
      $x = "/{section {$this->all}}/imU";
      $file = preg_replace($x, '<?php \$this->sectionSave($1,"', $file);
      $file = str_replace('{/section}', '"); ?>', $file);
      return $file;
   }

   /*Estrutura de impressão*/
   private function print($file)
   {
      $x = "/{print {$this->all}}/imU";
      $file = preg_replace($x, '<?php echo $1; ?>', $file);
      return $file;
   }

   /*Renderiza a view e salva o seu resultado em cache*/
   private function create($view, $scope = null)
   {
      $viewPath = $this->getViewPath($view);

      if (!$viewPath)
      {
         throw new Exception("Não foi possível encontrar o arquivo de visualização para '{$view}'.");
      }

      $this->scope = $scope;

      if (is_array($scope)) {
         extract($scope, EXTR_PREFIX_SAME, "np");
      }

      $compiledPath = $this->local($this->cache . '/' . md5($viewPath) . '.php');

      if ($this->needsRecompilation($viewPath, $compiledPath)) 
      {
           $content = file_get_contents($viewPath);
           $content = $this->transform($content);
           $content = $this->writeCompiledFile($compiledPath, $content);
        
      }

      include($compiledPath);
   }

   private function getViewPath($view)
   {
      $view = str_replace('.', '/', $view);

      if (substr($view, 0, 1) === '@') {
         $view = substr($view, 1);
         $parts = explode('/', $view);
         $moduleDir = ucfirst($parts[0]);
         unset($parts[0]);
         $view = implode('/', $parts);
         $view = "modules/{$moduleDir}/Views/{$view}.view.php";
      } else {
         $view = "resources/views/{$view}.view.php";
      }

      $path = $this->local($view);

      return file_exists($path) ? $path : null;
   }

   private function needsRecompilation($viewPath, $compiledPath)
   {
      if (!file_exists($compiledPath)) {
         return true;
      }

      $viewTimestamp = filemtime($viewPath);
      $compiledTimestamp = filemtime($compiledPath);

      return $viewTimestamp > $compiledTimestamp;
   }

   private function writeCompiledFile($path, $content)
   {
      $result = $this->transform($content);

      if (file_put_contents($path, $result) === false) {
         throw new Exception("Não foi possível gravar o arquivo compilado em '{$path}'.");
      }

      return $result;
   }


   /*Transforma tudo em algo legivel para o PHP*/
   public function transform($content)
   {
      $content = $this->section($content);
      $content = $this->viewTokenCSRF($content);
      $content = $this->show($content);
      $content = $this->componet($content);
      $content = $this->viewPHP($content);
      $content = $this->viewIf($content);
      $content = $this->viewVH($content);
      $content = $this->viewFor($content);
      $content = $this->viewEcho($content);
      $content = $this->print($content);
      return $content;
   }

   /*Estrutura de componente*/
   public function componet($file)
   {
      $x = "/{x\-{$this->all} {$this->all}}/imU";
      $file = preg_replace($x, "<?php echo \$$1->render($2); ?>", $file);
      return $file;
   }

   /*Estrutura para código PHP bruto*/
   public function viewPHP($file)
   {
      $file = str_replace(['{php}', '{/php}'], ['<?php', '?>'], $file);
      $file = str_replace(['<php>', '</php>'], ['<?php', '?>'], $file);
      $file = str_replace(['<vh>', '</vh>'], ['<?php', '?>'], $file);
      $file = str_replace(['{vh}', '{/vh}'], ['<?php', '?>'], $file);
      return $file;
   }

   /*Token CSRF e field*/
   public function viewTokenCSRF($file)
   {
      $token = csrf_token();
      $input = '<input type="hidden" name="_token" value="'.$token.'">';
      $file = str_replace('{{csrf_token}}',$token, $file);
      $file = str_replace('{{csrf_field}}',$input, $file);
      return $file;
   }

   /*Estrutura para impressão*/
   public function viewEcho($file)
   {
      $echo = "/\{{2}{$this->all}\}{2}/imU";
      $echox = "/\{{1}\!{$this->all}\!\}{1}/imU";
      $file = preg_replace($echox, "<?php echo $1; ?>", $file);
      $file = preg_replace($echo, "<?php echo htmlspecialchars(trim($1), ENT_QUOTES); ?>", $file);
      $file = str_replace('{!{', '{{', $file);
      $file = str_replace(['{--', '--}'], ['<?php /* ?>', '<php */ ?>'], $file);

      return $file;
   }

   /*Estrutura de repetição*/
   public function viewFor($file)
   {
      $forAs = "/\{for {$this->all} as {$this->all}\}/simU";
      $file = preg_replace($forAs, "<?php foreach($1 as $2): ?>", $file);
      $forIn = "/\{for {$this->all} in {$this->all}\}/simU";
      $file = preg_replace($forIn, "<?php foreach($2 as $1): ?>", $file);
      $file = str_replace('{/for}', '<?php endforeach; ?>', $file);
      return $file;
   }

   /*Estrutura de inclusão*/
   private function viewVH($file)
   {
      $cdnjs = "/{cdn\.js {$this->all}}/simU";
      $file = preg_replace($cdnjs, "<?php echo \$this->importCDN('js',$1); ?>", $file);

      $cdncss = "/{cdn\.css {$this->all}}/simU";
      $file = preg_replace($cdncss, "<?php echo \$this->importCDN('css',$1); ?>", $file);

      $include = "/{include {$this->all}}/simU";
      $file = preg_replace($include, "<?php \$this->create($1,\$this->scope); ?>", $file);

      $includex = "/{json {$this->all}}/simU";
      $file = preg_replace($includex, "<?php echo json_encode($1); ?>", $file);

      return $file;
   }
   /*Estrutura condicional*/
   private function viewIf($file)
   {
      $if = "/{if {$this->all}}/simU";
      $elseif = "/{elseif {$this->all}}/simU";

      $file = preg_replace($if, "<?php if($1): ?>", $file);
      $file = preg_replace($elseif, "<?php elseif($1): ?>", $file);

      $file = str_ireplace('{else}', "<?php else: ?>", $file);
      $file = str_ireplace('{/if}', "<?php endif; ?>", $file);

      return $file;
   }
}