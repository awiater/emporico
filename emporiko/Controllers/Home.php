<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

  
namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;

class Home extends BaseController
{
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'=>           AccessLevel::view,
		'dashboard'=>       AccessLevel::view,
                'dashboards'=>      AccessLevel::settings,
		'logout'=>          AccessLevel::view,
		'pageNotFound'=>    AccessLevel::view,
                'movementsTable'=>  AccessLevel::view,
                'taskaction'=>      AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
            'dash'=>'Settings/Dashboard',
            'tiles'=>'Settings/DashboardItem',
            'mov'=>'System/Movements',
	];
        
        /**
        * Array with methods which are excluded from authentication check
        * @var array
        */
        protected $no_access = 
        [
            'cron','index'
        ];
        
        /**
        * Access module class name if different that current controller
        * @var String
        */
        protected $access_controller='Settings';
        
        /**
         * Array with function names which are excluded from routes actions
         * @var array
         */
        protected $routerexlude = [
            'movementsTable'
        ];
	
	function index()
	{
            if (defined('_CRON') && _CRON)
            {
                return $this->cron();
            }
            if (intval(loged_user('iscustomer')) > 0 && intval($this->model_Settings->get('pages.pages_cfg_islivecustomer')) > 0)
            {
                return redirect()->to(url('portal','home.html'));
            }
            return $this->dashboard();
	}
	
        function cron()
        {
            \EMPORIKO\Libraries\Scheduler\Listener::init()->listen();exit;
        }
        
	
	
	function mobilemenu()
	{
		$notify=$this->model_Tasks_Notification->getMessagesForMobile();
		if (is_array($notify) && count($notify)>0)
		{
			$this->session->setFlashdata('error', createErrorMessage($notify));
		}
		return $this->view
				    ->setPageTitle('home')
					->setFile('Home/mobile_index')
				   	//->addData('locations_qty',$this->model_Locations->where('enabled',1)->count())
				   	->render();
	}
	
        
	function exception()
	{
		return redirect()->to(site_url())->with('error',$this->createMessage('System Exception/Error please contact support','danger'));
	}

	function logout()
	{
            $ref=$this->getRefUrl();
            $ref=!Str::startsWith($ref, url('/pages')) ? site_url() : $ref;
            auth()->logout();
            return redirect()->to($ref);
	}
	
	function pageNotFound()
	{
		return $this->view->setFile('errors/html/error_404')->render();
	}
        
        function movementsTable($ref,array $args=[])
        {
            if (array_key_exists('addlog', $args) && array_key_exists('button', $args['addlog']))
            {
                $args['addlog']['class']='dark btn-xs';
            }
            $model= Pages\HtmlItems\MovementsDataField::create()
                    ->setName('movements')
                    ->setID('movements')
                    ->setReferenceFilter($ref)
                    ->setArgs($args);
            if ($this->hasAccess(AccessLevel::settings))
            {
                $model->setEditable(TRUE);
            }
            
            return $model->render();
        }
        
        function pages(string $mode, Pages\FormView $view,array $data)
        {
            $arr=$this->model_Dash->getDashboardsForForm();
            $arr['0a']=lang('system.settings.home_pages_dashboard_custom').'=>'.lang('system.settings.home_pages_dashboard_custom_tooltip');
            ksort($arr);
            return $view->setTab('dashboard','system.settings.home_pages_dashboard_tab')
                        ->addDropDownField('system.settings.home_pages_dashboard_label', 'pg_cfg[dashboard]', $arr, array_key_exists('dashboard', $data) ? $data['dashboard'] : null, ['advanced'=>TRUE])
                        ->addHiddenField('pg_action', 'Home::dashboard@{dashboard},TRUE');
        }
        
        function taskaction()
        {
            $this->model_Tasks_Task->actionEnabled(formatDate());
        }
        
        function dashboard(string $dashboard=null,bool $isFromPage=FALSE)
	{
            $dashboard=$dashboard==null || $dashboard=='' || $dashboard=='0a' ? loged_user('dashboardaccess') : $dashboard;
            return $this->setDashBoardView()
                        ->addData('editable',FALSE)		
                        ->addData('_delete_tile_url',url($this,'deletetile',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
		        ->addData('_new_tile_url',url($this,'save',['dash'],['refurl'=>current_url(FALSE,TRUE)]))
                        ->addFromDB($dashboard)
                        ->addChartScript()
                        ->render($isFromPage ? 'justview' : 'raw');
	}
        
        public function dashboards($record=null)
        {
        
            if ($record!=null)
            {
                return $this->dashboard_edit($record);
            }
        
            $this->setTableView()
                    ->setData('dash',null,TRUE,null,[])
                    ->setPageTitle('system.dashboard.list_title')
                    //Fiilters settings
                    ->addFilters('dashboards')
                    ->addFilterField('name %')
                    //Table Columns settings
                    ->addColumn('system.dashboard.name','name','',TRUE)
                    ->addColumn('system.dashboard.desc','desc',TRUE,[],'len:100') 
                    ->addColumn('products.enabled','enabled',TRUE,'yesno')
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('system.dashboard.list_mainmenu',current_url())
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','dashboards',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addNewButton('dashboards/new')
                    ->addDeleteButton(AccessLevel::settings)
                    ->addEnableButton(AccessLevel::settings)
                    ->addDisableButton(AccessLevel::settings);
            return $this->view->render();
        }
        
        private function dashboard_edit($record)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            if (is_numeric($record))
            {
                $record=$this->model_Dash->find($record);              
            }else
            {
                $record=null;
            }
           
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError(true);
                }
                $isnew=TRUE;
                $record=$this->model_Dash->getNewRecordData(TRUE);
            }else
            {
                $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
            }
            $this->setFormView('Home/dashboard_edit')
                    ->setFormTitle('system.dashboard.edit_title')
                    ->setPageTitle('system.dashboard.edit_title')
                    ->setFormAction($this,'save',['dash'],['refurl'=>base64url_encode($refurl)])
                    ->parseArrayFields()
                    ->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Dash->primaryKey=>$record[$this->model_Dash->primaryKey],
                            
                        ]
                    ,['class'=>'col-12'])
                    ->setCustomViewEnable(FALSE)
                    ->setFormCancelUrl($refurl)
					
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('system.dashboard.list_mainmenu',current_url())
                    ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],'/')
			
                    ->addData('record',$record)
                    ->addData('tiles',$this->model_Tiles->getTiles())
                    ->setTab('general','system.general.tab_info')
                    ->setTab('editor','system.dashboard.tab_editor')
                    ->addFieldsFromModel('dash',$record,'system.dashboard.-key-')
                    ->addSelect2('.select2')
                    ->addGrapesJSLibrary();
        
            return $this->view->render();
    }
    
