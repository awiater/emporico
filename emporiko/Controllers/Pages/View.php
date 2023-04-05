<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

  
namespace EMPORIKO\Controllers\Pages;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\UserInterface;

class View
{
	/**
	 * Holds all view data
	 * @var array 
	 */
	protected $viewData;
	
	/**
	 * Path to view file
	 * @var string
	 */
	protected $viewFile;
	
	/**
	 * Session shared instance
	 * @var
	 */
	protected $session;
	
	/**
	 * Controller class
	 * @var 
	 */
	public $controller;
	
	/**
	 * Determines if view is cached
	 * @var bool
	 */
	 public $iscached=TRUE;
         
         /**
          * Main theme view path
          * @var string
          */
         private $_mainTheme=null;
	
	/**
	 * View Mode
	 * HTML - normal template mode
	 * JSON - JSON text mode
	 * TEXT - Only text mode (not using view file and not using tempalte)
	 * HTXT - Template only text mode (just not using view file)
	 * @var
	 */
	protected $mode='HTML';
        
        /**
         * Template sections
         * @var Array
         */
        private $_sections=[];
        
        /**
         * Main template file
         * 
         * @var string
         */
        private $_tplIndexFile=null;

	
	/**
	 * EMPORIKO Page helper library
	 */
	public function __construct($controller,$iscached=FALSE)
	{
		helper('form');
		helper('html');
		$this->usetemplate=TRUE;
		$this->controller=$controller;
		$this->iscached=$iscached;
		if (is_object($controller) && property_exists($controller, 'session'))
		{
			$this->session=$controller->session;
		}else
		{
			$this->session=\Config\Services::session();
		}
		
		$systemSettings=model('Settings/SettingsModel')->get('system.*');
		$systemSettings['app']=config('APP');
		$config=include(parsePath('@template/'.$systemSettings['theme'].'/config.php',TRUE));
                if (is_array($config) && array_key_exists('sections', $config))
                {
                    $this->addSectionsFromDB($config['sections']);
                }

		/*Add Script*/
		$this->addScript('jquery','@vendor/jquery/jquery.min.js');
		$this->addScript('jqueryui','@vendor/jquery/jquery-ui.min.js');
                $this->addScript('jquery.fullscreen-min','@vendor/jquery/jquery.fullscreen-min.js');
                $this->addScript('file_saver.min.js','@vendor/jquery/FileSaver.min.js');
		$this->addScript('popper','@vendor/jquery/popper.js');
		$this->addScript('bootstrap','@vendor/bootstrap/js/bootstrap.bundle.min.js');
		$this->addScript('boostrapswitch','@vendor/bootstrap/js/bootstrap-switch-button.js');
		$this->addScript('EMPORIKO_system_js','@vendor/system/js/system.js');
                $this->addCustomScript('enableTooltip','$(function(){enableTooltip();});',TRUE);
                $this->addDataUrlScript();
		if (array_key_exists('scripts', $config))
		{
			foreach (is_array($config['scripts']) ? $config['scripts'] : [$config['scripts']] as $key => $value) 
			{
				$this->addScript($key,$value);
			}	
		}
                
                if (intval(loged_user('autologoff'))==1)
                {
                    $this->addCustomScript('userlogoff_script',view('System/userlogoff_script',['noscript'=>TRUE]),FALSE);
                }
                
		/*Add CSS*/
		$this->addCss('bootstrap','@vendor/bootstrap/css/bootstrap.min.css');
		$this->addCss('bootstrapswitch','@vendor/bootstrap/css/bootstrap-switch-button.min.css');
                $this->addCss('icheck-bootstrap','@vendor/bootstrap/css/icheck-bootstrap.min.css');
		$this->addCss('fontawesome','@vendor/fontawesome/css/all.min.css');
		$this->addCss('jquery-ui','@vendor/jquery/jquery-ui.min.css');
                $this->addCss('systemcss','@vendor/system/system.css');
		$this->setTitle(config('APP')->APPName);
		if (array_key_exists('css', $config))
		{
			foreach (is_array($config['css']) ? $config['css'] : [$config['css']] as $key => $value) 
			{
				$this->addCss(is_numeric($key) ? ($key<1 ? 'templatecss' :'templatecss_'.$key) : $key,$value);
			}	
		}
		/*Add base data*/
		$this->addData('metadata',[]);
		$this->addData('buttons',[]);
		$this->addData('fields',[]);
		$this->addData('_vars',[]);
		$config['index']=parsePath('@template/index.php',TRUE);
		$this->addData('_template',$config,TRUE);
		$this->addData('currentView',$this);
		$this->addData('_ismobile',$this->ismobile());
		$systemSettings['app']=config('APP');		
		$this->addData('config',$systemSettings,TRUE);
		$this->addData('_User', loged_user('object'),TRUE);
		$this->setFile('System/blank');
		$item=debug_backtrace();
		helper('array');
	}	
	
	/**
	 * Determine if current viewport is on mobile
	 * 
	 * @return bool
	 */
	 
	function ismobile()
	{
            return html_isMobile();
	}
	 
	 
	 function getSkeleton(array $data=[])
	 {
	 	$data=array_merge($data,$this->getViewData());
		return view('System/Elements/skeleton',$data);
		
	 }
	 
         /**
          * Get Compiled data for icon
          * 
          * @param  string  $type
          * @param  string  $field
          * @return mixed
          */
         function getIconData($type,$field=null,$access= \EMPORIKO\Helpers\AccessLevel::view)
         {
            if (loged_user('auth')->hasAccess($access))
            {
                $type=model('Settings/SettingsModel')->getViewIconData($type);
                if (!is_array($type))
                {
                    return null;
                }
                if (!Arr::KeysExists(['controller'], $type))
                {
                    return null;
                }
                $data= loadModuleFromArray($type);
                return is_array($data) && array_key_exists($field, $data) ? $data[$field] : $data;
            }
            return null;
            
         }
         
