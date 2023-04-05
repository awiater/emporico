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
 
namespace EMPORIKO\Models\Orders;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class OrderModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='orders';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'ordid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['ord_ref','ord_refcus','ord_addon','ord_addby','ord_recon','ord_recby'
                                  ,'ord_status','ord_cusacc','ord_paid','ord_cancelref' ,'ord_invoicenr'
                                  ,'ord_done','ord_doneon','ord_type','ord_value','ord_source_ref'
                                  ,'ord_source','ord_prdsource','ord_desc','enabled'];
	
        
        protected $validationRules =
	 [
            'ord_ref'=>'required|is_unique[orders.ord_ref,ordid,{ordid}]',
	 ];
	
	protected $validationMessages = 
        [
            'ord_ref'=>['is_unique'=>'orders.error_ord_ref_exists'],
        ];
        
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'ordid'=>           ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
		'ord_ref'=>         ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE,'unique'=>TRUE],
                'ord_refcus'=>      ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'ord_addon'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'ord_addby'=>       ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'ord_recon'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
                'ord_recby'=>       ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
                'ord_status'=>      ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'ord_cusacc'=>      ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE,'index'=>TRUE],
                'ord_paid'=>        ['type'=>'VARCHAR','constraint'=>'11','null'=>FALSE,'default'=>0],
                'ord_cancelref'=>   ['type'=>'TEXT','null'=>TRUE],
                'ord_desc'=>        ['type'=>'TEXT','null'=>TRUE],
                'ord_invoicenr'=>   ['type'=>'VARCHAR','constraint'=>'250','null'=>TRUE],
                'ord_value'=>       ['type'=>'DOUBLE','null'=>FALSE],
                'ord_source_ref'=>  ['type'=>'TEXT','null'=>TRUE],
                'ord_source'=>      ['type'=>'TEXT','null'=>TRUE],
                'ord_prdsource'=>   ['type'=>'TEXT','null'=>TRUE],
                'ord_done'=>        ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
                'ord_type'=>        ['type'=>'INT','constraint'=>'2','null'=>FALSE,'default'=>0],
		'enabled'=>         ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
	];
        
        /**
         * Get filtered order data
         * 
         * @param array $filters
         * @param type $orderby
         * @param type $paginate
         * @param type $logeduseraccess
         * @param type $Validation
         * 
         * @return array
         */
        function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
        {
            //return parent::filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
            return $this->getView('vw_orders_info')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
        }
        
        function getPartGrid(string $curr='$',bool $addTotal=TRUE,bool $render=FALSE)
        {
            $curr=$this->getModel('Settings')->getCurrencyIcons($curr,TRUE,FALSE);
            $curr= is_array($curr) ? 'far fa-money-bill-alt' : $curr;
            $field= \EMPORIKO\Controllers\Pages\HtmlItems\DataGrid::create()
                    ->setName('parts')
                    ->setID('parts')
                    ->setText('')
                    ->setTab('tab_parts')
                    ->addListColumn('prd_brand', 'products.prd_brand',$this->getModel('Products/Brand')->getBrands(),TRUE,TRUE,['style'=>'width:250px;'])
                    ->addColumn('prd_apdpartnumber', 'products.prd_apdpartnumber')
                    ->addColumn('prd_description', 'products.prd_description')
                    ->addNumberColumn('qty', 'orders.opportunities_qty',0,100000,['field'=>['dir'=>'rtl'],'style'=>'width:10%;vertical-align: middle!important;text-align: right;'])
                    ->addMoneyColumn('value', 'orders.opportunities_value', $curr,TRUE,['field'=>['dir'=>'rtl'],'style'=>'width:10%;vertical-align: middle!important;text-align: right;'])
                    ->addMoneyColumn('rvalue', 'orders.opportunities_rvalue', $curr,TRUE,['field'=>['dir'=>'rtl'],'style'=>'width:10%;vertical-align: middle!important;text-align: right;'])
                    
                    ->setHeaderClass('card-header')
                    ;
            if ($addTotal)
            {
                $field->addTotalColumn('orders.total','x',['value','qty'],['style'=>'width:7%;vertical-align: middle!important;text-align: right;']);
            }
            return $render ? $field->render() : $field;
        }
        
        /**
         * Get edit form fields definition for opportunity
         * 
         * @return array
         */
        function getFieldForOportForm(array $record=[])
        {
            $arr=[];
            
            $arr['ord_cusacc']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('ord_cusacc')
                    ->setID('ord_cusacc')
                    ->setText('ord_cusacc')
                    ->setTab('general')
                    ->setOptions($this->getModel('Customers/Customer')->getCustomersForDropDown('code',null,TRUE))
                    ->setAsAdvanced()
                    ->setAsRequired();
            
            if (array_key_exists('ord_cusacc', $record) && $record['ord_cusacc']!=null)
            {
                $arr['ord_cusacc']->setReadOnly()->setName('ord_cusacc_disabled');
                $arr['ord_cusacc_disabled']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                    ->setName('ord_cusacc')
                    ->setID('ord_cusacc')
                    ->setValue($record['ord_cusacc']);
            }        
              
            $arr['ord_ref']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('ord_ref')
                    ->setID('ord_ref')
                    ->setText('opportunities_ref')
                    ->setTab('general')
                    ->setMaxLength(80)
                    ->setAsRequired();
            
            $arr['ord_refcus']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('ord_refcus')
                    ->setID('ord_refcus')
                    ->setText('opportunities_refcus')
                    ->setTab('general')
                    ->setMaxLength(80);
            
            $arr['ord_source']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('ord_source')
                    ->setID('ord_source')
                    ->setText('opportunities_source')
                    ->setTab('general')
                    ->setOptions($this->getOportSources());
            
            $arr['ord_desc']=\EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('ord_desc')
                    ->setID('ord_desc')
                    ->setText('opportunities_desc')
                    ->setTab('general')
                    ->setMaxLength(350);
            
            $arr['parts']=\EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField::create()
                    ->setName('parts')
                    ->setID('parts')
                    ->setText('')
                    ->setTab('tab_parts')
                    //->setListFields($this->getOrdersSettings('orders_oport_partpickerfields'))
                    ->setValueFieldVisibility()
                    ->setDefValueCurrency($record['ord_cus_curr']);
            
            $arr['parts']=$this->getPartGrid(array_key_exists('ord_cus_curr', $record) ? $record['ord_cus_curr'] : '');
            
            if (array_key_exists($this->primaryKey, $record) && is_numeric($record[$this->primaryKey]))
            {
                $arr['ord_cusacc']->setReadOnly();
                $arr['ord_refcus']->setReadOnly();
                $arr['ord_source']->setReadOnly();
                $arr['parts']->setReadOnly()
                             ->addTotalRow(['qty','value','rvalue','_total']);
                //$arr['parts']->setReadOnly()->setTotalsVisibility(TRUE);
            }else
            {
                $arr['parts']->removeColumn(['rvalue','total'])
                             ->addRemoveRowColumn()
                             ->setColumnEditable('*',FALSE)
                             ->setColumnEditable('qty',TRUE);
            }
            
            if (array_key_exists('ord_done', $record) && ($record['ord_done']=='1' || $record['ord_done']==1))
            {
                $arr['ord_cusacc']->setReadOnly(TRUE,TRUE);
                $arr['ord_ref']->setReadOnly(TRUE,TRUE);
                $arr['ord_refcus']->setReadOnly(TRUE,TRUE);
                $arr['ord_source']->setReadOnly(TRUE,TRUE);
                $arr['ord_desc']->setReadOnly(TRUE,TRUE);
                $arr['parts']->setReadOnly()->setTotalsVisibility(TRUE);
            }
            /*'','',,'ord_recon','ord_recby'
                                  ,'','','','' ,''
                                  ,'','',''
                                  ,'','enabled'*/
            
            return $arr;
        }
        
        /**
         * Returns array with available opportunity sources for form field
         * 
         * @return array
         */
        function getOportSources()
        {
            $arr=[];
            foreach(['call','email','cust','web','campaign','other'] as $src)
            {
                $arr[$src]=lang('orders.opportunities_source_'.$src);
            }    
            return $arr;
        }
        
        /**
         * Returns array with opportunity statuses for form field
         * 
         * @return array
         */
        function getOportStatuses()
        {
            $arr=[];
            foreach($this->getModel('Settings')->get('orders.orders_status_oport_*',FALSE,'*') as $rec)
            {
                $arr[$rec['value']]=lang($rec['tooltip']);
            } 
            asort($arr);
            return $arr;
        }
        
        /**
         * Creates new opportunity
         * 
         * @param string $ref
         * @param string $customer
         * @param string $source
         * @param array  $lines
         * 
         * @return bool
         */
        function createOportunity(string $ref,string $customer,string $source,array $lines=[])
        {
            $data=
            [
                'ord_ref'=>'OPORT'.$customer.formatDate(),
                'ord_refcus'=>$ref,
                'ord_addon'=>formatDate(),
                'ord_addby'=>loged_user('username'),
                'ord_cusacc'=>$customer,
                'ord_done'=>0,
                'ord_type'=>0,
                'ord_source_ref'=>'portal',
                'ord_source'=>'web',
                'odr_status'=>'prop',
                //'ord_desc',
                'enabled'=>0
            ];
            
            if ($this->save($data))
            {
                $lines=array_values($lines);
                foreach($lines as $key=>$line)
                {
                    $lines[$key]=
                    [
                        'ol_ref'=>$data['ord_ref'],
                        'ol_qty'=>$line['qty'],
                        'ol_cusprice'=>$line['price'],
                        'ol_ourpart'=>$line['part'],
                    ];
                }
                if ($this->getModel('OrderLine')->insertBatch($lines))
                {
                    return $this->getLastID();
                }
                return FALSE;
            }
            return FALSE;
        }
        
        /**
         * Returns array with human readable order types
         *  
         * @param bool $showImages
         * @param bool $getText
         * @param bool $langParse
         * 
         * @return array
         */
        function getTypes(bool $showImages=FALSE,bool $getText=TRUE,bool $langParse=FALSE)
        {
            $arr=[];
            $images=
            [
                'opportunities'=>'<i class="fas fa-hand-holding-usd"></i>',
                'quotes'=>'<i class="fas fa-file-invoice-dollar"></i>',
                'orders'=>'<i class="fas fa-file-invoice"></i>'
            ];
            foreach($images as $key=>$image)
            {
                $arr[$key]='';
                if ($showImages)
                {
                    $arr[$key]=$image.'&nbsp;&nbsp;';
                }
                if ($getText)
                {
                    $arr[$key].=$langParse ? lang('orders.ord_type_'.$key) : $key;
                }
            }
            return array_values($arr);
        }
        
        /**
         * Convert opportunity to quote
         * 
         * @param int|array $record
         * 
         * @return boolean
         */
        function convertOportToQuote($record,bool $createQuote=TRUE)
        {
            if (!is_array($record))
            {
                $record=$this->find(is_numeric($record) ? $record : 0);
                if (!is_array($record))
                {
                    return FALSE;
                }
            }
            if ($record['ord_type']!='0' && $record['ord_type']!=0)
            {
                return FALSE;
            }
            $this->save(['ordid'=>$record['ordid'],'ord_done'=>'1','ord_status'=>'win','ord_doneon'=> formatDate()]);
            $ord_ref=$record['ord_ref'];
            $record['ord_ref']=$this->generateNewQuoteNr($record['ordid']);
            $record['ord_type']=1;
            $record['ord_status']='';
            $record['ord_source']='#'.$record['ordid'];
            $record['opport_id']=$record['ordid'];
            $record['ord_value']= floatval($record['ord_cus_value'])-floatval($record['ord_our_value']);
            unset($record['ordid']);
            if ($this->save($record))
            {
                if ($createQuote)
                {
                    $data=$this->getModel('OrderLine')->filtered(['ol_ref'=>$ord_ref])->find();
                    foreach($data as $i=>$line)
                    {
                        $data[$i]['ol_ref']=$record['ord_ref'];
                        unset($data[$i]['olid']);
                    }
                    if ($this->getModel('OrderLine')->insertBatch($data))
                    {
                        $ord_ref=url_tag(url('Orders','opportunities',[$record['opport_id']],['refurl'=>'-curl-']), $ord_ref);
                        $record['ord_desc']=url_tag(url('Orders','quotes',[$this->getLastID()],['refurl'=>'-curl-']), $record['ord_ref']);
                        $this->getModel('Movements')->addItem('oport_to_quote', loged_user('username'),$ord_ref , $record['ord_ref'], $record['ord_cusacc'], $record['ord_desc'],null, '');
                    }
                }
                return TRUE;
            }
            return FALSE;
        }
        
        /**
         * Generate new quote reference number
         * 
         * @param Int $customerID
         * 
         * @return string
         */
        function generateNewQuoteNr(Int $customerID)
        {
            $patern=$this->getOrdersSettings('orders_quotenrpatern');
            if (!Str::contains($patern,'%date%'))
            {
                $patern.='%date%';
            }
            return str_replace('%date%', $customerID.formatDate(),$patern);
        }
        
        
        function getFieldsForUploadForm($edit_acc,$mode='order',bool $isGrid=FALSE)
        {
            $arr=[];
            $mode=$mode=='quotes';
            
            if ($edit_acc)
            {
                $arr['ord_cusacc']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('ord_cusacc')
                        ->setID('ord_cusacc')
                        ->setText('ord_cusacc')
                        ->setAsAdvanced()
                        ->setTab('general')
                        ->setOptions($this->getModel('Customers/Customer')->getCustomersForDropDown('code',null,TRUE))
                        ->setValue($this->getModel('Customers/Customer')->getCustomerForUser(null,'code'));
                
                $arr['ord_ref']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('ord_ref')
                    ->setID('ord_ref')
                    ->setText($mode=='quotes' ? 'ord_ref_quote' : 'ord_ref')
                    ->setMaxLength($this->fieldsTypes['ord_ref']['constraint'])
                    ->setTab('general')
                    ->setValue($mode ? $this->getModel('Quote')->generateNewOrderNr(FALSE) : $this->generateNewOrderNr(FALSE));
           
            } else 
            {
                $arr['ord_cusacc']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('ord_cusacc')
                        ->setID('ord_cusacc')
                        ->setText('ord_cusacc')
                        ->setTab('general')
                        ->setValue($this->getModel('Customers/Customer')->getCustomerForUser(null,'code'));
                
                $arr['ord_ref']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('ord_ref')
                        ->setID('ord_ref')
                        ->setText($mode=='quotes' ? 'ord_ref_quote' : 'ord_ref')
                        ->setMaxLength($this->fieldsTypes['ord_ref']['constraint'])
                        ->setTab('general')
                        ->setValue($mode ? $this->getModel('Quote')->generateNewOrderNr(FALSE) : $this->generateNewOrderNr(FALSE));
            }
                    
            
            if (!$mode)
            {
                $arr['ord_refcus']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName('ord_refcus')
                        ->setID('ord_refcus')
                        ->setText('ord_refcus')
                        ->setMaxLength($this->fieldsTypes['ord_refcus']['constraint'])
                        ->setTab('general')
                        ->setValue($this->generateNewOrderNr(TRUE));
            }
            if (!$isGrid)
            {
                $arr['parts_file']= \EMPORIKO\Controllers\Pages\HtmlItems\UploadField::create()
                        ->setName('parts_file')
                        ->setID('parts_file')
                        ->setText('quotes_upload_parts_file')
                        ->setFormat($edit_acc ? '.xlsx,.csv' :'.xlsx')
                        ->setTab('general')
                        ->setAsJustFileName();
            }
            return $arr;
        }
        
        function getFieldsForForm(array $record=[])
        {
            $arr=[];
            
            $arr['ord_cusacc']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('ord_cusacc')
                    ->setID('ord_cusacc')
                    ->setText('ord_cusacc')
                    ->setTab('general')
                    ->setOptions($this->getModel('Customers/Customer')->getCustomersForDropDown('code',null,TRUE))
                    ->setAsAdvanced()
                    ->setAsRequired();
            
            if (array_key_exists('ord_cusacc', $record) && $record['ord_cusacc']!=null && intval($record['enabled'])==1)
            {
                $arr['ord_cusacc']->setReadOnly()->setName('ord_cusacc_disabled');
                $arr['ord_cusacc_disabled']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                    ->setName('ord_cusacc')
                    ->setID('ord_cusacc')
                    ->setValue($record['ord_cusacc']);
            }        
              
            $arr['ord_ref']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('ord_ref')
                    ->setID('ord_ref')
                    ->setText('opportunities_ref')
                    ->setTab('general')
                    ->setMaxLength(80)
                    ->setAsRequired();
            
            $arr['ord_refcus']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('ord_refcus')
                    ->setID('ord_refcus')
                    ->setText('opportunities_refcus')
                    ->setTab('general')
                    ->setMaxLength(80);

            if (Arr::KeysExists(['ord_status','ord_type','ord_prdsource'], $record) && $record['ord_prdsource']!='lines' && $record['ord_type']==1)
            {
                if (intval($record['enabled'])==1)
                {
                    $arr['ord_prdsource']= \EMPORIKO\Controllers\Pages\HtmlItems\UploadField::create()
                            ->setName('ord_prdsource')
                            ->setID('ord_prdsource')
                            ->setText('quotes_upload_parts_file_email')
                            ->setTab('general');
                }else
                if ((intval($record['enabled'])==1 && array_key_exists('ord_prdsource_list', $record) && is_array($record['ord_prdsource_list'])) || intval($record['enabled'])==0)
                {
                    $arr['ord_value']= \EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField::createCurrButton('far fa-money-bill-alt')
                            ->setName('ord_value')
                            ->setID('ord_value')
                            ->setText('quotes_value')
                            ->setTab('general');
                    
                    if (array_key_exists('ord_prdsource', $record) && $record['ord_prdsource']!='lines')
                    {
                        $arr['ord_prdsource_view']= \EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField::create()
                                ->setName('ord_prdsource_view')
                                ->setID('ord_prdsource_view')
                                ->setText('quotes_prdsource_view')
                                ->setButtonIcon('fas fa-eye')
                                ->setButtonArgs(['data-url'=>url('Documents','show',[0],['file'=>base64url_encode($record['ord_prdsource'])]),'data-newtab'=>'true'])
                                ->setTab('general')
                                ->setReadOnly()
                                ->addArg('style','background-color:#FFF!important;')
                                ->setValue($record['ord_prdsource']);
                    }
                }
                
                if (array_key_exists('ord_prdsource_list', $record) && is_array($record['ord_prdsource_list']))
                {
                    $arr['ord_prdsource']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::createField($arr['ord_prdsource'])
                            ->setOptions($record['ord_prdsource_list']);
                }else
                if (!is_numeric($record[$this->primaryKey]) && strlen($record['ord_prdsource']) > 0)
                {
                    $arr['ord_prdsource']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::createField($arr['ord_prdsource']);
                }
            }else
            {
                $arr['parts']=\EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField::create()
                        ->setName('parts')
                        ->setID('parts')
                        ->setText('')
                        ->setTab('tab_parts')
                        ->setValueFieldVisibility()
                        ->setDefValueCurrency($record['ord_cus_curr']);
            }
            
            if (array_key_exists($this->primaryKey, $record) && is_numeric($record[$this->primaryKey]) && intval($record['enabled'])==1)
            {
                $arr['ord_cusacc']->setReadOnly();
                $arr['ord_refcus']->setReadOnly();
            }
            
            if (array_key_exists('ord_done', $record) && ($record['ord_done']=='1' || $record['ord_done']==1))
            {
                $arr['ord_cusacc']->setReadOnly(TRUE,TRUE);
                $arr['ord_ref']->setReadOnly(TRUE,TRUE);
                $arr['ord_refcus']->setReadOnly(TRUE,TRUE);
                $arr['parts']->setReadOnly()->setTotalsVisibility(TRUE);
            }
            return $arr;
        }
        
        function getForApi($ref,bool $isAdmin=FALSE)
        {
            $model=$this->filtered(['ordid'=>$ref,'|| ord_ref'=>$ref]);
            if (!$isAdmin)
            {
                $select=[];
                foreach($this->getView('vw_orders_info')->allowedFields as $field)
                {
                    $field='`'.$field.'` as `'.strtolower(lang('orders.'.$field)).'`';
                    if (!Str::contains($field, '.'))
                    {
                        $select[]=$field;
                    }
                }
                $select[]='ord_ref';
                $select[]='ord_isquote';
                if (count($select) > 0)
                {
                    $model->select(implode(',',$select));
                }
            }
            $arr=$model->first();
            if (!is_array($arr))
            {
                return null;
            }
            $arr['lines']=$this->getModel('OrderLine')->getAsCSV($arr['ord_ref'],!$isAdmin,$arr['ord_isquote']==1,TRUE);
            if (!$isAdmin)
            {
               unset($arr['ord_ref']); 
               unset($arr['ord_isquote']); 
            }
            return $arr;
        }
        
        /**
         * Generate new random customer order number
         * 
         * @param string $acc
         * 
         * @return string
         */
        function generateCustomerOrderNr(string $acc='',$forOrderRef=FALSE)
        {
            $acc= strlen($acc) < 3 ? $this->getModel('Customers/Customer')->getCustomerForLogedUser(): $acc;
            return ($forOrderRef ? 'CRM' : '').$acc.formatDate();
        }
        
        /**
         * Returns array with available order statuses
         *  
         * @return array
         */
        function getAvaliableStatuses()
        {
            return $this->getModel('Settings')->getListSettings('orders.orders_status_type_'); 
        }
        
        /**
         * Return array with given customer orders references
         * 
         * @param string $cust
         * 
         * @return array
         */
        function getLiveOrdersForForm(string $cust='*',bool $showQuotes=FALSE)
        {
            if ($cust=='*')
            {
                $cust=$this->getModel('Customers/Customer')->getCustomerForLogedUser();
            }
            return $this->getForForm('ord_ref',$showQuotes ? 'ord_quoteref' :'ord_refcus', FALSE, null, ['ord_cusacc'=>$cust,'ord_isquote'=>$showQuotes ? 1 : 0]);
        }
        
        /**
         * Returns array with live orders details
         * 
         * @param string $cust
         * 
         * @return array
         */
        function getLiveOrders(string $cust='*')
        {
            if ($cust=='*')
            {
                $cust=$this->getModel('Customers/Customer')->getCustomerForLogedUser();
            }
            return $this->filtered(['ord_cusacc'=>$cust])->find();
        }
        
        /**
         * Returns array with invalid (not enabled) orders id`s
         * 
         * @param string $customer
         * 
         * @return array
         */
        function getInvalidOrdersList(string $customer=null)
        {
            $filters=['enabled'=>0];
            if ($customer!=null)
            {
                $filters['ord_cusacc']=$customer;
            }
            $arr=$this->getForForm('ordid', 'ord_ref', FALSE, 0, $filters);
            return count($arr) > 0 ? array_values(array_flip($arr)) : [];
        }
        
        /**
         * Returns array with invoiced orders by customer
         * 
         * @param string $customer
         * @param string $valueField
         * 
         * @return array
         */
        function getInvoicedOrders(string $customer=null,string $valueField='ord_ref')
        {
            $filters=['enabled'=>0,'ord_invoicenr len<'=>2];
            if ($customer!=null)
            {
                $filters['ord_cusacc']=$customer;
            }
            
            $arr=$this->getForForm('ordid', $valueField, FALSE, 0, $filters);
            return count($arr) > 0 ? array_values(array_flip($arr)) : [];
        }
        
        /**
         * Returns array with payments info for given order
         * 
         * @param string $order
         * 
         * @return array
         */
        function getPaymentsHistory(string $order)
        {
            $order=$this->where('ord_ref',$order)->first();
            if (!is_array($order))
            {
                return [];
            }
            $order=$order['ord_invoicenr'];
            $select=[];
            $select[]='`mhfrom` as `paidref`';
            $select[]='`mhto` as `paidvalue`';
            $select[]='`mhdate` as `date`';
            return $this->getView('vw_movements')->select(implode(',',$select))->where(['mhtype_desc'=>'payment_info','mhref'=>$order])->find();
        }
        
        /**
         * Enable / Disable order and order lines
         * 
         * @param string $ordref
         * @param bool   $enabled
         * 
         * @return boolean
         */
        function enableOrder(string $ordref,bool $enabled=TRUE)
        {
            $enabled=$enabled ? 1 : 0;
            if ($this->builder()->set('enabled',$enabled)->where('ord_ref',$ordref)->update())
            {
                return $this->getModel('OrderLine')->builder()->set('enabled',$enabled)->where('ol_ref',$ordref)->update();
            }
            return FALSE;
        }
        
        /**
         * Returns array with emails to which notifications have to be send
         * 
         * @return array
         */
        function getNotificationEmails()
        {
            $arr=$this->getOrdersSettings('orders_notifygroups');
            $arr=$this->getModel('Auth/User')->getUsersByAccessGroups(is_array($arr) ? $arr : []);
            $emails=[];
            foreach($arr as $email)
            {
                if (Str::isValidEmail($email['email']))
                {
                    $emails[]=$email['email'];
                }
            }
            return $emails;
        }
        
        /**
         * Returns array with orders settings
         * 
         * @param string $setting
         * 
         * @return array
         */
        function getOrdersSettings(string $setting='*')
        {
            $arr=$this->getModel('Settings')->get('orders.'.$setting,TRUE);
            return is_array($arr) && count($arr) > 0 ? $arr : ($setting=='*' ? [] : $arr); 
        }
        
        /**
         * Returns array with available order placing API methods
         * 
         * @return array
         */
        function getApiPlacingSettings()
        {
            $arr=$this->getModel('Settings')->getListSettings('orders.orders_apiplaceorder_',TRUE);
            array_unshift($arr,lang('orders.settings_sendnewordertoapi_no'));
            return $arr;
        }
        
        /**
         * Update paid info against invoice
         * 
         * @param string $invoicenr
         * @param string $paidref
         * @param mixed  $paidvalue
         * 
         * @return bool
         */
        function updatePaymentInfo(string $invoicenr,string $paidref,$paidvalue)
        {
            
            $order=$this->filtered(['ord_invoicenr'=>$invoicenr])->first();
            if (!is_array($order))
            {
                return FALSE;
            }
            $paidvalue=$order['ord_paidvalue']+$paidvalue;
            return $this->builder()->set(['ord_paidvalue'=>$paidvalue])->where('ord_invoicenr',$invoicenr)->update();
        }
        
        /**
         * Add order upload task to queue
         * 
         * @param string $order
         * @param string $file
         * @param string $customer
         * 
         * @return boolean
         */
        function addUploadTask(string $order,string $file,string $customer)
        {
            if (!file_exists($file))
            {
                return FALSE;
            }
            
            if (strlen($order) < 3)
            {
                return FALSE;
            }
            
            if (strlen($customer) < 3)
            {
                return FALSE;
            }
            $order=$this->where('ord_ref',$order)->first();
            if (!is_array($order))
            {
                return FALSE;
            }
            $data=[];
            if (intval($order['ord_isquote'])==0)
            {
                $data[]='orders';
            }else
            {
                $data[]='quotes';
            }
            $data[]=FALSE;
            $data[]=['order'=>$order['ord_ref'],'customer'=>$customer,'file'=>$file,'iscustomer'=> intval(loged_user('iscustomer'))==1];
            return $this->getModel('Tasks/Task')->addNew('Upload Order',['controller'=>'Orders','action'=>'upload','args'=>$data],'upload_order');
        }
        
        
        private function file_get_contents_chunked($sql, $file,$parts, $chunk_size, $queryValuePrefix, $callback)
        {
        try 
        {
            $handle = fopen($file, "r");
            $i = 0;
            while (! feof($handle)) 
            {
                call_user_func_array($callback, array(
                    fread($handle, $chunk_size),
                    &$handle,
                    $i,
                    &$queryValuePrefix,
                    $sql,
                    &$parts
                ));
                $i ++;
            }   
            fclose($handle);
            } catch (Exception $e) {
                trigger_error("file_get_contents_chunked::" . $e->getMessage(), E_USER_NOTICE);
                return false;
            }

            return true;
        }
        
        function getPartsForOrder(string $customer,string $fieldToCompare)
        {
            $arr=[];
            $parts=$this->getModel('Products/Product')->generatePriceFileFromDB(['customer'=>$customer],['prd_tecdocpart','prd_commodity','prd_origin','prd_apdpartnumber']);
            foreach(is_array($parts) ? $parts : [] as $record)
            {
                $arr[$record[$fieldToCompare]]=$record;
            }
            return $arr;
        }
        
        /**
         * Upload order to database from file
         * 
         * @param mixed  $orderNr
         * @param string $fileName
         * @param string $customer
         * 
         * @return boolean
         */
        function uploadOrder($orderNr,$fileName,string $customer,bool $fromCustomer)
        {
            if (is_string($fileName))
            {
               $fileName= parsePath($fileName,TRUE); 
               if (!file_exists($fileName))
                {
                    return FALSE;
                }
                if (Str::endsWith(strtolower($fileName), 'json'))
                {
                    $fileName= json_decode(file_get_contents($fileName),TRUE);
                    if (!is_array($fileName))
                    {
                       return FALSE;
                    }
                }
            }else
            if (!is_array($fileName) || (is_array($fileName) && count($fileName) < 1))
            {
                return FALSE;
            }
            
            
            if (is_array($orderNr))
            {
               $order=$orderNr;
               $orderNr=$order['ord_ref'];
            }else
            {
                $order=$this->where('ord_ref',$orderNr)->first();
            }
            //Enable order
            $this->builder()->set(['enabled'=>2])->where('ord_ref',$orderNr)->update();
            
            //Get parts data for validation
            $parts=$this->getPartsForOrder($customer,'prd_apdpartnumber');
           
            $sql=$this->getModel('OrderLine')->builder()->set(
                    [
                        'ol_oepart'=>'{ol_oepart}',
                        'ol_qty'=>'{ol_qty}',
                        'ol_ref'=>$orderNr,
                        'enabled'=>'{enabled}',
                        'ol_commodity'=>'{prd_commodity}',
                        'ol_origin'=>'{prd_origin}',
                        'ol_ourpart'=>'{prd_apdpartnumber}',
                        'ol_price'=>'{prd_cusprice}',//prd_cusprice
                        'ol_cusprice'=>'{ol_cusprice}',
                        'enabled'=>'{enabled}',
                        'ol_status'=>'{ol_status}'
                    ])->getCompiledInsert(FALSE);
            if (is_array($fileName))
            {
                file_as_array:
                foreach($fileName as $line)
                {
                    if (is_array($line) && count($line) > 1 && is_numeric($line[1]))
                    {
                        $repl=
                        [
                            '{ol_oepart}'=>'',
                            '{ol_qty}'=>$line[1],
                            '{enabled}'=>0,
                            '{prd_commodity}'=>'',
                            '{prd_origin}'=>'',
                            '{prd_apdpartnumber}'=>$line[0],
                            '{prd_cusprice}'=>0,
                            '{ol_cusprice}'=>count($line) > 2 ? $line[2] : 0,
                            '{ol_status}'=>''
                        ];
                        if (array_key_exists($line[0], $parts))
                        {
                            $line=$parts[$line[0]];
                            $repl['{prd_commodity}']=$line['prd_commodity'];
                            $repl['{prd_origin}']=$line['prd_origin'];
                            $repl['{prd_apdpartnumber}']=$line['prd_apdpartnumber'];
                            $repl['{ol_oepart}']=$line['prd_tecdocpart'];
                            $repl['{prd_cusprice}']=$line['prd_price'];
                            $repl['{enabled}']=1;
                            if (doubleval($repl['{ol_cusprice}']) > 0 && doubleval($repl['{ol_cusprice}'])!= doubleval($line['prd_price']))
                            {
                                $repl['{ol_status}']=lang('orders.error_rderline_status_price',[$repl['{ol_cusprice}']]);
                                //$repl['{enabled}']=0;
                            }
                        }
                        $this->getModel('OrderLine')->db()->query(str_replace(array_keys($repl), $repl, $sql));
                    }
                }
                return TRUE;
            }else
            {
                if ( $xlsx = \Shuchkin\SimpleXLSX::parse($fileName) ) 
                {
                    $fileName=$xlsx->rows();
                    if (!is_array($fileName))
                    {
                        return FALSE;
                    }
                    if (count($fileName) < 2)
                    {
                        return FALSE;
                    }
                    
                    unset($fileName[0]);
                    if (count($fileName) < 1)
                    {
                        return FALSE;
                    }
                    goto file_as_array;
                } else 
                {
                    return FALSE;
                }
            }
            
            /*$handle = fopen($fileName, "r");
            $arr=[];
            while (($line = fgets($handle)) !== false)
            {
                $colsValuses=explode(',', $line);
                if (is_array($colsValuses) && count($colsValuses) > 1 && is_numeric($colsValuses[1]))
                {
                    $repl=
                    [
                        '{ol_oepart}'=>$colsValuses[0],
                        '{ol_qty}'=>$colsValuses[1],
                        '{enabled}'=>0,
                        '{prd_commodity}'=>'',
                        '{prd_origin}'=>'',
                        '{prd_apdpartnumber}'=>'',
                        '{prd_cusprice}'=>0
                    ];
                    if (array_key_exists($colsValuses[0], $parts))
                    {
                        $colsValuses=$parts[$colsValuses[0]];
                        $repl['{prd_commodity}']=$colsValuses['prd_commodity'];
                        $repl['{prd_origin}']=$colsValuses['prd_origin'];
                        $repl['{prd_apdpartnumber}']=$colsValuses['prd_apdpartnumber'];
                        $repl['{prd_cusprice}']=$colsValuses['prd_price'];
                        $repl['{enabled}']=1;
                    }
                    $this->getModel('OrderLine')->db()->query(str_replace(array_keys($repl), $repl, $sql));
                }
            }/**/
            if (file_exists($fileName))
            {
                //unlink($fileName);
            }
        }
        
        /**
         * Set order enabled field
         * 
         * @param string $orderNr
         * @param bool   $asActive
         * 
         * @return bool
         */
        function setOrderActive(string $orderNr,bool $asActive=TRUE)
        {
            return $this->builder()->set(['enabled'=>$asActive ? 1 : 0])->where('ord_ref',$orderNr)->update();
        }
        
        function setOrderAsConfirmed($orderRef)
        {
            $orderRef=$this->filtered(['ordid'=>$orderRef,'|| ord_ref'=>$orderRef,'|| ord_refcus'=>$orderRef])->first();
            if (!is_array($orderRef))
            {
                return FALSE;
            }
            
            $this->getModel('OrderLine')->builder()->set(['enabled'=>1,'ol_status'=>''])->where('ol_ref',$orderRef['ord_ref'])->update();
            return $this->builder()->set(['enabled'=>1,'ord_status'=>'bo'])->where('ord_ref',$orderRef['ord_ref'])->update();
        }
        
        /**
         * Set given order reference (and/or customer reference)
         * 
         * @param int          $orderID
         * @param string|null  $ref
         * @param string|null  $custRef
         * 
         * @return boolean
         */
        function setOrderRef(int $orderID,$ref,$custRef=null)
        {
            $set=[];
            if ($custRef!=null)
            {
                $set['ord_refcus']=$custRef;
            }
            
            if ($ref!=null)
            {
                $set['ord_ref']=$ref;
            }
            if (count($set) < 1)
            {
                return FALSE;
            }
           
            return $this->builder()->set($set)->where('ordid',$orderID)->update();
        }
        
        function generateOrderFile($orderRef,string $mode,string $fileName=null)
        {
            $record=$this->filtered(['ordid'=>$orderRef,'|| ord_ref'=>$orderRef,'|| ord_refcus'=>$orderRef])->first();
            if (!is_array($record))
            {
                return FALSE;
            }
            if ($fileName==null || ($fileName!=null && strlen($fileName) < 2))
            {
                $fileName= is_numeric($orderRef) ? $record['ord_ref'] : $orderRef;
            }
            if (Str::contains($fileName, '.'))
            {
                $fileName=Str::before($fileName, '.');
            }
            
            $mode=$mode!='csv' ? 'xlsx' : 'csv';
            $fileName.='.'.$mode;
            $fileName=parsePath('@temp/'.$fileName,TRUE);
            
            $lines=$this->getModel('OrderLine')->getAsCSV($record['ord_ref'],TRUE);
            if (count($lines) < 1)
            {
                return FALSE;
            }
            
            if ($mode=='csv')
            {
                Arr::toCSVFile($lines,$fileName,TRUE);
            } else 
            {
                array_unshift($lines, array_keys($lines[0]));
                $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($lines);
                $xlsx->saveAs($fileName);
            }
            
            return $fileName;
        }
        
        /**
         * Returns new unique order number
         * 
         * @param bool $forCustomer
         * 
         * @return string
         */
        function generateNewOrderNr(bool $forCustomer)
        {
            if ($forCustomer)
            {
                return ($this->getModel('Customers/Customer')->getCustomerForUser(null,'code')).formatDate();
            }
            return 'CRM'.formatDate();
        }
        
        /**
         * Get qty of live items (opportunities, quotes, orders) for given customer
         * 
         * @param string $cust
         * 
         * @return Int
         */
        function getLiveItemsForCustomer(string $cust)
        {
            return 
            [
                'opportunities'=>$this->count(['enabled'=>1,'ord_type'=>0,'ord_done'=>'0','ord_cusacc'=>$cust]),
                'quotes'=>$this->count(['enabled'=>1,'ord_type'=>1,'ord_done'=>'0','ord_cusacc'=>$cust]),
                'orders'=>$this->count(['enabled'=>1,'ord_type'=>2,'ord_done'=>'0','ord_cusacc'=>$cust])
            ];
        }
        
        function getLiveOrdersForCustomer(string $cust=null)
        {
            if ($cust==null && intval(loged_user('iscustomer'))==1)
            {
                $cust=$this->getModel('Customers/Customer')->getCustomerForUser(0,'code');
            }
            $filters=['enabled'=>1,'ord_done'=>0];
            if (strtolower($cust)!='all' && $cust!=null)
            {
                $filters['ord_cusacc']=$cust;
            }
            return $this->filtered($filters)->orderBy('ord_addon DESC')->limit(10)->find();
        }
        
        function getYearUsage(bool $addTotal=TRUE,string $cust=null)
        {
            $filters=[];
            if ($cust!=null)
            {
                $filters['odr_cusacc']=$cust;
            }
            $filters['ord_addon %']= formatDate('now', 'Y').'mth';
            $month_sql=$this->filtered($filters)->selectCount('ord_addon')->getCompiledSelect();
            $months_names=lang('system.general.months_names');
            $select=[];
            if ($addTotal)
            {
                $select[]='('.str_replace('mth', '', $month_sql).') AS `total`';
            }
            for($i=1;$i<13;$i++)
            {
                $select[]= '('.str_replace('mth', $i < 10 ? '0'.$i: $i, $month_sql).') AS `'.(array_key_exists($i-1, $months_names) ? $months_names[$i-1] : $i).'`';
            }
            $arr=$this->select(implode(',',$select))->find();
            return  count($arr) > 0 ? $arr[0] : [];
        }
        
        function installstorage() 
        {
            //parent::installstorage();
            if ($this->existsInStorage() && $this->getModel('Customers/Customer')->existsInStorage())
            {
                $this->setView('vw_orders_info', "
                    SELECT 
`ord`.*,
(CASE 
 WHEN `ord`.`ord_type`=1 THEN 'quotes'
 WHEN `ord`.`ord_type`=2 THEN 'orders'
 ELSE 'opportunities'
 END) as `ord_type_full`,
`cus`.`name` as `ord_cus_name`,
(SELECT REPLACE(GROUP_CONCAT(`ct`.`ct_email`,';'),',',';') FROM `contacts` as `ct` WHERE `ct`.`ct_account` LIKE CONCAT('%',`ord`.`ord_cusacc`,'%')) as `ord_cus_emails`,
`cus`.`terms_curr` as `ord_cus_curr`,
`cus`.`terms_price` as `ord_cus_price`,
(SELECT SUM(`ol1`.`ol_qty`*`ol1`.`ol_cusprice`) FROM `orders_lines` as `ol1` WHERE `ol1`.`ol_ref`=`ord`.`ord_ref`) as `ord_cus_value`,
(IF (LENGTH(`ord`.`ord_value`) > 0 AND `ord`.`ord_value` > 0, `ord`.`ord_value`,(SELECT SUM(`ol2`.`ol_qty`*`ol2`.`ol_price`) FROM `orders_lines` as `ol2` WHERE `ol2`.`ol_ref`=`ord`.`ord_ref`))) as `ord_our_value`
FROM `orders` as `ord`
LEFT JOIN `customers` as `cus` ON `cus`.`code`=`ord`.`ord_cusacc`
                ");
            }
        }
}

