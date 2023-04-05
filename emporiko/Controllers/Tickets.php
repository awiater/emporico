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

class Tickets extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'index'=>               AccessLevel::view,
        'getDashboardTile'=>    AccessLevel::view,
        'templates'=>           AccessLevel::modify,
        'template'=>            AccessLevel::modify,
        'cases'=>               AccessLevel::view,
        'case'=>                AccessLevel::view,
        'types'=>               AccessLevel::settings,
        'save'=>                AccessLevel::modify,
    ];


    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = [];

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
        'template'=>'Customers/TicketTemplate',
        'ticket'=>'Customers/Ticket',
        'type'=>'Customers/TicketType'
    ];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actual function name)
     */
    public $remaps = 
    [
        'index'=>'cases',
    ];
    protected $routerexlude = ['test'];
    
   
    public function types($record=null)
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        if ($record!=null)
        {
            return $this->type($record);
        }
        
        $this->setTableView()
                    ->setData('type','tit_order',TRUE,null,[])
                    ->setPageTitle('tickets.types_main')
                    //Fiilters settings
                    ->addFilters('types')
                    ->addFilterField('tit_name %')
                    //Table Columns settings
                    ->addColumn('tickets.tit_name','tit_name',TRUE)
                    ->addColumn('tickets.tit_desc','tit_desc',TRUE,[],'len:40')
                    ->addColumn('tickets.tit_icon','tit_icon',FALSE,[],'icon')
                    ->addColumn('tickets.tit_type','tit_type',FALSE,[],'color')
                    ->addColumn('tickets.tit_textcolor','tit_textcolor',FALSE,[],'color')
                    //Breadcrumb settings
                    ->addBreadcrumb('tickets.ticket_main',url($this))
                    ->addBreadcrumb('tickets.types_main',current_url())
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','types',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)
                    ->addDeleteButton(AccessLevel::edit)
                    ->addNewButton('types/new');
        if (!$this->model_Auth_User->isUserSuperAdmin())
        {
            $this->view->addDisabledRecords($this->model_Type->getNotEditable());
        }
        return $this->view->render();
    }
    
    private function type($record)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Type->find($record);              
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
            $record=$this->model_Type->getNewRecordData(TRUE);
        } 
        
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView('Customers/Tickets/ticket_type_edit')
                ->setFormTitle('tickets.types_edit')
		->setPageTitle('tickets.types_edit')
		->setFormAction($this,'save',['type'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Type->primaryKey=>$record[$this->model_Type->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('tickets.ticket_main',url($this))
                ->addBreadcrumb('tickets.types_main',current_url())
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['tit_name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('type',$record,'tickets.-key-')
                ->addSelect2();
        
            return $this->view->render();
    }
    
    public function cases($record=null)
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        if ($record!=null)
        {
            if ($record=='newlist')
            {
                return $this->casenewlist();
            }
            return $this->case($record);
        }
        $filters=[];
        if (!$edit_acc)
        {
            $filters['tck_account']=$this->model_Customers_Customer->getCustomerForLogedUser();
        }else
        if ($this->request->getGet('customer')!=null)
        {
            $filters['tck_account']=$this->request->getGet('customer');
        }
        $this->setTableView('Customers/Tickets/tickets_index')
                    ->setData('ticket',null,TRUE,null,$filters)
                    ->setPageTitle('tickets.ticket_main')
                    //Fiilters settings
                    ->addFilters('cases')
                    ->addFilterField('tck_subject %')
                    //Table Columns settings
                    ->addColumn('tickets.tck_subject','tck_subject',TRUE)
                    ->addColumn('tickets.tck_status','tck_status',TRUE,$this->model_Ticket->getTicketsStatuses())
                    ->addColumn('tickets.tck_type','tck_type',TRUE,$this->model_Ticket->getTicketsTypes(FALSE,FALSE))
                    //Breadcrumb settings
                    ->addBreadcrumb('tickets.ticket_main',url($this))
                    //Table Riows buttons
                    ->addEditButton('tickets.btn_showdetails','cases',null,'btn-info edtBtn','fas fa-info-circle',[], AccessLevel::modify)
                    //Table main buttons
                    //->addHeaderButton(null,'id_tickets_newbtn','button','btn btn-sm btn-dark','<i class="fas fa-plus"></i>','tickets.tck_newmodal_btnopen',AccessLevel::view,['onclick'=>"$('#id_tickets_newmodal').modal('show');"])
                    ->addNewButton('cases/newlist', AccessLevel::create)
                    ->setNoDataMessage('tickets.msg_no_data');
        if ($edit_acc)
        {
            $this->view->addColumn('tickets.tck_priority','tck_priority',FALSE,lang('tickets.tck_priority_list'))
                       ->addColumn('tickets.tck_account','tck_account',FALSE,$this->model_Customers_Customer->getForForm('code','name'))
                       //->addData('templates',$this->model_Template->getTilesForEmployee())
                       ->addHeaderButton(null,null,'button','btn btn-sm btn-warning ml-2','<i class="fas fa-puzzle-piece"></i>','tickets.templates_main',AccessLevel::settings,['data-url'=>url($this,'templates',[],['refurl'=> current_url(FALSE,TRUE)])])
                       ->addModuleSettingsButton(); 
        }else
        {
            //$this->view->addData('templates',$this->model_Template->getTilesForCustomer());
        }
        return $this->view->render();
    }
    
    private function casenewlist()
    {
        $filters=['refurl'=> base64url_encode($this->getRefUrl())];
        if ($this->request->getGet('acc'))
        {
            $filters['acc']=$this->request->getGet('acc');
        }
        
        if (!$this->hasAccess(AccessLevel::state))
        {
            $templates=$this->model_Template->getTilesForCustomer($filters);
        } else 
        {
            $templates=$this->model_Template->getTilesForEmployee();
        }
        
        $this->view->setFile('Customers/Tickets/ticket_newdash')
                   ->addBreadcrumb('tickets.ticket_main',url($this))
                   ->addBreadcrumb('system.buttons.new','/')
                   ->addData('refurl',$this->getRefUrl())
                   ->addData('templates',$templates)
                   ->addDataUrlScript('#id_newcase_templates_container');
        return $this->view->render();
    }
    
    private function case($record)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
       
        if (is_numeric($record))
        {
            $record=$this->model_Ticket->find($record);
            if (!is_array($record))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('tickets.error_case_id','danger'));
            }
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
            $record=$this->model_Ticket->getNewRecordData(TRUE);
            $mode='edit';
            $record['tck_extrafields']='';
            if ($this->request->getGet('tpl')!=null)
            {
                $record=$this->model_Template->getByName($this->request->getGet('tpl'),$record);
            }
            $customers=$this->model_Customers_Customer->getForForm('ciid','code');
            if ($this->request->getGet('acc'))
            {
               $record['tck_account']=$this->request->getGet('acc');
               $record['tck_account']=array_key_exists($record['tck_account'], $customers) ? $customers[$record['tck_account']] : array_values($customers)[0]; 
            }else
            {
                $record['tck_account']=array_key_exists(loged_user('customer'), $customers) ? $customers[loged_user('customer')] : array_values($customers)[0];
            }
            
            $this->setFormView();
            $record['tck_extrafields']= strlen($record['tck_extrafields']) > 0 ? json_decode($record['tck_extrafields'],TRUE) : $record['tck_extrafields'];
            if (is_array($record['tck_extrafields']))
            {
                $record['tck_extrafields']=$this->model_Template->getExtraFields($record['tck_extrafields'],TRUE);
            }else
            {
                $record['tck_extrafields']=[];
            }
        }else
        {
            if ($record['tck_status']=='0' && $record['tck_user']!= loged_user('username'))
            {
                $record['tck_status']=1;
                $record['tck_target']= json_decode($record['tck_target'],TRUE);
                if (is_array($record['tck_target']) && count($record['tck_target']) > 0)
                {
                    if (array_key_exists('creator', $record['tck_target']))
                    {
                        $record['tck_target']['creator']='arturwiater@gmail.com';
                        $record['comment']=lang('tickets.msg_ticket_assigned');
                        $this->triggerRule('ticket_assigned', ['data'=>$record,'cust'=>[$record['tck_target']['creator']]]);
                        $contact=$this->model_Ticket->getContactName(null);
                        $this->addMovementHistory('tickets_casefollow', $contact, null, $record['tiid'], lang('tickets.msg_assigned_mov',[$contact]), 'tickets');
                    }
                    
                }
                unset($record['tck_target']);
                $this->model_Ticket->save($record);
            }
            $this->setFormView('Customers/Tickets/ticket_view');
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->view
                ->setFormTitle('tickets.ticket_edit')
		->setPageTitle('tickets.ticket_edit')
		->setFormAction($this,'save',['ticket'],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Ticket->primaryKey=>$record[$this->model_Ticket->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('tickets.ticket_main',url($this))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['tck_subject'].'-'.$record['tiid'],'/')
			
		->addData('record',$record)
                ->addEditorScript(TRUE,'simple','');
        if ($isnew)
        {
            $this->view ->setTab('general','system.general.tab_info')
                        ->addFieldsFromModel('ticket',$record,'tickets.-key-')
                        ->addSelect2('.select2');
        }else
        {
            $toolbarButtons=[];
            $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createBackButton($refurl,'tickets.btn_backtolist','dark mr-3');
            $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createPrintButton('id','id_tickets_container',['tickets.msg_ticket_print_title',[$record['tiid']]],'system.buttons.print',[]);
            $readonly=in_array($record['tck_status'], $this->model_Settings->get('tickets.tickets_ticketnonpendingstatus',TRUE));
            if ($this->hasAccess(AccessLevel::modify) && !$readonly)
            {
                $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createModalStarter('id_tickets_modal_comment', 'fas fa-comment-medical', 'primary', 'tickets.btn_addcomm', null,[]);
            }
            
            if ($record['edit_acc'])
            {
                $href=url($this,'save',['ticket'],['refurl'=> current_url(FALSE,TRUE)]);
                if ($readonly)
                {
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-retweet','success',url($this,'reopencase',[$record['tiid']],['refurl'=>current_url(FALSE,TRUE)]),'tickets.btn_ticketreopen',null);
                }else
                {
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createModalStarter('id_tickets_modal_changepriority', 'fas fa-stream', 'warning ml-3', 'tickets.btn_changepriority', null,[]);
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-window-close','danger ml-1',$href,'tickets.btn_ticketreject',null,['data-status'=>'3']);
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-check-square','dark',$href,'tickets.btn_ticketclose',null,['data-status'=>'2']);
                    $convertable=$this->model_Type->getConversionPath($record['tck_type'],$record['tiid']);
                    if($convertable!=FALSE)
                    {
                       $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-exchange-alt','success ml-2',$convertable,'tickets.btn_convert',null); 
                    }
                }
            }
            
            $this->view->addData('ticket_types',$this->model_Ticket->getTicketsTypes(FALSE,FALSE))
                       ->addData('ticket_priority',$this->model_Ticket->getTicketPriorities())
                       ->addData('ticket_statuses',$this->model_Ticket->getTicketsStatuses())
                       ->addData('movements',$this->model_Ticket->getTicketMovements($record['tiid']))
                       ->addData('logeduser',loged_user('username'))
                       ->addData('commendaddurl',url($this,'save',['ticket'],['refurl'=> current_url(FALSE,TRUE)]))
                       ->addMenuBar($toolbarButtons,['background'=>'white'])
                       ->addPrintLibrary();
        }
               
        
            return $this->view->render();
    }
    
    
    public function templates($record=null)
    {
        if ($record!=null)
        {
            return $this->template($record);
        }
        /*echo Pages\HtmlItems\PartNumbersListField::create()
                ->setName('part_numbers')->setID('id_part_numbers')->setText('products.tplextrafield_partslist')->setValue('')->serialize();exit;*/
        $this->setTableView()
                    ->setData('template',null,TRUE,null,[])
                    ->setPageTitle('tickets.templates_main')
                    //Fiilters settings
                    ->addFilters('templates')
                    ->addFilterField('name %')
                    //Table Columns settings
                    ->addColumn('tickets.templates_title','title',TRUE)
                    ->addColumn('tickets.templates_desc','desc',TRUE,[],'len:100') 
                    ->addColumn('tickets.templates_iscustomer','iscustomer',FALSE,'yesno')
                    ->addColumn('tickets.templates_enabled','enabled',FALSE,'yesno')
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('tickets.templates_main',current_url())
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','templates',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)
                    ->addDeleteButton(AccessLevel::edit)
                    ->addNewButton('templates/new');
        if ($this->hasAccess(AccessLevel::settings))
        {
            $this->view->addHeaderButton(null,null,'button','btn btn-sm btn-warning','<i class="fas fa-cog"></i>','tickets.types_main',AccessLevel::settings,['data-url'=>url($this,'types',[],['refurl'=> current_url(FALSE,TRUE)])]);
        }
        return $this->view->render();
    }
    
    private function template($record)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Template->find($record);              
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
            $record=$this->model_Template->getNewRecordData(TRUE);
            $record['template']='{Case Description}';
        } else 
        {
            $record['extrafields']= strlen($record['extrafields']) > 0 ? json_decode($record['extrafields'],TRUE) : $record['extrafields'];
        }
        
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView()
                ->setFormTitle('tickets.templates_edit')
		->setPageTitle('tickets.templates_edit')
		->setFormAction($this,'save',['template'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Template->primaryKey=>$record[$this->model_Template->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('tickets.ticket_main',url($this))
                ->addBreadcrumb('tickets.templates_main',url($this,'templates',[],[]))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->setTab('fields','tickets.templates_extrafields_tab')
                ->setTab('tpl','tickets.templates_tpltab')
                ->addFieldsFromModel('template',$record,'tickets.templates_-key-')
                ->addEditorScript(TRUE)
                ->addSelect2('.select2');
        
            return $this->view->render();
    }
    
    function getDashboardTile($type='notify')
    {
        $form=new Pages\FormView($this);
        $data=[];
        if ($type=='notify')
        {
            $type='System/Dashboard/tile';
            $name='tickets_tile_notify_'.rand(1,12);
            $data=$this->model_Movements->getNotifications(10,'*');
            $data=
            [
                'data'=>count($data) > 0 ? view('Tasks/Tickets/tile_notify',['data'=>$data,'name'=>$name]) : lang('tickets.notify_msg_noitems'),
                'header'=>lang('tickets.notify_tile_header'),
                'name'=>$name,
                'tilePrintButton'=>count($data) > 0,
                'header_style'=>'background-color:#fd7e14!important;color:#FFF!important',
                'ScrollBody'=>count($data) > 0
            ];
        }
        return view($type,$data);
    }
    
    function sendNotification(string $template, array $data, $to = null) 
    {
        foreach($data as $key=>$value)
        {
            if (Str::startsWith($key, 'tck_') && is_string($value))
            {
                $data[substr($key,4)]=$value;
                unset($data[$key]);
                $key=substr($key,4);
            }
            
            if ($key=='tiid')
            {
                $data['ticket_id']=$value;
                unset($data[$key]);
            }
            
            if ($key=='priority' && is_numeric($value))
            {
                $arr=$this->model_Ticket->getTicketPriorities();
                $data[$key]=$arr[$value];
            }
            
            if (is_array($value))
            {
                unset($data[$key]);
            }
        }
        if (!array_key_exists('contact', $data))
        {
            $data['contact']=null;
        }
        $data['contact']=$this->model_Ticket->getContactName($data['contact']);
        
        
        return parent::sendNotification($template, $data, $to);
    }
    
    function reopencase($caseid) 
    {
        $caseid=$this->model_Ticket->find($caseid);
        if (!is_array($caseid))
        {
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('tickets.error_case_id','danger'));
        }
        $caseid['tck_status']=$this->model_Settings->get('tickets.tickets_ticketreopenstatus');
        if ($this->model_Ticket->save($caseid))
        {
            $this->addMovementHistory('tickets_casefollow', loged_user('name'), null, $caseid['tiid'],lang('tickets.msg_casereopenok') , 'tickets');    
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('tickets.msg_casereopenok','success'));  
        }
        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('tickets.error_reopencasefailed','danger'));
    }


    function save($type, $post = null) 
    {
        $post = $post == null ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
        dump($post);exit;
        if ($type=='type')
        {
            if (array_key_exists('titid', $post) && !is_numeric($post['titid']))
            {
                if (array_key_exists('tit_name', $post))
                {
                    $post['tit_code']=mb_url_title(strtolower($post['tit_name']),'_');
                }
            }
            if (array_key_exists('tit_order', $post) && is_array($post['tit_order']))
            {
                $this->model_Type->setOrder($post['tit_order']);
                unset($post['tit_order']);
            }
        }else   
        if ($type=='ticket')
        {
            $post['_notify']=['data'=>[]];
            
            if (array_key_exists('ticket_id', $post))
            {
                $post['ticket_id']=$this->model_Ticket->find($post['ticket_id']);
                if (!is_array($post['ticket_id']))
                {
                    $post=[];
                    goto endof_function;
                }
                $post['tiid']=$post['ticket_id']['tiid'];
                $post['_notify']['to']=json_decode($post['ticket_id']['tck_target'],TRUE);
                if (array_key_exists('_only_comment', $post))
                {
                    $post['tck_subject']=$post['ticket_id']['tck_subject'];
                    $post['priority']=$post['ticket_id']['tck_priority'];
                }
               $post['ticket_id']=$post['tiid'];
               $post['_notify']['data']['ticket_id']=$post['tiid'];
            }
            
            if (array_key_exists('tck_subject', $post))
            {
                $post['_notify']['data']['subject']=$post['tck_subject'];
            }
            
            if (array_key_exists('priority', $post))
            {
                $post['tck_priority']=$post['priority'];
                $post['_notify']['data']['priority']=$post['priority'];
            }
            
            if (array_key_exists('comment', $post))
            {
                $post['_notify']['data']['comment']=$post['comment'];
            }
            
            $post['_notify']['data']['account']=$this->model_Customers_Customer->getCustomerForLogedUser();
            $post['_notify']['data']['contact']=loged_user('name');
            
            if (array_key_exists('tck_type', $post))
            {
                $post['_notify']['data']['type']=$this->model_Type->getTicketData($post['tck_type'],'tit_name');
            }
            $post['_parsers']=
            [
                'priority'=>$this->model_Ticket->getTicketPriorities()
            ];
            $post['_parsers']['fields']=[];
            
            if (array_key_exists('tck_extrafields', $post) && is_array($post['tck_extrafields']))
            {
                $extrafields=$this->model_Template->getExtraFields();
                foreach($post['tck_extrafields'] as $key=>$item)
                {
                    if ($key=='partslist' && is_array($item['value']))
                    {
                        $item['value']=view('System/Elements/partnumbers_list_table',['parts'=>$item['value']]);
                    }
                    $post['_notify']['data'][$key]=is_array($item['value']) ? json_encode($item['value']) : $item['value'];
                    if (array_key_exists($key, $extrafields))
                    {
                        $post['_parsers']['fields']['{'.$extrafields[$key].'}']= is_array($item['value']) ? json_encode($item['value']) : $item['value'];
                    }
                    
                }
                $post['tck_extrafields']=json_encode($post['tck_extrafields']);
            }
            
            if (!is_numeric($post['tiid']))
            {
                $post['tck_addedon']=formatDate();
                //$post['tck_account']=$post['_notify']['data']['account'];
                $post['tck_user']=$post['_notify']['data']['contact'];
                if (array_key_exists('tck_target', $post))
                {
                    $post['tck_target']= base64_decode($post['tck_target']);
                    $template=$this->model_Template->where('name',$post['tck_target'])->first();
                    if (is_array($template))
                    {
                        if (array_key_exists('template', $template))
                        {
                            if (array_key_exists('tck_desc', $post))
                            {
                                $post['_parsers']['fields']['{'.lang('tickets.templates_descfield').'}']=$post['tck_desc'];
                            }
                            $post['tck_desc']= str_replace(array_keys($post['_parsers']['fields']), $post['_parsers']['fields'], $template['template']);
                            $post['_notify']['data']['desc']=$post['tck_desc'];
                        }
                        if (array_key_exists('targetgrp', $template))
                        {
                            $template['targetgrp']=str_replace(':system_support',$this->model_Settings->get('tickets.tickets_itsupportemail'),$template['targetgrp']);
                            $post['tck_target']= json_decode($template['targetgrp'],TRUE);  
                        }else
                        {
                            goto def_target;
                        }
                        
                    } else 
                    {
                        goto def_target;
                    }
                }else
                {
                    def_target:
                    $post['tck_target']=$this->model_Settings->get('tickets.tickets_deftargetgroups');
                    $post['tck_target']= json_decode($template['tck_target'],TRUE);  
                } 
                $post['_notify']['to']=$post['tck_target'];
                $post['tck_target']['creator']=loged_user('email');
                $post['tck_target']= json_encode($post['tck_target']);
                $post['_notify']['template']=$this->model_Settings->get('tickets.tickets_newtickettpl');
            }else
            {
               $post['_notify']['template']=$this->model_Settings->get('tickets.tickets_ticketupdatetpl'); 
            }
            if (array_key_exists('_only_comment', $post))
            {
                if (Arr::KeysExists(['status','tiid'], $post))
                {
                    $this->model_Ticket->save(['tiid'=>$post['tiid'],'tck_status'=>$post['status']]);
                    if ($post['status']==3 || $post['status']=='3')
                    {
                        $post['comment']=lang('tickets.msg_casestatusrejected',[$post['comment']]);
                    }
                    if ($post['status']==2 || $post['status']=='2')
                    {
                        $post['comment']=lang('tickets.msg_casestatusclosed',[$post['comment']]);
                    }
                    
                    if (array_key_exists('comment', $post['_notify']['data']))
                    {
                        $post['_notify']['data']['comment']=$post['comment'];
                    }
                }
                $this->addMovementHistory('tickets_casefollow', $post['_notify']['data']['contact'], null, $post['tiid'],$post['comment'] , 'tickets');
                if (array_key_exists('_notify', $post) && is_array($post['_notify']) && Arr::KeysExists(['data','template','to'], $post['_notify']))
                {
                    $this->sendNotification($post['_notify']['template'], $post['_notify']['data'],$post['_notify']['to']);
                }
                return redirect()->to($refurl);
            }
        }else
        if ($type=='template')
        {
            if (Arr::KeysExists(['titid','title'], $post) && !is_numeric($post['titid']))
            {
                $post['name']=mb_url_title($post['title'],'_');
            }
        }
        endof_function:
        return parent::save($type, $post);
    }
    
    function _after_save($type, $post, $refurl, $refurl_ok): bool 
    {
        if (array_key_exists('_notify', $post) && is_array($post['_notify']) && Arr::KeysExists(['data','template'], $post['_notify']))
        {
            if (array_key_exists('tiid', $post))
            {
                $post['_notify']['data']['ticket_id']=$post['tiid'];
                $this->addMovementHistory('tickets_casefollow', $post['_notify']['data']['contact'], null, $post['_notify']['data']['ticket_id'], $post['_notify']['data']['comment'], 'tickets');
            } else 
            {
               $post['_notify']['data']['ticket_id']=$this->model_Ticket->getInsertID(); 
               $this->addMovementHistory('tickets_casefollow', $post['_notify']['data']['contact'], null, $post['_notify']['data']['ticket_id'], lang('tickets.casefollow_created', $post['_notify']['data']), 'tickets');
            }
            $this->sendNotification($post['_notify']['template'], $post['_notify']['data'],$post['_notify']['to']);
        }
        return TRUE;
    }
    
    
    
    function settings($tab,$record)
    {
        $settings=$this->model_Settings->get('tickets.*',FALSE,'*');
        $view=new Pages\FormView($this);
        if ($tab=='cfg')
        {
            $view->addEmailField('tickets.settings_itsupportemail', 'settings[tickets_itsupportemail]', $settings['tickets_itsupportemail']['value'], []);
            $view->addCustomElementsListField('tickets.settings_deftargetgroups', 'settings[tickets_deftargetgroups]', $settings['tickets_deftargetgroups']['value'], 
                    [
                        'input_type'=>\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()->setArgs(
                                 [
                                     'name'=>'targetgrp_input',
                                     'id'=>'id_settings_tickets_deftargetgroups_input',
                                     'options'=>$this->model_Ticket->getTargetGroups()
                                 ])
                    ]);
            
            $view->addDropDownField('tickets.settings_newtickettpl', 'settings[tickets_newtickettpl]',$this->model_Documents_Report->getTemplatesForForm(), $settings['tickets_newtickettpl']['value'], ['advanced'=>TRUE]);
            $view->addDropDownField('tickets.settings_ticketupdatetpl', 'settings[tickets_ticketupdatetpl]',$this->model_Documents_Report->getTemplatesForForm(), $settings['tickets_ticketupdatetpl']['value'], ['advanced'=>TRUE]);
            $view->addInputField('tickets.settings_supportteamname', 'settings[tickets_supportteamname]', $settings['tickets_supportteamname']['value'], []);
            $view->addCustomElementsListField('tickets.settings_ticketnonpendingstatus', 'settings[tickets_ticketnonpendingstatus]', $settings['tickets_ticketnonpendingstatus']['value'], 
                    [
                        'input_type'=>\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()->setArgs(
                                 [
                                     'name'=>'ticketnonpendingstatus_input',
                                     'id'=>'id_settings_tickets_ticketnonpendingstatus_input',
                                     'options'=>$this->model_Ticket->getTicketsStatuses(-1,FALSE)
                                 ])
                    ]);
            $view->addDropDownField('tickets.settings_ticketreopenstatus', 'settings[tickets_ticketreopenstatus]',$this->model_Ticket->getTicketsStatuses(-1,FALSE), $settings['tickets_ticketreopenstatus']['value']);
        }
        return view('System/form_fields',$view->getViewData());
    }
      
}




