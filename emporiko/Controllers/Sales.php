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

class Sales extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'index'=>           AccessLevel::view,
        'invoice_form'=>    AccessLevel::create,
        'update_invoice'=>  AccessLevel::view,
        'customers'=>       AccessLevel::view,
        'customer'=>        AccessLevel::view,
        'download'=>        AccessLevel::view,
        'update'=>          AccessLevel::view,
        'upload'=>          AccessLevel::view,
        'lines'=>           AccessLevel::view,
        'getDashboardTile'=>AccessLevel::view,
        'qoutes'=>          AccessLevel::view,
        'qoute'=>           AccessLevel::view,
        'enablesingle'=>    AccessLevel::view,
        'save'=>            AccessLevel::view,
        'api'=>             AccessLevel::view,
        'opportunities'=>   AccessLevel::edit,
    ];


    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = [];

    
    /**
     * Array with function names and linked models names
     */
    public $assocModels = 
    [
        'orders'=>'Orders/Order',
        'lines'=>'Orders/OrderLine',
    ];
    
    /**
     * Array with function names which are enabled when accessing from mobile device
     * @var Array
     */
    protected $mobilenebaled=['all'];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actual function name)
     */
    public $remaps = 
    [
        'index'=>        'all',
        'deleteorder'=>  ['deletesingle',['order','$1']],
        'cancelquote'=>  ['enablesingle',['quote','$1',0]],
        'cancelorder'=>  ['enablesingle',['order','$1',0]],
    ];
    
    
    function all($record=null)
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        $filters=
        [
            'enabled'=>1,
            'ord_done'=>0,
        ];
        
        $statuses=$this->model_Orders->getOportStatuses();
        $sources=$this->model_Orders->getOportSources();
        $settings=$this->model_Orders->getOrdersSettings();
        if ($record!=null)
        {
            $id='new';
            $mode=$this->request->getGet('mode');
            if ($mode==null || (!is_numeric($record) && $record!='new'))
            {
                error_id:
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_invalidrecordid','danger'));  
            }
            
            if (intval($mode)==0)
            {
                return $this->opportunities($record);
            }else
            if (intval($mode)==1)
            {
                return $this->quotes($record,'view');
            }else
            if (intval($mode)==2)
            {
                return $this->orders($record);
            }else
            {
                goto error_id;
            }
        }
        
        $this->setTableView('Orders/sales_table')
                    ->setData('orders',null,TRUE,null,$filters)
                    ->setPageTitle('orders.sales_title')
                    //Fiilters settings
                    ->addFilters('all')
                    ->addFilterField('ord_ref %')
                    ->addFilterField('ord_done',1,'DONE')
                    //Table Columns settings
                    ->addColumn('orders.ord_type','ord_type',TRUE,$this->model_Orders->getTypes(TRUE,TRUE,TRUE))
                    ->addColumn('orders.ord_ref_col','ord_ref',TRUE)
                    //Breadcrumb settings
                    ->addBreadcrumb('orders.sales',url($this))
                    //Table Riows buttons
                    ->addEditButton('products.brands_edit','-ord_type_full-',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    
                    ->addData('type_images',$this->model_Orders->getTypes(TRUE,FALSE));
        $new_btn_url=
        [
            lang('orders.opportunities_btn_new')=>url($this,'all',['new'],['mode'=>'opportunities','refurl'=>current_url(FALSE,TRUE)]),
            lang('orders.quotes_btn_new')=>url($this,'all',['new'],['mode'=>'quotes','refurl'=>current_url(FALSE,TRUE)]),
            lang('orders.orders_btn_new')=>url($this,'all',['new'],['mode'=>'orders','refurl'=>current_url(FALSE,TRUE)]),  
        ];
        if (!$this->isMobile())
        {
            $this->view->addNewButton($new_btn_url);
        }else
        {
            $new_btn_url_tool= array_keys($new_btn_url);
            foreach($new_btn_url as $tooltip=>$btn)
            {
                $this->view->addNewButton($btn, AccessLevel::create,['id'=>'id_btn_new_'. base64_encode($tooltip),'tooltip'=>$tooltip]);
            }
            
        }
        return $this->view->render();
        
    }
    
    function opportunities($record=null,string $mode='view')
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        $filters=
        [
            'ord_type'=>0,
            'enabled'=>1,
            'ord_done'=>0,
        ];
        
        $statuses=$this->model_Orders->getOportStatuses();
        $sources=$this->model_Orders->getOportSources();
        $settings=$this->model_Orders->getOrdersSettings();
        if ($record!=null)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=TRUE;
             
            if ($record=='new')
            {
                $record=$this->model_Orders->getNewRecordData(TRUE);
                $record['ord_cus_curr']='';
                if ($this->request->getGet('customer')!=null)
                {
                   $record['ord_cusacc']=$this->model_Customers_Customer->find($this->request->getGet('customer'));
                   if (is_array($record['ord_cusacc']) && array_key_exists('code', $record['ord_cusacc']))
                   {
                       $record['ord_cusacc']=$record['ord_cusacc']['code'];
                       $record['ord_source']='cust';
                   }else
                   {
                       $record['ord_cusacc']=null;
                   }
                }
                $view='Orders/Oport/oport_edit';
                if (is_numeric($this->request->getGet('email')))
                {
                    $record['_email']=$this->model_Emails_Email->filtered(['emid'=>$this->request->getGet('email')])->first();
                    if (is_array($record['_email']))
                    {
                        $record['ord_cusacc']=$record['_email']['mail_from_acc'];
                        $record['ord_desc']=lang('orders.opportunities_msg_convertemail_desc',[$record['_email']['mail_from_name'],$record['_email']['mail_from']]);
                        $record['ord_source']='email';
                        $record['ord_refcus']=$record['_email']['mail_subject'];
                        $record['ord_ref']=$record['_email']['mail_from_acc']. formatDate();
                        $record['ord_source_ref']=$record['_email']['emid'];
                        $record['_email']['mail_attachements']=[];
                        $record['_email']['mail_body']= base64_decode($record['_email']['mail_body']);
                    }
                }
            }else
            {
                $record=$this->model_Orders->filtered(['ord_ref'=>$record, '|| ordid'=>$record,'|| ord_refcus'=>$record])->first();
                $isnew=FALSE;
                $view='Orders/Oport/oport_lines';
                if (array_key_exists('ord_source_ref', $record) && $record['ord_source_ref']=='portal')
                {
                    $this->model_Lines->validateData($record);
                }
            }
             
            $record=$this->getFlashData('_postdata',$record);
        
            if (!is_array($record))
            {
                record_error:
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.opportunities_error_invalidrecordid','danger'));
            }else
            if($mode=='convert')
            {
                if ($isnew)
                {
                    goto record_error;
                }
                if (intval($settings['orders_oport_convertsendtoapi'])==1)
                {
                    return $this->sendtoapi($record, $this->model_Lines->getForApi($record['ord_ref'],'ol_cusprice'),TRUE);
                }else
                {
                    if ($this->model_Orders->convertOportToQuote($record))
                    {
                        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage(lang('orders.opportunities_msg_convert_ok',[url_tag(url($this,'quotes',[$this->model_Orders->getLastID()]), 'system.buttons.edit')]),'success'));
                    }
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.opportunities_error_convert','danger'));
                }
            }else
            if (!$isnew)
            {
               $record['parts']=$this->model_Lines->getForPicker($record['ord_ref']);
               
               if (array_key_exists('ord_done', $record) && intval($record['ord_done'])==0)
               {
                   $record['can_convert']=TRUE;
               }
            }
            
            $this->setFormView($view)
                 ->setFormTitle('orders.opportunities_editrecord')
                 ->setPageTitle('orders.opportunities_editrecord')
                 
                 ->parseArrayFields()
                 ->setFormArgs(['autocomplete'=>'off','id'=>'id'],
                        [
                            $this->model_Orders->primaryKey=>$record[$this->model_Orders->primaryKey],
                            'ord_source'=>$record['ord_source'],
                            'enabled'=>1
                        ]
                  ,['class'=>'col-12'])
		 ->setCustomViewEnable(FALSE)
		 ->setFormCancelUrl($refurl)
		
                 ->addBreadcrumb('orders.sales',url($this))    
                 ->addBreadcrumb('orders.opportunities_list',url($this,'opportunities'))
                 ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ord_ref'],'/')
			
		 ->addData('record',$record)
                 ->addData('statuses',$statuses)
                 ->addData('sources',$sources)
                 ->addData('showourcost',$edit_acc && intval($settings['orders_oport_showourcost'])==1)
                 ->addData('edit_acc',$edit_acc)
                 ->addData('converturl', url($this,'opportunities',[$record['ordid'],'convert'],['refurl'=>base64url_encode($this->getRefUrl())]))
                 ->setTab('general','system.general.tab_info')
                 ->setTab('tab_parts','orders.tab_parts')
                 ->addFieldsFromModel($this->model_Orders->getFieldForOportForm($record),$record,'orders.-key-')
                 //->addSelect2('.select2')
                 ->addSimpleValidation();
            if (!array_key_exists('ord_done', $record) || (array_key_exists('ord_done', $record) && ($record['ord_done']=='0' || $record['ord_done']==0)))
            {
                $this->view->setFormAction($this,'save',['opport'],['refurl'=>base64url_encode($refurl)]);
            }
            
            if (!$isnew)
            {
                $toolbarButtons=[];
                $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createBackButton($this->getRefUrl());
                $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-file-excel', 'warning ml-3 mr-3', 'orders.btn_downxlsx', null, url($this,'download',[$record['ordid'],'oport'],['refurl'=> current_url(FALSE,TRUE)]), ['data-noloader'=>TRUE]);
                if (array_key_exists('ord_status', $record) && $record['ord_status']=='prop')
                {
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-handshake', 'info mr-1', 'orders.opportunities_stage_neg_btn', null, null, ['data-conf'=>url($this,'enablesingle',['oport',$record['ordid'],'1'],['refurl'=>current_url(FALSE,TRUE)]),'data-mode'=>'neg']);
                }
                if (intval($record['ord_done'])==0)
                {
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-retweet', 'success', 'orders.opportunities_msg_convert', null, null, ['data-conf'=>url($this,'opportunities',[$record['ordid'],'convert'],['refurl'=> base64url_encode($this->getRefUrl())]),'data-mode'=>'win']);
                    $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createModalStarter('oport_cancelmodal', 'fas fa-handshake-slash', 'danger ml-1', 'orders.opportunities_msg_cancel', null,[]);
                    $this->view->addData('urlcancel',url($this,'enablesingle',['oport',$record['ordid'],0],['refurl'=> base64url_encode($this->getRefUrl())]));
                }
                $this->view->addMenuBar($toolbarButtons,['background'=>'white'])
                           ->addDataUrlScript();
            }
            
            return $this->view->render();    
        }
        
        if ($this->request->getGet('filtered')!=null)
        {
            unset($filters['ord_done']);
        }
        
        $this->getOrdersData('opportunities','Orders/Oport/oport_index',$filters);
        
        
        $this->view->addEditButton('system.buttons.show_details','opportunities',null,'btn-info','fas fa-info-circle',[], AccessLevel::view)
                   ->addEditButton('orders.opportunities_msg_convert','opportunities/-id-/convert',null,'btn-success','fas fa-retweet',['data-convert'=>'-ord_done-'], AccessLevel::edit)
                   ->addDeleteButton()
                   ->addNewButton('opportunities/new')
                   ->addColumn('orders.opportunities_ref','ord_ref',TRUE,[],null,'ord_desc')
                   ->addModuleSettingsButton('',null,['tabName'=>'oport']);
        
        
        if ($edit_acc)
        {
            $this->view->addColumn('orders.ord_cusacc','ord_cusacc',TRUE,[],null,'ord_cus_name')
                       ->addFixedFilterListDivider('orders.ord_status');
            foreach($statuses as $key=>$value)
            {
                $this->view->addFilterField('ord_status',$key,$value);
            }
            $this->view->addFixedFilterListDivider('orders.opportunities_source');
            foreach($sources as $key=>$value)
            {
                $this->view->addFilterField('ord_source',$key,$value);
            }
        }
        $this->view->addColumn('orders.ord_status','ord_status',TRUE,$statuses)
                   ->addColumn('orders.opportunities_source','ord_source',TRUE,$sources)
                   ->addColumn('orders.opportunities_value','ord_cus_value',TRUE,[],'money:#ord_cus_curr')//ord_cus_curr far fa-money-bill-alt
                   ->addColumn('orders.ord_addon','ord_addon',TRUE,[],'date')
                ;
        return $this->view->render();
    }
    
    function quotes($record=null,string $mode='view')
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        $filters=
        [
            'ord_type'=>1,
            'enabled'=>1,
            'ord_done'=>0,
        ];
        
        if ($record!=null)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=TRUE;
            if ($record=='new')
            {
                if ($mode=='convert')
                {
                    if (!is_numeric($this->request->getGet('email')))
                    {
                        return redirect()->to($refurl)->with('error',$this->createMessage('orders.quotes_error_convertemailerror','danger'));
                    }
                    $mode=loadModule('Autopart', 'getQuotesFromEmails',[TRUE,['emid'=>$this->request->getGet('email')]]);
                    
                    if ($mode)
                    {
                        return redirect()->to($refurl)->with('error',$this->createMessage('orders.quotes_error_convertemailerror','success'));
                    }
                    $record=$this->model_Orders->getNewRecordData(TRUE);
                    $record['_email']=$this->model_Emails_Email->filtered(['emid'=>$this->request->getGet('email')])->first();
                    
                    if (is_array($record['_email']))
                    {
                        if (array_key_exists('mail_from_acc', $record['_email']))
                        {
                            $record['ord_cusacc']=$record['_email']['mail_from_acc'];
                            $record['ord_ref']=$record['_email']['mail_from_acc']. formatDate();
                        }
                        if (array_key_exists('mail_from_name', $record['_email']))
                        {
                            $record['ord_desc']=lang('orders.opportunities_msg_convertemail_desc',[$record['_email']['mail_from_name'],$record['_email']['mail_from']]);
                        }
                        $record['ord_source']='email';
                        $record['ord_refcus']=$record['_email']['mail_subject'];                       
                        $record['ord_source_ref']=$record['_email']['emid'];
                        $record['_email']['mail_body']= base64_decode($record['_email']['mail_body']);
                        if (strlen($record['_email']['mail_attachements']) > 0)
                        {
                            $record['ord_prdsource']=json_decode($record['_email']['mail_attachements'],TRUE);
                            
                            if (is_array($record['ord_prdsource']))
                            {
                                $record['_email']['mail_attachements']=$record['ord_prdsource'];
                                foreach ($record['ord_prdsource'] as $key=>$file)
                                {
                                    if (is_array($file) && Arr::KeysExists(['name','id'], $file))
                                    {
                                        $record['ord_prdsource'][base64_encode(json_encode([$record['_email']['emid'],$key,$file['name']]))]=$file['name'];
                                    }
                                    unset($record['ord_prdsource'][$key]);
                                }
                                $record['ord_prdsource_list']=$record['ord_prdsource'];
                            }
                            $record['ord_prdsource']='';
                        }     
                    } else 
                    {
                        $record['ord_prdsource']='lines';
                    }        
                }else
                {
                    $record=$this->model_Orders->getNewRecordData(TRUE);
                    if ($this->request->getGet('customer'))
                    {
                        $record['ord_cusacc']=$this->model_Customers_Customer->find($this->request->getGet('customer'));
                        if (is_array($record['ord_cusacc']) && array_key_exists('code', $record['ord_cusacc']))
                        {
                            $record['ord_cusacc']=$record['ord_cusacc']['code'];
                            $record['ord_source']='cust';
                        }else
                        {
                            $record['ord_cusacc']=null;
                        }
                    }
                    $record['enabled']=1;
                    $record['ord_prdsource']='lines';
                }
                $record['ord_cus_curr']='';
                $record['ord_type']=1;
            }else
            {
                $record=$this->model_Orders->filtered(['ord_ref'=>$record, '|| ordid'=>$record,'|| ord_refcus'=>$record])->first();
                $isnew=FALSE;
            }
            $record=$this->getFlashData('_postdata',$record);
            if (!is_array($record))
            {
                record_error:
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.opportunities_error_invalidrecordid','danger'));
            }else
            if($mode=='convert')
            {
                if ($isnew)
                {
                    goto record_error;
                }

                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.opportunities_error_convert','danger'));
            }else
            if (!$isnew && $record['ord_prdsource']=='lines')
            {
               $record['parts']=$this->model_Lines->getForPicker($record['ord_ref']);
            }
            
            if (is_numeric($record['ordid']) && $mode=='view' && intval($record['enabled'])==1)
            {
                return $this->lines($record);
            }
            
            $this->setFormView('Orders/Quotes/quotes_edit')
                 ->setFormTitle('orders.quotes_editrecord')
                 ->setPageTitle('orders.quotes_editrecord')
                 
                 ->parseArrayFields()
                 ->setFormArgs(['autocomplete'=>'off','id'=>'id'],
                        [
                            $this->model_Orders->primaryKey=>$record[$this->model_Orders->primaryKey],
                            'enabled'=>1,
                            'ord_prdsource'=>$record['ord_prdsource']
                        ]
                  ,['class'=>'col-12'])
		 ->setCustomViewEnable(FALSE)
		 ->setFormCancelUrl($refurl)
		
                 ->addBreadcrumb('orders.sales',url($this))   
                 ->addBreadcrumb('orders.quotes_list',url($this,'quotes'))
                 ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ord_ref'],'/')
			
		 ->addData('record',$record)
                 ->setTab('general','system.general.tab_info')
                 
                 ->addFieldsFromModel($this->model_Orders->getFieldsForForm($record),$record,'orders.-key-')
                 ->addDataUrlScript()
                 ->addSimpleValidation();
            if (!array_key_exists('ord_done', $record) || (array_key_exists('ord_done', $record) && ($record['ord_done']=='0' || $record['ord_done']==0)))
            {
                $this->view->setFormAction($this,'save',['quote'],['refurl'=>base64url_encode($refurl)]);
            }
            if ($record['ord_prdsource']=='lines')
            {
                $this->view->setTab('tab_parts','orders.tab_parts');
            }
            return $this->view->render();    
        }
        
        
        
        if ($this->request->getGet('filtered')!=null)
        {
            unset($filters['ord_done']);
        }
        
        if ($edit_acc)
        {
            unset($filters['enabled']);
        }
        $this->getOrdersData('quotes','Orders/Oport/oport_index',$filters);
        
        
        $this->view->addEditButton('system.buttons.show_details','quotes',null,'btn-info','fas fa-info-circle',[], AccessLevel::view)
                   //->addEditButton('orders.opportunities_msg_convert','opportunities/-id-/convert',null,'btn-warning','fas fa-retweet',['data-convert'=>'-ord_done-'], AccessLevel::edit)
                   ->addDeleteButton()
                   ->addNewButton('quotes/new')
                   ->addColumn('orders.ord_ref_col','ord_ref',TRUE,[],null)
                   ->addColumn('orders.ord_refcus','ord_refcus',TRUE,[],null)
                
                   ->addModuleSettingsButton('',null,['tabName'=>'cfg']);
        if ($edit_acc)
        {
            $this->view->addColumn('orders.ord_cusacc','ord_cusacc',TRUE,[],null,'ord_cus_name');
                      
        }
        $this->view->addColumn('orders.order_value','ord_our_value',TRUE,[],'money:#ord_cus_curr')//ord_cus_curr far fa-money-bill-alt
                   ->addColumn('orders.ord_addon','ord_addon',TRUE,[],'date');
        
        return $this->view->render();
    }
    
    function customers($record=null,string $mode='view')
    {
        return redirect()->to(url($this,'quotes'));
    }
    
    
    function getWidgetData(int $mode,$account=null,int $limit=10,bool $done=FALSE)
    {
        
        $done=$done ? 1 : 0;
        $filters=['enabled'=>1,'ord_done'=>$done];
        if ($mode>-1)
        {
            $filters['ord_type']=$mode;
        }
        if ($account!=null)
        {
            $filters['ord_cusacc']=$account;
        }
        $record=$this->model_Orders->filtered($filters)
                        ->orderBy('ord_type,ord_addon DESC')
                        ->limit($limit)
                        ->find();
        switch($mode)
        {
            case 1:$mode='quotes';break;
            case 2:$mode='orders';break;
            default:$mode='opportunities';break;
        }
        return view('Orders/widget_table',
                [
                    'records'=>$record,
                    'error_norecords'=>'orders.'.$mode.'_msg_no_data',
                    'url_view'=>url($this,'-mode-',['-id-'],['refurl'=> current_url(FALSE,TRUE)]),
                    'field_value'=>'ord_value',
                    'url_new'=>url($this,$mode,['new'],['customer'=>$account,'refurl'=>current_url(FALSE,TRUE)])
                ]);
    }
    /*
    function quotes($record=null,string $mode='view')
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        $filters=
        [
            'ord_done'=>0,
            'enabled'=>1,
        ];
        
        if ($record!=null)
        {
            if (is_numeric($record))
            {
                return $this->lines($record,'quote');
            }
            
            return $this->customer($record,'quote');
        }
        
        $this->getOrdersData('quote','Orders/Quotes/quotes_index',$filters)
                ->setPageTitle('orders.quotes_title');
        if ($edit_acc)
        {
            $this->view->addBreadcrumb('orders.mainmenu',url($this));
        }
        $this->view->addEditButton('system.buttons.show_details','quotes',null,'btn-info','fas fa-info-circle',[], AccessLevel::view);
        $this->view->addEditButton('orders.btn_orderconfirm',null,null,'btn-warning','fas fa-share-square',['data-conforder'=>'-ordid-','data-noloader'=>'true'], AccessLevel::edit);
        $this->view->addDeleteButton();
        $this->view->addColumn('orders.ord_ref_col','ord_quoteref',TRUE)
                   ->addColumn('orders.ord_addon','ord_addon',TRUE,[],'date')
                   ->setNoDataMessage('orders.msg.msg_quotes_no_data')
                   ->addFilterField('ord_done','1','orders.msg_filter_quote_disabled')
                   ->addBreadcrumb('orders.quotes_mainmenu',url($this,'quotes'));
        if ($this->view->isFilterApplied('enabled',0))
        {
            $this->view->setPageTitle('orders.quotes_disabled_title')
                 ->addColumn('orders.ord_doneon_quote','ord_doneon',FALSE,[],'date');
        }
        
        return $this->view->render();
    }
    function customers($record=null,$mode=null)
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        if ($record!=null)
        {
            if ($edit_acc)
            {
                if ($mode!=null && in_array($mode,['reply','confirm']))
                {
                    $data=$this->model_Orders->find($record);
                    if (is_array($data))
                    {
                        if ($mode=='reply')
                        {
                            $data['_tplname']=$this->model_Settings->get('emails.emails_newemailtpl');
                            $data['mail_subject']=lang('orders.msg_reply_tocancelorder');
                        }else
                        {
                            $data['_tplname']=$this->model_Settings->get('orders.orders_tplfileordercancelconfirm');
                        }
                        
                    }else
                    {
                        $data=['ord_cusacc'=>0];
                    }
                    return redirect()->to(url('Emails','compose',['template'],['customer'=>$data['ord_cusacc'],'track'=>$data['ord_cusacc'],'refurl'=>url($this,'customers',[],[],TRUE)]))->with('_template_data',$data);
                }else
                if ($mode!=null)
                {
                    return $this->getNoPageError();
                }
                
                return $this->customer($record,'order');
            }
            return $this->lines($record,'order');
        }
        
        $filters=
        [
            'ord_isquote'=>0,
            'ord_done'=>0,
        ];
        $this->getOrdersData('order','System/table',$filters);
        
        
        if (!$edit_acc)
        {
            $this->view->addEditButton('system.buttons.show_details','customers',null,'btn-info','fas fa-info-circle',[], AccessLevel::edit);
        }
        
        $this->view->addColumn('orders.ord_ref','ord_ref',TRUE)
                   ->addColumn('orders.ord_refcus','ord_refcus',TRUE)
                   ->addColumn('orders.ord_addon','ord_addon',FALSE,[],'date')
                   ->addColumn('orders.ord_status','ord_status',FALSE,$this->model_Orders->getAvaliableStatuses())
                   ->setNoDataMessage('orders.msg.msg_quotes_no_data')
                   ->addFilterField('ord_status=cancel&ord_done=1 ',FALSE,'orders.msg_filter_orders_disabled')
                   ->addHeaderButton(null,null,'button','btn btn-warning btn-sm','<i class="far fa-file-alt"></i>','orders.quotes_mainmenu',AccessLevel::edit,['data-url'=>url($this,'quotes',[],['refurl'=>current_url(FALSE,TRUE)])])
                   ->addModuleSettingsButton('orders.settings')
                   ->addBreadcrumb('orders.mainmenu',url($this));
        if ($this->view->isFilterApplied('ord_status','cancel'))
        {
            $this->view->setPageTitle('orders.mainmenu_title_cancel')
                    ->addBreadcrumb('orders.mainmenu_title_cancel',url($this,'customers'))
                    ->addEditButton('system.buttons.show_details','customers',null,'btn-info','fas fa-info-circle',[], AccessLevel::edit);
        }else
        {
            $this->view->setPageTitle('orders.mainmenu_title')
                       ->addEditButton('system.buttons.edit_details','customers',null,'btn-primary','fas fa-edit',[], AccessLevel::edit);
        }
        return $this->view->render();
    }
    */
    
    private function getOrdersData(string $mode,string $view,array $filters=[])
    {
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        $customer=$this->model_Customers_Customer->getCustomerForLogedUser();
        if (!$edit_acc)
        {
            $filters['ord_cusacc']=$customer;
        }
        
        if ($this->request->getGet('customer')!=null)
        {
            $filters['ord_cusacc']=$this->request->getGet('customer');
            $filters['ord_status <>']='done';
        }
        $api=$this->model_Orders->getOrdersSettings('orders_apiplaceorder');
        $this->setTableView($view)
                ->setCustomViewEnable(FALSE)
                ->setData('orders','ord_addon',TRUE,null,$filters)
                ->addFilters($mode)
                ->addFilterField('ord_ref %')
                ->addFilterField('|| ord_refcus %')
                
                ->setPageTitle('orders.'.$mode.'_list')
                ->setNoDataMessage('orders.'.$mode.'_msg_no_data')
                ->addBreadcrumb('orders.sales',url($this))
                ->addBreadcrumb('orders.'.$mode.'_list',url($this,$mode))
                
                //->addData('invoiced_orders',$this->model_Orders->getInvoicedOrders($customer,'ord_invoicenr'))
                ->addData('edit_acc',$edit_acc)
               //->addData('url_check',url($this,'customers',['-id-','validate'],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('url_ordcanc',url($this,'update',['cancel'],['refurl'=>current_url(FALSE,TRUE)]))
                //->addData('url_downloadxlsx',url($this,'download',['-id-','xlsx'],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('url_downloadinvoice',url($this,'download',['-id-','invoice'],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('url_ordupl',url($this,'uploadorder',[],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('url_confirm',url($this,'save',['confirm'],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('url_send',url($this,'save',['send'],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('cusordernr',$customer. formatDate())
                //->addData('isquote',$mode=='quotes')
                //->addInputMaskScript()
                ->addEditorScript();
        
        if ($api!='0' && $api!=0)
        {
            $this->view->addData('url_downloadapi',url($this,'download',['-id-','sendtoapi'],['refurl'=> current_url(FALSE,TRUE)]));
        }
        return $this->view;
    }
    
    function customers_old($record=null,$mode='edit')
    {
        $mode= is_array($mode) && count($mode)==1 ? $mode[0] : $mode;
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        if ($record!=null)
        {
            if ($mode=='form')
            {
                return $this->custordersform($record);
            }else
            if ($mode=='convertorder')
            {
                return $this->convertorder($record);
            }else
            if ($mode=='convertquote')
            {
                if (!$edit_acc)
                {
                  return $this->getAccessError();  
                }
                return $this->convertquote($record);
            }
            
            if ($record=='upload')
            {
                return $this->upload($mode!='quotes' ? 'order' : $mode);
            }
            
            if ($mode=='validate')
            {
                return $this->upload('validate',FALSE,['id'=>$record]);
            }
            
            if ($record=='newgrid')
            {
                return $this->upload($mode!='quotes' ? 'order' : $mode,TRUE);
            }
            
            if (!$edit_acc)
            {
                return $this->lines($record,$mode);
            }
            
            return $this->customer($record,$mode);
        }
        $filters=['ord_done'=>0];
        if ($mode=='quotes')
        {
            if ($edit_acc)
            {
                $filters['ord_quoteref len>']=1;
            }else
            {
               $filters['ord_isquote']=1; 
            }
        }else
        {
            $filters['ord_isquote']=0;
        }
        
        if (!$edit_acc)
        {
            $filters['ord_cusacc']=$this->model_Customers_Customer->getCustomerForLogedUser();
            $filters['enabled']=1;
        }
        
        if ($this->request->getGet('customer')!=null)
        {
            $filters['ord_cusacc']=$this->request->getGet('customer');
            $filters['ord_status <>']='done';
        }
        
        if ($mode=='quotes' && !$edit_acc)
        {
           $filters['enabled']=1; 
        }
        $customer=$this->model_Customers_Customer->getCustomerForUser(null,'code');
        
        $api=$this->model_Orders->getOrdersSettings('orders_apiplaceorder');
        $this->setTableView('Orders/orders_index')
                //Set data
                ->setCustomViewEnable(FALSE)
                ->setData('order','ord_addon',TRUE,null,$filters)
                
                //Fiilters settings
                ->addFilters($mode=='quotes' ? 'quotes' : 'customers')
                ->addFilterField('ord_ref %')
                ->addFilterField('|| ord_refcus %');
                //Table Columns settings
                
        if ($mode=='quotes')
        {
            $this->view->addColumn('orders.ord_ref_quote','ord_ref',TRUE);
            if (!$edit_acc)
            {
                 $this->view->addHeaderButton(null,null,'button','btn btn-dark btn-sm','<i class="fa fa-plus"></i>','system.buttons.new',AccessLevel::view,['data-url'=>url('Tickets','cases',['new'],['tpl'=>$this->model_Orders->getOrdersSettings('orders_newquotetpl'),'refurl'=> current_url(FALSE,TRUE)])]);
            }
        }else
        {
             $this->view->addColumn('orders.ord_ref','ord_ref',TRUE)
                        ->addColumn('orders.ord_refcus','ord_refcus',FALSE);
        }
                
        if ($edit_acc)
        {
            $this->view->addColumn('orders.ord_cusacc','ord_cusacc',FALSE);
            if ($mode!='quotes')
            {
                $this->view->addCustomHeaderButton(Pages\HtmlItems\ToolbarButton::createDropDownButton('fas fa-plus', 'dark', 'system.buttons.new', 
                               [
                                   'orders.btn_quote_newupload'=>url($this,$mode=='quotes' ? 'quotes' : 'customers',['upload'],['refurl'=> current_url(FALSE,TRUE)]),
                                   'orders.'.($mode!='quotes' ? 'btn_uploadnewordergrid' : 'btn_quote_newuploadgrid')=>url($this,$mode=='quotes' ? 'quotes' : 'customers',['newgrid'],['refurl'=> current_url(FALSE,TRUE)])
                               ],null,['mode'=>'btn-group dropleft']));
            } 
        }else
        {
            $this->view->addHeaderButton(null,null,'button','btn btn-dark btn-sm','<i class="fa fa-plus"></i>','system.buttons.new',AccessLevel::view,['data-url'=>url($this,$mode=='quotes' ? 'quotes' :'customers',['upload'],['refurl'=> current_url(FALSE,TRUE)])]);
        }
        
        if ($edit_acc && $mode!='quotes')
        {
            $this->view->addHeaderButton(null,null,'button','btn btn-primary btn-sm','<i class="fas fa-th-list"></i>','orders.quotes_mainmenu',AccessLevel::edit,['data-url'=>url($this,'quotes',[],['refurl'=> current_url(FALSE,TRUE)])])
                        ->addFilterField('ord_done',-1,'orders.btn_filtercanceled')
                        ->addFilterField('ord_done',1,'orders.btn_filtershipped');
        }
        
        $this->view->addColumn('orders.ord_addon','ord_addon',FALSE,[],'d M Y');
        
        if ($mode=='quotes')
        {
            $this->view->addBreadcrumb(!$edit_acc ? 'orders.quotes_mainmenu' : 'orders.bread_quotes',url($this,'quotes'))
                       ->setPageTitle(!$edit_acc ? 'orders.quotes_mainmenu' : 'orders.bread_quotes')
                       ->addData('url_edit',url($this,'quotes',['-id-'],['refurl'=> current_url(FALSE,TRUE)]));               
        }else
        {
            $this->view->addBreadcrumb(!$edit_acc ? 'orders.mainmenu' : 'orders.bread_orders',url($this))
                       ->setPageTitle(!$edit_acc ? 'orders.mainmenu' : 'orders.orders_header')
                       ->addColumn('orders.ord_paid','ord_paid',TRUE,'yesno')
                       ->addColumn('orders.ord_status','ord_status',FALSE,$this->model_Orders->getAvaliableStatuses())
                       ->addData('url_edit',url($this,'customers',['-id-'],['refurl'=> current_url(FALSE,TRUE)]));
        }        
        $this->view->addModuleSettingsButton('orders.settings')
                ->addEditButton('system.buttons.edit_details','customers',null,'btn-primary edtBtn','fa fa-edit',[], AccessLevel::view)
                ->addData('invoiced_orders',$this->model_Orders->getInvoicedOrders($customer,'ord_invoicenr'))
                ->addData('edit_acc',$edit_acc)
                ->addData('url_check',url($this,'customers',['-id-','validate'],['refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_ordcanc',url($this,'update',['cancel'],['refurl'=>current_url(FALSE,TRUE)]))
                ->addData('url_downloadxlsx',url($this,'download',['-id-','xlsx'],['refurl'=> current_url(FALSE,TRUE)]))
                //->addData('url_downloadinvoice',url($this,'download',['-id-','invoice'],['refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_ordupl',url($this,'uploadorder',[],['refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_confirm',url($this,'save',['confirm'],['refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_send',url($this,'save',['send'],['refurl'=> current_url(FALSE,TRUE)]))
                ->addData('cusordernr',$customer. formatDate())
                ->addData('isquote',$mode=='quotes')
                ->addData('customer',$this->model_)
                ->addInputMaskScript()
                ->addEditorScript();       
        
        if ($api!='0' && $api!=0)
        {
            $this->view->addData('url_downloadapi',url($this,'download',['-id-','sendtoapi'],['refurl'=> current_url(FALSE,TRUE)]));
        }
        return $this->view->render();
    }
    
    private function upload($mode='order',bool $isGrid=FALSE,array $data=[])
    {
        if ($mode=='validate' && array_key_exists('id', $data))
        {
            $data=$this->model_Orders->where('ordid',$data['id'])->first();
            if (!is_array($data) || (is_array($data) && !Arr::KeysExists(['ord_source','ord_cusacc','ord_ref'], $data)))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_id','danger'));
            }
            if ($this->model_Lines->count(['ol_ref'=>$data['ord_ref']]) > 0)
            {
                //orders_tplfileorderconfirm
                if (TRUE)//$this->model_Orders->setOrderActive($data['ord_ref']))
                {
                    $settings=$this->model_Orders->getOrdersSettings();
                    $settings=$this->model_Documents_Report->parseEmailTemplate($settings['orders_tplfileorderconfirm'],$data);
                    $settings['attachements']=[];
                    return redirect()->
                            to(url('Emails','compose',['customer',$data['ord_cusacc']],['track'=>$data['ord_cusacc'],'refurl'=> base64url_encode($this->getRefUrl())]))
                            ->with('mail_body',$settings);
                 }
                
            }
            if ($this->model_Orders->uploadOrder($data,$data['ord_source'],$data['ord_cusacc'],1))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.msg_validation_ok','success'));
            }
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_validation_failed','danger'));
        }else
        if (Arr::KeysExists(['order','customer','file'], $data))
        {
            $data['order']=$this->model_Orders->where('ord_ref',$data['order'])->first();
           
            if (is_array($data['order']))
            {
                //$this->model_Orders->uploadOrder($data['order']['ord_ref'],$data['file'],$data['customer'],$data['iscustomer']);
                if (intval($data['order']['ord_isquote'])==1)
                {
                    $this->sendNotification('#orders_tplfilequotecreatenew', $data['order'],$data['customer']);
                }else
                { 
                    $data['details']=$data['order'];
                    
                    $this->sendNotification('#orders_tplfileordercreatenew', $data['order']);
                }
                 
                return TRUE;
            }
            return FALSE;
        }
        $refurl=$this->getRefUrl();
        $this->setFormView()
            ->setFormTitle('orders.'.$mode.'_upload')
            ->setPageTitle('orders.'.$mode.'_upload')
            ->setFormAction($this,'save',['upload'],['refurl'=>base64url_encode($refurl)])
            ->parseArrayFields()
            ->setFormArgs(['autocomplete'=>'off'],
            [
                'ord_isquote'=>$mode=='quotes' ? 1 : 0
            ]
                ,['class'=>'col-12'])
            ->setCustomViewEnable(FALSE)
            ->setFormCancelUrl($refurl)
            ->addBreadcrumb('orders.bread_'.$mode,url($this,$mode=='quotes' ? $mode : 'customers'))
            ->addBreadcrumb('orders.'.$mode.'_upload','/')    
            ->setTab('general','system.general.tab_info')
            ->addFieldsFromModel($this->model_Orders->getFieldsForUploadForm($this->hasAccess(AccessLevel::edit),$mode,$isGrid),[],'orders.-key-');
        if ($isGrid)
        {
            $this->view->setFile('Orders/order_uploadgrid')
                       ->addJSpreadsheet();
        }
        return $this->view->render();
    }
   
    private function customer($record,$mode='edit')
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        if (is_numeric($record))
        {
            $filters=['ordid'=>$record];
            if ($mode=='quotes')
            {
                $filters['ord_isquote']=1;
            }
            $record=$this->model_Orders->filtered($filters)->first();
            if(!is_array($record))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage($mode=='quotes' ? 'orders.error_quote_id' : 'orders.error_order_id','danger'));
            }
        }else
        {
            $record=null;
        }
        if ($record['ord_status']=='done')
        {
            return $this->lines($record,'view');
        }
        $record=$this->getFlashData('_postdata',$record);
        if ((array_key_exists('enabled', $record) && $record['enabled']==0) || (strlen($record['ord_cancelref']) > 0))
        {
            if ($mode=='quotes' && strlen($record['ord_cancelref']) > 0)
            {
               $record['_notify']=lang('orders.msg_quote_disabled'); 
            }else
            if (strlen($record['ord_cancelref']) > 0)
            {
                $record['_notify']=lang(intval($record['enabled'])==0 ? 'orders.msg_order_disabled_done' : 'orders.msg_order_disabled');
            }
            
            $record['_deleteurl']=url($this,'deletesingle',['order',$record['ordid']],['refurl'=> url($this, $mode=='quotes' ? 'quotes' : 'custoemrs', [], [], TRUE)]);
        }
        if ($record==null || $record=='new')
        {
            if (!$this->hasAccess(AccessLevel::create))
            {
                return $this->getAccessError(true);
            }
            $isnew=TRUE;
            $record=$this->model_Orders->getNewRecordData(TRUE);
            $record['ord_status']='placed';
            $record['parts']=[];
        } else 
        {
             $record['parts']=$this->model_Lines->getForOrder($record['ord_ref'],FALSE,TRUE);
             $record['parts_pagination']=$record['parts']['pagination'];
             $record['parts']=$record['parts']['data'];
        }
        
        $record['edit_acc']=$edit_acc;
        $record['payments']=$this->model_Orders->getPaymentsHistory($record['ord_ref']);
        $record['_readonly']=!($mode!='quotes' || ($mode=='quotes' && intval($record['ord_isquote'])==1));
        $this->setFormView('Orders/Orders/order_edit')
                ->setFormTitle($mode=='quotes' ? 'orders.quotes_edit' : 'orders.order_edit')
		->setPageTitle($mode=='quotes' ? 'orders.quotes_edit' : 'orders.order_edit')
		->setFormAction($this,'save',['update'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Orders->primaryKey=>$record[$this->model_Orders->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb($mode=='quotes' ? 'orders.bread_quotes':'orders.bread_orders',url($this,$mode=='quotes' ? 'quotes' : 'customers'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ord_ref'],'/')
			
		->addData('record',$record)
                ->addData('validateparturl',$this->getApiUrl())
                ->addData('url_invoiceupl',url($this,'update',['invoice'],['refurl'=> current_url(FALSE,TRUE)]))
                ->addData('url_payment',url($this,'update',['payment'],['refurl'=>current_url(FALSE,TRUE)]))
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel($mode=='quotes' ? 'quote' : 'order',$record,'orders.-key-')
                ->addSelect2('.select2');
       
        if (!$record['_readonly'])
        {
            $this->view->setTab('tab_parts','orders.ord_tab_parts');
        }
        if (strlen($record['ord_invoicenr']) > 2)
        {
            $this->view->setTab('tab_payhist','orders.tab_payhist');
        }
        if ($record['edit_acc'])
        {
            $toolbarButtons=[];
            if (intval($record['enabled'])==1)
            {
                $toolbarButtons[]=Pages\HtmlItems\PartNumbersListField::create()->setArgs(['name'=>'add_parts_nr','id'=>'add_parts_nr','class'=>'form-control-sm','addfunction'=>'addPartToList()','notable'=>TRUE]);
            }
            
            $toolbarPButtons=[];
            if (intval($record['enabled'])==1)
            {
                $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-file-excel', 'success ml-3', url($this,'download',[$record['ordid'],'xlsx'],['refurl'=> current_url(FALSE,TRUE)]), 'orders.btn_downxlsx', null, ['data-noloader'=>TRUE]);
                
            }
            if (!$record['_readonly'] && $record['enabled']!=0)
            {
                $this->view->addButtonsToolBar('parts_toolbar',$toolbarButtons,['bg'=>'white','padding'=>'1','tab_name'=>'tab_parts']);
                $this->view->addButtonsToolBar('payments_toolbar',$toolbarPButtons,['bg'=>'white','padding'=>'1','tab_name'=>'tab_payhist']);
            }
            
        }
        $this->view->addCustomTextField(' ','ord_parts_list','',['tab_name'=>!$record['_readonly'] ? 'tab_parts' : 'general'])
                   ->addData('urldelete',url($this,'deleteorder',[$record['ordid']],['refurl'=> base64_encode($this->getRefUrl())]))
                   ->addData('curr_list',$this->model_Settings->getCurrencyIcons(null,FALSE))
                   ->addDataUrlScript();
        
        return $this->view->render();    
    }
    
    private function custordersform($cust)
    {
        $data=[];
        $filters=['ord_cusacc'=>$cust];
        $data['edit_acc']=$this->hasAccess(AccessLevel::edit);
        if ($data['edit_acc'])
        {
            $filters['enabled <>']=0;
        }else
        {
            $filters['enabled']=1;
        }
        $data['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $data['records']=$this->model_Orders->filtered($filters,'ord_addon',5);
        $data['url_show_orders']=url($this,'customers',[],['customer'=>$cust]);
        $data['statuses']=$this->model_Orders->getAvaliableStatuses();
        $data['url_edit']=url($this,'customers',['-id-'],['refurl'=>current_url(FALSE,TRUE)]);
        $data['url_confirm']=url($this,'save',['confirm'],['refurl'=>current_url(FALSE,TRUE)]);
        $data['url_send']=url($this,'save',['send'],['refurl'=> current_url(FALSE,TRUE)]);
        $data['url_downloadxlsx']=url($this,'download',['-id-','xlsx'],['refurl'=> current_url(FALSE,TRUE)]);
        return view('Orders/orders_index_forcust',$data);
    }

    public function download($record,$mode)
    {
        if (is_array($record) && Arr::KeysExists(['ord_ref','ord_type'], $record))
        {
            $mode=$record['ord_type'];
            goto get_data;
        } 
        $record=$this->model_Orders->filtered(['ord_ref'=>$record, '|| ordid'=>$record,'|| ord_refcus'=>$record])->first();
        if (!is_array($record) || (is_array($record) && !array_key_exists('ord_ref', $record)))
        {
            error_id:
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_id','danger'));
        }
        get_data:
        if ($mode=='oport' || $mode==0)
        {
           $edit_acc = $this->hasAccess(AccessLevel::edit) && intval($this->model_Orders->getOrdersSettings('orders_oport_showourcost'))==1;
           $data=$this->model_Lines->getForPicker($record['ord_ref'],TRUE,$edit_acc); 
        }else
        {
            $data=$this->model_Lines->getAsCSV($record['ord_ref'],TRUE,intval($record['ord_type'])==1); 
        } 
        //$data=$this->model_Lines->getAsCSV($record['ord_ref'],TRUE,intval($record['ord_isquote'])==1); 
        if (!is_array($data) || (is_array($data) && count($data) < 1))
        {
            goto error_id;
        }
        $headers=[];
        foreach(array_keys($data[0]) as $key=>$value)
        {
            $headers[$value]='string';
        }
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Sheet1', $headers);
        $writer->writeSheet($data);
        $writer->writeToStdOut(($record['ord_ref']).'.xlsx');exit;
    }
    
    function download1($record,$mode='csv',$redirect=TRUE)
    {
        if (is_numeric($record))
        {
            $record=$this->model_Orders->find($record);              
        }else
        {
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_id','danger'));
        }
        $iscust=loged_user('iscustomer')==1;
        if ($mode=='invoice')
        {
            $setings=$this->model_Orders->getOrdersSettings();
            $record= json_decode($record['ord_invoicefile'],TRUE);
            if (strlen($setings['orders_onedrivetoken']) > 0)
            {
                $storage= \EMPORIKO\Libraries\StorageEngine\StorageEngine::init('OneDrive\StorageEngine',$setings['orders_onedrivetoken']);
                $storage=$storage->DownloadFile($record[$record['name']],$this->getRefUrl());
                if (is_string($storage))
                {
                    return redirect()->to($storage);
                }else
                {
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage(lang('orders.error_invoice_download',[is_array($storage) && array_key_exists('error', $storage)? ', '.$storage['error'] : '']),'danger'));
                }
            } else 
            {
                $storage= \EMPORIKO\Libraries\StorageEngine\StorageEngine::init('local');
            }
            
          
            return $storage->DownloadFile($record[$record['name']]);exit;
        }
        $data=$this->model_Lines->getAsCSV($record['ord_ref'],TRUE,intval($record['ord_isquote'])==1);        
        if ($mode=='xlsx')
        {
            $headers=[];
            foreach(array_keys($data[0]) as $key=>$value)
            {
                if ($key==2)
                {
                    $headers[$value]='integer';
                }else
                if ($key==3)
                {
                    $headers[$value]='price';
                }else
                {
                    $headers[$value]='string';
                }
            }
            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $headers);
            $writer->writeSheet($data);
            $writer->writeToStdOut(($iscust ? $record[(intval($record['ord_isquote'])==0 ? 'ord_refcus' : 'ord_ref')] : $record['ord_ref']).'.xlsx');exit;
        }else
        if ($mode=='sendtoapi')
        {
          return $this->sendtoapi($record,$data,$redirect);
        }else
        {
            $filename=parsePath('@temp/'. base64_encode($record['ord_ref']).'.csv',TRUE);
            $data=Arr::toCSVFile($data,$filename,$iscust);
            header('Content-Disposition: attachment; filename="' .$record['ord_ref'].'.csv"');
        }
        
        $this->response->setHeader('Content-Type','application/octet-stream');
        ob_clean();
        flush();
        readfile($filename);
        unlink($filename);
        exit;
    }

    function update($mode)
    {
        $get=$this->request->getGet();
        $post=$this->request->getPost();
        $iscust=loged_user('iscustomer')==1;
        
        if ($mode=='invoice' && Arr::KeysExists(['invoicenr','invoicevalue','order'], $post))
        {
            if (strlen($post['invoicenr']) < 2)
            {
                $msg=$this->createMessage('orders.error_order_invoice_invoicenr','warning');
                goto redirect_func;
            }
            
            if (strlen($post['invoicevalue']) < 2)
            {
                $msg=$this->createMessage('orders.error_order_invoice_invoicevalue','warning');
                goto redirect_func;
            }
            $post['order']=$this->model_Orders->find($post['order']);
            if (!is_array($post['order']))
            {
               $msg=$this->createMessage('orders.error_order_id','danger');
               goto redirect_func; 
            }
            
            $setings=$this->model_Orders->getOrdersSettings();
            $setings['orders_invoicefolder']= strtolower($setings['orders_invoicefolder']);
            if (strlen($setings['orders_onedrivetoken']) >0)
            {
                $post['_storage_engine']=\EMPORIKO\Libraries\StorageEngine\StorageEngine::init('OneDrive\StorageEngine');
                $post['_storage_engine']->setToken($setings['orders_onedrivetoken']);
                $post['_uploads_dir']=array_flip($post['_storage_engine']->mapDir());
                if (array_key_exists($setings['orders_invoicefolder'], $post['_uploads_dir']))
                {
                    $post['_uploads_dir']=$post['_uploads_dir'][$setings['orders_invoicefolder']];
                }else
                {
                    $post['_uploads_dir']='.';
                }
            }
            $post['_upload_filename']=$post['invoicenr'];
            $status=$this->uploadFiles($post);
            if ((is_bool($status) && $status!=TRUE) || is_string($status))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage(lang('orders.error_invoice_upload',[is_string($status) ? ', '.$status : '']),'danger'));
            }
            $post['order']['ord_invoicenr']=$post['invoicenr'];
            $post['order']['ord_invoicevalue']=$post['invoicevalue'];
            $post['order']['ord_invoicefile']=$post['invoicefile'];
            $post=$post['order'];
            if ($this->model_Orders->save($post))
            {
               $this->triggerRule('order_add_invoice',['data'=>$post,'cust'=>$post['ord_cusacc']]);
               $msg=$this->createMessage('orders.msg_invoice_added_ok','success');
               goto redirect_func; 
            }
            $msg=$this->createMessage('orders.error_order_id','danger');
        }else
        if ($mode=='cancel' && (Arr::KeysExists(['reason','order'], $post)|| (!$iscust && Arr::KeysExists(['order'], $get))))
        {
            if (!$iscust)
            {
                $setings=$this->model_Orders->getOrdersSettings();
                $get=$this->model_Orders->find($get['order']);
                if (!is_array($get))
                {
                   goto error_orderid; 
                }
                $get['ord_status']='cancel';
                $get['enabled']=0;
                $get['ord_done']='-1';
                $redirect=FALSE;
                if (strlen($get['ord_cancelref']) < 1)
                {
                    $get['ord_cancelref']='Canceled by user:'.loged_user('username');
                    $redirect=TRUE;
                }
                if ($this->model_Orders->save($get))
                {
                    if (!$redirect)
                    {
                       $this->sendNotification($setings['orders_tplfileordercancelconfirm'], $get,$get['ord_cusacc']);
                       return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.msg_order_cancel','success'));
                    } else
                    {
                        return redirect()->to(url('Emails','compose',['template',$setings['orders_tplfileordercancelconfirm']],['customer'=>$get['ord_cusacc'],'refurl'=> base64url_encode($this->getRefUrl())]))->with('_template_data',$get);
                    }
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_cancel_failed','danger'));
            }
            if ($this->model_Orders->count(['ordid'=>$post['order']]) < 1)
            {
                error_orderid:
                $msg=$this->createMessage('orders.error_order_id','danger');
                goto redirect_func;
            }
            
            if (strlen($post['reason']) < 5)
            {
                $msg=$this->createMessage('orders.error_order_cancel_reason','warning');
                goto redirect_func;
            }
            
            $this->model_Orders->save(['ordid'=>$post['order'],'ord_cancelref'=>$post['reason']]);
            $order=$this->model_Orders->find($post['order']);
            if (!is_array($order))
            {
                $order=[];
            }
            $order['ordid']=$post['order'];
            $order['ord_cancelref']=$post['reason'];
            $this->triggerRule('order_cancel',['data'=>$order]);
            $this->addMovementHistory('order_cancel', null, null, $order['ordid'],$order['ord_cancelref'], 'orders');
            $msg=$this->createMessage('orders.error_order_cancel_ok','success');
            goto redirect_func;
        }else
        if ($mode=='payment' && Arr::KeysExists(['paidref','invoicenr','paidvalue'], $post))
        {
            if (strlen($post['invoicenr']) < 3)
            {
                $msg=$this->createMessage('orders.error_invalid_invoicenr','danger');
                goto redirect_func;
            }
            
            if (strlen($post['paidref']) < 3)
            {
                $msg=$this->createMessage('orders.error_invalid_paidref','warning');
                goto redirect_func;
            }
            
            if (!is_numeric($post['paidvalue']))
            {
                $msg=$this->createMessage('orders.error_invalid_paidvalue','warning');
                goto redirect_func;
            }
            
            $msg=$this->createMessage('orders.error_paymentref','danger');
            if ($this->model_Orders->updatePaymentInfo($post['invoicenr'],$post['paidref'],$post['paidvalue']))
            {
                if ($iscust)
                {
                    $this->triggerRule('order_addpayment_info',['data'=>Arr::Prefix($post, 'ord_')]);
                }
                $this->addMovementHistory('payment_info', $post['paidref'], $post['paidvalue'], $post['invoicenr'],null, 'orders');
                $msg=$this->createMessage('orders.msg_paymentref_ok','success');
            }
        }else
        {
            $this->getNotFoundError();
        }
        redirect_func:
        return redirect()->to($this->getRefUrl())->with('error',$msg);
    }
    
    function sendNotification(string $template, array $data, $to=null) 
    {
        if (array_key_exists('ord_invoicenr', $data))
        {            
            $order=$this->model_Orders->where('ord_invoicenr',$data['ord_invoicenr'])->first();
            if (is_array($order))
            {
                $data=$data+$order;
            }
        }
        
        if (!array_key_exists('code', $data) && $to==null)
        {
            $data['code']=$this->model_Customers_Customer->getCustomerForLogedUser();
        }
        
        if (Str::startsWith($template, '#'))
        {
            $template=$this->model_Settings->get('orders.'.substr($template,1));
        }
        
        if ($to==null)
        {
            $to=$this->model_Orders->getNotificationEmails();
        }else
        if (is_string($to) && !Str::contains($to, '@'))
        {
            $cust=$this->model_System_Contact->getEmailsByAcc($to);
            if (is_array($cust) && count($cust) > 0)
            {
                $to=$cust;
            }else
            {
                return FALSE;
            }
        }
        
        return parent::sendNotification($template, $data, $to);
    }
    
    function getDashboardTile($type='list')
    {
        $form=new Pages\FormView($this);
        $data=[];
        if ($type=='custlive')
        {
            $type='System/Dashboard/tile';
            $data=
            [
                'data'=>view('Orders/Tiles/custlive',['data'=>$this->model_Orders->getLiveOrdersForCustomer(),'status'=>$this->model_Orders->getAvaliableStatuses(),'iscustomer'=>loged_user('iscustomer')]),
                'header'=>lang('orders.tiles_custlive_header'),
                'name'=>'orders_custlive_'.rand(1,12),
                'tilePrintButton'=>TRUE,
                'header_style'=>'background-color:#007bff!important;color:#FFF!important',
            ];
        }else
        if ($type=='yearusage')
        {
            $type='System/Dashboard/graph';
            $data=
            [
                'data'=>$this->model_Orders->getYearUsage(FALSE),
                'header'=>lang('orders.tiles_yearusage'),
                'header_chart'=>lang('orders.tiles_yearusage_chart'),
                'name'=>'orders_yearusage_'.rand(1,12),
                'chart_type'=>'line',
                'tilePrintButton'=>TRUE,
                'header_style'=>'background-color:#007bff!important;color:#FFF!important',
            ];
        }else
        if ($type=='uploadorder')
        {
            $type='System/Dashboard/tile';
            $data=
            [
                'data'=>view('Orders/order_upload',
                        [
                            'noUploadModal'=>TRUE,
                            'url_ordupl'=>url($this,'save',['upload'],['refurl'=> current_url(FALSE,TRUE)]),
                            'ordernr'=>$this->model_Orders->generateNewOrderNr(TRUE),
                            'customers'=> !loged_user('iscustomer') ? Pages\HtmlItems\DropDownField::create()->setArgs(['name'=>'customer','id'=>'id_ordertile_uplcustomer','options'=>$this->model_Customers_Customer->getForForm('code','name'),'advanced'=>TRUE]) : null
                        ]),
                'header'=>lang('orders.ord_upltiletitle'),
                'name'=>'uploadorder'.rand(1,12),
                'chart_type'=>'line',
                'header_style'=>'background-color:#007bff!important;color:#FFF!important',
                'tileNoScroll'=>TRUE
            ];
        }else
        {
            return null;
        }
        return view($type,$data);
    }
    
    /**
     * Array with available menu items (keys as function names and values as description)
     * @var Array
     */
    public $availablemenuitems = [];
    
    function enablesingle($model, $id, $value, $field = null) 
    {
        if ($model=='oport')
        {
            $data=$this->model_Orders->find($id);
            $post=$this->request->getPost();
            $model=$this->model_Orders->builder();
            if (is_array($post) && count($post) > 0)
            {
                if (!array_key_exists('comment', $post))
                {
                    goto return_failed;
                }
                $model->set('ord_cancelref',$post['comment'])
                      ->set('ord_done',1)
                      ->set('ord_doneon', formatDate())
                      ->set('ord_doneby', loged_user('username'))
                      ->set('ord_status', 'lost');
                goto return_save;
            }else
            {
                $model->set('ord_recon', formatDate())
                      ->set('ord_recby', loged_user('username'))
                      ->set('ord_status','neg');
            }
            if (is_array($data) && array_key_exists('ord_cusacc1', $data))
            {
                $this->sendNotification($this->model_Orders->getOrdersSettings('orders_tplfileoportconfirm'), $data,$data['ord_cusacc']);
            }
            return_save:
            if ($model->where('ordid', $id)->update()) 
            {
                return_ok:
                return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('system.general.msg_enbale_ok', 'success'));
            } else {
                return_failed:
                return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('system.errors.msg_enbale_no', 'danger'));
            }
        }else
        if ($model=='quote' || $model=='order')
        {
            $data=$this->model_Orders->find($id);
            if (!is_array($data))
            {
                if ($model=='quote')
                {
                   return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_quote_id','danger')); 
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_id','danger'));
            }
            $reason=$this->request->getPost('reason');
            $data['ord_cancelref']=['user'=> loged_user('name'),'note'=>''];
            if ($model=='order')
            {
                $data['ord_status']='cancel';
            }
            if ($reason!=null)
            {
               $data['ord_cancelref']['note']= base64_encode($reason);
               $data['reason']=$reason;
            } else 
            {
                if ($model=='quote')
                {
                   return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_quote_cancel_reason','warning')); 
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_cancel_reason','warning'));
            }
           
           $data['ord_cancelref']= json_encode($data['ord_cancelref']);
           
           $data['ord_done']=1;
           $data['ord_doneon']= formatDate();
           if ($this->model_Orders->save($data))
           {
                if ($model=='quote')
                {
                    $this->sendNotification($this->model_Settings->get('orders.orders_tplfilequotecancel'), $data);
                    return redirect()->to(url($this,'quotes'))->with('error',$this->createMessage('orders.msg_quote_cancel','success'));
                }
                $this->sendNotification($this->model_Settings->get('orders.orders_tplfileordercancel'), $data);
                return redirect()->to(url($this,'customers'))->with('error',$this->createMessage('orders.error_order_cancel_ok','success'));
           }
           return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_cancel_failed','danger'));
        }
        return parent::enablesingle($model, $id, $value, $field);
    }
    
    public function save($type, $post = null) 
    {
        $post = $post == null ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
       
        if ($type=='sendemail')
        {
            $error=['orders.quotes_error_sendemailinvalidfields','danger'];
            if (Arr::KeysExists(['mail_from','mail_to','mail_subject','mail_body'], $post))
            {
                if (!Str::isValidEmail($post['mail_from']))
                {
                    $error=['orders.quotes_error_sendemailinvalidfrom','warning'];goto error_sendmail;
                }
                
                if (!Str::contains($post['mail_to'],'@'))
                {
                    $error=['orders.quotes_error_sendemailinvalidto','warning'];goto error_sendmail;
                }
                
                if (strlen($post['mail_subject']) < 3 || strlen($post['mail_body']) < 3)
                {
                    $error=['orders.quotes_error_sendemailinvalidsubjectbody','warning'];goto error_sendmail;
                }
                $email=$this->sendEmail($post['mail_from'], $post['mail_to'], $post['mail_subject'], $post['mail_body']);
                if (!$email)
                {
                    $error=['orders.quotes_error_sendemailfailed','danger'];goto error_sendmail;
                }
                $error=['orders.quotes_error_sendemail_ok','success'];
                if (array_key_exists('record', $post))
                {
                    $post['record']=$this->model_Orders->filtered(['ord_ref'=>$post['record']])->first();
                    if (is_array($post['record']))
                    {
                       $post['record']['ordid']=$this->model_Emails_Email->storeEmailInOutbox($post['mail_from'],$post['mail_to'],$post['mail_subject'],$post['mail_body']);
                       if (is_numeric($post['record']['ordid']))
                       {
                           $post['record']['ordid']= url_tag(url('Emails','message',['view',$post['record']['ordid']],['refurl'=>'-curl-']), 'emails.btn_open');
                           $this->addMovementHistory('emails_sendcust', $post['mail_from'], $post['mail_to'], $post['record']['ord_cusacc'], $post['record']['ordid']);
                       }
                    }
                }
            }
            error_sendmail:
            return redirect()->to($refurl)->with('error', $this->createMessage($error[0], $error[1]));
            
             dump($post);exit;//itsupport@apdcw.co.uk;arturwiater@gmail.com
        }else
        if ($type=='quote')
        {
            $post['ord_type']=1;
            $post['ord_addon']= formatDate();
            $post['ord_addby']= loged_user('username');
            $type='orders';
            $post['_msg_ok']=lang('orders.quotes_msg_save_ok');
        }else
        if ($type=='opport')
        {
            if (array_key_exists('ordid', $post) && !is_numeric($post['ordid']))
            {
                $post['ord_addon']= formatDate();
                $post['ord_addby']= loged_user('username');
                $post['ord_status']=$this->model_Orders->getOrdersSettings('orders_oport_status_def');
                
            }
            $post['ord_type']=0;
            $parts=[];
            if (array_key_exists('parts', $post) && is_array($post['parts']))
            {
                $parts=$post['parts'];
                unset($post['parts']);
            }
            
            $type=$this->model_Orders->save($post);
            if (count($parts) > 0 && $type)
            {
                $post['parts']=$parts;
                foreach($post['parts'] as $key=>$part)
                {
                    if (array_key_exists('data', $part))
                    {
                        $post['parts'][$key]['data']= json_decode(base64_decode($part['data']),TRUE);
                        if (!is_array($post['parts'][$key]['data']) || (is_array($post['parts'][$key]['data']) && count($post['parts'][$key]['data']) < 1))
                        {
                            goto end_parts_loop;
                        }
                       
                        $post['parts'][$key]=
                        [
                            'ol_ref'=>$post['ord_ref'],
                            'ol_oepart'=>$post['parts'][$key]['data']['prd_tecdocpart'],
                            'ol_partdesc'=>$post['parts'][$key]['data']['prd_description'],
                            'ol_partbrand'=>$post['parts'][$key]['data']['prd_brand'],
                            'ol_qty'=>$post['parts'][$key]['qty'],
                            'ol_cusprice'=> array_key_exists('value', $post['parts'][$key]) ? $post['parts'][$key]['value'] : 0,
                            'ol_price'=>$this->model_Products_PriceFilePart->getPartPriceForCustomer($post['ord_cusacc'],$post['parts'][$key]['data']['prd_apdpartnumber']),
                            'ol_cusacc'=>$post['ord_cusacc'],
                            'ol_ourpart'=>$post['parts'][$key]['data']['prd_apdpartnumber'],
                            'ol_commodity'=>$post['parts'][$key]['data']['prd_commodity'],
                            'ol_origin'=>$post['parts'][$key]['data']['prd_origin'],
                            'enabled'=>1
                        ];
                        if (array_key_exists('id', $post['parts'][$key]))
                        {
                           $post['parts'][$key]['olid']=$post['parts'][$key]['id'];
                        }
                        $this->model_Lines->save($post['parts'][$key]);
                    }
                    end_parts_loop:
                }
            }
            if (!$type)
            {
                return redirect()->to($refurl)->with('error', $this->createMessage($this->model_Orders->errors(), 'danger'))->with('_postdata', $post);
            }
            if(Arr::KeysExists(['ord_cusacc','ord_ref','ord_refcus','ordid'], $post) && !is_numeric($post['ordid']))
            {
                $post['ord_desc']=url_tag(url($this,'opportunities',[$this->model_Orders->getLastID()],['refurl'=>'-curl-']), $post['ord_ref']);
                $this->addMovementHistory('oport_create', $post['ord_ref'], $post['ord_refcus'], $post['ord_cusacc'],$post['ord_desc']);
            }
            return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_save_ok', 'success'));
        }
        //dump($post);exit;
        return parent::save($type,$post);
    }
    
    
    public function save1($type, $post = null) 
    {
        $post = $post == null ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
        if ($type=='send')
        {
            if (!array_key_exists('confirm_form_id', $post))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_order_id','danger'));
            }
            
            if (!Arr::KeysExists(['mail_to','mail_subject','mail_body'], $post))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_confirm_fields_notvalid','warning'));
            }
            
            $attachements=$this->model_Orders->generateOrderFile($post['confirm_form_id'],'xlsx');
            if (!file_exists($attachements))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_orderfile_notvalid','danger'));
            }
            if ($this->sendEmail([loged_user('name'),loged_user('email')], explode(';',$post['mail_to']), $post['mail_subject'], $post['mail_body'],[],[],[$attachements]))
            {
                if (file_exists($attachements))
                {
                    unlink($attachements);
                }
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.msg_order_send','success'));
            } else 
            {
                if (file_exists($attachements))
                {
                    unlink($attachements);
                }
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_order_send_failed','danger'));
            }         
        }else
        if ($type=='confirm')
        {
            if (!array_key_exists('confirm_form_id', $post))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_order_id','danger'));
            }
            
            if (!Arr::KeysExists(['mail_to','ref','mail_subject','mail_body'], $post))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_confirm_fields_notvalid','warning'));
            }
            
            if ($this->model_Orders->setOrderAsConfirmed($post['confirm_form_id']))
            {
                $this->model_Orders->setOrderRef($post['confirm_form_id'],$post['ref']);
                $attachements=$this->model_Orders->generateOrderFile($post['confirm_form_id'],'xlsx');
                if (!file_exists($attachements))
                {
                    return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_orderfile_notvalid','danger'));
                }
                
                if ($this->sendEmail([loged_user('name'),loged_user('email')], explode(';',$post['mail_to']), $post['mail_subject'], $post['mail_body'],[],[],[$attachements]))
                {
                    if (file_exists($attachements))
                    {
                        unlink($attachements);
                    }
                    return redirect()->to($refurl)->with('error',$this->createMessage('orders.msg_order_confirmation','success'));
                }else
                {
                    if (file_exists($attachements))
                    {
                        unlink($attachements);
                    }
                    return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_order_conf_sendfailed','danger'));
                }
            } else 
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_confirm_fields_notvalid','warning'));
            }
        }else
        if ($type=='upload')
        {
            $post['_export_justname']=TRUE;
            $this->uploadFiles($post);
            
            if (!array_key_exists('parts_file', $post) && !array_key_exists('parts_data', $post))
            {
                missing_error:
               return redirect()->to($refurl)->with('error',$this->createMessage('orders.error_upload_missing_field','danger')); 
            }
            
            if (array_key_exists('parts_data', $post))
            {
                $post['parts_data']= json_decode($post['parts_data'],TRUE);
                $post['parts_file']=$post['parts_data'];
                unset($post['parts_data']);
                goto add_other_data;
            }
            
            if (!file_exists(parsePath($post['parts_file'],TRUE)))
            {
                goto missing_error;
            }
            
            add_other_data:
            $post['ord_addon']= formatDate();
            $post['ord_addby']= loged_user('username');
            $post['enabled']= 1;
            if (!array_key_exists('ord_isquote', $post))
            {
                $post['ord_isquote']=1;
            }
            
            
            $post['_add_task']=$this->model_Settings->get('orders.orders_placeinbackground');
            
            if (is_string($post['parts_file']))
            {
                $post['parts_file']= parsePath($post['parts_file'],TRUE);
            }
            if ((is_string($post['parts_file']) && filesize($post['parts_file']) > 4000)|| $post['_add_task'])
            {
                add_task:
                $post['_add_task']=1;
                $post['enabled']=0;
                $post['ord_source']='upload';
                if (intval(loged_user('iscustomer'))==0)
                {
                    $post['_msg_ok']='orders.'.(intval($post['ord_isquote'])==1 ? 'msg_uploadquote_bigfile' : 'msg_uploadorder_bigfile');
                }else
                {
                    $post['_msg_ok']='orders.'.(intval($post['ord_isquote'])==1 ? 'msg_uploadquote_bigfile_cus' : 'msg_uploadorder_bigfile_cus');   
                }
            }else
            if ((is_array($post['parts_file']) && count($post['parts_file']) > 150) || $post['_add_task'])
            {
                $post['parts_file']=['_file'=>parsePath('@temp/'.Str::createUID().'.json'),'_data'=>$post['parts_file']];
                file_put_contents($post['parts_file']['_file'], json_encode($post['parts_file']['_data']));
                $post['parts_file']=$post['parts_file']['_file'];
                goto add_task;
            }
            if (array_key_exists('customer', $post))
            {
                $post['ord_cusacc']=$post['customer'];
                unset($post['customer']);
            }else
            if (loged_user('iscustomer'))
            {
                $post['ord_cusacc']= $this->model_Customers_Customer->getCustomerForLogedUser();
            }
            
            if (!array_key_exists('ord_cusacc', $post) || (strlen($post['ord_cusacc']) < 1))
            {
                if (is_string($post['parts_file']) && file_exists($post['parts_file']))
                {
                    unlink($post['parts_file']);
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_orderupload_acc','danger'));
            }
            
            if (array_key_exists('reference', $post))
            {
                $post['ord_refcus']=$post['reference'];
                $post['ord_ref']=$post['reference'];
                unset($post['reference']);
            }
            
            if (strlen($post['ord_refcus']) < 1)
            {
                if (is_string($post['parts_file']) && file_exists($post['parts_file']))
                {
                    unlink($post['parts_file']);
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_orderupload_refempty','warning'));
            }
            
            if ($this->model_Orders->count(['ord_ref'=>$post['ord_refcus'],'|| ord_refcus'=>$post['ord_refcus']]) > 0)
            {
                if (is_string($post['parts_file']) && file_exists($post['parts_file']))
                {
                    unlink($post['parts_file']);
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage(lang('orders.error_orderupload_refexists',[$post['ord_refcus']]),'warning'));
            }
            
            if (intval($post['ord_isquote'])==1)
            {
                $post['ord_quoteref']=$post['ord_ref'];
            }
            $type='order';
        }else
        if ($type=='update')
        {
            if (Arr::Exists(['ord_ref','lines'], $post) && is_array($post['lines']))
            {
                if (array_key_exists('delete', $post['lines']))
                {
                    $this->model_Lines->filtered(['olid In'=>$post['lines']['delete']])->builder()->delete();
                    unset($post['lines']['delete']);
                }
                if (array_key_exists('new', $post['lines']))
                {
                    $post['lines']=$post['lines']+$post['lines']['new'];
                    unset($post['lines']['new']);
                }
                $this->model_Lines->updateOrder($post['lines'],$post['ord_ref']);
                
            }
            unset($post['lines']);
            $type='order';
        }
        return  parent::save($type, $post);
    }

    function _after_save($type, $post, $refurl, $refurl_ok): bool 
    {
        if (array_key_exists('ord_type', $post) && $post['ord_type']==1)
        {
            if (Arr::KeysExists(['ord_prdsource','_id'], $post) && !array_key_exists('ordid', $post))
            {
                $post['ord_prdsource']= json_decode(base64_decode($post['ord_prdsource']),TRUE);
                if (is_array($post['ord_prdsource']) && count($post['ord_prdsource']) > 0)
                {
                    $post['ord_prdsource']=$this->model_Emails_Email->saveAttachement($post['ord_prdsource'][0],$post['ord_prdsource'][1],$post['ord_prdsource'][2],'onedrive');
                    if (is_array($post['ord_prdsource']))
                    {
                        $this->model_Orders->save(['ordid'=>$post['_id'],'ord_prdsource'=> json_encode($post['ord_prdsource'])]);
                    }
                }
            }
            
            if (Arr::KeysExists(['ord_prdsource','ordid','ord_ref','ord_refcus','ord_cusacc','ord_value'], $post))
            {
                $post['ord_desc']=url_tag(url($this,'quotes',[$post['ordid']],['refurl'=>'-curl-']), $post['ord_ref']);
                $this->addMovementHistory('quote_create', $post['ord_ref'], $post['ord_refcus'], $post['ord_cusacc'],$post['ord_desc']);
            }
            /*if (Arr::KeysExists(['parts','ord_ref'], $post))
            {
                $post['parts']= json_decode($post['parts'],TRUE);
                if (is_array($post['parts']) && count($post['parts']) > 0)
                {
                    $this->model_Lines->updateOrder($post['parts'],$post['ord_ref']);
                    $this->sendNotification($this->model_Settings->get('orders.settings_tplfilequoteupdate'), $post,$post['ord_cusacc']);
                }  
            }*/
        } else
        if ($type=='order' || $type=='model_order')
        {
            if (Arr::KeysExists(['parts_file','ord_ref','_add_task','ord_cusacc'], $post))
            {
                if (is_string($post['parts_file']) && Str::isJson($post['parts_file']))
                {
                    $post['parts_file']= json_decode($post['parts_file'],TRUE);
                }
                if (intval($post['_add_task'])==1)
                {
                 $this->model_Orders->addUploadTask($post['ord_ref'],$post['parts_file'],$post['ord_cusacc']);   
                }else
                {
                   $this->model_Orders->uploadOrder($post['ord_ref'],$post['parts_file'],$post['ord_cusacc']); 
                }
                
            }
        }
        return TRUE;
    }
    
    function api($command,array $post=[])
    {
        $result=null;
        if (!array_key_exists('ref', $post))
        {
            error_id:
            $result=['error'=>lang('orders.error_order_id')];goto end_func;
        }
        if ($command=='checkref')
        {
           $result=['data'=>TRUE];
           if ($this->model_Orders->count(['ord_ref'=>$post['ref']]) > 0)
           {
               $result=['error'=>lang('orders.error_ord_ref_exists')];
           }
        }else
        if ($command=='getinfo')
        {
            $result=$this->model_Orders->getForApi($post['ref'],loged_user('iscustomer')==0);
            if (!is_array($result))
            {
                goto error_id;
            }
            $result=['data'=>$result];
        }else
        if ($command==strtolower('getOrderTpl'))
        {
            $result=$this->model_Orders->filtered(['ordid'=>$post['ref'],'|| ord_ref'=>$post['ref']])->first();
            if (!is_array($result))
            {
                $result=['error'=>lang('orders.error_order_id')];goto end_func;
            }
            //
            $result=['data'=>$result,'title'=>lang('orders.ord_modalsend_title')];
            if (array_key_exists('template', $post) && $post['template'])
            {
                $template=$this->model_Documents_Report->parseEmailTemplate($this->model_Settings->get('emails.emails_newemailtpl'),[]);
                $result['template']=$template;
            }
        }else
        if ($command=='getOrderConf')
        {
            if (!array_key_exists('_ref', $post))
            {
                $result=['error'=>lang('orders.error_order_id')];goto end_func;
            }
            $result=$this->model_Orders->filtered(['ordid'=>$post['_ref']])->first();
            if (!is_array($result))
            {
                $result=['error'=>lang('orders.error_order_id')];goto end_func;
            }
            $result=['data'=>$result,'title'=>lang('orders.ord_modalconfirm_title')];
            if (array_key_exists('_template', $post) && $post['_template'])
            {
                $template=$this->model_Orders->getOrdersSettings('orders_tplfileorderconfirm');
                $template=$this->model_Documents_Report->parseEmailTemplate($template,$result['data']);
                $result['template']=$template;
            }
            
        }else
        if ($command=='updateline')
        {
            if (!array_key_exists('data', $post) || (array_key_exists('data', $post) && !is_array($post['data'])))
            {
                $result=['error'=>lang('orders.error_orderline_save')];goto end_func;
            }
            if (Arr::isAssoc($post['data']))
            {
                $post['data']=[$post['data']];
            }
            
            if ($this->model_Lines->updateOrder($post['data'],$post['ref']))
            {
               $result=['msg'=>lang('orders.msg_orderline_save_ok')];goto end_func; 
            }else
            {
                $result=['error'=>lang('orders.error_orderline_save')];goto end_func;
            }
        }
        
        end_func:
        return $result;
    }
    
    function settings($tab,$record)
    {
        $settings=$this->model_Settings->get('orders.*',FALSE,'*');
        $view=new Pages\FormView($this);
        $tpls=$this->model_Documents_Report->getTemplatesForForm();
        $args=['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE))])];
        
        if ($tab=='cfg')
        {
            //$view->addDropDownField('orders.settings_newquotetpl', 'settings[orders_newquotetpl]', $this->model_Customers_TicketTemplate->getForDropDown(), $settings['orders_newquotetpl']['value'],['advanced'=>TRUE]);
            $view->addDropDownField('orders.settings_sendnewordertoapi', 'settings[orders_apiplaceorder]', $this->model_Orders->getApiPlacingSettings(), $settings['orders_apiplaceorder']['value']);
            
            $view->addYesNoField('orders.settings_placeinbackground', $settings['orders_placeinbackground']['value'],'settings[orders_placeinbackground]');
            $view->addYesNoField('orders.settings_autoorderconfirm', $settings['orders_autoorderconfirm']['value'],'settings[orders_autoorderconfirm]');
        }else
        if ($tab=='status')
        {
            foreach($settings as $key=>$value)
            {
                if (Str::startsWith($key,'orders_status_type_'))
                {
                    $view->addInputField('orders.status_'.$value['value'], 'settings['.$key.'][tooltip]',lang($value['tooltip']), []);
                }
            }
        }else
        if($tab=='tpls')
        {
            $args['url'].='&tab=tpls';
            $view->addYesNoField('orders.settings_notifyaboutorder', $settings['orders_notifyaboutorder']['value'],'settings[orders_notifyaboutorder]');
            $view->addCustomElementsListField('orders.settings_notifygroups', 'settings[orders_notifygroups]', $settings['orders_notifygroups']['value'], ['input_type'=>$this->model_Auth_UserGroup->getForForm('ugref','ugname')]);
            //Oport tpls
            $view->addDropDownEditableField('orders.settings_tplfileoportcreated','settings[orders_tplfileoportcreated]',$tpls,$settings['orders_tplfileoportcreated']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfileoportconfirm','settings[orders_tplfileoportconfirm]',$tpls,$settings['orders_tplfileoportconfirm']['value'],$args);
            
            $view->addDropDownEditableField('orders.settings_tplfilequotecreatenew', 'settings[orders_tplfilequotecreatenew]',$tpls, $settings['orders_tplfilequotecreatenew']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfilequoteupdate', 'settings[orders_tplfilequoteupdate]',$tpls, $settings['orders_tplfilequoteupdate']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfilequotecreate', 'settings[orders_tplfilequotecreate]',$tpls, $settings['orders_tplfilequotecreate']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfilequotecancel', 'settings[orders_tplfilequotecancel]',$tpls, $settings['orders_tplfilequotecancel']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfileordercancelconfirm','settings[orders_tplfileordercancelconfirm]',$tpls,$settings['orders_tplfileordercancelconfirm']['value'],$args);
            
            $view->addDropDownEditableField('orders.settings_tplfileordercreatenew', 'settings[orders_tplfileordercreatenew]',$tpls, $settings['orders_tplfileordercreatenew']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfileordercancel', 'settings[orders_tplfileordercancel]',$tpls, $settings['orders_tplfileordercancel']['value'], $args);
            $view->addDropDownEditableField('orders.settings_tplfileordercreatenew','settings[orders_tplfileordercreatenew]',$tpls,$settings['orders_tplfileordercreatenew']['value'],$args);
            $view->addDropDownEditableField('orders.settings_tplfileorderconfirm','settings[orders_tplfileorderconfirm]',$tpls,$settings['orders_tplfileorderconfirm']['value'],$args);
        }else
        if($tab=='oport')
        {
            $args['url'].='&tab=oport';
            $view->addDropDownField('orders.opportunities_cfg_status_def','settings[orders_oport_status_def]' , $this->model_Orders->getOportStatuses(), $settings['orders_oport_status_def']['value'], []);
            $view->addCustomElementsListField('orders.settings_partpicker', 'settings[orders_oport_partpickerfields]', $settings['orders_oport_partpickerfields']['value'], ['input_type'=>$this->model_Products_Product->getFieldsNamesForPicker()]);
            $view->addYesNoField('orders.opportunities_cfg_showourcost', $settings['orders_oport_showourcost']['value'], 'settings[orders_oport_showourcost]', []);
            $view->addYesNoField('orders.opportunities_cfg_convertsendtoapi', $settings['orders_oport_convertsendtoapi']['value'], 'settings[orders_oport_convertsendtoapi]', []);
        }
        return view('System/form_fields',$view->getViewData());
        
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
    
    private function lines($record,$mode='edit')
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        $mode='model_'.$mode;
        if (is_numeric($record))
        {
            $record=$this->model_Orders->filtered(['ordid'=>$record])->first();
            if(!is_array($record))
            {
                error_order_id:
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_order_id','danger'));
            }
             
        }else
        if (!is_array($record))
        {
            goto error_order_id;
        }
        if (!in_array($record['ord_type'], [1,2],))
        {
            goto error_order_id;
        }
        $record['parts']=[];
        
        $edit_acc= $this->hasAccess(AccessLevel::edit);
        $editable=$edit_acc;
        $settings=$this->model_Orders->getOrdersSettings();
        
        $toolbarButtons=
        [
            Pages\HtmlItems\ToolbarButton::create('fas fa-arrow-alt-circle-left', 'dark mr-2', 'system.buttons.back', null, null, ['data-url'=>$this->getRefUrl()]),
            //Pages\HtmlItems\ToolbarButton::createPrintButton('id_orderlines_print', 'print_container', ['orders.msg_orderprintinfo',$record], 'orders.btn_orderlines_print_tooltip', []),
            'download'=>Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-file-excel', 'info ml-2', url($this,'download',[$record['ordid'],'xlsx'],['refurl'=> current_url(FALSE,TRUE)]), 'orders.btn_downxlsx',null,['data-noloader'=>'true'])
        ];
        
        $this->view->addBreadcrumb('orders.sales',url($this));
        
        if (intval($record['ord_type'])==1)
        {
            $this->view->addBreadcrumb('orders.quotes_mainmenu',url($this,'quotes'))
                       ->addBreadcrumb($record['ord_ref'], current_url())
                       ->setFile('Orders/Quotes/quotes_lines')
                       ->addData('urlcancel','')
                       ->addData('urlsave',url($this,'save',['quotes'],['refurl'=> base64url_encode($this->getRefUrl())]));
            if (array_key_exists('ord_prdsource', $record) && $record['ord_prdsource']!='lines')
            {
                $record['parts']=$this->model_Documents_Document->filtered(['did'=>$record['ord_prdsource'],'|| doc_name'=>$record['ord_prdsource']])->first();
                if (is_array($record['parts']))
                {
                    if (Str::contains(strtolower($record['parts']['doc_type']), 'pdf'))
                    {
                        $record['parts']=
                        [
                            'type'=>'pdf',
                            'url'=>url('Documents','show',[0],['file'=> base64url_encode($record['ord_prdsource'])])
                        ];
                    }
                    $record['_pdfurl']=url('Pages','download.html',[],['file'=>base64url_encode($record['ord_prdsource'])]);
                    $toolbarButtons['download']=Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-file-download', 'info ml-2', url('Pages','download.html',[],['file'=>base64url_encode($record['ord_prdsource']),'tracked'=>base64url_encode($record['ord_cusacc'].';'. loged_user('username'))]), 'orders.quotes_btn_download',null,['data-noloader'=>'true']);
                }
                $editable=FALSE;
            }else
            {
                $record['parts']=$this->model_Lines->getForOrder($record['ord_ref']);
            }
        }else
        {
            $this->view->addBreadcrumb('orders.mainmenu',$refurl)
                       ->addBreadcrumb($record['ord_refcus'], current_url());
        }
        
        if ($edit_acc)
        {
            $toolbarButtons[]= Pages\HtmlItems\ToolbarButton::createModalStarter('quotes_sendemailmodal', 'fas fa-share-square', 'warning mr-1', 'orders.quotes_btn_email', null, []);
            if (intval($record['ord_done'])==0)
            {
                if (array_key_exists('_pdfurl', $record))
                {
                    $record['url_download']=$record['_pdfurl'].'&tracked='. base64url_encode($record['ord_cusacc'].';'.$record['ord_cus_emails']);
                }else
                {
                    $record['url_download']=url('Pages','download.html',[],['file'=> base64url_encode($record['ordid']),'tracked'=>base64url_encode($record['ord_cusacc'].';'.$record['ord_cus_emails'])]);
                }
                $record['_email']=$this->model_Documents_Report->parseEmailTemplate($settings['orders_tplfilequotecreatenew'],$record);
                
                $record['_email']=
                [
                    'mail_to'=>$record['ord_cus_emails'],
                    'mail_subject'=>$record['_email']['subject'],
                    'mail_body'=>$record['_email']['body'],
                    'mail_from'=> $this->model_Emails_Mailbox->getDropdDownField('emm_inuser','emm_name',FALSE,TRUE)
                ];
                $this->view->addData('url_sendemail',url($this,'save',['sendemail'],['refurl'=>current_url(FALSE,TRUE)]));
            }
        }
        
        $record['is_error']= is_array($record['parts']) ? Arr::SumValues($record['parts'], 'ol_iserror') > 0 : FALSE;
        
        return $this->view->addData('record',$record)
                          ->addMenuBar($toolbarButtons,['background'=>'white'])
                          ->addData('order_status',$this->model_Orders->getAvaliableStatuses())
                          ->addData('url_payment',url($this,'update',['payment'],['refurl'=>current_url(FALSE,TRUE)]))
                          ->addData('url_invoice',url($this,'invoices',[$record['ord_invoicenr']],['refurl'=>current_url(FALSE,TRUE)]))
                          ->addData('edit_acc',$edit_acc)
                          ->addData('editable',$editable)
                          ->addPrintLibrary()
                          ->addInputMaskScript()
                          ->addEditorScript()
                          ->render();
    }
    
    private function sendtoapi($order,array $data,$redirect=FALSE)
    {
        if (is_string($order)|| is_numeric($order))
        {
            $order=$this->model_Orders->filtered([(is_numeric($order) ? 'ord_id' : 'ord_ref')=>$order])->first();
        }
        
        $result=TRUE;
        if (!is_array($order) || (is_array($order) && !Arr::KeysExists(['ord_cusacc','ord_refcus','ord_ref'], $order)))
        {
            $result=FALSE;
            goto end_of_fnc;
        }
        $set=$this->model_Orders->getOrdersSettings('orders_apiplaceorder');
        if ($set!=0 && $set!='0')
        {
                $set= base64_decode($set);
                $set= json_decode($set,TRUE);
                if (is_array($set))
                {
                    $set['args']=
                    [
                       $order['ord_cusacc'],
                       $order['ord_refcus'],
                       $order['ord_ref'],
                       $data,
                       array_key_exists('_odrdesc', $order) ? $order['_odrdesc'] : ''
                    ];
                    loadModuleFromArray($set);
                }    
         }
         end_of_fnc:
         if ($redirect)
         {
             if ($result)
             {
                 return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.msg_autopart_set','success'));
             }else
             {
                 return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_autopart_set','danger'));
             }
         }else
         {
             return $result;
         }
    }
    
    private function validateOrder($record)
    {
        $post=$this->request->getPost();
        if (is_array($post) && array_key_exists('lines', $post) && is_array($post['lines']) && count($post['lines']) > 0)
        {
            $this->model_Lines->updateBatch($post['lines'],'olid');
        }
        if (is_array($post) && array_key_exists('remove', $post) && is_array($post['remove']) && count($post['remove']) > 0)
        {
            $this->model_Lines->orWhereIn('olid',$post['remove'])->delete();
        }    
            if ($this->model_Lines->validateData($record['ord_ref']))
            {
                if ($this->model_Orders->enableOrder($record['ord_ref']))
                {
                    $set=$this->model_Orders->getOrdersSettings();
                    if (array_key_exists('orders_notifyaboutorder', $set) && intval($set['orders_notifyaboutorder'])==1)
                    {
                        
                    }
                    if (array_key_exists('orders_apiplaceorder', $set) && ($set['orders_apiplaceorder']!=0 && $set['orders_apiplaceorder']!='0'))
                    {
                        $this->download($record['ordid'],'sendtoapi',FALSE);
                    }
                }
                //
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.msg_validation_ok','success'));
            }else
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_validation_failed','warning'));
            }
    }
    
    private function convertquote($record)
    {
        $record=$this->model_Customers_Ticket->find($record);
        if (!is_array($record) || (is_array($record) && !Arr::KeysExists(['tck_type','tck_extrafields'],$record)))
        {
            error:
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('tickets.error_case_id','danger'));
        }
        $record['tck_type']=$this->model_Customers_TicketType->getConversionPath($record['tck_type'],$record['tiid']);
        if ($record['tck_type']==FALSE)
        {
            goto error;
        }
        $record['tck_extrafields']= json_decode($record['tck_extrafields'],TRUE);
        if (!is_array($record['tck_extrafields']))
        {
            goto error;
        }
        if (!array_key_exists('partslist', $record['tck_extrafields']) || (array_key_exists('partslist', $record['tck_extrafields']) && !array_key_exists('value', $record['tck_extrafields']['partslist'])))
        {
            error_convert:
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('tickets.error_convert_to_quote_failed','danger'));
        }
        $record['tck_extrafields']=$record['tck_extrafields']['partslist']['value'];
        if (!is_array($record['tck_extrafields']))
        {
            goto error_convert;
        }
        
        $record=$this->model_Quote->createQuote('QUOTE'.$record['tck_account'].formatDate(),$record['tck_account'],'Ticket_'.$record['tiid'],$record['tck_extrafields']);
        if ($record==FALSE)
        {
            goto error_convert;
        }
        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage(lang('tickets.msg_convert_to_quote_ok',[url_tag(url($this,'quotes',[$record]),'orders.quote_link')]),'success'));
    }
    
    private function convertorder($record)
    {
        $record=$this->model_Orders->find($record);
        $post=$this->request->getPost();
        if (!is_array($record))
        {
            error_id:
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_quote_id','danger'));
        }
        if (!array_key_exists('ord_refcus', $post))
        {
            goto error_id;
        }
        $settings=$this->model_Settings->get('orders.*');
        $record['ord_refcus']=$post['ord_refcus'];
        $record['ord_status']=$settings['orders_status_type_placed'];
        $record['ord_isquote']=0;
        $record['ord_ref']=$this->model_Orders->generateCustomerOrderNr('',TRUE);
        $record['ord_source']=(strlen($record['ord_source']) > 0 ? $record['ord_source'].',' : '').'Quote:'.$record['ord_quoteref'];
        if ($this->model_Orders->save($record))
        {
            $this->sendNotification($settings['orders_tplfilequotecreate'], $record);
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage(lang('orders.msg_quote_convert',[ url_tag(url($this,'customers',[$record['ordid']]), $record['ord_ref'])]),'success'));
        }
        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('orders.error_quote_convert','danger'));
    }
    
    function addMovement($type,string $ordRef) 
    {
        $this->addMovementHistory($mhtype, $mhfrom, $mhto, $mhref, $mhinfo, $type, $user);
    }
}