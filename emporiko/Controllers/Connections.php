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

class Connections extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'index'=>           AccessLevel::view,
        'recordcall'=>      AccessLevel::view,
        'contacts'=>        AccessLevel::view,
        'contact'=>         AccessLevel::edit,
        'validatefield'=>   AccessLevel::edit,
        'campaigntargets'=> AccessLevel::edit,
        'campaigns'=>       AccessLevel::edit,
    ];

    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = ['url'];

    /**
     * Array with function names and linked models names
     */
    public $assocModels = 
    [
        'contacts'=>'System/Contact',
        'redirect'=>'System/Redirect',
        'contacts'=>'System/Contact',
        'cust'=>'Customers/Customer',
        'target'=>'Emails/Target',
        'campaign'=>'Emails/Campaign'
    ];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actual function name)
     */
    public $remaps =[];
    
    /**
     * Array with function names which are excluded from routes actions
     * @var Array
     */
    protected $routerexlude = [];
      
    
    function campaigns($record=null,$mode='edit')
    {
        $settings=$this->model_Campaign->getSettings();
        if ($record!=null)
        {
                if ($mode=='start' && is_numeric($record))
                {
                    $record=$this->model_Campaign->start($record,TRUE);
                    if ($record!=FALSE)
                    {
                        if (is_array($record) && array_key_exists('ec_notify', $record))
                        {
                            $record['mode']=lang('connections.msg_campaign_status_start');
                            $record['ec_notify']= json_decode($record['ec_notify'],TRUE);
                            if (is_array($record['ec_notify']))
                            {
                               $this->sendNotification($this->model_Settings->get('connections.campaigns_defnotifytpl'), $record, array_values($record['ec_notify'])); 
                            } 
                        }
                        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.msg_campaign_start','success'));
                    }
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.error_campaign_start','danger'));
                }
                 
                $isnew=FALSE;
                if ($record=='new')
                {
                    if (!$this->hasAccess(AccessLevel::create))
                    {
                        return $this->getAccessError(true);
                    }
                    $isnew=TRUE;
                    $record=$this->model_Campaign->getNewRecordData(TRUE);
                    $record['enabled']='1';
                    if ($this->request->getGet('type')!=null && array_key_exists($this->request->getGet('type'), $this->model_Campaign->getTypeListForDropDown()))
                    {
                        $record['ec_type']=$this->request->getGet('type');
                    }else
                    {
                        $record['ec_type']='email';
                    }
                    $record['ec_links']=[];
                }else
                {
                    $record=$this->model_Campaign->filtered(['ecid'=>$record,'|| ec_name'=>$record])->first();
                    if (strlen($record['ec_tpl']) > 0 && Str::startsWith($record['ec_tpl'],'{'))
                    {
                        $record['ec_tpl']= json_decode($record['ec_tpl'],TRUE);
                    }
                    $record['ec_notify']= json_decode($record['ec_notify'],TRUE);
                    $record['ec_links']= json_decode($record['ec_links'],TRUE);
                }
                $record=$this->getFlashData('_postdata',$record);
                if (!is_array($record))
                {
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.error_invalid_ecid','danger'));
                }
                if ($mode=='stop')
                {
                    $record['ec_status']='complete';
                    if ($this->model_Campaign->save(['ecid'=>$record['ecid'],'ec_status'=>$record['ec_status']]))
                    {
                        if (is_array($record) && array_key_exists('ec_notify', $record))
                        {
                            $record['mode']=lang('connections.msg_campaign_status_stop');
                            $record['ec_notify']= json_decode($record['ec_notify'],TRUE);
                            if (is_array($record['ec_notify']))
                            {
                               $this->sendNotification($this->model_Settings->get('connections.campaigns_defnotifytpl'), $record, array_values($record['ec_notify'])); 
                            } 
                        }
                        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.msg_campaign_stop','success'));
                    }
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.error_campaign_stop','danger'));
                }
                
                $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
                $record['settings_acc']=$this->hasAccess(AccessLevel::settings);
                $record['_movements']=[];//$this->model_Campaign->getTrackedLinkClicks($record['ecid']);
                $this->setFormView('Emails/Campaigns/campaigns_edit')
                        ->setFormTitle('connections.ec_editheader')
                        ->setPageTitle('connections.ec_editheader')
                        ->setFormAction($this,'save',['campaign'],['refurl'=>base64url_encode($this->getRefUrl())])
                        ->parseArrayFields()
                        ->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Campaign->primaryKey=>$record[$this->model_Campaign->primaryKey],
                            'ec_type'=>$record['ec_type']
                        ]
                        ,['class'=>'col-12'])
                        ->setCustomViewEnable(FALSE)
                        ->setFormCancelUrl($this->getRefUrl())
			
                        ->addBreadcrumb('connections.ec_mainmenu',base64url_encode($this->getRefUrl()))
                        ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ec_name'],'/')
			
                        ->addData('record',$record)
                        ->setTab('general','system.general.tab_info')
                        
                        ->addFieldsFromModel('campaign',$record,'connections.-key-')
                        ->addSelect2('.select2')
                        //->addEditorScript()
                        ;
                if ($record['ec_type']=='email')
                {
                    $this->view->setTab('editor','connections.ec_tabeditor');
                }
                
                if ($record['ec_type']=='paper')
                {
                    $this->view->setTab('wyswig','connections.ec_tabpaper')
                               ->addGrapesJSLibrary(['newsletter']);
                }
                if (is_array($record['_movements']) && count($record['_movements']) > 0)
                {
                    $this->view->setTab('movements','connections.ec_tabmov');
                }
                
                $this->view->setTab('notify','connections.ec_tabnotify');
                return $this->view->render();
            }
        $this->setTableView('Emails/Campaigns/campaigns_index')
            ->setData('campaign',null,TRUE,null,[])
            ->setPageTitle('connections.ec_mainmenu')
            //Fiilters settings
            ->addFilters('campaigns')
            ->addFilterField('ec_name %')
            //Table Columns settings
            ->addColumn('connections.ec_name','ec_name',TRUE,[],null,'ec_desc')
            //->addColumn('connections.ec_budget','ec_budget',FALSE,[],'money:far fa-money-bill-alt')
            ->addColumn('connections.ec_starton','ec_starton',FALSE,[],'d M Y')
            ->addColumn('connections.ec_addedon','ec_addedon',FALSE,[],'d M Y')
            ->addColumn('connections.ec_status','ec_status',FALSE,$this->model_Campaign->getStatusListForDropDown())
            ->addColumn('connections.ec_type','ec_type',FALSE,$this->model_Campaign->getTypeListForDropDown())
            //Breadcrumb settings
            ->addBreadcrumb('connections.ec_mainmenu',url($this,'campaigns'))
            //Table Riows buttons
            ->addEditButton('system.buttons.edit_details','campaigns',null,'btn-primary edtBtn','fa fa-edit',[])
            ->addData('edit_url',url($this,'campaigns',['-id-'],['refurl'=> current_url(FALSE,TRUE)]))
            ->addData('start_url',url($this,'campaigns',['-id-','start'],['refurl'=> current_url(FALSE,TRUE)]))
            ->addData('stop_url',url($this,'campaigns',['-id-','stop'],['refurl'=> current_url(FALSE,TRUE)]))
            ->addData('settings',$settings)
            ->addCustomHeaderButton(Pages\HtmlItems\ToolbarButton::createDropDownButton('fas fa-plus', 'dark', 'system.buttons.new',$this->model_Campaign->getTypeListForDropDown(TRUE),null,['mode'=>'btn-group dropleft']))
            ->addDeleteButton(AccessLevel::edit)
            ->addHeaderButton(null,null,'button','btn btn-sm btn-warning ml-2','<i class="fas fa-tasks"></i>','connections.ect_mainmenu',AccessLevel::edit,['data-url'=>url($this,'campaigntargets',[],['refurl'=>current_url(FALSE,TRUE)])])
            ->addModuleSettingsButton('system.buttons.module_settings',AccessLevel::settings,['tabName'=>'campaigns']);
        
        return $this->view->render();
    }
        
        function campaigntargets($record=null)
        {
            if ($record!=null)
            {
                $isnew=FALSE;
                if ($record=='new')
                {
                    if (!$this->hasAccess(AccessLevel::create))
                    {
                        return $this->getAccessError(true);
                    }
                    $isnew=TRUE;
                    $record=$this->model_Target->getNewRecordData(TRUE);
                    $record['enabled']='1';
                }else
                {
                    $record=$this->model_Target->filtered(['ectrgid'=>$record,'|| ect_code'=>$record])->first();
                    $record['ect_contacts']= json_decode($record['ect_contacts'],TRUE);
                }
                $record=$this->getFlashData('_postdata',$record);
                if (!is_array($record))
                {
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.error_invalid_ectid','danger'));
                }
                $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
                
                $this->setFormView()
                        ->setFormTitle('connections.ect_editheader')
                        ->setPageTitle('connections.ect_editheader')
                        ->setFormAction($this,'save',['target'],['refurl'=>base64url_encode($this->getRefUrl())])
                        ->parseArrayFields()
                        ->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Target->primaryKey=>$record[$this->model_Target->primaryKey],
                        ]
                        ,['class'=>'col-12'])
                        ->setCustomViewEnable(FALSE)
                        ->setFormCancelUrl($this->getRefUrl())
					
                        ->addBreadcrumbSubSettings()
                        ->addBreadcrumb('connections.ect_mainmenu',base64url_encode($this->getRefUrl()))
                        ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ect_name'],'/')
			
                        ->addData('record',$record)
                        ->setTab('general','system.general.tab_info')
                        ->setTab('contacts','connections.ect_contacts_tab')
                        ->addFieldsFromModel('target',$record,'connections.-key-')
                        ->addSelect2('.select2');
                return $this->view->render();
            }
            $this->setTableView()
                    ->setData('target',null,TRUE,null,[])
                    ->setPageTitle('connections.ect_mainmenu')
                    //Fiilters settings
                    ->addFilters('campaigntargets')
                    ->addFilterField('ect_name %')
                    //Table Columns settings
                    ->addColumn('connections.ect_name','ect_name',TRUE)
                    ->addColumn('connections.ect_desc','ect_desc',TRUE,[],'len:100')
                    ->addColumn('connections.ect_enabled','enabled',FALSE,'yesno')
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('connections.ect_mainmenu','/')
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','campaigntargets',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)
                    ->addDeleteButton(AccessLevel::edit)
                    ->addNewButton('campaigntargets/new');
            return $this->view->render();
        }
    
    function url($record=null,$inframe=0)
    {
        if ($record=='generate')
        {
            if ($this->request->getGet('link')==null)
            {
                return json_encode(['error'=>'Invalid link']);
            }
            $code=$this->model_Redirect->addLink($this->request->getGet('link'),$this->request->getGet('ref'),$this->request->getGet('info'));
            return json_encode(['link'=>url($this,'url'.$code)]);
        }
        
        if ($record==null)
        {
            if ($this->request->getGet('link')!=null)
            {
                $record=$this->request->getGet('link');
            } else 
            {
               return FALSE; 
            }
        }
        if (is_string($record) && Str::startsWith($record, ['http']))
        {
            if (intval($inframe)==1 || (is_bool($inframe) && $inframe))
            {
                return view('System/Elements/iframe_embed',['url'=>$record]);
            }
            return redirect()->to($record);
        }
        $record=$this->model_Redirect->where('rd_code',$record)->first();
        if (!is_array($record)||(is_array($record) && !array_key_exists('rd_target', $record)))
        {
            return FALSE;
        }
        $record['rd_target']= Str::startsWith($record['rd_target'],'@') ? parsePath($record['rd_target']) : $record['rd_target'];
        if (Arr::KeysExists(['rd_info','rd_ref'], $record))
        {
            $this->addMovementHistory('notify', null, $record['rd_target'], $record['rd_info'], $record['rd_ref'],'tracked_link','auto');
        }
        if (Str::isJson($record['rd_target']))
        {
            $record['rd_target']= json_decode($record['rd_target'],TRUE);
            if (!is_array($record['rd_target']))
            {
                return $this->getNotFoundError();
            }
            return loadModuleFromArray($record['rd_target']);
        }
        return redirect()->to(url($record['rd_target']));    
    }
    
    function recordcall($getform=null)
    {
        if (is_array($getform))
        {
            return view('Tasks/record_call',
                    [
                        'caller_number'=>$getform['callernumber'],
                        'action_url'=>url($this,'recordcall',[],['refurl'=> current_url(FALSE,TRUE)]),
                        'initcall'=>$this->getFlashData('_initcall'),
                        'call_target'=>$getform['call_target'],
                        'call_modalinit'=> array_key_exists('call_modalinit', $getform) ? (is_bool($getform['call_modalinit']) ? 'data-phone' : $getform['call_modalinit']): null
                    ]);
        }
        $post=$this->request->getPost();
        if (!array_key_exists('caller_number', $post) || (array_key_exists('caller_number', $post) && strlen($post['caller_number']) < 4))
        {
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connection.error_no_caller_number','warning'));
        }
        if (!array_key_exists('call_action', $post) || (array_key_exists('call_action', $post) && strlen($post['call_action']) < 1))
        {
            error_no_call_action:
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connection.error_no_call_action','danger'));
        }
        if (!in_array($post['call_action'], ['call','record']))
        {
            goto error_no_call_action;
        }
        if (Arr::KeysExists(['call_info','call_target','caller_number'], $post) && strlen($post['call_info'])>0)
        {
            $post['user']= loged_user('name');
            $this->addMovementHistory('record_call', null, $post['caller_number'], $post['call_target'],lang('connections.mov_record_call_info',$post), 'record_call');
        }
        if ($post['call_action']=='call')
        {
          return redirect()->to($this->getRefUrl())->with('_initcall',$post['caller_number'])->with('error',$this->createMessage('connections.msg_call_recorded'));  
        }
        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('connections.msg_call_recorded'));
    }
    
    function contacts($record=null,$acc=null)
    {
        if ($record!=null)
        {
            return $this->contact($record,$acc);
        }
        $columns=$this->model_Settings->get('customers.cust_accounts_columns',TRUE);
        $this->setTableView()
                    ->setData('contacts',null,TRUE,null,[])
                    ->setPageTitle('connections.contacts_mainmenu')
                    //Fiilters settings
                    ->addFilters('contacts')
                    ->addFilterField('ct_name %')
                    ->addFilterField('|| ct_email %')
                    ->addFilterField('|| ct_phone %')
        
                    //Table Columns settings
                    ->addColumn('connections.ct_name','ct_name',TRUE)
                    ->addColumn('connections.ct_email','ct_email',TRUE)
                    ->addColumn('connections.ct_phone','ct_phone',FALSE);
                    
        //Breadcrumb settings            
        $this->view->addBreadcrumbSubSettings()
                    ->addBreadcrumb('connections.contacts_mainmenu',current_url())
                    //Table Rows buttons
                    ->addEditButton('connections.contacts_viewbtn','contacts/-id-',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addDeleteButton()
                    ->addNewButton('contacts/new')
                    ->addUploadButton('contacts','id_button_upload','connections.contacts_btn_upload');
        
        return $this->view->render();
    }
    
    function contact($record,$acc=null)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Contacts->find($record);              
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
            $record=$this->model_Contacts->getNewRecordData(TRUE);
            if ($acc!=null)
            {
                $record['ct_account']=is_array($acc) ? $acc : [strtoupper($acc)];
            }
        }//dump($record);exit;
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        
        if (!$isnew)
        {
            if (is_string($record['ct_account']))
            {
               $record['ct_account']=Arr::fromString($record['ct_account']);
            }else
            {
                $record['ct_account']=[];
            }
        }
        
        $this->setFormView('Customers/edit_contact')
                ->setFormTitle('customers.accounts_editform')
		->setPageTitle('customers.accounts_editform')
		->setFormAction($this,'save',['contacts'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Contacts->primaryKey=>$record[$this->model_Contacts->primaryKey]
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
                
		->addBreadcrumbSubSettings()		
                ->addBreadcrumb('connections.contacts_mainmenu',url($this,'contacts'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ct_name'],'/')
			
		->addData('record',$record)   
                ->addData('validateurl',url($this,'validatefield',['contacts'],[]))
                ->addData('customers',$this->model_Contacts->getAccountList())
                ->addValidation('contacts','ctid',$record['ctid'],['ct_name','ct_email'])
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('contacts',$record,'connections.-key-')
                ->setTab('cttab','connections.contacts_cttab')  
                ->setTab('custab','connections.contacts_custab')
                ->setTab('soctab','connections.contacts_soctab')
                ->setTab('othtab','connections.contacts_othtab')
                ->addSelect2();// $this->model_Cust->getCustomersForDropDown('code',null,TRUE)
            return $this->view->render();
    }
    
    function save($type, $post = null) 
    {
        $post = $post == null ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
        if ($type=='contacts')
        {
            if (array_key_exists('ct_account', $post) && is_array($post['ct_account']))
            {
                $post['ct_account']=implode(';',$post['ct_account']);
            }
        }
        return parent::save($type, $post);
    }
    
    
    function pages(string $mode, Pages\FormView $view, array $data)
    {
        if ($mode=='redirect')
        {
            return $view->setTab('settings','system.general.tab_cfg')
                        ->addInputField('connections.pages_link','pg_cfg[link]',array_key_exists('link', $data) ? $data['link'] : '',[])
                        ->addYesNoField('connections.pages_inframe',array_key_exists('inframe', $data) ? $data['inframe'] : '','pg_cfg[inframe]',[])
                        ->addHiddenField('pg_action', 'Connections::url@{link},{inframe}');
        }
    }
}