         /**
          * Generate table from array
          * 
          * @param  array $data
          * @param  array $headers
          * @return string
          */
         function generateTable(array $data,array $headers=[])
         {
             $headers=count($headers) > 0 ? $headers : array_keys($data[0]);
             $table = new \CodeIgniter\View\Table(
                     [
                         'table_open'=> '<table class="table">'
                     ]);
             $table->setHeading($headers);
             return $table->generate($data);
         }
        
	
	
	function addChartObject($type,$name,array $data,array $args=[])
	{
		
		$types=['bar','pie'];
		$charts=$this->getViewData('_chartobject');
		$charts=is_array($charts) ? $charts : [];
		$chart=['name'=>$name];
		$chart['data']=$data;
		$this->session->set('_chartdata',$data);
		$chart['labels']=[];
		$chart['data']=[];
		$chart['type']=in_array(strtolower($type), $types) ? strtolower($type) : 'bar';
		
		$chart=$chart+$args;
		if (!array_key_exists('legend', $chart))
		{
			$chart['legend']=true;
		}
		
		if (!array_key_exists('multivalue', $chart))
		{
			$chart['multivalue']=true;
		}
		
		if (!array_key_exists('defcolor', $chart))
		{
			$chart['defcolor']='red';
		}
		
		if (array_key_exists('title', $chart))
		{
			$chart['title']=lang($chart['title']);
		}else{
			$chart['title']='';
		}

		if (!array_key_exists('datalabels', $chart))
		{
			$chart['datalabels']=['display'=>'false'];
		}
		
		if (array_key_exists('colors', $chart) && is_array($chart['colors']) && !$chart['multivalue'])
		{
			$colors=array_keys($data);
			$colors=array_fill(0, count($colors), $chart['defcolor']);
			$colors=array_combine(array_keys($data), $colors);
			$chart['colors']=array_merge($colors,$chart['colors']);
		}else
		{
			$chart['colors']=$chart['defcolor'];
		}
		
		foreach ($data as $key => $value) 
		{
			$chart['labels'][]=$key;
			if (is_array($value))
			{
				if (Arr::getType($value)=='ASSOC')
				{
					foreach ($value as $skey => $svalue) 
					{
						$skey=str_replace('_', ' ', $skey);
						if (is_numeric($svalue))
						{
							if ($chart['multivalue'])
							{
								$chart['data'][$skey][]=$svalue;
							}else
							{
								$chart['data'][$skey]=$svalue;
							}
							
						}
						
					}
				}else
				{
					$chart['data'][$key]=$value;
				}
			}else
			if (is_numeric($value))
			{
				$chart['data'][$key]=$value;
				if ($chart['multivalue']==TRUE)
				{
					if (!is_array($chart['data'][$key]))
					{
						$chart['data'][$key]=[];
					}
					$chart['data'][$key][]=$value;
				}else
				{
					$chart['data'][$key]=$value;
				}
			}
		}
		$chart['labels']=is_array($chart['labels']) ? '["'.implode('","',$chart['labels']).'"]' : $chart['labels'];
		$chart['object']=view('System/Elements/chart',$chart);
		$charts[$name]=$chart;
		$this->addScript('chart.js','@vendor/chartjs/Chart.min.js')
		     ->addScript('chart.plugin.js','@vendor/chartjs/Chart.plugin.labels.min.js')
			 ->addScript('chart.plugin.js','@vendor/chartjs/jspdf.umd.min.js')
			 ->addData('_chartcolors',json_decode(file_get_contents(parsePath('@vendor/chartjs/colorcodes.json',TRUE)),TRUE))
			 ->addData('_chartobject',$charts)
			 ;
		if (array_key_exists('datalabels', $chart))
		{
			$this->addScript('chartjs-plugin-datalabels','@vendor/chartjs/chartjs-plugin-datalabels.min.js');
		}
		return $this;
	}
	
	function getChartObject($name,$field='object')
	{
		$name=$this->getViewData('_chartobject.'.$name);
		if ($field==null)
		{
			return $name;
		}
		return is_array($name) && array_key_exists($field, $name) ? $name[$field] : $name;
	}
	
        /**
         * Add ChartJS scripts to DOM
         * 
         * @return \EMPORIKO\Helpers\Pages\View
         */
        function addChartScript()
        {
            return $this->addScript('chart.js','@vendor/chartjs/Chart.min.js')
                        ->addScript('chart.plugin.js','@vendor/chartjs/Chart.plugin.labels.min.js')
			->addScript('jspdf','@vendor/jspdf/jspdf.umd.min.js');
        }
	/**
	 * Insert path to CSS file into view data container
	 * 
	 * @param  string $tag  Name of CSS file (or token used later in view file)
	 * @param  string $path Path to CSS file
	 * @return EMPORIKO\Libraries\View
	 */
	public function addCss($tag,$path)
	{
		$path=parsePath($path);
		$this->viewData['css'][$tag]=$path;
		return $this;
	}
	
	/**ata['css'
	 * Returns all CSS tags
	 * 
	 * @return string;
	 */
	function getCss(array $css=[])
	{
		$string='';
		foreach ($this->viewData['css'] as $key=>$path) 
		{
                    if (count($css)==0 || (count($css) > 0 && in_array($key, $css)))
                    {
                        $string.=link_tag($path).PHP_EOL;
                    }
                    
		}
		return $string;
	}
	
