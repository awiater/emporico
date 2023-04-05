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
use \EMPORIKO\Helpers\AccessLevel;

class DashBoardView extends View
{

	private $_colors=[];
        
        private $_tiles=[];
        
        private $_dashboard=[];
	
	public function __construct($controller,$iscached)
	{
		parent::__construct($controller,$iscached);
		$this->addData('_tiles',[]);
		$this->addScript('chart.js','@vendor/chartjs/Chart.min.js');
		$this->addScript('chart.plugin.js','@vendor/chartjs/Chart.plugin.labels.min.js');
		$this->_colors=json_decode(file_get_contents(parsePath('@vendor/chartjs/colorcodes.json',TRUE)),TRUE);
		$this->addData('_dashboardview_colors',[['hex'=>'#f56954'], ['hex'=>'#00a65a'], ['hex'=>'#f39c12'], ['hex'=>'#00c0ef'], ['hex'=>'#3c8dbc'], ['hex'=>'#d2d6de']]);
                $this->addData('_rows',3)->addData('_cols',3)->addData('_cols_size',4);
                $this->addSelect2('.select2');
                $this->setFile('System/dashboard');
	}
	
	/**
	 * Add big box tile to view
	 * 
	 * @param  string $header
	 * @param  string $text
	 * @param  mixed  $url
	 * @param  string $background
	 * @param  string $name
	 * 
	 * @return \EMPORIKO\Helpers\Pages\DashBoardView
	 * 
	 */
	function addTile($header,$text,$url,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$arr=$this->getViewData('_tiles');
		$name=$name==null ? 'tile_'.count($arr) : $name;
		if (is_array($url))
		{
			$url=url_from_array($url);
		}
		
		if (!Str::startsWith($url,site_url()))
		{
			$url=url($url);
		}
		$arr[$name]=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'url'=>$url,
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'type'=>0
			
		];
		return $this->addData('_tiles',$arr);
	}
	
	/**
	 * Add small box tile to view (no url)
	 * 
	 * @param  string $header
	 * @param  string $text
	 * @param  string $background
	 * @param  string $name
	 * 
	 * @return \EMPORIKO\Helpers\Pages\DashBoardView
	 * 
	 */
	function addBadge($header,$text,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$args=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'type'=>'badge'
			
		];
                $args['data']=view('System/Dashboard/badge',$args);
                $args['action']=FALSE;
                $this->_tiles[]=$args;
		return $this;
	}
	
        function addBadgeLink($header,$text,$url,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$args=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'type'=>'badge',
                        'url'=>$url
			
		];
                $args['data']=view('System/Dashboard/tile_link',$args);
                $args['action']=FALSE;
                $this->_tiles[]=$args;
		return $this;
	}
        
	function addList($header,$text,array $options,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$arr=$this->getViewData('_tiles');
		$name=$name==null ? 'list_'.count($arr) : $name;
		$arr[$name]=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'options'=>$options,
			'type'=>''
			
		];
		return $this->addData('_tiles',$arr);
	}
	
	/**
	 * 
	 */
	function addFromDB($dashboard)
	{
            $dashboard=model('Settings/DashboardModel')->filtered(['name'=>$dashboard])->first();
            
            if (!is_array($dashboard))
            {
                $this->_dashboard=[];
                $this->addData('_content', '');
            }else
            {
                $this->_dashboard=$dashboard;
                if (Str::startsWith($dashboard['data'], '@'))
                {
                    $dashboard['data']= parsePath($dashboard['data'],TRUE);
                    $dashboard['data']= file_get_contents($dashboard['data']);
                }
                $this->addData('_content', $dashboard['data']);
            }
            $this->loadTiles();           
            return $this;
	}
	
        function setFile($fileName) 
        {
            $fileName= parsePath('@views/'.$fileName.'.php',TRUE);
            if (file_exists($fileName))
            {
                $fileName= file_get_contents($fileName);
            }else
            {
                $fileName='';
            }
            return $this->addData('_content', $fileName);
        }
        
	function loadTiles()
        {
            $this->_tiles=model('Settings/DashboardItemModel')->getTiles();
        }
        
        /**
         * Return given tile data
         * 
         * @param  string $name
         * @param  bool   $render
         * 
         * @return mixed
         */
        function getTile($name,$render=TRUE)
        {
            if (array_key_exists($name, $this->_tiles))
            {
                $name=$this->_tiles[$name];
                if (!$render)
                {
                    return $name['name'];
                }
                if (array_key_exists('data', $name))
                {
                    if (Str::isJson($name['data']))
                    {
                        $name['data']= json_decode($name['data'],TRUE);
                        if (is_array($name['data']))
                        {
                            return loadModuleFromArray($name['data']);
                        }   
                    }
                }
            }return '';
        }
        
        function render($mode = 'raw', $stop = TRUE) 
        {
            $this->addData('_tiles', $this->_tiles);
            $this->addPrintLibrary();
           
            if ($mode=='justview')
            {
                return view('#'.view('System/dashboard',['content'=> str_replace(['<body>','</body>'], '', $this->getViewData('_content'))]),$this->getViewData());
            }
             $this->addData('_content',view('System/dashboard',['content'=> str_replace(['<body>','</body>'], '', $this->getViewData('_content'))]));
            return parent::render($mode, $stop);
        }
	
}