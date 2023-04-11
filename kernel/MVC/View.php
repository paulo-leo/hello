<?php
namespace Kernel\MVC;

use Kernel\Http\URI;

class View
{
   /*caminho dos templates*/
	protected $path;
	/*Caminho dos caches*/
	protected $cache =  'storage/cache/views';
	/*regra ER para trasnformação*/
	private $all = "([\[\]\t\n\r\f\v\-A-Za-zÀ-ú0-9\s\{\} &,\_\$\.\"\'\:\|\(\)\+\-\*\%\/\!\?\>\<\=@#;]+)";
	/*armazena os dados do scope*/
	private $scope;

   private $props = array();

   private $sections = [];

   public static function render($mfile, $scope = null,$vh=false)
   {
       $view = new View;
       $view->create($mfile,$scope,$vh);
   }

   /*retonar o caminho de raz do site*/
	final public function local($local)
	{
		$config = new URI();
		$config = $config->local($local);
      return $config;
	}

   /*Salva uma sessão div*/
   private function sectionSave($name,$content)
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
      $file = str_replace('{/section}','"); ?>',$file);
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
      $view = str_replace('.','/',$view);
      $local = "resources/views/{$view}.view.php";

      if(substr($view,0,1) == '@')
      {
         $view = substr($view,1);
         $mod = explode('/',$view);
         $mod_dir = ucfirst($mod[0]);
         unset($mod[0]);
         $view = implode('/',$mod);
         $local = "modules/{$mod_dir}/Views/{$view}.view.php";
      }
 
      $this->scope = $scope;
		if(is_array($scope)) extract($scope, EXTR_PREFIX_SAME, "np");

		$com = $this->local($this->cache . '/' . md5($view) . '.php');

		//gmdate("M d Y H:i:s", mktime(0, 0, 0, 1, 1, 1998));
		$datecom = file_exists($com) ? date('Y.m.d.H.i.s', filemtime($com)) : 'com';
		$dateview = file_exists($view) ? date('Y.m.d.H.i.s', filemtime($view)) : 'view';

		if ($datecom == $dateview)
      {
			include($com);
		}
      else
      {
         $view = $this->local($local);

         if(!file_exists($view))
         {
            echo "<h3 style='color: red; font-family: Arial;'>Não foi possível renderizar esta visualização, pois o arquivo correspondente não está disponível para compilação em: {$view}</h3>";
           exit;
         }

			$content = file_get_contents($view);

			$content = $this->transform($content);

			if (file_put_contents($com, $content)) include($com);
		}
	}

   /*Transforma tudo em algo legivel para o PHP*/
	public function transform($content)
	{  
      $content = $this->section($content);
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
      $file = str_replace(['{php}','{/php}'],['<?php','?>'],$file);
      $file = str_replace(['<php>','</php>'],['<?php','?>'],$file);
      $file = str_replace(['<vh>','</vh>'],['<?php','?>'],$file);
      $file = str_replace(['{vh}','{/vh}'],['<?php','?>'],$file);
      return $file;
   }

   /*Estrutura para impressão*/
   public function viewEcho($file)
   {
      $echo = "/\{{2}{$this->all}\}{2}/imU";
		$echox = "/\{{1}\!{$this->all}\!\}{1}/imU";
      $file = preg_replace($echox, "<?php echo $1; ?>", $file);
		$file = preg_replace($echo, "<?php echo htmlspecialchars(trim($1), ENT_QUOTES); ?>", $file);
      $file = str_replace('{!{','{{',$file);
      $file = str_replace(['{--','--}'],['<?php /* ?>','<php */ ?>'],$file);

		return $file;
   }

   /*Estrutura de repetição*/
   public function viewFor($file)
   {
		$forAs = "/\{for {$this->all} as {$this->all}\}/simU";
		$file = preg_replace($forAs, "<?php foreach($1 as $2): ?>", $file);
      $forIn = "/\{for {$this->all} in {$this->all}\}/simU";
      $file = preg_replace($forIn, "<?php foreach($2 as $1): ?>", $file);
      $file = str_replace('{/for}','<?php endforeach; ?>',$file);
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
		$file = preg_replace($include, "<?php \$this->create($1,\$this->scope); ?>",$file); 

      $includex = "/{json {$this->all}}/simU";
		$file = preg_replace($includex, "<?php echo json_encode($1); ?>",$file); 
      
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