	/**
	 * Add custom data to view data container
	 * 
	 * @param  string $tag           Name token used later in view file
	 * @param  mixed  $value         Value of data
	 * @param  bool   $valueAsObject Determines if $value will be treated as object (only if originaly is array)
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function addData($tag,$value,$valueAsObject=FALSE)
	{
		if (is_array($value) && $valueAsObject)
		{
			$value=json_decode(json_encode($value));
		}
		if ($tag=='[]')
		{
			$this->viewData=$value;
		}else
		if ($tag==null)
		{
			$this->viewData[]=$value;
		}else
		{
			$this->viewData[$tag]=$value;
		}
		return $this;
	}
	
	/**
	 * Set data to view container
	 * 
	 * @param  array $data
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function setViewData(array $data,$merge=FALSE)
	{
		if ($merge)
		{
			foreach ($data as $key => $value) 
			{
				if ($key=='scripts')
				{
					foreach ($value as $scriptskey => $scriptsvalue) 
					{
						if(is_array($scriptsvalue))
						{
							$this->addScript($scriptskey,$scriptsvalue['src']);
						}else
						{
							$this->addCustomScript($scriptskey,str_replace(['<script>','</script>'], '', $scriptsvalue),FALSE);
						}
					}
					
				}else
				if (array_key_exists($key, $this->viewData) && is_array($this->viewData[$key]))
				{
					$this->viewData[$key]=$this->viewData[$key]+$value;
				}else
				{
					$this->viewData[$key]=$value;
				}
			}
		}else
		{
			$this->viewData=$data;
		}
		
		
		return $this;
	}
	
	/**
	 * Insert path to script file into view data container
	 * 
	 * @param  string $tag  Name of script file (or token used later in view file)
	 * @param  string $path Path to script file
	 * @return EMPORIKO\Libraries\View
	 */
	public function addScript($tag,$path,array $args=[])
	{
		$path=parsePath($path);
		foreach ($args as $key => $value) 
		{
			$args[$key]=parsePath($value);
		}
		$args['src']=$path;
		$this->viewData['scripts'][$tag]=$args;
		return $this;
	}
	
	/**
	 * Returns all CSS tags
	 * 
	 * @return string;
	 */
	function getScripts()
	{
            $string='';
            foreach ($this->viewData['scripts'] as $key=>$args) 
            {
			if (is_array($args) && array_key_exists('src', $args))
			{
				$string.=script_tag($args).PHP_EOL;
			}else
			{
				$string.=$args.PHP_EOL;
			}
			
            }
            return $string;
	}
	
        /**
         * Returns given script body/tag
         * 
         * @param  string $name
         * 
         * @return mixed
         */
        function getScript($name)
        {
            if (array_key_exists($name, $this->viewData['scripts']))
            {
                $args=$this->viewData['scripts'][$name];
                if (is_array($args) && array_key_exists('src', $args))
                {
                    return script_tag($args).PHP_EOL;
                }else
                {
                    return $args.PHP_EOL;
                }
            }
            return null;
        }
        
	/**
	 * Insert custom script into view data container
	 * 
	 * @param  string $tag      Name of script file (or token used later in view file)
	 * @param  string $body     Script body
	 * @param  bool   $docReady Determine if script body will be enclosed in document ready function
	 * @return EMPORIKO\Libraries\View
	 */
	public function addCustomScript($tag,$body,$docReady=FALSE)
	{
		if ($docReady)
		{
			$body='$(document).ready(function(){'.$body.'})';
		}
		$this->viewData['scripts'][$tag]='<script>'.$body.'</script><!--'.$tag.'-->';
		return $this;
	}
	
        	/**
	 * Add wyswig editor script tag to scripts section
	 * 
	 * @param bool   $autoInit    Determine if tinymce init script will be added to page
	 * @param string $editortType Type of editor toolbar (simple,email,emailext,full)
	 * @param string $editorTag   Editor tag name
	 * 
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addEditorScript($autoInit=FALSE,$editortType='simple',$editorTag='.editor')
	{
            
		$this->addScript('tinymce','@vendor/tinymce/tinymce.min.js');
		//$this->addScript('elfinder_js','@vendor/elfinder/js/elfinder.min.js');
		//$this->addScript('tinymceElfinder','@vendor/elfinder/tinymceElfinder.js');
                //$this->addFileManagerLib();
		
		if ($autoInit==TRUE)
		{
			$data=
			[
                            'id'=>$editorTag,
                            'tinytoolbar'=>$editortType,
                            'height'=>200,
                            'language'=>config('APP')->defaultLocale
			];
			if ($editortType!='simple')
			{
				$data['connector']=url('Media/MediaAdminController','api');
				$data['editorid']='tinymce_editor_'.$editortType;
				$data['toolbar']=base64_encode(json_encode([['back','upload']]));
			}	
			$this->addCustomScript('tinymce_init',view('System/tinymce',$data));
		}else
                if ($autoInit=='script')
                {
                    $data=
                    [
                        'id'=>$editorTag,
                        'tinytoolbar'=>$editortType,
                        'height'=>200,
                        'language'=>config('APP')->defaultLocale	
                    ];
                    
                    $this->addCustomTextField(null, null,'<script>'.view('System/tinymce',$data).'</script>');
                }
               
		return $this;
	}
        
	/**
	 * Disable main template (mark to not use template)
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	 public function templateDisable()
	 {
	 	$this->usetemplate=FALSE;
		return $this;
	 }
	 
	 /**
	 * Enable main template (mark to use template)
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	 public function templateEnable()
	 {
	 	$this->usetemplate=TRUE;
		return $this;
	 }
	 
	 /**
	 * Determine if template is mark as disabled (not to use)
	 * 
	 * @return Bool
	 */
	 public function isTemplateEnabled()
	 {
	 	return $this->usetemplate;
	 }
	 