/*
 * 
 * 


SELECT
`ord`.`olid`,
`ord`.`ol_ref`,
`ord`.`ol_oepart`,
`prd`.`prd_apdpartnumber` as `ol_ourpart`,
`ord`.`ol_qty`,
IF(length(`prd`.`prd_apdpartnumber`) > 0, 1, 0) as `is_valid`
FROM `orders_lines` as `ord`
LEFT JOIN `products` as `prd` ON `prd`.`prd_tecdocpart`=`ord`.`ol_oepart`


select `orl`.`olid` AS `olid`,`orl`.`ol_ref` AS `ol_ref`,`prd`.`prd_tecdocpart` AS `ol_oepart`,`prd`.`prd_apdpartnumber` AS `ol_ourpart`,`orl`.`ol_qty` AS `ol_qty`,`price`.`prdpr_lvl_t1` AS `price_t1`,`price`.`prdpr_lvl_js2` AS `price_js2`,`price`.`prdpr_lvl_js3` AS `price_js3`,`prd`.`prd_commodity` AS `ol_commodity`,`prd`.`prd_origin` AS `ol_origin` from ((`dbs2054639`.`orders_lines` `orl` left join `dbs2054639`.`products` `prd` on(((`prd`.`prd_tecdocpart` = `orl`.`ol_oepart`) or (`prd`.`prd_apdpartnumber` = `orl`.`ol_oepart`)))) left join `dbs2054639`.`products_prices` `price` on((`price`.`prdpr_part` = `prd`.`prd_apdpartnumber`))) where (`orl`.`enabled` = 0)


SELECT 
`ol`.`olid`, 
`ol`.`ol_ref`, 
(CASE WHEN `prd`.prd_tecdocpart IS NOT NULL THEN `prd`.prd_tecdocpart ELSE `ol`.`ol_oepart` END) as `ol_oepart`, 
`prd`.`prd_apdpartnumber` as `ol_ourpart`, 
`ol`.`ol_qty`, 
`ol`.`ol_price`, 
`ol`.`ol_cusprice`, 
`prd`.prd_commodity as `ol_commodity`, 
`prd`.prd_origin as `ol_origin`, 
`ol`.`ol_status`, 
`ol`.`ol_avalqty`, 
`ol`.`ol_cusacc`, 
`ol`.`enabled`,
`prd`.`prd_price_eur100` as  'prd_price_eur100',
`prd`.`prd_price_eur100` as  'prd_price_eur100',
`prd`.`prd_price_eur300` as  'prd_price_eur300',
`prd`.`prd_price_eur400` as  'prd_price_eur400',
`prd`.`prd_price_eur500` as  'prd_price_eur500',
`prd`.`prd_price_eur600` as  'prd_price_eur600',
`prd`.`prd_price_eur700` as  'prd_price_eur700',
`prd`.`prd_price_eur850` as  'prd_price_eur850',
`prd`.`prd_price_eur900` as  'prd_price_eur900',
`prd`.`prd_price_row300` as  'prd_price_row300',
`prd`.`prd_price_row400` as  'prd_price_row400',
`prd`.`prd_price_row500` as  'prd_price_row500',
`prd`.`prd_price_row600` as  'prd_price_row600',
`prd`.`prd_price_row700` as  'prd_price_row700',
`prd`.`prd_price_rowstg` as  'prd_price_rowstg'
FROM `orders_lines` as `ol`
LEFT JOIN `products_new` as `prd` ON `prd`.`prd_apdpartnumber`=`ol`.`ol_oepart` OR `prd`.`prd_tecdocpart`=`ol`.`ol_oepart`
WHERE `ol`.`enabled`=0


SELECT 
`ord`.*,
(SELECT GROUP_CONCAT(`ct`.`ct_email` SEPARATOR ';') FROM `contacts` as `ct` WHERE `ct`.`ct_account` LIKE CONCAT('%',`ord`.`ord_cusacc`,'%')) as `acc_emails`,
(SELECT count(`ol`.`olid`) FROM `orders_lines` as `ol` WHERE `ol`.`ol_ref`=`ord`.`ord_ref` AND `ol`.`enabled`=1) as `lines_qty`,
ROUND((SELECT sum(`olv`.`ol_qty`*`olv`.`ol_price`) FROM `orders_lines` as `olv` WHERE `olv`.`ol_ref`=`ord`.`ord_ref` AND `olv`.`enabled`=1),2) as `order_value`,
(SELECT `cus`.`terms_curr` FROM `customers` as `cus` WHERE `cus`.`code`=`ord`.`ord_cusacc`) as `order_curr`,
(SELECT `cusi`.`name` FROM `customers` as `cusi` WHERE `cusi`.`code`=`ord`.`ord_cusacc`) as `ord_cus_name`
FROM `orders` as `ord`


  */
?>