function save($type, $post = null) 
{
    $post = $post == null ? $this->request->getPost() : $post;
    $refurl = $this->getRefUrl();
    
    if ($type=='addlog')
    {
        if (Arr::KeysExists(['msgs','ref','info'], $post))
        {
            $post['msgs']= json_decode(base64_decode($post['msgs']),TRUE);
            if (is_array($post['msgs']) && Arr::KeysExists(['ok','error'], $post['msgs']))
            {
                if ($this->addMovementHistory('notify', null, null, $post['ref'], $post['info'], 'history'))
                {
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage($post['msgs']['ok'],'success'));
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage($post['msgs']['error'],'danger'));
            }
        }
        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.movements.addlog_saveerror','danger'));
    }else
    if ($type=='dash')
    {
        if (Arr::KeysExists(['data_edit_html','data_edit_css'], $post))
        {
            $post['data_edit_html']= base64_decode($post['data_edit_html']);
            $post['data_edit_css']= base64_decode($post['data_edit_css']);
            $patern='#<img data-tile=\"(.*?)\" src=\"(.*?)\" id=\"(.*?)\"/>#s';
            preg_match_all($patern, $post['data_edit_html'], $matches);
            $post['data']=$post['data_edit_html'];
            foreach(is_array($matches[0]) ? $matches[0] : [] as $key=>$match)
            {
                $post['data']= str_replace($match,'<?= $currentView->getTile('."'".$matches[1][$key]."'".') ?>', $post['data']);
            }
                
            $post['data']='<style>'.($post['data_edit_css']).'</style>'.($post['data']);
        }
    }
    return parent::save($type, $post);
}    
        
}