	 /**
	  * Get temporary data from session flash
	  * 
	  * @param  string $key
	  * @param  mixed  $defaultData
	  * 
	  * @return EMPORIKO\Libraries\View
	  */
	 public function addFlashData($key,$defaultData=null)
	 {
	 	if (is_array($this->session->getFlashdata()) && array_key_exists($key, $this->session->getFlashdata()))
		{
			
			if (!array_key_exists($key, $this->viewData) || (array_key_exists($key, $this->viewData) && strlen($this->viewData[$key])<1))
			{
				$data=$this->session->getFlashdata($key);
				$this->addData($key,is_string($data)?lang($data):$data);
			}
			
		}else
		if ($defaultData!=null)
		{
			$this->addData($key,$defaultData);
		}
		return $this;
	 }
	 
	 /**
	  * Add pagination to view data
	  * 
	  * @param  mixed  $type      Model instance
	  * @param  mixed  $type      Type of pagination links
	  * @param  string $groupName Links group name
	  * 
	  * @return EMPORIKO\Libraries\View
	  */
	 public function addPagination($model,$type='default',$groupName='default',$viewTag='pagination')
	 {
	 	$type=$type==null?'default':$type;
		$groupName=$groupName==null?'default':$groupName;
	 	if ($model->pager!=null)
		{
	 		if (is_array($type)&&Arr::KeysExists(['page','perPage','total'],$type))
			{
				$this->viewData[$viewTag]=$model->pager->makeLinks($type['page'], $type['perPage'], $type['total']);
			}else
			if(is_string($type)&&$type=='simple')
			{
				$this->viewData[$viewTag]=$model->pager->simpleLinks($groupName);
			}else
			{
				$this->viewData[$viewTag]=$model->pager->links($groupName);
			}
		}
		return $this;
	 }
	 
	 public function addBreadcrumbsFromPage()
	 {
	 	$crumbs=[];
	 	$segments=[];
                foreach(service('uri')->getSegments() as $segment)
                {
                    $segments[]=$segment;
                    $crumbs[ucwords($segment)]=url('/'.implode('/',$segments));
                    
                }
                
		return $this->addBreadcrumbs($crumbs);
	 }
	 
	 /**
          * Add breadcrumb variable to view data
          * 
          * @param string $text
          * @param string $url
          * 
          * @return EMPORIKO\Libraries\View
          */
	 public function addBreadcrumb(string $text,string $url)
	 {
	 	$text=lang($text);
	 	$crumbs=$this->getViewData('_breadcrumb_items');
		$crumbs=is_array($crumbs) ? $crumbs : [];
	 	if (!array_key_exists($text, $crumbs))
		{
			$crumbs[$text]=$url;
		}
		
		if (count($crumbs)>0)
		{
			$this->addData('_breadcrumb_items',$crumbs);
		}
		return $this;
	 }
	 
         /**
          * Add breadcrumb data from array (keys as text and values as urls)
          * 
          * @param array $crumbs
          * 
          * @return EMPORIKO\Libraries\View
          */
         public function addBreadcrumbs(array $crumbs)
         {
             foreach($crumbs as $key=>$value)
             {
                 if (is_array($value))
                 {
                     $value= url_from_array($value);
                 }
                 if (is_string($value))
                 {
                     $this->addBreadcrumb($key, $value);
                 }
             }
             return $this;
         }
         
         /**
          * Add breadcrumb variable with settings sub menu link
          * 
          * @return EMPORIKO\Libraries\View
          */
         public function addBreadcrumbSubSettings()
         {
             return $this->addBreadcrumb('system.menu.settings_header',model('Menu/MenuItemsModel')->getSettingsSubMenuItem(TRUE));
         }
         
	 public function getBreadcrumbs($auto=FALSE,array $class=[])
	 {
             $crumbs=$this->getViewData('_breadcrumb_items');
             $home=[lang('system.general.home_breadcrumb')=> site_url()];
             if (array_key_exists('home', $class))
             {
                 $home=[$class['home']=> site_url()];
             }   
		if (!is_array($crumbs) || (is_array($crumbs) && count($crumbs)<1))
		{
                    if ($auto)
                    {
                       $crumbs=$home;
                       
                       foreach (service('uri')->getSegments() as $value)
                       {
                            $name = ucwords(str_replace(array(".php", "_"), array("", " "), $value));
                            $name = ucwords(str_replace('-', ' ', $name));
                            $name=str_replace('+', '-', $name);
                            $crumbs[$name]=site_url([$value]);
                       }
                    }else
                    {
                       $crumbs=null; 
                    }         
		}else
                {
                    $crumbs=$home+$crumbs;
                }
                return is_array($crumbs) ? view($this->chekForTemplatesViews('System/breadcrumb'),['crumbs'=>$crumbs,'count'=>count($crumbs)-1,'class'=>$class]):null;
	 }
	
	/**
	 * Setting view path
	 * 
	 * @param  string $fileName Path to view file
	 * @return EMPORIKO\Libraries\View
	 */
	public function setFile($fileName)
	{
		//echo $fileName."<br>" ;
		$this->viewFile=parsePath($fileName,TRUE);
		return $this->addHelpContent($fileName);
	}
	
	/**
	 * Sets page meta title value
	 * 
	 * @param  string $value
	 * @return EMPORIKO\Libraries\View
	 */
	public function setTitle($value)
	{
		$this->viewData['metadata']['title']=$value;
		
		return $this;
	}
	
	function getTitle()
	{
		$str=$this->getViewData('metadata.title');
		$cfg=$this->getViewData('config');
                if (!is_string($str))
                {
                    $str=$cfg->app->APPName;
                }
		return $str;
	}
	
