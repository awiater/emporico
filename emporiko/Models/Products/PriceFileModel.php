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

class PriceFileModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products_pricefiles';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'ppfid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['ppf_name','ppf_desc','ppf_pricingmode','ppf_source'
                                   ,'ppf_fields','ppf_curr','ppf_updated','ppf_istmp','enabled'];
	
	protected $validationRules =
	 [
	 	'ppf_name'=>'required|is_unique[products_pricefiles.ppf_name,ppfid,{ppfid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'ppfid'=>           ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'ppf_name'=>        ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
                'ppf_desc'=>        ['type'=>'VARCHAR','constraint'=>'200','null'=>FALSE],
                'ppf_fields'=>      ['type'=>'LONGTEXT','null'=>FALSE],
                'ppf_pricingmode'=> ['type'=>'VARCHAR','constraint'=>'20','null'=>FALSE],
                'ppf_source'=>      ['type'=>'LONGTEXT','null'=>FALSE],
                'ppf_curr'=>        ['type'=>'VARCHAR','constraint'=>'3','null'=>FALSE],
                'ppf_updated'=>     ['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
                'ppf_istmp'=>       ['type'=>'INT','constraint'=>'1','null'=>FALSE,'default'=>'0'],
                'enabled'=>         ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>'1'],
                
	];
        
        function createTempPriceFile(string $file,array $brands,string $desc,string $name='',bool $saveToDB=TRUE)
        {
            $file=$this->filtered(['ppf_name'=>$file,'|| ppf_name'=> base64url_decode($file)])->first();
            if (!is_array($file))
            {
                return FALSE;
            }
            unset($file['ppfid']);
            $file['ppf_desc']=$desc;
            $file['ppf_updated']= formatDate();
            $file['ppf_istmp']=1;
            $file['ppf_source']=['name'=>$file['ppf_name'],'calc'=>0];
            if (Arr::KeysExists(['brands','picker'], $brands))
            {
                $file['ppf_source']['brands']=$brands['brands'];
                $file['ppf_source']['picker']=$brands['picker'];
            }else
            {
                $file['ppf_source']['brands']=$brands;
            }
            $file['ppf_source']= json_encode($file['ppf_source']);
            $file['ppf_name']=strlen($name) < 0 ? $file['ppf_name'].formatDate() : $name;
            if ($saveToDB)
            {
                return $this->save($file);
            }
            return FALSE;
        }
        
        /**
         * Returns array with upload modes for obsolete parts
         * 
         * @return array
         */
        function getUploadModes()
        {
            return 
            [
                'none'=>lang('products.import_obsolete_none'),
                'disable'=>lang('products.import_obsolete_disable'),
                'delete'=>lang('products.import_obsolete_delete')
            ];
        }
        
        /**
         * Add upload task 
         * 
         * @param string $name
         * @param string $file
         * @param string $updateMode
         * @param string $notifyEmail
         * 
         * @return bool
         */
        function addUploadTasks(string $name,string $file,string $updateMode,string $notifyEmail)
        {
            $action=
            [
                'controller'=>'Products/PriceFilePartModel',
                'action'=>'updateFromFile',
                'args'=>[$name,$file,$updateMode,$notifyEmail]
            ];
            return $this->getModel('Tasks/Task')->addNew('Update Prices from file',$action,'upload_pricefile');
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            
            $arr['ppf_name']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName('ppf_name')
                        ->setID('ppf_name')
                        ->setText('ppf_name')
                        ->setMaxLength(80)
                        ->setAsRequired()
                        ->setTab('general');
            
            $arr['ppf_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                        ->setName('ppf_desc')
                        ->setID('ppf_desc')
                        ->setText('ppf_desc')
                        ->setRows(3)
                        ->setTab('general');
            
            $arr['ppf_pricingmode']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('ppf_pricingmode')
                        ->setID('ppf_pricingmode')
                        ->setText('ppf_pricingmode')
                        ->setOptions($this->getPricingModes())
                        ->setAsAdvanced()
                        ->setAsRequired()
                        ->setTab('general');
            
            if (array_key_exists('ppf_pricingmode', $record) && $record['ppf_pricingmode']!=null)
            {
                $arr['ppf_pricingmode']->setReadOnly();
            }
            
            if ((array_key_exists('ppf_pricingmode', $record) && $record['ppf_pricingmode']!='dbe')||!array_key_exists('ppf_pricingmode', $record))
            {
                $arr['ppf_pricingmode']->setReadOnly();
            
                $arr['ppf_curr']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                            ->setName('ppf_curr')
                            ->setID('ppf_curr')
                            ->setText('ppf_curr')
                            ->setOptions($this->getModel('Settings')->getCurrencyIcons(null,FALSE,TRUE))
                            ->setAsAdvanced()
                            ->setAsRequired()
                            ->setTab('general');
            }
             $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                        ->setName('enabled')
                        ->setID('enabled')
                        ->setText('enabled')
                        ->setTab('general');
             
             if (array_key_exists('ppf_pricingmode', $record) && $record['ppf_pricingmode']=='dbe')
             {
                $arr['ppf_source_name']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('ppf_source_name')
                        ->setID('ppf_source_name')
                        ->setText('ppf_source_name')
                        ->setOptions($this->getPriceFilesForForm(FALSE))
                        ->setAsAdvanced()
                        ->setAsRequired()
                        ->setTab('source_brands');
                
                $arr['ppf_source_calcmode_list']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('ppf_source_calcmode_list')
                        ->setID('ppf_source_calcmode_list')
                        ->setText('ppf_source_calcmode')
                        ->setOptions($this->getPricingCalcModes())
                        ->setAsRequired()
                        ->setTab('source_brands');
             }
             
             $arr['ppf_source_brands']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                            ->setName('ppf_source_brands')
                            ->setID('ppf_source_brands')
                            ->setText('ppf_source_brands')
                            //->setInputField($this->getModel('Brand')->getForForm('prb_name','prb_name'))
                            ->setInputField(
                                    \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                                    ->setName('ppf_source_brands_input')
                                    ->setID('ppf_source_brands_input')
                                    ->setOptions($this->getModel('Brand')->getBrands(TRUE))
                                    ->setAsAdvanced()
                            )
                            ->setItemExistsErrorMsg('products.error_brandslist_item_exists')
                            ->setTab('source_brands');
             
             if (array_key_exists($this->primaryKey, $record) && is_numeric($record[$this->primaryKey]))
             {
                 $arr['ppf_pricingmode']->setReadOnly();
                 $arr['ppf_name']->setReadOnly();
                 if (array_key_exists('ppf_curr', $arr))
                 {
                     $arr['ppf_curr']->setReadOnly();
                 }else
                 {
                     $record['ppf_curr']='EUR';
                 }
                 if (array_key_exists('ppf_source_name', $arr))
                 {
                     $arr['ppf_source_name']->setReadOnly();
                 }
                 
                 
                $arr['ppf_source_brands']->setReadOnly();
                if (Arr::KeysExists(['ppf_pricingmode','ppf_name'], $record))
                {
                    $arr['ppf_fields_parts']= \EMPORIKO\Controllers\Pages\HtmlItems\DataGrid::create()
                            ->setName('ppf_fields_parts')
                            ->setID('ppf_fields_parts')
                            ->setText('')
                            ->addColumn('prd_brand', 'products.prd_brand', FALSE, [])
                            ->addColumn('prd_apdpartnumber', 'products.prd_apdpartnumber', FALSE, [])
                            ->addColumn('prd_description', 'products.prd_description', FALSE, [])
                            ->addMoneyColumn('prf_price', lang('products.prd_price',['']), $this->getModel('Settings')->getCurrencyIcons($record['ppf_curr'],TRUE,FALSE), FALSE, [])
                            ->setTab('source_parts')
                            ->setPagination()
                            ->setValue($this->getPriceFileByName($record['ppf_name'],['@limit'=>10],['prd_brand','prd_apdpartnumber','prd_description','prf_price']));
                    
                }
             }else
             {
                 foreach(['ppf_name','ppf_pricingmode','ppf_curr','enabled'] as $field)
                 {
                     $arr['enabled']->setAsRequired();
                 }
             }
            $arr['ppf_fields']=\EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                    ->setName('ppf_fields')
                    ->setID('ppf_fields')
                    ->setText('')
                    ->setInputField($this->getColumnNames())
                    ->setTab('fields');
            return $arr;
            /*'','','',''
                                   ,'ppf_fields','','ppf_updated','enabled'*/
        }
        
        /**
         * Returns price calculation modes
         * 
         * @return array
         */
        function getPricingCalcModes()
        {
            return 
            [
                0=>lang('products.ppf_source_calcmode_none'),
                '-'=>lang('products.ppf_source_calcmode_percmin'),
                '+'=>lang('products.ppf_source_calcmode_percadd')
            ];
        }
        
        /**
         * Get product column names
         * 
         * @return array
         */
        function getColumnNames()
        {
            $arr=[];
            foreach($this->getModel('Product')->allowedFields as $field)
            {
                $arr['products@'.$field]=lang('products.'.$field);
            }
            $arr['products@prd_price']=lang('products.prd_price',['']);
            return $arr;
        }
        
        /**
         * Get default price file column names
         * 
         * @param bool $parse
         * 
         * @return array
         */
        function getDefaultColumnNames(bool $parse=FALSE)
        {
            $cfg=$this->getModel('Settings')->get('products.products_pricefiledeffields',TRUE);
            if (!is_array($cfg))
            {
                null;
            }
            $arr=[];
            foreach($cfg as $field)
            {
                $arr[$field]='products@'.$field;
            }
            return $parse ? $arr : json_encode($arr);
        }
        
        
        /**
         * Returns available price files names as array
         * 
         * @param bool $baseCoded
         * @param bool $baseCoded
         * 
         * @return string
         */
        function getPriceFilesForForm(bool $addExtra=TRUE,bool $baseCoded=FALSE)
        {
            if (!$addExtra)
            {
                $arr=[];
            }else
            {
                $arr=['loged'=>lang('products.pages_loged'),'email'=>lang('products.pages_email')];
            }
            foreach($this->filtered(['enabled'=>1])->find() as $file)
            {
                $arr[$baseCoded ? base64url_encode($file['ppf_name']) : $file['ppf_name']]=$file['ppf_name'].(strlen($file['ppf_desc']) > 0 ? ' - '.$file['ppf_desc'] : '');
            }
            return $arr;
        }
        
        /**
         * Returns array with price file data for given price file name
         * 
         * @param string $name
         * @param array  $filters
         * @param array  $fields
         * 
         * @return array|boolean
         */
        function getPriceFileByName(string $name,array $filters=[],array $fields=[])
        {
            $name=$this->where('ppf_name',$name)->first();
            if (!is_array($name) || (is_array($name) && !Arr::KeysExists(['ppf_fields','ppf_name','ppf_pricingmode'], $name)))
            {
                return FALSE;
            }
            $filters['prf_name']=$name['ppf_name'];
            if(count($fields) > 0)
            {
                if (!Arr::isAssoc($fields))
                {
                    $fields= array_combine($fields, $fields);
                }
                $name['ppf_fields']= $fields;
            }else
            {
               $name['ppf_fields']= json_decode($name['ppf_fields'],TRUE); 
            }
            
            if (!is_array($name['ppf_fields']))
            {
                return FALSE;
            }
            $id=0;
            foreach($name['ppf_fields'] as $key=>$field)
            {
                if (array_key_exists('_convertnames', $filters))
                {
                    if (is_string($filters['_convertnames']) && $filters['_convertnames']=='api')
                    {
                        $field=Str::afterLast($field, '@');
                    }else
                    {
                        $field=lang(str_replace('@', '.', $field),['']);
                    }
                }
                $tbl=Str::before($key, '_');
                $tbl='`'.$tbl.'`.`'.$key;
                $name['ppf_fields'][$key]=$tbl."` as '".$field."'";
            }
            
            if (Str::isJson($name['ppf_source']))
            {
                $name['ppf_source']= json_decode($name['ppf_source'],TRUE);
                if (is_array($name['ppf_source']))
                {
                    $arr['prf.prf_name']="prf.prf_name ='".$name['ppf_name']."'";
                    if (array_key_exists('name', $name['ppf_source']))
                    {
                        $filters['prf_name']=$name['ppf_source']['name'];
                    }
                    
                    if (array_key_exists('brands', $name['ppf_source']) && is_array($name['ppf_source']['brands']))
                    {
                        $filters['prd_brand In']=$name['ppf_source']['brands'];
                    }
                    
                    if (array_key_exists('calc', $name['ppf_source']) && is_numeric($name['ppf_source']['calc']) && intval($name['ppf_source']['calc'])!=0)
                    {
                         $name['ppf_fields']['prf_price']='round(((100'. $name['ppf_source']['calc'].')/100)*`prf_price`,2) as `prf_price`';
                    }
                    $name['ppf_source']=implode(' AND ',$arr);
                }else
                {
                    $name['ppf_source']= "prf.prf_name ='".$name['ppf_name']."'";
                }
            }
            if ($name['ppf_source']=='*')
            {
                $name['ppf_source']= "prf.prf_name ='".$name['ppf_name']."'";
            }
            $paginate=null;
            
            if (array_key_exists('@limit', $filters))
            {
                $paginate=$filters['@limit'];
                unset($filters['@limit']);
            }
            
            $products=$this->getModel('Product')->setTable('products as `prd`');
            $parts=$this->getModel('PriceFilePart');
            
            $parts=$parts->setTable($parts->table.' as `prf`')
                         ->filtered($filters,null,null,null, array_merge($parts->allowedFields,$products->allowedFields))
                         ->where('`prf`.`enabled`',1,FALSE)
                         ->select(implode(',',$name['ppf_fields']));
            
            if ($name['ppf_pricingmode']!='file')
            {
                $parts->join($products->table, "prf.prf_ourpart=prd.prd_apdpartnumber");
            } else 
            {
                $parts->where('`prd`.'.$name['ppf_pricing'].' >',0);
            }
            //echo $parts->getCompiledSelect();exit;
            return $paginate==null ? $parts->find() : ['data'=>$parts->paginate($paginate),'links'=>$parts->pager->links()];
        }
        
        
        function getPriceFileByName1(string $name,array $filters=[],array $fields=[])
        {
            $name=$this->where('ppf_name',$name)->first();
            if (!is_array($name) || (is_array($name) && !Arr::KeysExists(['ppf_fields','ppf_name','ppf_pricingmode'], $name)))
            {
                return FALSE;
            }
            if(count($fields) > 0)
            {
                if (!Arr::isAssoc($fields))
                {
                    $fields= array_combine($fields, $fields);
                }
                $name['ppf_fields']= $fields;
            }else
            {
               $name['ppf_fields']= json_decode($name['ppf_fields'],TRUE); 
            }
            
            if (!is_array($name['ppf_fields']))
            {
                return FALSE;
            }
            $id=0;
            foreach($name['ppf_fields'] as $key=>$field)
            {
                if (array_key_exists('_convertnames', $filters))
                {
                    if (is_string($filters['_convertnames']) && $filters['_convertnames']=='api')
                    {
                        $field=Str::afterLast($field, '@');
                    }else
                    {
                        $field=lang(str_replace('@', '.', $field),['']);
                    }
                }
                $name['ppf_fields'][$key]='`'.$key."` as '".$field."'";
            }
            if (Str::isJson($name['ppf_source']))
            {
                $name['ppf_source']= json_decode($name['ppf_source'],TRUE);
                if (is_array($name['ppf_source']))
                {
                    $arr['prf.prf_name']="prf.prf_name ='".$name['ppf_name']."'";
                    if (array_key_exists('name', $name['ppf_source']))
                    {
                        $arr['prf.prf_name']="prf.prf_name ='".$name['ppf_source']['name']."'";
                    }
                    
                    if (array_key_exists('brands', $name['ppf_source']) && is_array($name['ppf_source']['brands']))
                    {
                        $arr['brands']="prd.prd_brand IN ('".implode("','",$name['ppf_source']['brands'])."')";
                    }
                    
                    if (array_key_exists('calc', $name['ppf_source']) && is_numeric($name['ppf_source']['calc']) && intval($name['ppf_source']['calc'])!=0)
                    {
                         $name['ppf_fields']['prf_price']='round(((100'. $name['ppf_source']['calc'].')/100)*`prf_price`,2) as `prf_price`';
                    }
                    $name['ppf_source']=implode(' AND ',$arr);
                }else
                {
                    $name['ppf_source']= "prf.prf_name ='".$name['ppf_name']."'";
                }
            }
            if ($name['ppf_source']=='*')
            {
                $name['ppf_source']= "prf.prf_name ='".$name['ppf_name']."'";
            }
            
            $paginate=null;
            
            if (array_key_exists('@limit', $filters))
            {
                $paginate=$filters['@limit'];
                unset($filters['@limit']);
            }
            
            $products=$this->getModel('Product')
                    ->setTable('products as `prd`')
                    ->filtered($filters)
                    ->select(implode(',',$name['ppf_fields']));
            if ($name['ppf_pricingmode']!='file')
            {
                $products->join($this->getModel('PriceFilePart')->table.' as `prf`', $name['ppf_source']." AND prf.prf_ourpart=prd.prd_apdpartnumber");
            } else 
            {
                $products->where('`prd`.'.$name['ppf_pricing'].' >',0);
            }
            echo $products->getCompiledSelect();exit;
            return $paginate==null ? $products->find() : ['data'=>$products->paginate($paginate),'links'=>$products->pager->links()];
        }
        
        /**
         * Returns array with price file pricing modes
         * 
         * @ return array
         */
        function getPricingModes(bool $forNewForm=FALSE)
        {
            $arr=
            [
                'db'=>lang('products.ppf_pricingmode_db'),
                'dbe'=>lang('products.ppf_pricingmode_dbe')
            ];
            if ($forNewForm)
            {
                foreach ($arr as $key=>$val)
                {
                    $arr[$val]=url('Products','pricefiles',['new'],['mode'=>$key,'refurl'=> current_url(FALSE,TRUE)]);
                    unset($arr[$key]);
                }
            }
            return $arr;
            
        }
        
        /**
         * Returns currency for given price file
         * 
         * @param string $name
         * 
         * @return string
         */
        function getCurrencyForPriceFile(string $name)
        {
            $name=$this->filtered(['ppf_name'])->first();
            if (is_array($name) && array_key_exists('ppf_curr', $name))
            {
                return $name['ppf_curr'];
            }
            return null;
        }
        
        
}