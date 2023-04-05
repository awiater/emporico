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
use \EMPORIKO\Helpers\MathHelper as Math;
use \EMPORIKO\Libraries\XLSXWriter;

class Products extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'parts'=>               AccessLevel::view,
        'part'=>                AccessLevel::edit,
        'upload'=>              AccessLevel::modify,
        'pricefiles'=>          AccessLevel::modify,
        'pricefile'=>           AccessLevel::edit,
        'brands'=>              AccessLevel::modify,
        'brand'=>               AccessLevel::edit,
        'downloadfile'=>        AccessLevel::view,
        'download'=>            AccessLevel::view,
        'index'=>               AccessLevel::view,
        'getPriceFileTile'=>    AccessLevel::view,
        'suppliers'=>           AccessLevel::edit,
        'supplier'=>            AccessLevel::edit,
        'getReportSources'=>    AccessLevel::settings,
        'validatepart'=>        AccessLevel::view,
        'getDashboardTile'=>    AccessLevel::view,
        'info'=>                AccessLevel::view,
    ];


    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = ['downloadfile','pricing','download'];


    /**
     * Array with function names and linked models names
     */
    public $assocModels = 
    [
        'brands'=>'Products/Brand',
        'pricing'=>'Products/PriceFile',
        'pricepart'=>'Products/PriceFilePart',
        'supp'=>'Products/Supplier',
        'product'=>'Products/Product',
        'updates'=>'Products/BrandUpdate',
    ];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actuall function name)
     */
    public $remaps = 
    [
        'index'=>'parts'
    ];
    
    /**
     * Array with available menu items (keys as function names and values as description)
     * @var Array
     */
    public $availablemenuitems = [];
    
    function suppliers($record=null,$mode='edit')
    {
        if ($record!=null&&$mode=='edit')
        {
            return $this->supplier($record);
        }
        
        if ($mode=='contacts')
        {
            return loadModule('Customers','contact',[$record]);
        }
        
        $filters=[];
        if ($record==null)
        {
            $record=$this->model_Supp->getFirstID();
        }
        
        if ($this->request->getPost('filter_part')!=null)
        {
            $filters['supid']=$this->request->getPost('filter_part');
        }else
        {
            $filters['supid']=$record;
        }
        
        $record=$this->model_Supp->filtered($filters)->first();
        $_nodata=FALSE;
        if (!is_array($record))
        {
            $_nodata=TRUE;
            $record=$this->model_Supp->getNewRecordData(TRUE);
        }
        $record['contacts']=$this->model_System_Contact->getByAcc($record['sup_code']);
        $record['sup_contactnr_list']=[$record['sup_contactnr']=>$record['sup_name'].' - '.$record['sup_contactnr']];
        foreach($record['contacts'] as $contact)
        {
            if (strlen($contact['ct_phone']) > 0)
            {
                $record['sup_contactnr_list'][$contact['ct_phone']]=$contact['ct_name'].' - '.$contact['ct_phone'];
            }
        }
        $nav=$this->model_Supp->getPrevNextID($record['supid']);
        $toolbar_buttons=[];
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        
        if ($this->hasAccess(AccessLevel::settings))
        {
            $toolbar_buttons['settings']=Pages\HtmlItems\ToolbarButton::createModuleSettingsButton($this->getModuleSettingsUrl('sup'),null,'secondary');
            $toolbar_buttons['upload']= Pages\HtmlItems\UploadButton::create('products_suppliers', 'id_suppview_btn_upload', null, null, 'products.sup_btnupload');
        }
        $toolbar_buttons['print']=Pages\HtmlItems\ToolbarButton::create('fas fa-print', 'secondary', 'products.sup_print', 'id_suppliers_btn_print');
        if (!$_nodata)
        {
        //$toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create($icon, $color, $tooltip, $id, $href, $args);
            $toolbar_buttons['customer_list']= Pages\HtmlItems\DropDownField::create()->setClass('form-control form-control-sm')
                            ->setName('filter_part')
                            ->setID('id_suppliers_filter_part')
                            ->setOptions($this->model_Supp->getAccountList('supid'))
                            ->setAsAdvanced()
                            ->addArg('style', 'min-width:350px;')
                            ->setValue($filters['supid']);
            $toolbar_buttons['customer_prev']=Pages\HtmlItems\ToolbarButton::create('fas fa-chevron-circle-left','dark ml-1', 'system.buttons.nav_prev', 'id_suppliers_btn_prev', null, ['data-url'=>url($this,'suppliers',[$nav['prev'],'view'],[])]);
            $toolbar_buttons['customer_next']=Pages\HtmlItems\ToolbarButton::create('fas fa-chevron-circle-right','dark', 'system.buttons.nav_next', 'id_suppliers_btn_next', null, ['data-url'=>url($this,'suppliers',[$nav['next'],'view'],[])]);
        }
        
        if ($this->hasAccess(AccessLevel::create))
        {
            $toolbar_buttons['new']= Pages\HtmlItems\ToolbarButton::createNewButton([$this,'suppliers'], 'id_suppview_btn_new', null,['class'=>'ml-2']);//
            
        }
        if (!$_nodata && $edit_acc)
        {
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createEditButton([$this,'suppliers',[$record['supid'],'edit']],'id_suppview_btn_edit');
            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::createDataUrlButton('far fa-file', 'warning ml-2',url('Documents','files',[$record['sup_code'],'new'],['refurl'=> current_url(FALSE,TRUE)]),'documents.btn_addnew');
        
            $this->view->addData('url_cts_edit',url('Connections','contact',['-id-'],['refurl'=> current_url(FALSE,TRUE)]))
                       ->addData('url_cts_del',url('Connections','deletesingle',['contacts','-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                       ->addData('url_cts_email',url('Emails','compose',['contact','-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                       ->addData('filesform',loadModule('Documents','filesform',[$record['sup_code'],[],[['doc_name','doc_desc']],'id_suppinfo_filestable']))
                       ->addData('curr_icon',$this->model_Settings->getCurrencyIcons($record['sup_currency'],TRUE));
        }
        
        if (!$_nodata)
        {
           $toolbar_buttons['record_call']= Pages\HtmlItems\ToolbarButton::create('fas fa-phone-volume', 'success', 'products.sup_call', 'id_suppliers_btn_call', null, ['data-phone'=>$record['sup_contactnr']]);
        }
        
        if ($this->model_Supp->isEmailEnabled($record['sup_ordermode']))
        {
           $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::createModalStarter('supp_sendordermodal', 'fas fa-fax', 'primary', 'products.btn_suppsendorder');
           $this->view->addData('orderemail_modal',url($this,'save',['sendorder'],['refurl'=> current_url(FALSE,TRUE)]))
                      ->addData('orderemail_tpl',$this->model_Documents_Report->parseEmailTemplate($this->model_Settings->get('products.products_supporderemailtpl'),[]))
                      ->addEditorScript(FALSE,'email','#supp_sendordermodal_form_mail_body');
        }
        
        $this->view->addData('record',$record)
                ->addMenuBar($toolbar_buttons)
                ->addBreadcrumb('products.sup_mainmenu',current_url())
                ->addData('brands',$this->model_Brands->getBrandsDataForSupplier($record['sup_code']))
                ->addData('url_filter',url($this,'suppliers',['-id-','view'],[]))
                ->addData('edit_acc',$edit_acc)
                ->addData('_nodata',$_nodata)
                ->addData('url_contacts_new',url('Connections','contacts',['new',$record['sup_code']],['track'=>$record['sup_code'],'refurl'=>current_url(FALSE,TRUE)]))
                ->addData('_record_call_form', loadModule('Connections','recordcall',[['callernumber'=>$record['sup_contactnr_list'],'call_target'=>$record['sup_code'],'call_modalinit'=>TRUE]]))
                ->addData('movements',$this->model_Supp->getMovements($record['sup_code']))
                ->addData('sup_orderdays_list', array_combine(['ADHOC','MON','TUE','WED','THU','FRI','SAT','SUN'], lang('products.sup_orderdays_list')))
                ->addData('sup_orderdays_list_tooltip',array_combine(['ADHOC','MON','TUE','WED','THU','FRI','SAT','SUN'],lang('products.sup_orderdays_list_tooltip')))
                ->addData('movements', loadModule('Home','movementsTable',[$record['sup_code'],['addlog'=>['button'=>TRUE,'ref'=>$record['sup_code']]]]))
                ->addPrintLibrary()
                ->addCountryFlags()
                ->addSelect2();
        return $this->view->setFile('Products/sup_index')->render();
    }
    
    function supplier($record)
    {        
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if(is_numeric($record))
        {
            $record=$this->model_Supp->find($record);              
        }else
        if (!is_array($record))
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
            $record=$this->model_Supp->getNewRecordData(TRUE);
        }
        
        $record['edit_acc']=TRUE;//$this->hasAccess(AccessLevel::edit);
        
        $this->setFormView('Products/sup_edit')
                ->setFormTitle('customers.accounts_editform')
		->setPageTitle('customers.accounts_editform')
		->setFormAction($this,'save',['supp'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Supp->primaryKey=>$record[$this->model_Supp->primaryKey]
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('products.sup_mainmenu',current_url())
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['sup_name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->setTab('ordersnotes','products.tab_ordersnotes')
                ->setTab('other','products.tab_other')
                ->addFieldsFromModel('supp',$record,'products.-key-')
                ->addSelect2('.select2')
                ->addCountryFlags();
            return $this->view->render();
        }
    
    /*
     * Parts/Products Section
     */
    function parts($record=null,$mode='view')
    {
        if ($mode=='edit')
        {
            if (!is_numeric($record))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.error_edit_id','danger'));
            }
            return $this->part($record);
        }
        
        if ($mode=='disable' || $mode=='enable')
        {
            if (!is_numeric($record))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.error_edit_id','danger'));
            }
            return $this->enablesingle('product', $record, $mode=='disable' ? 0 : 1);
        }
        
        $post=$this->request->getPost();
        $filtered=FALSE;
        if (is_array($post) && array_key_exists('filter_part', $post))
        {
            $this->view->addData('_filter_value',$post['filter_part']);
            $fixed=
            [
                'brand'=>'prd_brand',
                'commodity'=>'prd_commodity %',
                'tecdocpart'=>'prd_tecdocpart %',
                'tecdocid'=>'prd_tecdocid'
            ];
            if (Str::startsWith($post['filter_part'], array_keys($fixed)) && Str::contains($post['filter_part'], '='))
            {
               $record=[$fixed[Str::before($post['filter_part'],'=')]=>str_replace(array_keys($fixed)+['='], '', $post['filter_part'])]; 
            } else 
            {
                $record=['( prd_apdpartnumber %'=>$post['filter_part'],'|| prd_tecdocpart % )'=>$post['filter_part']];
            }
            $filtered=TRUE;
        }
        
        if (is_array($mode) && Arr::KeysExists(['brand','showpricefile'], $mode))
        {
            if (!is_numeric($mode['brand']))
            {
                if (is_array($record))
                {
                    $record['prd_brand']=$mode['brand'];
                }else
                {
                    $record=['prd_brand'=>$mode['brand'],'@limit'=>1];
                }
            }
        }
        $record=['prid'=>$record,'enabled'=>'1'];
        if (!is_array($record))
        {
            $record=$record==null || $record=='null'? $this->model_Product->getFirstID() : $record;
            $record=$this->model_Product->find($record);
        }else
        {
            $record=$this->model_Product->filtered($record)->find();
            
            if (count($record)==0)
            {
                $record=$this->model_Product->find($this->model_Product->getFirstID());
                $this->addWarningMsgData('products.error_filter_no_results');
            }else
            if (count($record) > 0 && count($record)< 2)
            {
                $record=$record[0];
            }else
            if (count($record) > 1)
            {
                $record[0]['_filters']=$record;
                $record=$record[0];
            }    
            
        }
        $edit_acc=$this->hasAccess(AccessLevel::edit);
        //TRWPFB425
        if (!is_array($record))
        {
            if ($filtered)
            {
                $this->addWarningMsgData('products.error_filter_no_results');
            }
            $record=$this->model_Product->filtered(['prid'=>$this->model_Product->getFirstID()])->first();
        }
        
        $this->session->setFlashdata('_part_record',$record);
        $nav=$this->model_Product->getPrevNextID($record['prid']);
        
        if (is_array($mode))
        {
            $toolbar_buttons=[];
            $viewFile='Products/parts_customer';
            if (array_key_exists('showbasket', $mode) && intval($mode['showbasket']) > 0)
            {
                $post=$this->request->getPost();
                $mode['showbasket']=$this->session->get('_quotebasket');
                $mode['showbasket']=!is_array($mode['showbasket']) ? [] : $mode['showbasket'];
                if (array_key_exists('basket_ref', $post))
                {
                    if (array_key_exists('basket_line', $post))
                    {
                        $post['basket_line']= json_decode(base64_decode($post['basket_line']),TRUE);
                    }
                    if (!is_array($post['basket_line']))
                    {
                        $this->view->addData('msg_alert',$this->createMessage('products.error_empty_basket','danger'));
                    }
                    $notify=
                    [
                        'customer'=>$this->model_Customers_Customer->getCustomerForLogedUser('code'),
                        'reference'=>$post['basket_ref'],
                        'lines'=>Arr::toTable($post['basket_line'],[lang('products.prd_apdpartnumber'),lang('products.prd_qty'),lang('products.prd_price',[''])]),
                    ];
                    $notify['id']=$this->model_Orders_Order->createOportunity($notify['reference'],$notify['customer'],'soource',$post['basket_line']);
                    if ($notify['id']!=FALSE)
                    {
                        $this->session->remove('_quotebasket');
                        $mode['showbasket']=[];
                        $this->sendNotification($this->model_Settings->get('orders.orders_tplfileoportcreated'), $notify,$this->model_Orders_Order->getNotificationEmails());
                        $this->view->addData('msg_alert',$this->createMessage('orders.msg_uploadquote_bigfile_cus','success'));
                    }else
                    {
                        $this->view->addData('msg_alert',$this->createMessage($this->model_Orders_Order->errors(),'danger'));
                    }
                            
                }else
                if (array_key_exists('basket_part_del', $post))
                {
                    if (array_key_exists($post['basket_part_del'], $mode['showbasket']))
                    {
                        unset($mode['showbasket'][$post['basket_part_del']]);
                        $this->session->setTempdata('_quotebasket',$mode['showbasket'],300);
                    }
                }else
                if (Arr::KeysExists(['basket_qty','basket_part'], $post))
                {
                    if (array_key_exists($post['basket_part'], $mode['showbasket']))
                    {
                        $mode['showbasket'][$post['basket_part']]['qty']+=$post['basket_qty'];
                    }else
                    {
                        $mode['showbasket'][$post['basket_part']]=['part'=>$post['basket_part'],'qty'=>$post['basket_qty'],'price'=>$post['basket_price']];
                    }
                    $this->session->setTempdata('_quotebasket',$mode['showbasket'],300);
                }
                
                $this->view->addData('_quotebasket',count($mode['showbasket']) > 0 ? $mode['showbasket'] : 'products.msg_empty_basket');
            }
            goto add_toolbar;
        }
        
        $toolbar_buttons=[];
        if ($this->request->getGet('refurl')!=null)
        {
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createBackButton($this->getRefUrl());
        }
        
        if ($this->hasAccess(AccessLevel::settings))
        {
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-cogs', 'secondary', 'system.buttons.module_settings', 'id_module_cfg', $this->getModuleSettingsUrl(), []);
        }
        $toolbar_buttons[]= Pages\HtmlItems\ToolbarFilterField::create('filter_part',current_url(FALSE),null, 'secondary', 'products.filter_by_part', 'id_products_btn_search', ['pre_submit_func'=>"addLoader('.card');"]);
        $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-chevron-circle-left','dark ml-1', 'system.buttons.nav_prev', 'id_products_btn_prev', null, ['data-url'=>url($this,'parts',[$nav['prev'],'view'],[])]);
        $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-chevron-circle-right','dark', 'system.buttons.nav_next', 'id_products_btn_next', null, ['data-url'=>url($this,'parts',[$nav['next'],'view'],[])]);
       
        $viewFile='Products/parts_customer';
        if ($edit_acc)
        {
            $viewFile='Products/parts'; 
            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fa fa-fa fa-edit','primary ml-2', 'products.btn_partedit', 'id_products_btn_edit', null, ['data-url'=>url($this,'parts',[$record['prid'],'edit'],['refurl'=>current_url(FALSE,TRUE)])]);
            if (intval($record['enabled'])==1)
            {
                $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-eye-slash','danger', url($this,'parts',[$record['prid'],'disable'],['refurl'=> current_url(FALSE,TRUE)]), 'products.btn_enable_no', null, []);
            }else
            {
                $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-eye','success', url($this,'parts',[$record['prid'],'enable'],['refurl'=> current_url(FALSE,TRUE)]), 'products.btn_enable', null, []);
            }
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-cloud-upload-alt','dark', '$href', 'products.btn_updatebulk_all', null, []);

            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-download', 'success ml-3', 'products.download_pricefile', 'id_products_btn_download', null, ['data-action'=>url($this,'download',[],['brand'=>'-brand-','acc'=>'-customer-'])]);
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('far fa-file-archive', 'warning', 'products.btn_pricefiles', null, url($this,'pricefiles',[],['refurl'=> current_url(FALSE,TRUE)]), []);
                        $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-tasks', 'primary ml-2', 'products.btn_brands', 'id_products_btn_brands', null, ['data-url'=>url($this,'brands',[],['refurl'=>current_url(FALSE,TRUE)])]);
            
        }
       
        
        add_toolbar:
        if (count($toolbar_buttons) > 0)
        {
            $toolbar=new Pages\FormView($this);
            $toolbar->addButtonsToolBar('menu',$this->model_Settings->getButtonsForToolbar('parts*',$toolbar_buttons) , []);
            $this->view->addData('toolbar',view('System/Elements/toolbar',$toolbar->getViewData('fields.menu.args')));
            $upload=new Pages\FormView($this);
        
            $upload->addUploadField('products.modal_upload_file', 'file', null, ['accept'=>'.csv','upload_dir'=>'@storage/temp']);
            $upload->addCheckboxField('','products.modal_upload_bck', 'bck_upload', FALSE);
            $this->view->addData('upload_form',view('System/form_fields',$upload->getViewData()));
            $this->view->addData('url_upload',url($this,'upload',[],['refurl'=>current_url(FALSE,TRUE)]));
            
            $download=new Pages\FormView($this);        
            if($edit_acc)
            {
                $download->addDropDownField('products.filter_customer', 'customer', $this->model_Customers_Customer->getCustomersForDropDown(null,FALSE,TRUE),null,['class'=>'select2']);
            }else
            {
                $download->addHiddenField('customer', $this->model_Customers_Customer->getCustomerForUser(null,'cid'));
            }
            $download->addDropDownField('products.prd_brand', 'brand', $this->model_Brands->getBrands(TRUE,TRUE),$record['prd_brand'],['class'=>'form-control-sm mr-2 select2']);

            $this->view->addData('download_form',view('System/form_fields',$download->getViewData()));
        }
        
        
        $record['edit_acc']=$edit_acc;
        $curr_icons=$this->model_Settings->getCurrencyIcons();
        if (is_array($mode) && array_key_exists('pricefile', $mode) && $mode['pricefile']!='loged')
        {
            $record['customer']=$mode['pricefile'];
        }else
        {
            $record['customer']=$this->model_Customers_Customer->getCustomerForLogedUser('*');
        }
        if (is_array($record['customer']) && Arr::KeysExists(['terms_price','code'], $record['customer']))
        {
            $record['_orders']=$this->model_Product->getOrdersForProduct($record['prd_apdpartnumber'],$record['customer']['code']);
            $record['customer_curr']=$record['customer']['terms_curr'];
            $record['customer']=$record['customer']['terms_price'];
            if (array_key_exists($record['customer_curr'], $curr_icons))
            {
                $record['customer_curr_icon']=$curr_icons[$record['customer_curr']];
            }
        }else
        {
            $record['_orders']=$this->model_Product->getOrdersForProduct($record['prd_apdpartnumber']);
        }
        $record['mode']=$mode;
        if (is_array($mode) && Arr::KeysExists(['brand','showpricefile','pricefile'], $mode) && intval($mode['showpricefile'])==1)
        {
            $mode['pricefile']='@'.base64url_encode($record['customer']);
            
            if (!is_numeric($mode['brand']))
            {
                $this->view->addData('download_url',url($this,'download',[],['brand'=>$mode['brand'],'customer'=>$mode['pricefile']]));
            }else
            {
                $this->view->addData('download_url',
                [
                    'url'=>url($this,'download',[],['customer'=>$mode['pricefile'],'brand'=>'-brand-']),
                    'brands'=>$this->model_Brands->getBrands(TRUE,TRUE),
                    'field'=> Pages\HtmlItems\DropDownField::create()
                                ->setName('brand')
                                ->setID('brand')
                                ->setOptions($this->model_Brands->getBrands(TRUE,TRUE))
                                ->setAsAdvanced()
                                ->render()
                ]);
            }                           
        }  
        
        
        if ($edit_acc)
        {
            $record['pricefiles']=$this->model_PricePart->getPartPriceForPriceFiles('*',$record['prd_apdpartnumber']);
            $this->view->addData('price_label','products.prd_price');
        }else
        {
            $record['pricefiles']=$this->model_PricePart->getPartPriceForPriceFiles($record['customer'],$record['prd_apdpartnumber']);
            $this->view->addData('price_label','products.prd_price_cust');
        }
        
        
        $this->view->addData('url_filter', current_url(FALSE))
                   ->addData('record',$record)
                   //->addData('movements', $edit_acc ? $this->model_Files->getPriceFilesMovements($record['prd_brand']) : null )
                   ->addData('movements_types',['13'=>'products.download_pricefile_notify'])
                   ->addData('orderurl',url('Orders','customers',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                   ->addBreadcrumb('products.mainmenu',url($this))
                   ->addBreadcrumb($record['prd_apdpartnumber'],current_url())
                   ->addData('curr_icons',$curr_icons)
                   ->addSelect2('.select2');
        if (is_array($mode))
        {
            return view($viewFile,$this->view->getViewData());
        }
        return $this->view->setFile($viewFile)->render();
    }
    
    function part($record)
    {        
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        $record=$this->getFlashData('_part_record',$record);
        
        if (is_numeric($record))
        {
            $record=$this->model_Product->filtered(['prid'=>$record])->first();              
        }else
        if (!is_array($record))
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
            $record=$this->model_Product->getNewRecordData(TRUE);
        }
        $record['edit_acc']=TRUE;//$this->hasAccess(AccessLevel::edit);
        $this->setFormView()
                ->setFormTitle('customers.accounts_editform')
		->setPageTitle('customers.accounts_editform')
		->setFormAction($this,'save',['product'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Product->primaryKey=>$record[$this->model_Product->primaryKey]
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('customers.mainmenu',url($this))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['prd_apdpartnumber'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->setTab('tab_costs','products.tab_costs')
                ->setTab('tab_other','products.tab_other')
                ->addFieldsFromModel('product',$record,'products.-key-')//product
                ->addSelect2('.select2');
            return $this->view->render();
        }
    
    function upload($mode='upload')
    {
        if ($mode=='template')
        {
            $file=parsePath('@storage/temp/template.csv',TRUE);
            $file=Arr::toCSVFile([$this->model_Product->getPriceUpdateFileColumns()],$file,FALSE);
            header('Content-Disposition: attachment; filename="template.csv"');
            $this->response->setHeader('Content-Type','application/octet-stream');
            ob_clean();
            flush();
            readfile($file);
            unlink($file);exit;
        }
        $post=$this->request->getPost();
        
        $post['_export_justname']=TRUE;
        $post['_uploads_dir']=$this->model_Product->getPricingUpdateFolderPath();
        $insert=$this->model_Pricing->count()==0;
        $this->uploadFiles($post);
        $post['file']=parsePath($post['file'],TRUE);
       
        if (!file_exists($post['file']))
        {
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.error_upload_file','danger'));
        }
        if (array_key_exists('bck_upload', $post))
        {
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.msg_upload_bck_ok','success'));
        }
        $this->model_Product->updatePriceFileInBackground();
        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.msg_upload_ok','success'));
    }
        
    public function pricefiles($record=null)
    {
        if ($record!=null)
        {
            if ($record=='sendtoacc')
            {
                return $this->pricefile_sendtoacc(null, null,FALSE);
            }else
            if ($record=='import')
            {
                return $this->import('pricefiles');
            }
            return $this->pricefile($record);
        }
        $filters=['ppf_istmp'=>0];
        $this->setTableView()
                    ->setData('pricing',null,TRUE,null,$filters)
                    ->setPageTitle('products.pricefile_list')
                    //Fiilters settings
                    ->addFilters('pricefiles')
                    ->addFilterField('ppf_name %')
                    ->addFilterField('prf_cust %')
                    ->addFilterField('ppf_istmp',1,'products.ppf_istmp_filter')
                    ->addFixedFilterListDivider()
                    //Table Columns settings
                    ->addColumn('products.ppf_name','ppf_name',TRUE,[],null,'ppf_desc')
                    ->addColumn('products.ppf_pricingmode','ppf_pricingmode',TRUE,$this->model_pricing->getPricingModes()) 
                    ->addColumn('products.ppf_updated','ppf_updated',TRUE,[],'d M Y') 
                    ->addColumn('products.ppf_curr','ppf_curr',FALSE,'yesno')
                    //Breadcrumb settings
                    ->addBreadcrumb('products.mainmenu',url($this))
                    ->addBreadcrumb('products.pricefile_bread',url($this,'pricefiles'))
                    //Table Riows buttons
                    ->addEditButton('products.btn_partedit','pricefiles',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addNewButton($this->model_pricing->getPricingModes(TRUE))
                    ->addDeleteButton(AccessLevel::edit)
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)
                    ->addHeaderButton('Products::deletesingle/tmpfiles/all',null,'button','btn btn-danger btn-sm ml-1 tableview_def_btns','<i class="fas fa-dumpster-fire"></i>','products.ppf_istmp_delbtn',AccessLevel::settings,['data-actiontype'=>'delete','data-delmsg'=>'products.ppf_istmp_delbtnmsg'])
                    ->addHeaderButton('Products::pricefiles/import',null,'link','btn btn-dark btn-sm ml-3','<i class="fas fa-cloud-upload-alt"></i>','products.btn_importfiles',AccessLevel::view)
                    ->addHeaderButton('Products::parts',null,'link','btn btn-warning btn-sm ml-2','<i class="fas fa-car-battery"></i>','products.btn_parts',AccessLevel::view)
                    ->addHeaderButton('Products::brands',null,'link','btn btn-primary btn-sm ml-1','<i class="fa fa-fas fa-tasks"></i>','products.btn_brands',AccessLevel::view)
                    ->addHeaderButton('Settings::modules/1',null,'link','btn btn-secondary btn-sm ml-1','<i class="fas fa-cogs"></i>','products.btn_config',AccessLevel::settings);
        return $this->view->render();
    }
    
    private function pricefile($record)
    {
       
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Pricing->find($record);              
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
            $record=$this->model_Pricing->getNewRecordData(TRUE);
            if ($this->request->getGet('mode')!=null)
            {
                $record['ppf_pricingmode']=$this->request->getGet('mode');
            }
            $record['ppf_fields']=$this->model_Pricing->getDefaultColumnNames(TRUE);
        }else
        {
            $record['ppf_fields']= json_decode($record['ppf_fields'],TRUE);
            if ($record['ppf_source']=='*')
            {
                $record['ppf_source_brands']=$this->model_Brands->getForForm('prb_name','prb_name');
            }
            
            if (Str::isJson($record['ppf_source']))
            {
                $record['ppf_source']= json_decode($record['ppf_source'],TRUE);
            }
            if (is_array($record['ppf_source']))
            {
                if (array_key_exists('brands', $record['ppf_source']))
                {
                   $record['ppf_source_brands']=$record['ppf_source']['brands'];
                }
                if (array_key_exists('calc', $record['ppf_source']))
                {
                    if (is_numeric($record['ppf_source']['calc']) && intval($record['ppf_source']['calc']) > 0)
                    {
                        $record['ppf_source_calcmode_list']='+';
                    }else
                    if (is_numeric($record['ppf_source']['calc']) && intval($record['ppf_source']['calc']) < 0)
                    {
                        $record['ppf_source_calcmode_list']='-';
                    }else
                    {
                        $record['ppf_source_calcmode_list']='0';
                    }
                    $record['ppf_source_calcmode']=$record['ppf_source']['calc'];
                }
                if (array_key_exists('name', $record['ppf_source']))
                {
                    $record['ppf_source_name']=$record['ppf_source']['name'];
                }
            }
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView('Products/pricefile_edit')
                ->setFormTitle('products.pricefile_edit')
		->setPageTitle('products.pricefile_edit')
		->setFormAction($this,'save',['files'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Pricing->primaryKey=>$record[$this->model_Pricing->primaryKey],
                            'ppf_pricingmode'=>$record['ppf_pricingmode']
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
		
                ->addBreadcrumb('products.mainmenu',url($this))
		->addBreadcrumb('products.pricefile_bread',url($this,'pricefiles'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['ppf_name'],'/')
			
		->addData('record',$record)
                ->addData('import_url',url($this,'pricefiles',['import'],['pricefile'=>$record['ppfid'],'refurl'=>current_url(FALSE,TRUE)]))
                ->setTab('general','system.general.tab_info')
                ->setTab('fields','products.tab_fields')
                ->setTab('source_brands','products.tab_source_brands')
                ->addFieldsFromModel('pricing',$record,'products.-key-')
                ->addSimpleValidation()
                ->addDataUrlScript();
        
        if (!$isnew)
        {
            $this->view->setTab('source_parts','products.tab_source_parts');
        }
        
        return $this->view->render();
    }
    
        
    
    function pricefile_sendtoacc($customer=null,$pricefile='default',bool $modal=TRUE)
    {
        $post=$this->request->getPost();
        $refurl=$this->getRefUrl();
        if ($customer==null)
        {
            if ($this->request->getGet('acc')==null)
            {
                goto error_customer;
            }
            $customer=$this->request->getGet('acc');
            $customer=$this->model_Customers_Customer->filtered(['cid'=>$customer,'|| code'=>$customer])->first();
            if (is_array($customer))
            {
                $customer['emails']=$this->model_System_Contact->getByAcc($customer['code'],'ct_email','ct_name');
                $customer['emailsto']=$customer['emails'];
                $customer['emails']= array_flip($customer['emails']);
            }else
            {
                $customer['emails']=$this->model_System_Contact->getAccountsEmails(TRUE,TRUE);
                $customer['emails']= array_flip($customer['emails']);
                $customer['code']=':selected';
                $customer['emailsto']=[];
            }
        }
        //dump($customer);exit;
        if (!is_array($customer))
        {
            error_customer:
            return $this->getError('products.error_sendpricefile_invalidcust', !$modal);
        }
        $mailbox=$this->model_Emails_Mailbox->getDefaultOutMailbox(FALSE,'emm_inuser');
        $tpl=$this->model_Documents_Report->parseEmailTemplate($this->model_Settings->get('products.products_pricingemailtpl'),[]);
        $this->setFormView('Products/send_pricefile')
                ->setFormTitle('products.sendtoacc_title')
		->setPageTitle('products.sendtoacc_title')
		->setFormAction($this,'save',['pricefile_sendtoacc'],['refurl'=> base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off','inline'=>$modal],['customer'=>$customer['code']],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
                ->setFormSaveUrl(['text'=>'products.sendtoacc_savebtn','icon'=>'fas fa-share-square'])
                ->addSimpleValidation()
					
		//Breadcrumb settings
                ->addBreadcrumb('products.mainmenu',url($this))
                ->addBreadcrumb('products.pricefile_bread',url($this,'pricefiles')) 
                //
		
                ->addData('sendtoacc_emails', base64_encode(json_encode($customer['emails'])));
        if ($customer['code']==':selected')
        {
            $this->view->addCustomElementsListField('products.sendtoacc_emailsto','emailsto',$customer['emailsto'],['input_type'=>$customer['emails'],'class'=>'form-control-sm','item_color'=>'primary','item_tooltip'=>TRUE,'addbtn_action'=>'emailsto_listadd()','advanced_list'=>TRUE])
                    ->addBreadcrumb(lang('products.sendtoacc_title'),'/');
        }else
        {
            $this->view->addCustomElementsListField('products.sendtoacc_emailsto','emailsto',$customer['emailsto'],['input_type'=>$customer['emails'],'class'=>'form-control-sm','item_color'=>'primary','item_tooltip'=>TRUE,'advanced_list'=>TRUE])
                    ->addBreadcrumb(lang('products.sendtoacc_title').' - '.$customer['name'],'/');
        }
        $this->view->addDropDownField('products.sendtoacc_file', 'file', $this->model_Pricing->getPriceFilesForForm(FALSE,TRUE),$pricefile,['class'=>'form-control-sm select2'])
                ->addDropDownField('products.sendtoacc_brand', 'brand', $this->model_Brands->getBrands(TRUE,TRUE),null,['class'=>'form-control-sm select2'])
                ->addYesNoField('products.sendtoacc_brandpicker',0,'brandpicker',[])
                ->addCustomElementsListField('products.sendtoacc_brand','brand',null,['input_type'=>$this->model_Brands->getBrands(TRUE,TRUE),'class'=>'form-control-sm','item_color'=>'dark','advanced_list'=>TRUE,'required'=>TRUE])
                ->addDropDownField('products.sendtoacc_emailsfrom', 'from',$this->model_Emails_Mailbox->getDropdDownField('emm_inuser','emm_name',FALSE,TRUE),$mailbox,['class'=>'form-control-sm select2'])
                ->addCustomElementsListField('products.sendtoacc_emailscc','emailscc',[],['input_type'=>'@email_field','class'=>'form-control-sm','item_color'=>'warning','item_tooltip'=>TRUE])
                
                ->addInputField('products.sendtoacc_subject','subject',$tpl['subject'],['class'=>'form-control-sm'])
                ->addEditor('products.sendtoacc_body','msg',$tpl['body'],'simple','200',null,TRUE,['toolbar_buttons'=>[
                                'pricelink'=>['tooltip'=>lang('products.btn_sendpricefile_insertlink'),'icon'=>'link','action_text'=>'aa'],
                                'emailbody'=>['tooltip'=>lang('products.btn_sendpricefile_inserttpl'),'icon'=>'gamma','action'=>"editor.insertContent(atob('". base64_encode($tpl['body'])."'))"]
                    ]])
                ->addSelect2('.select2');
            if ($modal)
            {
                return view($this->view->getFile(),['modal_form_fields'=>$this->view->getViewData('fields'),'form_action'=>$this->view->getViewData('_formview_action')]);
            }else
            {
                $this->view->setFormCancelUrl($refurl);
            }
            return $this->view->render();
    }
    
    private function import($mode)
    {
        
        $refurl=$this->getRefUrl();
        $this->setFormView()//
                ->setFormTitle('products.import_title')
		->setPageTitle('products.import_title')
		->setFormAction($this,'save',['import'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],['upload_mode'=>$mode],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
                ->setFormSaveUrl('products.import_savebtn')
                ->addSimpleValidation()
                ->addSelect2()
		
                ->addBreadcrumb('products.mainmenu',url($this));  
        if ($mode=='brands')
        {
            $downscript="window.location='".url('Settings','uploadtpls',['gettemplate'],['id'=>'-id-','refurl'=> current_url(FALSE,TRUE)])."'.replace('-id-',$('#id_mode').find('option:selected').val());";
            $this->view->addBreadcrumb('products.brands_bread',url($this,'brands'))
                       ->addInputButtonField('products.import_brandmode','mode',null,$downscript,['input_field'=>$this->model_Brands->getUploadModes(),'button_icon'=>'fas fa-file-download','button_class'=>'btn btn-dark']);
            $upload_driver='';
        }else
        if ($mode=='pricefiles')
        {
            $this->view->addBreadcrumb('products.pricefile_bread',url($this,'pricefiles'))
                       ->addDropDownField('products.import_pricename','ppf_name',$this->model_Pricing->getPriceFilesForForm(FALSE),null,['advanced'=>TRUE])
                       ->addDropDownField('products.import_obsolete','ppf_obsolete',$this->model_Pricing->getUploadModes(),'disable',['advanced'=>TRUE]);
            $upload_driver=$this->model_Settings->get('products.products_pricinguploadtpl');
        }
        
        $this->view->addYesNoField('products.import_notify',0,'ppf_notify',[])
                   ->addUploadField('products.import_file','ppf_file',null,['accept'=>'csv','required'=>TRUE,'upload_driver'=>$upload_driver]);
                
        return $this->view->addBreadcrumb('products.import_title','/')->render();
    }
    
    public function brands($record=null)
    {
        if ($record!=null)
        {
            if ($record=='updateform')
            {
                return $this->brand_update();
            }else
            if ($record=='update')
            {
                return $this->save('updates',$this->request->getGet());
            }else
            if ($record=='import')
            {
                return $this->import('brands');
            }
            return $this->brand($record);
        }
        
        $this->setTableView()
                    ->setData('brands',null,TRUE,null,[])
                    ->setPageTitle('products.brands_list')
                    //Fiilters settings
                    ->addFilters('brands')
                    ->addFilterField('prb_name %')
                    ->addFilterField('prb_supp %')
                    //Table Columns settings
                    ->addColumn('products.prb_name','prb_name',TRUE)
                    ->addColumn('products.prb_logo_col','prb_logo',TRUE,[],'img') 
                    ->addColumn('products.lastupdt','lastupdt',FALSE,[],'d M Y')
                    ->addColumn('products.nextupdt','nextupdt',FALSE,[],'d M Y')
                    ->addColumn('products.prb_supp','prb_supp',FALSE,[],'rep:;=</br>')
                    ->addColumn('products.enabled','enabled',FALSE,'yesno')
                    //Breadcrumb settings
                    ->addBreadcrumb('products.mainmenu',url($this))
                    ->addBreadcrumb('products.brands_bread',current_url())
                    //Table Riows buttons
                    ->addEditButton('products.brands_edit','brands',null,'btn-primary edtBtn','fa fa-edit',[])
                    ->addEditButton('products.brand_list_tile_upd',url($this,'brands',['updateform'],['brand'=>'-prb_name-','refurl'=>current_url(FALSE,TRUE)]),null,'btn-warning','fas fa-calendar-plus',[])
                    //Table main buttons
                    ->addNewButton('brands/new')
                    ->addDeleteButton(AccessLevel::edit)
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)

                    ->addUploadButton('Products::brands/import','id_button_upload','products.brands_btn_upload')
                    ->addHeaderButton('brands/updateform',null,'link','btn btn-sm btn-warning tableview_def_btns','<i class="fas fa-calendar-plus"></i>','products.brand_list_tile_upd',AccessLevel::settings,[]);
        return $this->view->render();
    }
    
    private function brand($record)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Brands->find($record);              
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
            $record=$this->model_Brands->getNewRecordData(TRUE);
        }else
        {
            $record['prb_supp']=explode(';',$record['prb_supp']);
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView()
                ->setFormTitle('products.brands_edit')
		->setPageTitle('products.brands_edit')
		->setFormAction($this,'save',['brands'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Brands->primaryKey=>$record[$this->model_Brands->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('products.pricefile_bread',url($this))
                ->addBreadcrumb('products.brands_bread',url($this,'brands'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['prb_name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('brands',$record,'products.-key-')
                ->addSelect2('.select2');
        
            return $this->view->render();
    }
    
    private function brand_update()
    {
        
        $refurl=$this->getRefUrl();
        $this->setFormView()//
                ->setFormTitle('products.brand_list_tile_upd')
		->setPageTitle('products.brand_list_tile_upd')
		->setFormAction($this,'save',['updates'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],[],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
                ->setFormSaveUrl('products.import_savebtn')
                ->addSimpleValidation()
                ->addSelect2()
		
                ->addBreadcrumb('products.mainmenu',url($this))
                ->addBreadcrumb('products.brands_bread',url($this,'brands'))
                ->addBreadcrumb('products.brand_list_tile_upd','/')
                
                ->addDropDownField('products.brand_update_widget_brand','brand',$this->model_Brands->getForForm('prb_name','prb_name'),$this->request->getGet('brand'),['readonly'=>$this->request->getGet('brand')!=null])
                ->addDatePicker('products.brand_update_widget_date','date', formatDate(),[]);  
        return $this->view->render();
    }
    
    public function downloadfile($file=null,$brand=null,$url=null)
    {
        $filters=['name'=>''];
        $error_msg='';
        $post=$this->request->getPost()+$this->request->getGet();
        //dump($post);exit;
        if ($file!=null)
        {
            $post['file']=$file;
        }
        if ($brand!=null)
        {
            $post['brand']=$brand;
        }
        
        if ($url!=null)
        {
            $post['url']=$url;
        }
        if (array_key_exists('file', $post))
        {
            $post['customer']=$post['file'];
        }
        
        if (is_array($post) && Arr::KeysExists(['brand','customer'], $post))
        {
            if ($post['customer']=='loged')
            {
                $post['customer']=loged_user('customer');
            }
            
            $post=$this->download(['customer'=>$post['customer'],'brand'=>$post['brand']]);
            
            if ($post==FALSE || is_string($post))
            {
                $error_msg= is_string($post) ? $post : $this->createMessage('products.error_products_download','danger');
                return $this->response->setStatusCode(403)->setBody($error_msg);
            }else
            {
                
                return $post;
            }
        }
        
        if ($file=='loged' && !$this->auth->isLoged())
        {
            return $this->createMessage('system.errors.no_acces','danger');
        }else
        {
            $post['file']= base64url_decode($post['file']);
            $post['file']=$this->model_Pricing->filtered(['ppf_name'=>$post['file']])->first();
            if (is_array($post['file']) && Arr::KeysExists(['ppf_source'], $post['file']))
            {
                
            }
        }
        dump($post);exit;
        if ($this->auth->isLoged())
        {
            $file='loged';
        }
        price_file_form:
        $email= loged_user('customer');
        $data=[];
        $data['url']=$url==null ? url($this,'download',[],['term'=>$file,'link'=>1]) : ($url=='current_url' ? current_url() : $url);
        $data['brands']=$this->model_Brands->getBrands(TRUE,TRUE);
        $data['brand']=$brand;
        $data['isemail']= is_string($file) && $file=='email';
        $data['file']=$file;
        $data['error_msg']=$error_msg;
        return view('Products/download_pricefile',$data);
    }
    
    function download(array $filters=[])
    {
        $get=$this->request->getGet();
        $post=$this->request->getPost();
        $filters=$filters+$get+$post;
        $mode='download';
        $bigFile=FALSE;
        if (count($filters) < 1)
        {
            return FALSE;
        }
        
        if (array_key_exists('link', $filters))
        {
            $mode='link';
            unset($filters['link']);
        }
        
        if (array_key_exists('brand', $filters))
        {
            $filters['prd_brand']=$filters['brand'];
            unset($filters['brand']);
        }
        
        if (array_key_exists('prd_brand', $filters) && (strlen($filters['prd_brand']) < 1 || $filters['prd_brand']=='0'))
        {
            $bigFile=TRUE;
            unset($filters['prd_brand']);
        }
        
        if (array_key_exists('customer', $filters))
        {
            if (Str::isValidEmail($filters['customer']))
            {
                $filters['customer']=$this->model_Customers_Customer->getCustomerByEmail($filters['customer'],'terms_price');
                
                if ($filters['customer']==null)
                {
                    return $this->createMessage('products.error_invalid_email_pricing','warning');
                }
                $filters['name']=$filters['customer'];
                unset($filters['customer']);
            } else
            {
                $filters['name']=$filters['customer'];
                unset($filters['customer']);
            } 
        }

        if (array_key_exists('acc', $filters))
        {
            $filters['name']=$filters['acc'];
            unset($filters['acc']);
        }
        
        if (array_key_exists('file', $filters))
        {
            $filters['name']= base64url_decode($filters['file']);
            unset($filters['file']);
        }
        
        if (!array_key_exists('name', $filters))
        {
            if ($this->auth->isLoged())
            {
                $filters['name']=loged_user('customer');
            }else
            {
               return FALSE; 
            }
            
        }
        if (is_numeric($filters['name']))
        {
            $filters['name']=$this->model_Customers_Customer->find($filters['name']);
            if (!is_array($filters['name']) || (is_array($filters['name']) && !Arr::KeysExists(['terms_price'], $filters['name'])))
            {
                return FALSE;
            }
            $filters['name']=$filters['name']['terms_price'];
        }
       
        if (is_string($filters['name']) && Str::startsWith($filters['name'], '@'))
        {
            $filters['name']= base64url_decode(substr($filters['name'], 1));
        }
        dump($filters);exit;
        $data=$this->model_Pricing->getPriceFileByName($filters['name'],$filters);
        
        if (!is_array($data))
        {
            return FALSE;
        }
        dump($filters);exit;
        if ($this->request->getGet('t')!=null)
        {
            
        }
        $headers=[];
        $formats=[];
        foreach(array_keys($data[0]) as $key)
        {
            $headers[]=lang(str_replace('@', '.', $key),['']);
            $formats[]='@text';
        }
        if ($bigFile)
        {
            $xlsx = \avadim\FastExcelWriter\Excel::create(['Sheet1']);
            $sheet = $xlsx->getSheet();
            $sheet->setColFormats($formats)
                ->writeRow($headers);
        
            foreach($data as $row)
            {
                $sheet->writeRow($row);
            }
        }else
        {
            array_unshift($data,$headers);
            $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
        }
        //
        
        $fileName=lang('products.allbrands');
        if (array_key_exists('prd_brand', $filters))
        {
            $fileName=$filters['prd_brand'];
        }
        $fileName=$fileName.'_'.formatDate().'.xlsx';
        if ($mode=='link')
        {
            $fileName= base64url_encode($fileName);
            //file_put_contents(parsePath('@temp/'.$fileName,TRUE),$writer->writeToString());
            $xlsx->{$bigFile ? 'save' : 'saveAs'}(parsePath('@temp/'.$fileName,TRUE));//save saveAs
            $url=str_replace('-link-',$fileName,url($this,'downloadfile',['-link-']));
            return json_encode(['url'=>$url,'name'=>$fileName]);
        }else
        {
            $xlsx->{$bigFile ? 'output' : 'downloadAs'}($fileName);exit; //downloadAs output
        }
    }
    
    function download1(array $filters=[],$mode='download')
    {
        $post=$this->request->getPost();
        if (is_array($post) && array_key_exists('filters', $post))
        {
            $filters=$post['filters'];
        }
        
        $get=$this->request->getGet();
        if (is_array($get))
        {
            if (array_key_exists('brand', $get) && strlen($get['brand']) >0)
            {
               $filters['prd_brand']=$get['brand']; 
            }
            if (array_key_exists('customer', $get) && strlen($get['customer']) >0)
            {
               $filters['customer']=$get['customer']; 
            }
            
            if (array_key_exists('pricefile', $get) && strlen($get['pricefile']) >0)
            {
               $filters['_pricing_level']=$get['pricefile']; 
            }
            
            if (array_key_exists('acc', $get))
            {
                if (Str::isValidEmail($get['acc']))
                {
                    $get['acc']=$this->model_Customers_Customer->getCustomersLinkedToContact($get['acc']);
                    if (is_array($get['acc']))
                    {
                        $get['acc']=$this->model_Customers_Customer->where('code',$get['acc'][0])->first();
                        if (is_array($get['acc']) && array_key_exists('terms_price', $get['acc']))
                        {
                            $filters['customer']=$get['acc']['terms_price'];
                            $get['acc']=$get['acc']['terms_price'];
                        }else
                        {
                            $get['acc']=0;
                        }
                    }
                }
                get_acc_from_number:
                if (is_numeric($get['acc']))
                {
                    $get['acc']=$this->model_Customers_Customer->find($get['acc']);
                    if (is_array($get['acc']) && array_key_exists('code', $get['acc']))
                    {
                        $get['acc']=$get['acc']['terms_price'];//code terms_price
                        $filters['customer']=$get['acc'];
                    }
                }else
                {
                    $get['acc']=0;
                }
            }
            if (array_key_exists('link', $get) && intval($get['link'])==1)
            {
                $mode='link';  
            }
        }
        
        if (!array_key_exists('acc', $get))
        {
            $get['acc']=loged_user('customer');
            goto get_acc_from_number;
        }
        
        if ($get['acc']!=1 && !array_key_exists('customer', $filters))
        {
            return json_encode(['error'=>lang('products.error_invalid_email_pricing'),'errormode'=>'warning']);
        }
        
        if (!is_array($filters))
        {
            $filters=[];
        }
        
        if (array_key_exists('brand', $filters))
        {
            $filters['prd_brand']=$filters['brand'];
            unset($filters['brand']);
        }
        
        $gFilters=$this->getFlashData('_parts_filters',null);
        if (strlen($gFilters) > 0)
        {
            $gFilters= base64url_decode($gFilters);
            if (Str::isJson($gFilters))
            {
                $gFilters= json_decode($gFilters,TRUE);
                unset($gFilters['-value-']);
            }else
            {
                $gFilters=[];
            }
        }else
        {
            $gFilters=[];
        }
        
        filters:
        $gFilters=$filters+$gFilters;
        
        if (array_key_exists('_file', $gFilters))
        {
           $param='file'; 
        }else
        {
            $param=$this->model_Product->getPartsEditMode();
        }
        
        if (is_array($get) && array_key_exists('acc', $get))
        {
            find_customer_for_movemnets:
            if (is_numeric($get['acc']))
            {
               $get['acc']=$this->model_Customers_Customer->find($get['acc']); 
            }
            
            if (is_array($get['acc']) && array_key_exists('code', $get['acc']))
            {
               $get['acc']=$get['acc']['code']; 
            }
            //$this->model_Files->addPricefileDownloadMovement($get['acc'],array_key_exists('brand', $get) ? $get['brand'] : null);
        }else
        if (is_array($get))
        {
            $get['acc']=loged_user('customer');
            goto find_customer_for_movemnets;
        }
        
        if ($param=='db')
        {
            $data=$this->model_Files->getPriceFileByName($gFilters['customer'],$gFilters);
            //$data=$this->model_Product->generatePriceFileFromDB($gFilters);//model_Files
        }else
        {
            $data=$this->model_Files->getPriceFileForCustomer(array_key_exists('prd_brand', $gFilters) ? $gFilters['prd_brand'] : null,array_key_exists('_file', $gFilters) ? $gFilters['_file'] : null);
        }
        dump($data);exit;
        if (!is_array($data))
        {
            return FALSE;
        }
        
        $headers=[];
        foreach(array_keys($data[0]) as $key)
        {
            $key=lang('products.'.$key);
            $headers[$key]='string';
        }
        //$headers= array_combine($headers, array_fill(0, count($headers), 'string'));
        /*$writer = new XLSXWriter();
        $writer->writeSheetHeader('Sheet1', $headers);
        
        foreach($data as $key=>$row)
        {
            //$writer->writeSheetRow('Sheet1', $row);
            $row=array_values($row);
            $data[$key]= array_values($row);
        }*/
        
        array_unshift($data, array_keys($headers));
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
        //dump($xlsx);exit;
        $fileName=lang('products.allbrands');
        if (array_key_exists('prd_brand', $gFilters))
        {
            $fileName=$gFilters['prd_brand'];
        }
        $fileName=$fileName.'_'.formatDate().'.xlsx';
        if ($mode=='link')
        {
            $fileName= base64url_encode($fileName);
            //file_put_contents(parsePath('@temp/'.$fileName,TRUE),$writer->writeToString());
            $xlsx->saveAs(parsePath('@temp/'.$fileName,TRUE));
            $url=str_replace('-link-',$fileName,url($this,'downloadfile',['-link-']));
            return json_encode(['url'=>$url,'name'=>$fileName]);
        }else
        {
           //$writer->writeToStdOut($fileName); 
            $xlsx->downloadAs($fileName);exit;
        }
        
    }
    
    function getDashboardTile($type='list')
    {
        $form=new Pages\FormView($this);
        $data=[];
        
        if ($type=='yearusage_orders')
        {
            $type='System/Dashboard/graph';
            $data=
            [
                'data'=>$this->model_Brands->getBrandsOrderUsage(),
                'header'=>'products.brand_usage_tile_header',
                'header_chart'=>'products.brand_usage_tile_ordqty',
                'name'=>'yearusage_orders_tile',
                'tilePrintButton'=>TRUE,
                'header_style'=>'background-color:#ffc107!important'
            ];
        }else
        if ($type=='yearusage_download')
        {
            $type='System/Dashboard/graph';
            $data=
            [
                'data'=>$this->model_Brands->getBrandsYearDownloadUsage(),
                'header'=>'products.brand_usage_tile_header',
                'header_chart'=>'products.brand_usage_tile_dwonqty',
                'name'=>'yearusage_download_tile',
                'tilePrintButton'=>TRUE,
                'header_style'=>'background-color:#ffc107!important'
            ];
        }else
        if ($type=='pricefile')
        {
            $type='Products/Tiles/pricefile_tile';
            $iscust= intval(loged_user('iscustomer'))==1;
            $data=
            [
                'brands'=>$this->model_Brands->getBrands(TRUE,TRUE),
                'iscust'=>$iscust,
                'action'=>url($this,'download',[],$iscust ? ['brand'=>'-brand-'] : ['brand'=>'-brand-','acc'=>'-acc-'])
            ];
            if (!$iscust)
            {
                $data['customers']=$this->model_Customers_Customer->getCustomersForDropDown(null,null,TRUE);
            }
        }else
        if ($type=='update')
        {
            $type='Products/Tiles/brand_update_tile';
            $data=
            [
                'brands_dropdown'=>$this->model_Brands->getForForm(),
                'url'=>url($this,'brands',['update'],['brand'=>'-id-','date'=>'-date-','refurl'=> current_url(FALSE,TRUE)])
            ];
        }else
        if ($type=='list')
        {
            $type='Products/Tiles/brands_tile';
            $form->addDropDownField('products.brand_update_modal_brand', 'brand_update_modal_brand', $this->model_Brands->getForForm(), '',['class'=>'select22']);
            $data=
            [
                'brands_prev'=>$this->model_Updates->filtered(['enabled'=>1,'updt %'=> formatDate('now','-1 month','Ym')],'prb_name')->find(),
                'brands_curr'=>$this->model_Updates->filtered(['enabled'=>1,'updt %'=> formatDate('now',FALSE,'Ym')],'prb_name')->find(),
                'brands_next'=>$this->model_Updates->filtered(['enabled'=>1,'updt %'=> formatDate('now','+1 month','Ym')],'prb_name')->find(),
                'brands_after'=>$this->model_Updates->filtered(['enabled'=>1,'updt %'=> formatDate('now','+2 month','Ym')],'prb_name')->find(),
                'brands'=>$this->model_Brands->getBrandsWithUpdates(['enabled'=>1],'prb_name')->find(),
                'edit_acc'=>$this->hasAccess(AccessLevel::edit),
                'brands_dropdown'=>$this->model_Brands->getForForm(),
                '_brand_tile_nocard'=>TRUE,
                'month'=> formatDate(),
                'url'=>url($this,'brands',['update'],['brand'=>'-id-','date'=>'-date-','fdate'=>'-fdate-','refurl'=> current_url(FALSE,TRUE)])
            ];
        }else
        {
            return '';
        }
        return view($type,$data);
    }
    
    
    function save($type, $post = null) 
    {
        $post=$post==null ? $this->request->getPost() : $post;
        if ($type=='brands')
        {
            if (array_key_exists('prb_supp', $post) && is_array($post['prb_supp']))
            {
                $post['prb_supp']=implode(';',$post['prb_supp']);
            }
            //dump($post);exit;
        }else
        if ($type=='pricefile_sendtoacc')
        {
            $post['file_name']= base64url_decode($post['file']);
            $name=$post['file'].$post['customer'].formatDate();
            
            $post['file']=TRUE;//$this->model_Pricing->createTempPriceFile($post['file_name'],['picker'=>$post['brandpicker'],'brands'=>$post['brand']],lang('products.sendtoacc_filedesc',[$post['customer'],loged_user('name')]),$name,TRUE);
            if (!$post['file'])
            {
               return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.sendtoacc_savefailed', 'danger')); 
            }
            if (array_key_exists('brandpicker', $post) && intval($post['brandpicker'])==1)
            {
                $post['link']=url($this,'downloadfile',[],['file'=> base64url_encode($name),'t'=>'-%@email%-','p'=>1]);
            }else
            {
                $post['link']=url($this,'downloadfile',[],['file'=> base64url_encode($name),'t'=>'-%@email%-']);
            }
            
            $post['msg']= str_replace('{link}', $post['link'], $post['msg']);
            if (array_key_exists('emailscc', $post) && is_array($post['emailscc']))
            {
                $post['emailsto']= array_merge($post['emailsto'],$post['emailscc']);
            }
            //$this->model_Emails_Email->storeEmailInOutbox($post['from'],$post['emailsto'],$post['subject'],$post['msg']);
            if (array_key_exists('customer', $post))
            {
                if ($post['customer']==':selected')
                {
                    $post['customer']=$this->model_System_Contact->getAccountsFromEmails($post['emailsto']);
                } else 
                {
                    $post['customer']=[$post['customer']];
                }
                dump($post);exit;
                foreach($post['customer'] as $customer)
                {
                    $this->addMovementHistory('products_sendpricemail', null,implode(', ',$post['emailsto']), $customer, $post['file_name']);
                }
            }
            
            return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.sendtoacc_saveok', 'success'));
        }else
        if ($type=='updates')
        {
            if (Arr::KeysExists(['date','brand'], $post))
            {
                if ($this->model_Updates->addUpdate($post['brand'],$post['date']))
                {
                    return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.msg_brand_update', 'success'));
                }else
                {
                    return redirect()->to($this->getRefUrl())->with('error', $this->createMessage($this->model_Updates->errors(), 'danger'))->with('_postdata', $post);
                }
            }
            return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.error_brand_update', 'danger'));
        }else
        if ($type=='sendorder')
        {
            $post['_export_justname']=TRUE;
            if (array_key_exists('ordernr', $post))
            {
                $post['_upload_filename']=$post['ordernr'];
            }
            $this->uploadFiles($post);
            if (!array_key_exists('mail_file', $post))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.error_sendorder_nofile','warning'));
            }
            $fromName='mailbox:'.$this->model_Settings->get('products.products_suppemailbox');
            if ($this->sendEmail($fromName, $post['mail_to'], $post['mail_subject'], $post['mail_body'],[],[],[$post['mail_file']]))
            {
                $this->addMovementHistory('products_sendorderemail', null, $post['mail_to'], $post['account'], $post['ordernr'], 'suppliers');
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.msg_sendorder_ok','success'));
            }
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('products.error_sendorder','danger'));
        }else
        if ($type=='files')
        {
            if (array_key_exists('ppf_fields', $post) && is_array($post['ppf_fields']))
            {
                $arr=[];
                foreach($post['ppf_fields'] as $key=>$value)
                {
                    $key=Str::afterLast($value, '@');
                    $arr[$key]=$value;
                }
                $post['ppf_fields']= json_encode($arr);
            }
            $post['ppf_source']=[];
            if (Arr::KeysExists(['ppf_source_name'], $post))
            {
                $post['ppf_source']=['name'=>$post['ppf_source_name'],'brands'=>'*'];               
                if (array_key_exists('ppf_source_calcmode_list', $post))
                {
                    if (array_key_exists('ppf_source_calcmode', $post))
                    {
                        $post['ppf_source']['calc']=$post['ppf_source_calcmode'];
                    }else
                    {
                        $post['ppf_source']['calc']=0;
                    }
                }
                $post['ppf_curr']=$this->model_Pricing->getCurrencyForPriceFile($post['ppf_source_name']);
            }
            
            if (array_key_exists('ppf_source_brands', $post) && is_array($post['ppf_source_brands']))
            {
                $post['ppf_source']['brands']=$post['ppf_source_brands'];
            }
            
            if (is_array($post['ppf_source']))
            {
                $post['ppf_source']= json_encode($post['ppf_source']);
            }
            $post['_upload_filename']='@';
            $post['_fieldname']='ppf_source';
            $post['_export_justname']=TRUE;
            $this->uploadFiles($post);
            if (!array_key_exists('ppf_source', $post))
            {
                $post['ppf_source']='*';
            }
            $type='pricing';
        }else
        if ($type=='import')
        {
            if (array_key_exists('upload_mode', $post))
            {
                $post['_export_justname']=TRUE;
                //$post['_upload_filename']=formatDate();
                $this->uploadFiles($post);
                if (array_key_exists('ppf_notify', $post))
                {
                    $post['ppf_notify']=$post['ppf_notify'] ? loged_user('email') : '';
                }
                if (Arr::KeysExists(['mode','ppf_file'], $post) && $post['upload_mode']=='brands')
                {
                    if (!$this->model_Brands->updateFromFile($post['ppf_file'],$post['mode'],$post['ppf_notify'],FALSE))
                    {
                        goto error_save;
                    }
                }
                if (array_key_exists('ppf_name', $post) && $post['upload_mode']=='pricefiles')
                {
                    $post['upl']=$this->model_Pricing->addUploadTasks($post['ppf_name'],$post['ppf_file'],$post['ppf_obsolete'],intval($post['ppf_notify']) == 1 ? loged_user('email'): '');
                }
                return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.import_msg_done', 'success'));
            }
            error_save:
            return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.import_msg_error', 'error'));
        }
        //dump($post);exit;
        return parent::save($type, $post);
    }
    
    function _after_save($type, $post, $refurl, $refurl_ok): bool
    {
        if ($type=='pricing' || $type=='model_pricing')
        {
            if (!array_key_exists('ppfid', $post) || (array_key_exists('ppfid', $post) && !is_numeric($post['ppfid'])))
            {
                if (!is_array($post['ppf_source_brands']))
                {
                    $post['ppf_source_brands']= json_decode($post['ppf_source_brands'],TRUE);
                }
                if (Arr::KeysExists(['ppf_source_brands','ppf_pricingmode','ppf_name'], $post) && $post['ppf_pricingmode']=='db')
                {
                    $this->model_PricePart->generatePartsFromBrands($post['ppf_name'],$post['ppf_source_brands']);
                }
            }
        }
        return TRUE;
    }
    function api($command=null,array $post=[])
    {
        $result=null;
        if (array_key_exists('acc', $post))
        {
            $post['customer']=$post['acc'];
        }
        if (!array_key_exists('customer', $post))
        {
            $post['customer']=$this->model_Customers_Customer->getCustomerForLogedUser();
        }
        if ($command=='findpart')
        {
            if (!array_key_exists('part', $post))
            {
                error_part:
                $result=['error'=>lang('products.error_invalid_partnr')];goto end_func;
            }
            $data=$this->model_Product->generatePriceFileFromDB(['prd_apdpartnumber'=>$post['part'],'customer'=>$post['customer']],[],TRUE);
            $result=['data'=>$data];
        }else
        if ($command=='findpartforfield')
        {
            if (!array_key_exists('part', $post))
            {
                goto error_part;
            }
            $result=$this->model_Product->findByCommonFields($post['part']);
            if (array_key_exists('acc', $post))
            {
                $result=$this->model_Product->findByCommonFieldsWithPrice($post['part'],$post['acc']);
            }else
            {
               $result=$this->model_Product->findByCommonFields($post['part']); 
            }
            
            if (!is_array($result) || (is_array($result) && count($result) < 1))
            {
                goto error_part;
            }
            goto end_func;
        }else
        if ($command=='getbybrand')
        {
            if (!array_key_exists('brand', $post))
            {
                $result=['error'=>lang('products.error_invalid_brand')];goto end_func;
            }
            $data=$this->model_Product->generatePriceFileFromDB(['prd_brand'=>$post['brand'],'customer'=>$post['customer']],[],TRUE);
            $result=['data'=>$data];
        }
        end_func:
        return $result;
    }

    
    function validatepart($part,$acc=null)
    {
        $part= base64_decode($part);
        if (Str::startsWith($part, ' '))
        {
            $part= substr($part, 1);
        }
        $part=['( prd_tecdocpart %'=>$part,'|| prd_apdpartnumber % )'=>$part];
        if ($acc!=null)
        {
            $part['customer']=$acc;
        }
        $arr=$this->model_Files->generatePriceFileFromDB($part);
        if (is_array($arr) && count($arr) > 0)
        {
             return json_encode(['parts'=>$arr]);
        }
        return json_encode(['error'=>lang('products.error_invalid_partnr')]);
    }
    
    function settings($tab,$record)
    {
        $settings=$this->model_Settings->get('products.*',FALSE,'*');
        $view=new Pages\FormView($this);
        $tpls=$this->model_Documents_Report->getTemplatesForForm();
        $upload_drv=$this->model_Settings->getUploadDrivers('*',TRUE);
        if ($tab=='cfg')
        {
           $view->addCustomElementsListField('products.settings_pricefiledeffields', 'settings[products_pricefiledeffields]', $settings['products_pricefiledeffields']['value'], ['input_type'=>$this->model_Pricing->getColumnNames()]);
           $view->addYesNoField('products.settings_enablepricefiles', $settings['products_enablepricefiles']['value'], 'settings[products_enablepricefiles]');
           $view->addDropDownEditableField('products.settings_pricingemailtpl','settings[products_pricingemailtpl]',$tpls,$settings['products_pricingemailtpl']['value'],['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE).'&tab=cfg')])]);
           $view->addDropDownField('products.settings_pricinguploadtpl', 'settings[products_pricinguploadtpl]', $upload_drv, $settings['products_pricinguploadtpl']['value'], ['advanced'=>TRUE]);
           $view->addDropDownEditableField('products.settings_pricinguploadnotifytpl','settings[products_pricinguploadnotifytpl]',$tpls,$settings['products_pricinguploadnotifytpl']['value'],['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE).'&tab=cfg')])]);
           $view->addDropDownField('products.settings_branduploadtpl', 'settings[products_branduploadtpl]', $upload_drv, $settings['products_branduploadtpl']['value'], ['advanced'=>TRUE]);
           $view->addDropDownField('products.settings_brandupdtuploadtpl', 'settings[products_brandupdtuploadtpl]',$upload_drv, $settings['products_brandupdtuploadtpl']['value'], ['advanced'=>TRUE]);
        }else
        if ($tab=='sup')
        {
            $view->addCustomElementsListField('products.settings_suppemailmodes', 'settings[products_suppemailmodes]', $settings['products_suppemailmodes']['value'], ['input_type'=>$this->model_Supp->getOrderModes()]);
            $view->addDropDownField('products.settings_suppemailbox', 'settings[products_suppemailbox]', $this->model_Emails_Mailbox->getDropdDownField(), $settings['products_suppemailbox']['value'], ['advanced'=>TRUE]);
            $view->addDropDownEditableField('products.settings_suppemailbox','settings[products_suppemailbox]',$this->model_Emails_Mailbox->getDropdDownField('emmid'),$settings['products_suppemailbox']['value'],['advanced'=>TRUE,'url'=>url('Emails','mailboxes',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE))])]);
            $view->addDropDownEditableField('products.settings_supporderemailtpl','settings[products_supporderemailtpl]',$tpls,$settings['products_supporderemailtpl']['value'],['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE).'&tab=sup')])]);
            $view->addDropDownField('products.settings_suppuploadtpl', 'settings[products_suppuploadtpl]',$upload_drv, $settings['products_suppuploadtpl']['value'], ['advanced'=>TRUE]);
        }
        
        
        return view('System/form_fields',$view->getViewData());
    }   
    
    function deletesingle($model, $value, $field = null) 
    {
        if ($model=='tmpfiles')
        {
            $filters=['ppf_istmp'=>1];
            if (is_numeric($value))
            {
                $filters['ppfid']=$value;
            }else
            if (is_string($value) && $value!='all')
            {
                $filters['ppf_name']=$value;
            }
            if ($this->model_Pricing->filtered($filters)->delete())
            {
                return redirect()->to(url($this,'pricefiles'))->with('error', $this->createMessage('products.ppf_istmp_delok', 'success'));
            }
            return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('products.ppf_istmp_delfail', 'danger')); 
        }
        parent::deletesingle($model, $value, $field);
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
    
    function pages(string $mode, Pages\FormView $view, array $data)
    {
        if ($mode=='downloadfile')
        {
            return $view->setTab('products','products.pages_cfg_tab')
                        ->addDropDownField('products.pages_pricefile', 'pg_cfg[pricefile]', $this->model_Files->getPriceFilesForForm(), array_key_exists('pricefile', $data) ? $data['pricefile'] : null, ['advanced'=>TRUE])
                        ->addDropDownField('products.pages_brand', 'pg_cfg[brand]', $this->model_Brands->getBrands(TRUE), array_key_exists('brand', $data) ? $data['brand'] : null, ['advanced'=>TRUE])
                        ->addHiddenField('pg_cfg[url]','current_url')
                        ->addHiddenField('pg_action', 'Products::downloadfile@{pricefile},{brand},{url}');
        }else
        if ($mode=='custinfo')
        {
              return $view->setTab('products','products.pages_cfg_tab')
                        ->addYesNoField('products.pages_showbasket', array_key_exists('showbasket', $data) ? $data['showbasket'] : null, 'pg_cfg[showbasket]')
                        ->addYesNoField('products.pages_pricefileshow', array_key_exists('showpricefile', $data) ? $data['showpricefile'] : null, 'pg_cfg[showpricefile]')
                        ->addYesNoField('products.pages_costhow', array_key_exists('showcost', $data) ? $data['showcost'] : null, 'pg_cfg[showcost]')
                        ->addDropDownField('products.pages_pricefile', 'pg_cfg[pricefile]', $this->model_Files->getPriceFilesForForm(), array_key_exists('pricefile', $data) ? $data['pricefile'] : null, ['advanced'=>TRUE])
                        ->addDropDownField('products.pages_brand', 'pg_cfg[brand]', $this->model_Brands->getBrands(TRUE), array_key_exists('brand', $data) ? $data['brand'] : null, ['advanced'=>TRUE])
                        ->addHiddenField('pg_action', 'Products::parts@null');
        }
        return $view;
    }
        
}