	function getPageTitle()
	{
		$title=$this->getViewData('_vars.pagetitle');
		return is_array($title) ? '' : $title;
	}
	
	function setPageTitle($title,$tags=[])
	{
		$tags=is_array($tags) ? $tags : [$tags];
		$this->viewData['_vars']['pagetitle']=lang($title,$tags);
		return $this;
	}
	
	/**
	 * Sets page meta description value
	 * 
	 * @param  string $value
	 * @return EMPORIKO\Libraries\View
	 */
	public function setDescription($value,$join=FALSE)
	{
		if (!$join)
		{
			$this->viewData['metadata']['description']=$value;
		}else
		{
			if (strlen($this->viewData['metadata']['description'])<1)
			{
				$this->viewData['metadata']['description']='';
			}
			$this->viewData['metadata']['description'].=' '.$value;
		}
		return $this;
	}
	
	
	
	/**
	 * Sets page meta keywords value
	 * 
	 * @param  mixed $value
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function setKeywords($value,$join=FALSE)
	{
		$value=is_array($value)?implode(',',$value):$value;
		if ($join)
		{
			if (strlen($this->viewData['metadata']['keywords'])<1)
			{
				$this->viewData['metadata']['keywords']='';
			}
			$this->viewData['metadata']['keywords'].=','.$value;
		}else
		{
			$this->viewData['metadata']['keywords']=$value;
		}
		return $this;
	}

	
	/**
	 * Returns linked to page view path
	 * 
	 * @return string
	 */
	public function getFile()
	{
            return $this->chekForTemplatesViews();
	}
	
        
        private function chekForTemplatesViews($viewFile=null,$_template=null)
        {
            $viewFile=$viewFile==null ? $this->viewFile: $viewFile;
            if ($_template==null)
            {
                $_template=$this->getViewData('_template');
                $_template=Arr::ObjectToArray($_template); 
            }
            if ($this->ismobile() && is_array($_template) && array_key_exists('mobile_views', $_template) 
                && is_array($_template['mobile_views']) && array_key_exists($viewFile, $_template['mobile_views']))
            {
                $viewFile=$_template['mobile_views'][$viewFile];
            }
            $viewFile= str_replace(parsePath('@views',TRUE), '', $viewFile);
            if (is_array($_template) && array_key_exists('views', $_template) && is_array($_template['views']) && array_key_exists($viewFile, $_template['views']))
	    {
	        $viewFile=$_template['views'][$viewFile];
	    } 
            
	    $viewFile=parsePath($viewFile,TRUE);
            return $viewFile;
        }
        
	/**
	 * Return view data as array
	 * 
	 * @return Array
	 */
	public function getViewData($key=null)
	{
		$this->addFlashData('error');		
		return $key!=null ? dot_array_search($key,$this->viewData) : $this->viewData;
	}
	
	/**
	 * Returns menu (ul) html section
	 */
	public function getHTMLMenu($menu,$class=null,$onlyLinks=FALSE)
	{
		return loadModule('Menu','htmlmenu',[$menu,$class,$onlyLinks]);
	}
	
	/**
	 * Returns given view file body
	 * 
	 * @param string $viewName  View file name
	 * @param array  $ViewData  Data   
	 * @param bool   $mergeData Determines if given data will be merged with original view data   
	 * 
	 * @return string
	 */
	public function includeView($viewName,$ViewData=null,$mergeData=FALSE)
	{
            if ($viewName=='exception')
            {
                $viewName='errors/html/exception';
            }
            $ViewData=is_array($ViewData) ? ($mergeData ? array_merge($this->getViewData(),$ViewData) :$ViewData) : $this->getViewData();
            $ViewData['currentView']=$this;
            $viewName= parsePath($viewName,TRUE);
            return view($viewName,$ViewData);
	}
	
	/**
	 * Add text data used in TEXT and HTXT modes
	 * 
	 * @param string $text
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function addTextData($text)
	{
		if (is_string($text) && is_numeric($text))
		{
			$this->addData('_textdata',$text);
		}
		return $this;
	} 
	
	/**
	 * Sets view mode 
	 * 
	 * @param string $mode See $mode property
	 */
	public function setViewMode($mode='HTML')
	{
		$this->mode=$mode;
		return $this;
	}
	
        /**
         * Set main theme view path
         * 
         * @param string $theme
         * 
         * @return $this
         */
        public function setMainTheme($theme)
        {
            $theme=parsePath($theme,TRUE);
            $this->_mainTheme=$theme;
            return $this;
        }
        
