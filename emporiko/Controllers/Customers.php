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

class Customers extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'accounts'=>     AccessLevel::view,
        'account'=>      AccessLevel::edit,
        'terms'=>        AccessLevel::edit,
        'term'=>         AccessLevel::edit,
        'profile'=>      AccessLevel::modify,
        'account_view'=> AccessLevel::edit,
        'index'=>        AccessLevel::edit,
        'save'=>         AccessLevel::modify,
    ];

    /**
     * Access module class name if different that current controller
     * @var String
     */
    protected $access_controller;

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
        'cust'=>'Customers/Customer',
        'contacts'=>'System/Contact',
        'terms'=>'Customers/CustomerTerm',
    ];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actuall function name)
     */
    public $remaps = 
    [
        'index'=>'accounts',
        
    ];
    
    /**
     * Array with available menu items (keys as function names and values as description)
     * @var Array
     */
    public $availablemenuitems = [];
    
    
    function terms($record=null)
    {
        if ($record!=null)
        {
            return $this->term($record);
        }
        //
        $this->setTableView()
                    ->setData('terms',null,TRUE,null,[])
                    ->setPageTitle('customers.terms')
                    //Fiilters settings
                    ->addFilters('contacts')
                    ->addFilterField('name %')
                    //Table Columns settings
                    ->addColumn('customers.terms_name','name',TRUE)
                    ->addColumn('customers.terms_note','note',FALSE,[],'len:50')
                    //->addColumn('customers.term_','ct_phone',FALSE)
                    
                    ->addBreadcrumb('customers.mainmenu',url($this))
                    ->addBreadcrumb('customers.terms',current_url())
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','terms/-id-',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addDeleteButton()
                    ->addNewButton('terms/new');
        
        return $this->view->render();
    }
    
    function term($record)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Terms->find($record);              
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
            $record=$this->model_Terms->getNewRecordData(TRUE);
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView()
                ->setFormTitle('customers.terms_edit')
		->setPageTitle('customers.terms_edit')
		->setFormAction($this,'save',['terms'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Terms->primaryKey=>$record[$this->model_Terms->primaryKey]
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('customers.mainmenu',url($this))
                ->addBreadcrumb('customers.terms',$this->getRefUrl())
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('terms',$record,'customers.terms_-key-')
                ->addSelect2();
            return $this->view->render();
    }
    
    
    
    
    function accounts($record=null,$mode='edit')
    {
        if ($record!=null)
        {
            if ($record=='view')
            {
                $mode='view';
                $record=1;
            }
            if ($mode=='view')
            {
                return $this->account_view($record);
            }
            
            return $this->account($record,$mode);
        }
        $columns=$this->model_Settings->get('customers.cust_accounts_columns',TRUE);
        $this->setTableView()
                    ->setData('cust',null,TRUE,null,[])
                    ->setPageTitle('customers.accounts')
                    //Fiilters settings
                    ->addFilters('accounts')
                    ->addFilterField('name %')
                    ->addFilterField('|| code %')
                    ->addFilterField('|| terms_price %');
        
        //Table Columns settings
        $this->view=$this->model_Cust->getAvalColumns($columns,$this->view);
                    
        //Breadcrumb settings            
        $this->view->addBreadcrumb('customers.mainmenu',url($this))
                    //Table Riows buttons
                    ->addEditButton('customers.accounts_viewbtn','accounts/-id-/view',null,'btn-info edtBtn','fas fa-info-circle',[])
                    //Table main buttons
                    ->addEnableButton()
                    ->addDisableButton()
                    ->addDeleteButton()
                    ->addNewButton('accounts/new')
                    ->addColumnsEditButton($columns,$this->model_Cust->getAvalColumns([],null,TRUE),'customers.cust_accounts_columns')
                    ->addUploadButton('customers','id_button_upload','customers.customers_btn_upload')
                    ->addModuleSettingsButton()
                    ->setNoDataMessage('customers.msg_cust_no_data'); 
        
        return $this->view->render();
    }
    
    private function account_view($record)
    {
        //$this->model_Emails_Email->sendEmailsFromQueue();exit;
        $refurl=$this->getRefUrl(url($this,'accounts',[],[],TRUE));
        $isnew=FALSE;
        $record=$this->model_Cust->filtered(['code'=>$record,'|| cid'=>$record])->first();  
        if (!is_array($record))
        {
            error:
            return redirect()->to($refurl)->with('error',$this->createMessage('customers.error_invalidaccnr','danger'));
        }
        
        if ($record==null)
        {
            goto error;
        } else 
        {
            $record['contacts']=$this->model_Contacts->getByAcc($record['code']);
            $record['emails']=$this->model_Contacts->getByAcc($record['code'],'ct_email','ct_name');
            $record['stat_orders']=$this->model_Orders_Order->getLiveItemsForCustomer($record['code']);
        }
        $records=$this->model_Cust->getPrevNextID($record['cid']);

        /*Fetching module settings*/
        $settings=$this->model_Cust->getSettings();
        
        $buttons=[];
        $buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-th-list', 'danger',url($this), 'customers.accounts_listbtn');
        if ($this->hasAccess(AccessLevel::settings))
        {
            $buttons[]= Pages\HtmlItems\ToolbarButton::createModuleSettingsButton($this);
        }
        $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fa fa-plus', 'dark ml-1',url($this,'accounts',['new'],['refurl'=> current_url(FALSE,TRUE)]), 'system.buttons.new');
        
        $buttons[]= Pages\HtmlItems\DropDownField::create()->setClass('form-control form-control-sm')
                    ->setOptions($this->model_Cust->getCustomersForDropDown('cid',null,TRUE))
                    ->setAsAdvanced()
                    ->setName('customer_filter')
                    ->setID('id_customer_filter_field')
                    ->setValue($record['cid']);
        $buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-chevron-circle-left', 'dark',url($this,'accounts',[$records['prev'],'view'],['refurl'=>base64url_encode($refurl)]), 'customers.accounts_prevbtn');
        $buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-chevron-circle-right', 'dark', url($this,'accounts',[$records['next'],'view'],['refurl'=>base64url_encode($refurl)]), 'customers.accounts_nextbtn');
        $buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-print', 'secondary ml-1', 'customers.accounts_prntbtn', 'accounts_btn_print');
        $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fa fa-edit', 'primary ml-2',url($this,'accounts',[$record['cid']],['refurl'=> current_url(FALSE,TRUE)]), 'customers.accounts_editbtn');
        if (intval($record['enabled']) == 1)
        {
           $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fa fa-eye-slash', 'danger',url($this,'enablesingle',['cust',$record['cid'],'0'],['refurl'=> current_url(FALSE,TRUE)]), 'customers.btn_disable'); 
        } else 
        {
            $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fa fa-eye', 'success',url($this,'enablesingle',['cust',$record['cid'],'1'],['refurl'=> current_url(FALSE,TRUE)]), 'customers.btn_enable');
        }
        $buttons['emails']=Pages\HtmlItems\ToolbarButton::createDataUrlButton('far fa-envelope', 'info ml-2',url('Emails','compose',['customer',$record['cid']],['track'=>$record['code'],'refurl'=>current_url(FALSE,TRUE)]), 'customers.accounts_emailbtn');
        
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        $filesform=loadModule('Documents','filesform',['customer_'.$record['cid'],[],[['doc_name','doc_desc']],'id_customerinfo_filestable']);
        if ($edit_acc)
        {
            if ($filesform==null)
            {
                $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('far fa-file', 'warning',url('Documents','files',['customer_'.$record['cid'],'new'],['refurl'=> current_url(FALSE,TRUE)]),'documents.btn_addnew');
            }
            if ($this->model_Settings->get('products.products_enablepricefiles',TRUE))
            {
                $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-car-battery', 'dark', url('Products','pricefiles',['sendtoacc'],['acc'=>$record['cid'],'refurl'=> current_url(FALSE,TRUE)]), 'products.btn_sendpricefile', null, []);
            }
        }
        /*New Case button*/
        $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-exclamation-triangle', 'outline-danger ml-2',url('Tickets','cases',['newlist'],['acc'=>$record['cid'],'refurl'=> current_url(FALSE,TRUE)]),'tickets.btn_addnew');
        
        /*New Oport button*/
        $buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-hand-holding-usd', 'outline-dark ml-2',url('Sales','opportunities',['new'],['customer'=>$record['cid'],'refurl'=> current_url(FALSE,TRUE)]),'orders.opportunities_btn_new');
        
        $this->setFormView('Customers/view_cust')
                ->setFormTitle('customers.accounts_editform')
		->setPageTitle('customers.accounts_editform')
		->setFormAction($this,'save',['cust'],['refurl'=>current_url(FALSE,TRUE)])
                ->parseArrayFields()
		->setFormArgs([],[],[])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('customers.mainmenu',url($this))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],'/')
			
		->addData('record',$record)
                ->addPrintLibrary()
                ->addButtonsToolBar('toolbar',$buttons,[])
                ->addData('url_ct_email',url('Emails','compose',['contact','-email-'],['track'=>$record['code'],'refurl'=>current_url(FALSE,TRUE)]))
                
                ->addData('url_email_new',$buttons['emails']->getArgs('data-url'))
                ->addData('url_email_reply',url('Emails','compose',['reply','-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                ->addData('url_email_unread',url('Emails','messages',[],['cust'=>$record['cid'],'refurl'=>current_url(FALSE,TRUE)]))
                ->addData('url_show_orders',url('Orders','customers',[],['customer'=>$record['code'],'refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_show_oport',url('Orders','opportunities',[],['customer'=>$record['code'],'refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_show_quotes',url('Orders','quotes',[],['customer'=>$record['code'],'refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_newoport',url('Orders','opportunities',['new'],['customer'=>$record['cid'],'refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_newquote',url('Orders','quotes',['new'],['customer'=>$record['cid'],'refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_neworders',url('Orders','sales',['new'],['customer'=>$record['cid'],'refurl'=> current_url(FALSE,TRUE)]))
                ->addData('edit_acc',$edit_acc)
                ->addData('_record_call_form', loadModule('Connections','recordcall',[['callernumber'=>'','call_target'=>$record['code'],'call_modalinit'=>TRUE]]))
                ->addData('url_cts_new',url('Connections','contacts',['new',$record['code']],['track'=>$record['code'],'refurl'=>current_url(FALSE,TRUE)]))
               
                ->addData('unread_emails',$this->model_Cust->getUnseenEmailsQtyForCustomer($record['code']))
                ->addData('url_view',url($this,'accounts',['-id-','view']))
                ->addData('tickets',$this->model_Customers_Ticket->getLiveTickets(['tck_account'=>$record['code']]))
                ->addData('filesform', $filesform)
                ->addData('movements', loadModule('Home','movementsTable',[$record['code'],['addlog'=>['button'=>TRUE,'ref'=>$record['code']]]]))
                ->addData('settings',$settings)
                ->addSelect2()
                ->addEditorScript();;
            if ($edit_acc)
            {
                $this->view->addData('url_cts_edit',url('Connections','contact',['-id-'],['refurl'=> current_url(FALSE,TRUE)]))
                           ->addData('url_cts_del',url('Connections','deletesingle',['contacts','-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                           ->addData('url_cts_email',url('Emails','compose',['contact','-id-'],['track'=>$record['code'],'refurl'=>current_url(FALSE,TRUE)]));
            }
            return $this->view->render();
        
    }
    
    function account($record=null,$mode='edit')
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Cust->find($record);              
        }else
        {
            $record=null;
        }
        
        $edit_acc=$this->hasAccess(AccessLevel::edit); 
        $record=$this->getFlashData('_postdata',$record);
        if ($record==null || $record=='new')
        {
            if (!$this->hasAccess(AccessLevel::create))
            {
                return $this->getAccessError(true);
            }
            $isnew=TRUE;
            $record=$this->model_Cust->getNewRecordData(TRUE);
        } else 
        {
            if (is_string($record['notes']))
            {
                $record['notes']= str_replace(['<br/>','<br>'],PHP_EOL, $record['notes']);
            }
        }
        $record['edit_acc']=$edit_acc;
        $record['_readonly']=$mode=='view';
        $this->setFormView('Customers/edit_cust')
                ->setFormTitle('customers.accounts_editform')
		->setPageTitle('customers.accounts_editform')
		->setFormAction($this,'save',['cust'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Cust->primaryKey=>$record[$this->model_Cust->primaryKey]
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('customers.mainmenu',url($this))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],$isnew ? '/' : $this->getRefUrl())
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('cust',$record,'customers.accounts_-key-')
                ->setTab('addrr','customers.tab_addr')
                ->setTab('empl','customers.tab_emp')
                ->setTab('others','customers.tab_others');
        if (!$isnew)
        {
            $this->view->addBreadcrumb('system.buttons.edit',current_url());
        }
       return $this->view->render();
    }
    
    function profile($record=null)
    {
        $refurl=$this->getRefUrl(null);
        if (is_numeric($record))
        {
            $record=$this->model_Cust->find($record);              
        }else
        {
            $record=null;
        }
        
        if ($record==null)
        {
            $record=$this->model_Cust->getProfileData(['cid'=>loged_user('customer')])->first();
        }
        
        if ($record==null)
        {
            return $this->getAccessError();
        }
        $record=$this->getFlashData('_postdata',$record);
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        if ($record['edit_acc'])
        {
            return redirect()->to(url('Users','profile'));
        }
       
        $this->setFormView('Customers/profile')
                ->setFormTitle('system.auth.profile_myaccdetails')
		->setPageTitle('system.auth.profile_myaccdetails')
		->setFormAction($this,'save',['profile'],['refurl'=>current_url(FALSE,TRUE)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Cust->primaryKey=>$record[$this->model_Cust->primaryKey],
                            base64_encode('usr_email_old')=> base64_encode($record['usr_email']),
                            base64_encode('address_ship_old')=> base64_encode($record['address_ship']),
                            base64_encode('address_pay_old')=> base64_encode($record['address_pay']),
                            base64_encode('name_old')=> base64_encode($record['name'])
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
				
		->addBreadcrumb('system.auth.profile_myaccbread', current_url())
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel($this->model_Cust->getFieldsForProfile($record,$this->model_Settings->get('customers.profile_fields')),$record,'customers.accounts_-key-')
                ->setTab('addrr','customers.tab_addr')
                ->setTab('acc','customers.profile_tablogin')
                ->addSelect2('[searchlist="1"]');
            return $this->view->render();
        
    }
        
    function save($type, $post = null) 
    {
        $post=$this->request->getPost();
        if ($type!='profile' && !$this->hasAccess(AccessLevel::edit))
        {
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.errors.no_acces','danger'));
        }
        if ($type=='cust')
        {
           if (array_key_exists('notes', $post))
           {
                if (is_array($post['notes']))
                {
                    $post['notes'][0]= base64_decode($post['notes'][0]);
                    $post['notes']=implode('<br/>',$post['notes']);
                }
                $post['notes']= preg_replace("/\r\n|\r|\n/", '<br/>', $post['notes']);
            }
        }else
        if ($type=='profile')
        {
           $refulr=$this->getRefUrl();
           $msg=[];
           if (Arr::KeysExists(['password_confirm','password','usr_username'], $post) && strlen($post['password']) > 0)
           {
               if (!Str::contains($refulr,'tab=acc'))
               {
                   $refulr=$refulr.(Str::contains($refulr,'?') ? '&' : '?').'tab=acc';
               }
               if (!Str::isValidPassword($post['password']))
               {
                   return redirect()->to($refulr)->with('error',$this->createMessage('customers.profile_error_passlen','danger'))->with('_postdata',$post); 
               }
               
               if (strcmp($post['password'], $post['password_confirm']) !== 0)
               {
                    return redirect()->to($refulr)->with('error',$this->createMessage('customers.profile_error_passmatch','danger'))->with('_postdata',$post); 
               }
               
               if (!$this->model_Auth_User->setUserPassword($post['usr_username'],$post['password']))
               {
                   return redirect()->to($refulr)->with('error',$this->createMessage('customers.profile_error_passchange','danger'))->with('_postdata',$post);
               }
               $msg[]=$this->createMessage('customers.profile_msg_passchanged','success');
           }
           $save=FALSE;
           
           if ((Arr::KeysExists([base64_encode('usr_email_old'),'usr_email'], $post) && strcmp($post[base64_encode('usr_email_old')], $post['usr_email']) !== 0)||
               (Arr::KeysExists([base64_encode('address_ship_old'),'address_ship'], $post) && strcmp($post[base64_encode('address_ship_old')], $post['address_ship']) !== 0)||
               (Arr::KeysExists([base64_encode('address_pay_old'),'address_pay'], $post) && strcmp($post[base64_encode('address_pay_old')], $post['address_pay']) !== 0) ||
               (Arr::KeysExists([base64_encode('name_old'),'name'], $post) && strcmp($post[base64_encode('name_old')], $post['name']) !== 0))
           {
               $save=TRUE;
           }
           
           if ($save)
           {
               $this->model_Cust->requestDetailsChange($post);exit;
               $msg[]=$this->createMessage('customers.profile_msg_changereq','success');
           }
           
           return redirect()->to($refulr)->with('error',implode('',$msg));
        }else
        if ($type=='contacts')
        {
            if (array_key_exists('ct_account', $post) && is_array($post['ct_account']))
            {
                $post['ct_account']=implode(';',$post['ct_account']);
            }
        }
        
        return parent::save($type, $post);
    }
    
    function settings($tab,$record)
    {
        $settings=$this->model_Settings->get('customers.*',FALSE,'*');
        $view=new Pages\FormView($this);
        if ($tab=='cfg')
        {
            $numbers= array_combine([0,5,10,25,50], [lang('customers.settings_viewcases_0'),5,10,25,50]);
            $view->addDropDownField('customers.settings_custlinkedfield', 'settings[customers_custlinkedfield]', $this->model_Customers_Customer->getFieldsForLink(), $settings['customers_custlinkedfield']['value'], []);  
            $view->addYesNoField('customers.settings_viewcases', $settings['customers_viewcases']['value'],'settings[customers_viewcases]');
            $view->addYesNoField('customers.settings_viewemails', $settings['customers_viewemails']['value'],'settings[customers_viewemails]');
            $view->addYesNoField('customers.settings_viewsales', $settings['customers_viewsales']['value'],'settings[customers_viewsales]');
        }
        return view('System/form_fields',$view->getViewData());
    }   
     
    
    function enablesingle($model, $id, $value, $field = null) 
    {
        if ($model=='cust')
        {
            $cust=$this->model_Cust->find($id);
            if (is_array($cust) && intval($value)==1)
            {
                $this->triggerRule('customers_change_status_live', $cust);
            } else 
            if (is_array($cust))
            {
                $this->triggerRule('customers_change_status_dead', $cust);
            }
        }
        return parent::enablesingle($model, $id, $value, $field);
    }
    
    function sendNotification(string $template, array $data, $to = null) 
    {
        if ($to=='cust')
        {
            $to=null;
        }
        return parent::sendNotification($template, $data, $to);
    }
    
    /**
     * Returns menu items form/array with items names
     * 
     * @param  mixed $value
     * @param  bool $justItems
     * @return mixed
     */
    function getMenuItemsData($value = null, $justItems = FALSE) 
    {
        //$this->availablemenuitems=array_combine($this->availablemenuitems, lang('workers.menu_action_list'));
        return parent::getMenuItemsData($value, $justItems);
    }
        
}