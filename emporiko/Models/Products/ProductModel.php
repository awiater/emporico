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
 
namespace EMPORIKO\Models\Products;

use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\Strings as Str;

class ProductModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products';//products_new products
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'prid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['prd_brand','prd_productfamily','prd_apdpartnumber','prd_tecdocpart','prd_description'
                                  ,'prd_weight','prd_origin','prd_tecdocid','prd_commodity','prd_unitofissue','prd_boxqty'
                                  ,'prd_leadtime','prd_updated',/*'prd_price_eur100','prd_price_eur300','prd_price_eur400'
                                  ,'prd_price_eur500','prd_price_eur600','prd_price_eur700','prd_price_eur850','prd_price_eur900'
                                  ,'prd_price_row300','prd_price_row400','prd_price_row500','prd_price_row600','prd_price_row700'
                                  ,'prd_price_rowstg',*/'enabled'];
	
	protected $validationRules =
	 [
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'prid'=>                ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'prd_brand'=>           ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'prd_productfamily'=>   ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'prd_apdpartnumber'=>   ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
                'prd_tecdocpart'=>      ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
                'prd_description'=>     ['type'=>'VARCHAR','constraint'=>'200','null'=>FALSE],
                'prd_weight'=>          ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'prd_origin'=>          ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'prd_tecdocid'=>        ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'prd_commodity'=>       ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'prd_unitofissue'=>     ['type'=>'INT','constraint'=>'11','null'=>FALSE],
                'prd_boxqty'=>          ['type'=>'INT','constraint'=>'11','null'=>FALSE],
                'prd_leadtime'=>        ['type'=>'INT','constraint'=>'11','null'=>FALSE],
                'prd_updated'=>         ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'prd_price_eur100'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur300'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur400'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur500'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur600'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur700'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur850'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_eur900'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_row300'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_row400'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_row500'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_row600'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_row700'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'prd_price_rowstg'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE],
                'enabled'=>             ['type'=>'INT','constraint'=>'11','default'=>1],
	];	
                
	/**
         * Generates price file from Database
         * 
         * @param array $filters
         * @param array $columns
         * @param bool $external
         * 
         * @return array
         */
        function generatePriceFileFromDB(array $filters,array $columns=[],bool $external=FALSE)
        {
            if (array_key_exists('prd_brand', $filters) && Str::contains($filters['prd_brand'], ','))
            {
                $filters['prd_brand In']=explode(',',$filters['prd_brand']);
                unset($filters['prd_brand']);
            }
            if (array_key_exists('customer', $filters))
            {
                $customer=$this->getModel('Customers/Customer')->filtered(['code'=>$filters['customer']])->first();
                if (is_array($customer) && array_key_exists('terms_price', $customer))
                {
                    $customer=$customer['terms_price'];
                } else 
                {
                    return [];
                }
            }else
            {
                return [];
            }
            $filters['_convertnames']=TRUE;
            return  $this->getModel('PriceFile')->getPriceFileByName($customer,$filters);
        }
        
        /**
         * Returns array with fields (columns) names for form field
         * 
         * @return array   
         */
        function getFieldsNamesForPicker()
        {
            $arr=[];
            $fields=
            [
                'prd_brand',
                'prd_productfamily',
                'prd_tecdocpart',
                'prd_weight',
                'prd_origin',
                'prd_tecdocid',
                'prd_commodity',
                'prd_unitofissue',
                'prd_boxqty',
                'prd_leadtime',
                'prd_updated'
            ];
            foreach($fields as $field)
            {
                $arr[$field]=lang('products.'.$field);
            }    
            return $arr;
        }
        
        function getFieldsNames(bool $external=FALSE,bool $asString=FALSE,$prefix=null,bool $forForm=FALSE) 
        {
            $arr=[];
            foreach($this->allowedFields as $field)
            {
                if ($field!='prd_updated' && $field!=$this->primaryKey && !Str::startsWith($field, 'prd_price_'))
                {
                    if ($external)
                    {
                        if ($field=='enabled')
                        {
                            goto end_of_loop;
                        }else
                        {
                            if ($forForm)
                            {
                               $arr[$field]=str_replace('prd_','',$field); 
                            }else
                            {
                                $arr[]=($prefix!=null ? $prefix.'.':'').'`'.$field.'` as `'.str_replace('prd_','',$field).'`';
                            }  
                        }
                    } else 
                    {
                        if ($forForm)
                        {
                            $arr[$field]=$field;
                        }else
                        {
                            $arr[]=$field;  
                        }
                    }
                    
                    
                    end_of_loop:
                }
                
            }
            return $asString ? implode(',',$arr) : $arr;
        }
        
        /**
         * Find products by using common filters and given search word
         * 
         * @param string               $search
         * @param string|array|null    $orderby
         * @param int|bool|null        $paginate
         * 
         * @return array
         */
        function findByCommonFields(string $search,string $orderby=null,$paginate=null)
        {
            $search=
            [
                'prd_brand %'=>$search,
                '|| prd_apdpartnumber %'=>$search,
                '|| prd_tecdocpart %'=>$search,
                '|| prd_description %'=>$search
            ];
            $search=$this->filtered($search,$orderby,$paginate);
            return $paginate!=null ? $search : $search->find();
        }
        
        /**
         * Find products and products price for given customer and by using common filters and given search word 
         * 
         * @param string             $search
         * @param string             $customer
         * @param string|array|null  $orderby
         * @param int|bool|null      $paginate
         * 
         * @return array
         */
        function findByCommonFieldsWithPrice(string $search,string $customer,string $orderby=null,$paginate=null)
        {
            //$customer=$this->getModel('Customers/Customer')->filtered(['code'=>$customer,'|| cid'=>$customer])->first();
            $search=
            [
                'prd_brand %'=>$search,
                '|| prd_apdpartnumber %'=>$search,
                '|| prd_tecdocpart %'=>$search,
                '|| prd_description %'=>$search
            ];
            $search=$this->filtered($search,$orderby);
            $customerTbl=$this->getModel('Customers/Customer')->table;
            $search->join($customerTbl,$customerTbl.".code='".$customer."'",'left');
            $fileTbl=$this->getModel('PriceFilePart')->table;
            $search->join($fileTbl,$fileTbl.'.prf_ourpart='.$this->table.'.prd_apdpartnumber AND '.$fileTbl.".prf_name=".$customerTbl.'.terms_price','left');
            $search=$search->select($this->table.'.*,'.$fileTbl.".prf_price as 'prd_price',".$customerTbl.".terms_curr as 'prd_curr'");
            if ($paginate!=null && $paginate!=FALSE)
            {
                if ($paginate==0)
		{
                    return $search->find();
		}
                if (is_bool($paginate) && $paginate)
                {
                    $paginate=config('Pager')->perPage;
                }
                        
                return $search->paginate($paginate);
            }
            return $search->find();
        }
        
        /**
         * Check whats parts edit mode is set
         * 
         * @param  bool $returnAsBool
         * 
         * @return mixed
         */
        function getPartsEditMode($returnAsBool=FALSE)
        {
            
            $param=$this->getModel('Settings')->get('products.products_pricingmode',FALSE,'value',FALSE);
            return $param==null ? ($returnAsBool ? TRUE :'db') : (is_dir(parsePath($param,TRUE)) ? ($returnAsBool ? FALSE :$param) : ($returnAsBool ? TRUE :'db'));  
        }

        /**
         * Add brands filter field to table view
         * 
         * @param \EMPORIKO\Controllers\Pages\TableView $view
         * 
         * @return array
         */
	function getBrandsFormForm(\EMPORIKO\Controllers\Pages\TableView &$view)
        {
            $arr=[];
            foreach($this->groupBy('prd_brand')->orderBy('prd_brand')->find() as $item)
            {
                $view->addFilterField('prd_brand', $item['prd_brand'], $item['prd_brand']);
            }
            return $arr;
        }
        
        /**
         * Returns array with unique data from given field (column)
         * 
         * @param  string $field
         * @param  string $value
         * @param  bool   $empty
         * @return array
         */
        function getDataForDropDown($field,$value=null,$empty=FALSE)
        {
            if (!in_array($field, $this->allowedFields))
            {
                return [];
            }
            $value=$value==null ? $this->primaryKey : $value;
            if (!in_array($value, $this->allowedFields))
            {
                return [];
            }
            $arr=[];
            if ($empty!=FALSE)
            {
                if (is_string($empty))
                {
                    $arr[]=$empty;
                } else 
                {
                    $arr[]=' ';
                }
            }
            foreach($this->groupby($field)->orderby($field)->find() as $record)
            {
                $arr[$record[$value]]=$record[$field];
            }
            return $arr;
        }
        
	function getPricingFields()
        {
            return ['prd_price_eur100','prd_price_eur300','prd_price_eur400'
                    ,'prd_price_eur500','prd_price_eur600','prd_price_eur700','prd_price_eur850','prd_price_eur900'
                    ,'prd_price_row300','prd_price_row400','prd_price_row500','prd_price_row600','prd_price_row700'
                    ,'prd_price_rowstg'];
        }
        
	/**
	 * Returns array with custom tabs
         * 
         * @return array
	 */
	function getCustomTabs()
	{
            return parent::getCustomTabsData('products','custom_tab_');
	}
        
        function getOrdersForProduct(string $product,string $customer=null)
        {
            $select=[];
            $select[]='`ol_ref` as `order`';
            $select[]='`ol_refcus` as `ordercus`';
            $select[]='`ol_ordid` as `orderid`';
            $select[]='`ol_price` as `price`';
            $select[]='`ol_status` as `status`';
            $select[]='`ol_acc` as `customer`';
            $select[]='SUM(`ol_qty`) as `qty`';     
            $filters=['( ol_ourpart'=>$product,'|| ol_ourpart )'=>$product];
            if ($customer!=null)
            {
                $filters['ol_acc']=$customer;
            }
            return $this->getView('vw_products_partsinorders')->filtered($filters)->select(implode(',',$select))->groupBy('ol_ref')->find();
        }
        
        /**
         * Return array with columns names for pricing update
         * 
         * @return array
         */
        function getPriceUpdateFileColumns()
        {
            return [lang('products.prd_apdpartnumber'),lang('products.prd_price',['']),lang('products.prf_name_colupl')];
        }
        
        /**
         * Returns path to pricing upload folder
         * 
         * @param bool $parse
         * 
         * @return string
         */
        function getPricingUpdateFolderPath(bool $parse=FALSE)
        {
            $path=$this->getModel('Settings')->get('products.products_priceupdatedir');
            if (!Str::startsWith($path, '@storage'))
            {
                $path='@storage'.(Str::startsWith($path, '/') ? '' : '/').$path;
            }
            return $parse? parsePath($path,TRUE) : $path;
        }
        
        function updatePriceFileInBackground()
        {
            $dir=$this->getPricingUpdateFolderPath(TRUE);
            $columns=$this->getModel('Settings')->get('products.products_priceterms');
            $columns=explode(',',$columns);
            $arr=[];
            foreach (directory_map($dir) as $file)
            {
                $fileName=$dir.'/'.$file;
                if (file_exists($fileName))
                {
                    $file = fopen($fileName, "r");
                    while (($raw_string = fgets($file)) !== false)
                    {
                        $row = str_getcsv($raw_string);
                        if (is_array($row) && count($row) > 2)
                        {
                           if (in_array($row[2], is_array($columns) ? $columns : []) && is_numeric($row[1]))
                           {
                               $this->builder()->set([('prd_price_'. strtolower($row[2]))=>$row[1]])->where('prd_apdpartnumber',$row[0])->update();
                           }
                        }
                    }
                    fclose($file);
                    unlink($fileName);
                }
            }
        }
        
        function getFieldsForForm(array $record) 
        {
            //return $this->getModel('Part')->getFieldsForForm($record);
           $arr=[];
           $arr['prd_apdpartnumber']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('prd_apdpartnumber')
                    ->setID('prd_apdpartnumber')
                    ->setText('prd_apdpartnumber')
                    ->setMaxLength(120)
                    ->setTab('general')
                    ->setAsRequired();
           
           $arr['prd_tecdocpart']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('prd_tecdocpart')
                    ->setID('prd_tecdocpart')
                    ->setText('prd_tecdocpart')
                    ->setMaxLength(120)
                    ->setTab('general')
                    ->setAsRequired();
           
           $arr['prd_description']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('prd_description')
                    ->setID('prd_description')
                    ->setText('prd_description')
                    ->setMaxLength(200)
                    ->setTab('general');
           
           foreach(['prd_brand','prd_productfamily'] as $field)
           {
               $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                           ->setName($field)
                           ->setID($field)
                           ->setText($field)
                           ->setOptions($this->getDataForDropDown($field,$field,TRUE))
                           ->setAsAdvanced()
                           ->setTab('general');
      
           }
           $arr['prd_brand']->setAsRequired();
           
           $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                    ->setName('enabled')
                    ->setID('enabled')
                    ->setText('enabled')
                    ->setTab('general');
           
           $arr['prd_origin']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('prd_origin')
                    ->setID('prd_origin')
                    ->setText('prd_origin')
                    ->setOptions($this->getDataForDropDown('prd_origin','prd_origin',TRUE))
                    ->setAsAdvanced()
                    ->setTab('tab_other');
           
           foreach(['prd_weight','prd_unitofissue','prd_boxqty','prd_leadtime'] as $field)
           {
               $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\NumberField::create()
                           ->setName($field)
                           ->setID($field)
                           ->setText($field)
                           ->setStep($field=='prd_weight' ? '0.01' : '1')
                           ->setMin($field=='prd_weight' ? '0.01' : '1')
                           ->setTab('tab_other');
      
           }
           
           $arr['prd_tecdocid']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('prd_tecdocid')
                    ->setID('prd_tecdocid')
                    ->setText('prd_tecdocid')
                    ->setMaxLength(50)
                    ->setTab('tab_other');
           
           $arr['prd_commodity']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('prd_commodity')
                    ->setID('prd_commodity')
                    ->setText('prd_commodity')
                    ->setMaxLength(50)
                    ->setTab('tab_other');
           
           foreach($this->getPricingFields() as $field)
           {
               $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField::create()
                           ->setName($field)
                            ->setID($field)
                            ->setTab('tab_costs')
                            ->setText('='.lang('products.prd_price').' '. strtoupper(str_replace('prd_price_', '', $field)))
                            ->setButtonbefore()
                            ->setButtonIcon($field=='prd_price_rowstg' ? '<i class="fas fa-pound-sign"></i>':'<i class="fas fa-euro-sign"></i>',FALSE)
                            ->setMask('$')
                            ->setButtonClass('input-group-text font-weight-bold border-right-0')
                            ->setButtonArgs(['style'=>'cursor:default'])
                            ->addClass('w-75');
           }
           
           return $arr;
        }
	
        function installstorage() 
        {
            parent::installstorage();
            if ($this->existsInStorage() && $this->getModel('PriceFilePart')->existsInStorage())
            {
                $this->setView('vw_products_parts_nopricefiles', "
                    SELECT t.prd_apdpartnumber FROM (
                    SELECT 
                    p.`prd_apdpartnumber`,
                    (CASE WHEN (select count(pp.prpid) from products_pricefiles_parts as pp WHERE pp.prf_ourpart=p.prd_apdpartnumber AND pp.enabled=1) > 0 THEN 1 ELSE 0 END) as 'enabled'
                    FROM `products` as p) as t WHERE t.enabled=0");
            }
        }
}