        /**
         * Add select2 plugin
         * 
         * @param bool $addScript
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addSelect2(bool $addScript=TRUE)
        {
            $this->addScript('select21', '@vendor/jquery/select2.min.js');
            if ($addScript)
            {
                $js="$('.select2').select2({theme: 'bootstrap4',templateResult:select2FormatSearch,templateSelection:select2FormatSelected});function select2FormatSearch(item) {var selectionText = item.text.split('=>');var returnString =$('<span>'+selectionText[0]+'</span>');if(selectionText[1]!=undefined && selectionText[1].length > 1){returnString =$('<span>'+selectionText[0]+'</br>'+selectionText[1]+'</span>');};return returnString;};function select2FormatSelected(item) {var selectionText = item.text.split('=>');return selectionText[0];};";
                $this->addCustomScript('addSelect2sscr',$js, TRUE);     
            }
            return  $this->addCss('select2', '@vendor/jquery/select2.min.css')
                         ->addCss('select2BS4','@vendor/jquery/select2-bootstrap4.min.css');
        }
        
        /**
         * Add data url script for links and peacer
         * 
         * @param string $container
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addDataUrlScript(string $container='body')
        {
            return $this->addCustomScript('data-url',view('System/data_url_script',['container'=>$container,'_just_script_content'=>TRUE]),TRUE);
        }
        
        /**
         * Add Country flags
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addCountryFlags()
        {
            return $this->addCss('country-flags', '@vendor/country_flags/country_flags.min.css');
        }
        
	/**
	 * Add references to jspreadsheet script
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function addJSpreadsheet()
	{
		return $this->addScript('jquery.csv','@vendor/jspreadsheet/jexcel.js')
					->addScript('jquery.jexcel','@vendor/jspreadsheet/jsuites.js')
					->addCss('jquery.jexcel','@vendor/jspreadsheet/jsuites.css')
					->addCss('jquery.jexcel','@vendor/jspreadsheet/jexcel.css');
	}
	
	/**
	 * Add references to datatable scripts and css
	 * 
	 * @param  string $tableID
	 * @param  Array  $options
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function addDataTableScript($tableID=null,array $options=[])
	{
		$this->addScript('datatables.min.js','@vendor/datatables/datatables.min.js');
		if ($tableID!=null)
		{
			if (count($options) < 1)
			{
				$options=['searching'=>'false','ordering'=>'false'];
			}
			$sOptions=[];
			foreach ($options as $key => $value) 
			{
				if (in_array($key, ['dom']))
				{
					goto add_value;
				}else
				if ($key=='buttons' && is_array($value))
				{
					$value=json_encode($value);
                                        $sOptions[]="dom: 'Bfrtip'";
				}else
				{
					$key="'".$key."'";
				}
				add_value:
                                $value= is_bool($value) ? ($value ? 'true': 'false') : $value;
				$sOptions[]=$key.":".$value;
			}
			$body="";
			foreach (is_array($tableID) ? $tableID : [$tableID] as $value) 
			{
				$body.='var '.$value."=$('#".$value."').DataTable({".implode(',', $sOptions)."});".PHP_EOL;
			}
			
			$this->addCustomScript('dataTables.ini',$body,TRUE);
		}
		return $this->addCss('datatables.min.css','@vendor/datatables/datatables.min.css')
					->addCss('dataTables.bootstrap4.min.css','@vendor/datatables/dataTables.bootstrap4.min.css');
	}
	
        /**
         * Add JSPDF Library script
         * 
         * @return EMPORIKO\Libraries\View
         */
	public function addPDFMakeScript()
	{
		return $this->addScript('html2canvas.min.js','@vendor/jspdf/html2canvas.js')
					->addScript('jspdf','@vendor/jspdf/jspdf.umd.min.js');
	}
	
        /**
         * Add elFinder File Manager Library script
         * 
         * @return EMPORIKO\Libraries\View
         */
        function addFileManagerScript()
        {
            $this->addScript('elfinder','@vendor/elFinder/js/elfinder.min');
            $this->addCss('Elfinder_base','@vendor/elFinder/css/elfinder.min.css');
            $this->addCss('Elfinder_theme','@vendor/elFinder/css/theme.css');
            return $this;
        }
        
        /**
         * Add Time picker Library script
         * 
         * @return EMPORIKO\Libraries\View
         */
        public function addTimePickerScript($ids=null)
        {
            $this->addScript('timepicker.min.js','@vendor/jquery/jquery-clock-timepicker.min.js');
            $this->addScript('timepicker.min.js','https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js');
            $this->addCss('timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');return $this;
            if ($ids!=null)
            {
                $ids= is_array($ids) ? $ids : [$ids];
                $ids=implode(',',$ids);
                $this->addCustomScript('TimePickerScript', "$('".$ids."').clockTimePicker();", TRUE);
            }
            return $this;
        }
        
        /**
         * Add JSPDF Library script
         * 
         * @return EMPORIKO\Libraries\View
         */
	public function addPDFJSScripts()
	{
		return $this->addScript('pdf.min.js','@vendor/jspdf/pdf.min.js');
	}
        
        function addSignaturePadLib($addDefVars=FALSE)
        {
            if ($addDefVars!=FALSE)
            {
                $args=
                [
                    'id'=>'CustomPad',
                    'pad_color'=>'#FFF',
                    'button'=>'btn btn-dark btn-sm',
                    'class'=>'border d-flex',
                    'name'=>'CustomPad',
                    'changeEvent'=>TRUE,
                    'value'=>null
                ];
                $allowed= array_keys($args);
                if (is_array($addDefVars))
                {
                    foreach($addDefVars as $key=>$kval)
                    {
                        if (in_array($key, $allowed))
                        {
                            $args[$key]=$kval;
                        }
                    }
                    if (array_key_exists('view_name', $addDefVars))
                    {
                        $addDefVars=$addDefVars['view_name'];
                    }
                }
                $addDefVars= is_string($addDefVars) ? $addDefVars :'_signPadView';
                $this->addData($addDefVars,view('System/Elements/sign_pad',$args));
            }
            return $this->addScript('signjs','@vendor/signature_pad/signature_pad.umd.min.js');
        }
        
        public function addTable2CSVScript()
        {
            return $this->addScript('table2csv.min.js','@vendor/jquery/table2csv.min.js');
        }
        
