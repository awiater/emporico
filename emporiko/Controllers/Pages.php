<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Libraries\XLSXWriter;

class Pages extends BaseController
{
 
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'index'=>   AccessLevel::view,
        'list'=>   AccessLevel::edit,
    ];


    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = ['page'];

    /**
     * Determines if authentication is enabled
     * @var bool
     */
    private $_noauth = FALSE;

    /**
     * Array with function names and linked models names
     */
    public $assocModels = 
    [
        'page'=>'Pages/Page',
        'type'=>'Pages/PageType',
    ];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actual function name)
     */
    public $remaps = 
    [
        'index'=>'page'
    ];
    
    
    public function list($record=null,$mode='edit')
    {
        if (!$this->auth->isLoged())
        {
            return redirect()->route('login')->with('refurl',current_url());
        }
        if ($record!=null)
        {     
            return $this->edit_page($record,$mode);
        }
        
        $this->setTableView()
                    ->setData('page','pg_order',TRUE,null,[])
                    ->setPageTitle('documents.pages_title')
                    //Fiilters settings
                    ->addFilters('list')
                    ->addFilterField('pg_name %')
                    ->addFilterField('pg_restricted',1,'documents.filt_pg_restricted_1')
                    ->addFilterField('pg_restricted','@0','documents.filt_pg_restricted_0')
                    //Table Columns settings
                    ->addColumn('documents.pg_title','pg_title',TRUE,[],null,'pg_desc')
                    ->addColumn('documents.pg_restricted','pg_restricted',FALSE,'yesno')
                    ->addColumn('documents.pg_enabled','enabled',FALSE,'yesno')
                    ->addColumn('documents.pg_access','access',FALSE,'access')
                
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('documents.pages_menu',url($this))
                    //Table Riows buttons
                    ->addEditButton('documents.pages_edit','list',null,'btn-primary edtBtn','fa fa-edit',[])
                    //->addEditButton('documents.pages_url','list/-id-/preview',null,'btn-dark','fas fa-link',['data-noloader'=>'true','data-newtab'=>'true'])
                    ->addEditButton('documents.pages_url','@/portal/-pg_name-.html',null,'btn-dark','fas fa-link',['data-noloader'=>'true','data-newtab'=>'true'])
                    //Table main buttons
                    ->addNewButton($this->model_Type->getPagesTypes(TRUE))
                    ->addDeleteButton(AccessLevel::edit)
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)
                    ->addModuleSettingsButton(null,null,['margin'=>'ml-3'])
                    ->addDisabledRecords($this->model_Page->getNotRemovablePages());
        return $this->view->render();
    }
    
    private function edit_page($record,$mode)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Page->find($record);              
        }else
        {
            $record=null;
        }
           
        $record=$this->getFlashData('_postdata',$record);
        
        if ($mode=='preview')
        {
            if (is_array($record) && array_key_exists('pg_name', $record))
            {
                return redirect()->to(url('/portal/'.$record['pg_name'].'.html'));
            }
        }
        $modes=$this->model_Type->getPagesTypes();
        if ($record==null || $record=='new')
        {
            if (!$this->hasAccess(AccessLevel::create))
            {
                return $this->getAccessError(true);
            }
            $isnew=TRUE;
            $record=$this->model_Page->getNewRecordData(TRUE);
            if ($mode=='static')
            {
                $record['pg_action']='static';
            }
            $record['pg_type']=$mode;
            if (array_key_exists($mode, $modes))
            {
                $mode=$modes[$mode];
                $record['pg_edit']=$mode['pgt_editable'];
                $record['allowguest']=$mode['pgt_allowguest'];
                $record['pg_restricted']=intval($mode['pgt_allowguest'])==1 ? 0 :1;
                $record['pg_remove']=$mode['pgt_removable'];
                $record['pg_order']=0;
                
            }else
            {
                error_mode:
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('documents.error_invalidpagetype','danger'));
            }
        }else
        {
            $mode=$record['pg_type'];
            if (array_key_exists($mode, $modes))
            {
                $mode=$modes[$mode];
                $record['allowguest']=$mode['pgt_allowguest'];
            }else
            {
                goto error_mode;
            }
            if (array_key_exists('pg_cfg', $record) && Str::isJson($record['pg_cfg']))
            {
                $record['pg_cfg']= json_decode($record['pg_cfg'],TRUE);
            }
        }
        
        $mode= is_string($mode) && $mode=='edit' ? 'static' : $mode;
        $record['acc_cfg']=$this->hasAccess(AccessLevel::settings);
        
        if (intval($record['pg_edit'])==0 && !$record['acc_cfg'])
        {
            $record['editable']=FALSE;
        } else 
        {
            $record['editable']=TRUE;
        }
        
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView('Documents/Pages/page_edit')
                ->setFormTitle('documents.pages_edit_title')
		->setPageTitle('documents.pages_edit_title')
		->setFormAction($this,'save',['page'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Page->primaryKey=>$record[$this->model_Page->primaryKey],
                            'pg_name'=>$record['pg_name'],
                            'pg_type'=>$record['pg_type'],
                            'pg_restricted'=>$record['pg_restricted']
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
                
                ->addBreadcrumbSubSettings()
		->addBreadcrumb('documents.pages_menu',url($this,'list'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['pg_name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('page',$record,'documents.-key-')
                ->addSelect2('.select2')
                ->addEditorScript();
            
            if ($mode['pgt_name']=='static' && $record['editable'])
            {
                $this->view->setTab('static','documents.pages_tab_static');
            }else
            if ($mode['pgt_name']=='contact' && $record['editable'])
            {
                $this->view->setTab('contact','documents.pages_tab_contact');
            }else
            if ($record['editable'])
            {
                
                if (is_string($record['pg_cfg']))
                {
                    $record['pg_cfg']= json_decode($record['pg_cfg'],TRUE);
                }
                if (!is_array($record['pg_cfg']))
                {
                    $record['pg_cfg']=[];
                }
                if (Str::contains($mode['pgt_cfgact'], '::'))
                {
                    $this->view=loadModuleFromString($mode['pgt_cfgact'],[$this->view,$record['pg_cfg']]);
                }
            }
            return $this->view->render();
    }
    
    function page($pageid=null)
    {
        if ($pageid==null)
        {
            return $this->getPageNotFoundError();
        }
        $post=$this->request->getPost();
        if (is_array($post) && count($post) > 0)
        {
            if (Arr::KeysExists(['login','password'], $post))
            {
                $loged=$this->loginform($post);
                if (!is_bool($loged)){return $loged;}
            }
        }
        
        /*Check if type exists*/
        $pgType=$this->model_Type->filtered(['pgt_name'=>$pageid])->first();
        if (is_array($pgType) && array_key_exists('pgt_cfgact', $pgType))
        {
            $pgType=$pgType['pgt_cfgact'];
            if (is_string($pgType) && Str::isJson($pgType))
            {
                $pgType= json_decode($pgType,TRUE);
                
                if (is_array($pgType) && Arr::KeysExists(['controller','action'], $pgType))
                {
                    return $this->getView(loadModuleFromArray($pgType));
                }
            }else
            if (is_string($pgType) && Str::contains ($pgType, '::'))
            {
                return $this->getView(loadModuleFromString($pgType));
            }
            return $this->getPageNotFoundError(); 
        }

        
        if ($pageid=='forgetpassword')
        {
            $content=loadModule('Users','forget');
            
                if (!is_string($content))
                {
                    return $content;
                }
                
                $this->view->addData('content',$content);
                return $this->view->setFile('Documents/Pages/pages_show')->render('plainhtml',TRUE);
            try{
                $content=loadModule('Users','forget');
                
                if (!is_string($content))
                {
                    return $content;
                }
                
                $this->view->addData('content',$content);
                return $this->view->setFile('Documents/Pages/pages_show')->render('plainhtml',TRUE);
            }catch(\Exception $ex){//
                $this->getNoPageError();
                exit;
            }
        }
        $pageid=$this->model_Page->filtered(['pg_name'=>$pageid])->first();
        
        if (!is_array($pageid) || (is_array($pageid) && !Arr::KeysExists(['pg_action'], $pageid)))
        {
            error:
            $this->view->addData('content',view('errors/html/error_404'));
            goto render;
        }
        
        if ((Arr::KeysExists(['pg_restricted'], $pageid) && intval($pageid['pg_restricted'])==1) || $this->auth->isLoged())
        {
            login_form:
            $loged=$this->loginform();
            if (!is_bool($loged)){return $loged;}
            $this->view->addData('menu',$this->model_Page->getPagesForMenu())
                       ->addData('menu_curpage',$pageid['pg_name']);
        }
        
        if (array_key_exists('access', $pageid) && $pageid['access']!=0 && $pageid['access']!='0' && !$this->hasAccess($pageid['access']))
        {
            if ($this->auth->isLoged())
            {
                return $this->getAccessError();
            } else 
            {
                goto login_form;
            }
        }
        
        if (Arr::KeysExists(['pg_type','pg_name','pg_cfg'], $pageid) && $pageid['pg_type']=='contact')
        {
            $msg='';
            if (is_array($post) && Arr::KeysExists(['email','name','msg'], $post))
            {
                $pageid['pg_cfg']= json_decode($pageid['pg_cfg'],TRUE);
                if (!is_array($pageid['pg_cfg']))
                {
                    $msg=$this->createMessage('documents.error_invalidcontacttarget','danger');
                    goto contact_form;
                }
                $this->sendNotification($this->model_Settings->get('pages.pages_cfg_contactsubmittpl'), $post,$pageid['pg_cfg']);
                $msg=$this->createMessage('documents.msg_contactsubmit','success');
            }
            contact_form:
            $this->contactform($msg);
            goto render;
        }
        
        if (Arr::KeysExists(['pg_type','pg_action','pg_cfg'], $pageid) && $pageid['pg_type']=='static' && $pageid['pg_action']=='static')
        {
            $this->view->addData('content',$pageid['pg_cfg']);
            goto render;
        }
        if (is_string($pageid['pg_cfg']) && Str::isJson($pageid['pg_cfg']))
        {
            $pageid['pg_cfg']= json_decode($pageid['pg_cfg'],TRUE);
        }
        $pageid['pg_action']=Arr::Replace(is_array($pageid['pg_cfg']) ? $pageid['pg_cfg'] : [], $pageid['pg_action'], '{value}');
        $content=loadModuleFromString($pageid['pg_action'],[$pageid['pg_cfg']]);
        
        if (!is_string($content))
        {
            return $this->getView($content);
        }
        $this->view->addData('content',$content);
        render:
        $this->renderpage();
    }
    
    private function renderpage(string $content=null)
    {
        if (is_string($content) && strlen($content) > 0)
        {
            $this->view->addData('content',$content);
        }
        $this->view->setFile('Documents/Pages/pages_show')
                   ->setMainTheme('template/customer')
                   ->addCookieConsentBar()
                   ->addSelect2()
                   ->addInputMaskScript()
                   ->render();
    }
    
    private function contactform(string $msg=null)
    {
        $form=new Pages\FormView($this);
        $form->setFormAction(current_url())
             ->setFormTitle('documents.pages_contactform_title');
        $hidden=[];
        if ($this->auth->isLoged())
        {
            $hidden=
            [
                'name'=>$this->auth->user()->name,
                'email'=>$this->auth->user()->email
            ];
        }else
        {
            $form->addInputField('documents.pages_contact_name', 'name', null, ['required'=>TRUE])
                 ->addEmailField('documents.pages_contact_email', 'email',null,['required'=>TRUE]);
        }   
        $form->addTextAreaField('documents.pages_contact_msg', 'msg', null, ['required'=>TRUE,'cols'=>90])
             ->setCustomSaveButton('documents.pages_contact_sub', '')
             ->addValidation('simple')
             ->addData('_form_error',$msg)
             ->setFormArgs([],$hidden,['class'=>'mx-auto col-8']);
        $this->view->addData('content',view($form->getFile(),$form->getViewData()));
    }
    
    private function loginform(array $creds=[])
    {
        $welcome_msg=lang('system.auth.loginform_title');
        if (count($creds) > 0)
        {
            $creds['username']=$creds['login'];
            $creds=$this->auth->authenticate($creds);
            if (!$creds->isOK())
            {
                $welcome_msg=$this->createMessage($creds->reason(),'danger');
            }else
            {
                return TRUE;
            }
        }
        
        if (!$this->auth->isLoged())
        {
            return $this->view->setFile('Auth/login_form')
                              ->addCookieConsentBar()
                              ->addData('welcome_msg',$welcome_msg)
                              ->render('plainhtml',TRUE);
        }
        return TRUE;
    }
    
    function save($type, $post = null) 
    {
        $post = !is_array($post) ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
        //dump($post);exit;
        if ($type=='page')
        {
            if (Arr::KeysExists(['pgid','pg_title'], $post) && !is_numeric($post['pgid']))
            {
                $post['pg_name']=mb_url_title(str_replace(['-'], '', $post['pg_title']),'_',TRUE);
                $count=$this->model_Page->count(['pg_name'=>$post['pg_name']]);
                if ($count > 0)
                {
                    $post['pg_name'].='-'.$count;
                }
            }
            if (Arr::KeysExists(['pgid','pg_order'], $post) && is_array($post['pg_order']))
            {
                $post['pg_order_arr']=$post['pg_order'];
                unset($post['pg_order']);
            }
        }
        return parent::save($type,$post);
    }
    
    function _after_save($type, $post, $refurl, $refurl_ok) 
    {
        if (($type=='page' || $type=='model_page') && array_key_exists('pg_order_arr', $post) && is_string($post['pg_order_arr']))
        {
            $post['pg_order_arr']= json_decode($post['pg_order_arr'],TRUE);
            if(is_array($post['pg_order_arr']) && count($post['pg_order_arr']) > 0)
            {
                 $this->model_Page->updateOrder($post['pg_order_arr']);
            }
        }
        return TRUE;
    }
    
    function settings($tab,$record)
    {
        $settings=$this->model_Settings->get('pages.pages_cfg*',FALSE,'*');
        $view=new Pages\FormView($this);
        if ($tab=='cfg')
        {
            $tpls=$this->model_Documents_Report->getTemplatesForForm();
            $args=['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE).'&tab=cfg')])];
            $view->addDropDownEditableField('documents.settings_pages_cfg_contactsubmittpl', 'settings[pages_cfg_contactsubmittpl]',$tpls, $settings['pages_cfg_contactsubmittpl']['value'],$args);
            $view->addYesNoField('documents.settings_pages_cfg_islivecustomer', $settings['pages_cfg_islivecustomer']['value'],'settings[pages_cfg_islivecustomer]');
        }
        return view('System/form_fields',$view->getViewData());
        
    }
    
    function getView($value)
    {
        if (is_array($value) && array_key_exists('content', $value))
        {
            return $this->renderpage($value['content']);
        }else
        if (is_array($value) && array_key_exists('error', $value))
        {
            return $this->getError($value['error']);
        }else
        if (is_bool($value) && !$value)
        {
            return $this->getPageNotFoundError();
        }
        return $value;
    }
    
    function getPageNotFoundError() 
    {
        return $this->view->setFile('Documents/Pages/pages_show')
                   ->setMainTheme('template/customer')
                   ->addCookieConsentBar()
                    ->addData('content',parent::getNotFoundError(TRUE))
                   ->render();
    }
    
    protected function getError($body, bool $redirect = FALSE, bool $render = TRUE) 
    {
        if ($redirect!=FALSE)
        {
            return redirect()->to(is_bool($redirect)? $this->getRefUrl():$redirect)->with('error',$this->createMessage($body,'danger'));
        }
        
        return $this->view->setFile('Documents/Pages/pages_show')
                   ->setMainTheme('template/customer')
                   ->addCookieConsentBar()
                   ->addData('content',parent::getError($body, $redirect, TRUE))
                   ->render();
    }
    
}