        public function addGrapesJSLibrary(array $plugins=[])
        {
            $this->addScript('grapes.min.js','@vendor/grapesjs/grapes.min.js')
                 ->addCss('grapes.min.css','@vendor/grapesjs/grapes.min.css');
            
            if (in_array('newsletter', $plugins))
            {
                $this->addScript('grapesjs-preset-newsletter', 'https://unpkg.com/grapesjs-preset-newsletter')
                     ->addCss('grapesjs-preset-newsletter', 'https://unpkg.com/grapesjs-preset-newsletter/dist/grapesjs-preset-newsletter.css');
            }
            if (in_array('mjml', $plugins))
            {
                $this->addScript('grapesjs-mjml', 'https://unpkg.com/grapesjs-mjml')
                     ->addCss('grapesjs-mjml', 'https://grapesjs.com/stylesheets/grapesjs-mjml.css');
                //
            }
            return $this;
        }
        
	
        /**
         * Add jquery mask script to document
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addInputMaskScript()
        {
            return $this->addScript('jquery_mask','@vendor/jquery/jquery.mask.min.js');
        }
        
        public function addPrintLibrary()
        {
            return $this->addScript('pdf.min.js','@vendor/jquery/print_this.js');
        }
        
        public function addMenuBar(array $items,array $args=[])
        {
            if (!array_key_exists('position', $args))
            {
                $args['position']='mr';
            }else
            {
                $args['position']=$args['position']=='right' ? 'ml' : 'mr';
            }
            
            if (!array_key_exists('bg', $args) && !array_key_exists('background', $args))
            {
                $args['background']='light';
            }else
            if (array_key_exists('bg', $args))
            {
                $args['background']=$args['bg'];
                unset($args['bg']);
            }
            
            $args['buttons']=$items;
            $args['name']='_menubar';
           
            if (count($args['buttons']) > 0)
            {
                $this->addData('_menubar',view('System/Elements/toolbar',$args));
            }
            return $this;
        }
        
        
        public function addCookieConsentBar()
        {
            $cookiesManager=service('CookiesManager');
            if (!$cookiesManager->isCookie())
            {
                $this->addCss('cookiesManager', '@vendor/cookiesmanager/styles.css')
                     ->addCustomScript('cookiesManager',"$('body').append(atob('". base64_encode(view(parsePath('@app/Libraries/CookiesManager/cookie_bar')))."'));",true);
            }
            return $this;
        }
        
        /**
         * Returns menu bar HTML
         * 
         * @return string
         */
        public function getMenuBar()
        {
            return $this->getViewData('_menubar');
        }
        
        /**
         * Show error bar
         * 
         * @param string $msg
         * @param string $type
         * @param bool   $isPernament
         * 
         * @return string
         */
        function getErrorBar(string $msg,string $type='danger',bool $isPernament=FALSE)
        {
            $args=['msg'=>lang($msg),'type'=>$type];
            if ($isPernament)
            {
                $args['pernament']=TRUE;
            }
            return $this->includeView('errors/html/exception',$args);
        }
        
        /**
         * Determines if menu bar is set
         * 
         * @return string
         */
        public function isMenuBarSet()
        {
            return array_key_exists('_menubar', $this->viewData);
        }
        
	/**
	 * Add path to help content view
	 * 
	 * @param mixed $item Path or array with controller and action
	 * 
	 * @return EMPORIKO\Libraries\View
	 */
	public function addHelpContent($item)
	{
		if (is_array($item) && count($item)==2)
		{
			$item=array_values($item);
			$controller=$item[0];
			if (!is_string($controller) && is_object($controller))
			{
				$controller=Str::afterLast(get_class($controller),'\\');
			}
			$item=$controller.'/'.$item[1];
		}
		if (!is_string($item))
		{
			return $this;
		}
		
		$item=str_replace([' ','/'], '_', strtolower($item));
		$item=parsePath($item,TRUE);
		if (!file_exists($item))
		{
			$item=parsePath('@app/Language/'.(config('APP')->defaultLocale).'/helpfiles/'.$item,TRUE);
		}
		if (file_exists($item.'.php'))
		{
			$item=['file'=>$item.'.php','mode'=>'view'];
		}else
		if (file_exists($item.'.pdf'))
		{
			$item=['file'=>$item.'.pdf','mode'=>'pdf'];
		}else
		{
			return $this;
		}
		return $this->addData('_helpcontent',$item);
	}
	
	/**
	 * 
	 */
	public function isHelpObjectEnabled()
	{
		return array_key_exists('_helpcontent',$this->viewData);
	}
	
	public function getHelpObject($mode=null,array $args=[])
	{
		$_helpcontent=$this->getViewData('_helpcontent');
		
		if ($_helpcontent!=null && is_array($_helpcontent) && Arr::KeysExists(['mode','file'],$_helpcontent) && file_exists($_helpcontent['file']))
		{
			$_helpcontent['content']=base64_encode(view($_helpcontent['file']));
			
			if ($mode=='view')
			{
				if (!array_key_exists('button_class', $args))
				{
					$args['button_class']='btn-info';
				}
				$data=['args'=>$args,'content'=>$_helpcontent];
				return view('System/helpcontent',$data);
			}else
			if (array_key_exists($mode, $_helpcontent))
			{
				return $_helpcontent[$mode];
			}else
			{
				return $_helpcontent;
			}
			
		}
	}
	
	/**
         * Returns section content
         * 
         * @param  string $name
         * 
         * @return type
         */
	public function getSection($name)
	{
            $section='';
	    if (is_array($this->_sections) && array_key_exists($name, $this->_sections))
	    {
                foreach ($this->_sections[$name] as $value)
                {
                    if (is_array($value) && count($value) > 1)
                    {
                       $value= array_combine(count($value) > 2 ? ['controller','action','args'] : ['controller','action'], $value);
                       $section.= loadModuleFromArray($value);
                    }else
                    if (is_string($value))
                    {
                        $section.=$value;
                    }
                }
	    }
	    return $section;
	}
	
	/**
	 * Set section content
	 *
	 * @param  String $name
	 * @param  mixed  $data
	 */
	public function setSection($name,$data)
	{
	    $this->_sections[$name][]=$data;
	    return $this;
	}
	
        /**
         * Determines if given section exists
         * 
         * @param  string $name
         * 
         * @return bool
         */
        public function isSection($name)
        {
            return is_array($this->_sections) && array_key_exists($name, $this->_sections);
        }
        
	/**
	 * Render view
	 * 
	 * @param string $mode Optional view mode (see $mode property)
	 * 
	 * @return string
	 */
	public function render($mode='html',$stop=TRUE)
	{
	    $engine=service('viewRenderer',TRUE);
	    $mode=strtolower($mode);
	    $systemSettings=$this->getViewData('config');
	    $options=$systemSettings->cache;
	   
	    $_template=$this->getViewData('_template');
	    $_template=Arr::ObjectToArray($_template);
	    $viewName=$this->chekForTemplatesViews(null,$_template);
            
            $cache=null;
            if ($this->iscached && $options!=0)
	    {
                $cacheFile=base64_encode(strlen($viewName) < 1 ? 'home' : $viewName);
                $cacheFile= base64_encode($cacheFile.loged_user('username'));
                $cache = ['engine'=>\Config\Services::cache(),'file'=>$cacheFile];
                $cacheFile=$cache['engine']->get($cacheFile);
                if ($cacheFile!=null)
                {
                    echo $cacheFile;exit;
                }
	        $options_tpl=[];
                $options=[];
                
	    }else
	    {
	        $options=[];
                $options_tpl=[];
	    }
            
	    $this->addThemeConfig();
	    
	    $data=$this->getViewData();
            
	    if ($mode=='raw' && array_key_exists('_content', $data))
            {
                $data['_content']=view('#'.$data['_content'],$data);
	        
            }else
	    if ($mode=='plainhtml')
	    {
	        $data['_content']=$engine->setData($data)->render($viewName,$options);
	        if ($stop)
	        {
	            echo $engine->setData($data)->render('System/Elements/skeleton',$options);exit;
	        }else
	        {
	            return $engine->setData($data)->render('System/Elements/skeleton',$options);
	        }
	    }else
	        if ($mode=='html')
	        {
	            $data['_content']=$engine->setData($data)->render($viewName,$options);
	        }else
	            if ($mode=='json')
	            {
	                return $this->controller->response->setJSON($data);
	            }else
	                if ($mode=='text')
	                {
	                    if ($stop)
	                    {
	                        echo $engine->setData($data)->render($viewName,$options);exit;
	                    }else
	                    {
	                        return $engine->setData($data)->render($viewName,$options);
	                    }
	                    
	                }else
	                    if ($mode=='htxt')
	                    {
	                        if (array_key_exists('_textdata', $data))
	                        {
	                            $data=$data['_textdata'];
	                        }else
	                        {
	                            $data=json_encode($data);
	                        }
	                        $data['_content']=$engine->setData($data)->render(parsePath('@views/System/text',TRUE),$options);
	                    }else
	                    {
	                        return '';
	                    }
	                    $startmodule=loged_user('startmodule');
	                    if (is_string($startmodule) && strlen($startmodule) >0)
	                    {
	                        $startmodule=json_decode(base64_decode($startmodule),TRUE);
	                        if (is_array($startmodule) && array_key_exists('theme', $startmodule))
	                        {
	                            $startmodule=$startmodule['theme'];
	                        }else
	                        {
	                            $startmodule='index';
	                        }
	                    }else 
	                    {
	                        $startmodule='index';
	                    }
                            
                            if ($this->_mainTheme!=null)
                            {
                                $viewName=$this->chekForTemplatesViews($this->_mainTheme,$_template);
                            }else
                            {
                                $viewName=parsePath('@template/'.($systemSettings->theme).'/mobile_index.php',TRUE);
                                if (!$this->ismobile() || ($this->ismobile()&& !file_exists($viewName)))
                                {
                                    $viewName=parsePath('@template/'.($systemSettings->theme).'/'.$startmodule.'.php',TRUE);
                                } 
                            }
                            
                            $viewName=view($viewName,$data,$options_tpl);//$engine->setData($data)->render($viewName,$options_tpl);
	                    $viewName.='<!-- '.config('App')->APPName.' ver '.config('App')->APPVersion.' by Artur Wiater-->';
                            if (is_array($cache))
                            {
                                $cache['engine']->save($cache['file'],$viewName,config('APP')->cacheExpiry);
                            }
                            echo $viewName;
                            exit;
	}
	
	private function addThemeConfig()
	{
	    $systemSettings=model('Settings/SettingsModel')->get('system.*');
	    $systemSettings['app']=config('APP');
	    $config=include(parsePath('@template/'.$systemSettings['theme'].'/config.php',TRUE));
	    if (array_key_exists('css', $config))
	    {
	        foreach (is_array($config['css']) ? $config['css'] : [$config['css']] as $key => $value)
	        {
	            $this->addCss($key,$value);
	        }
	    }
	    
	    if (array_key_exists('scripts', $config))
	    {
	        foreach (is_array($config['scripts']) ? $config['scripts'] : [$config['scripts']] as $key => $value)
	        {
	            $this->addScript($key,$value);
	        }
	    }
	}
        
        /**
         * Adds sections data from DB and Template config file
         * 
         * @param array $data
         */
	private function addSectionsFromDB(array $data=[])
        {
            $dbData=model('System/SectionModel')->getSections();
            $data=$data+$dbData;
            foreach ($data as $key=>$value)
            {
                $this->_sections[$key][]=$value;
            }
        }

	private function parseTagAttr(array $attr)
	{
		$result='';
		foreach ($attr as $key => $value) 
		{
			$result.=$key.'="'.$value.'" ';
		}
		return $result;
	}
}

