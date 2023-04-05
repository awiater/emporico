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

class BrandModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products_brands';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'prbid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['prb_name','prb_desc','prb_logo','prb_supp','enabled'];
	
	protected $validationRules =
	 [
	 	'prb_name'=>'required|is_unique[products_brands.prb_name,prbid,{prbid}]'
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
         * 
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'prbid'=>        ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'prb_name'=>     ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'prb_desc'=>     ['type'=>'TEXT','null'=>TRUE],
                'prb_supp'=>     ['type'=>'TEXT','null'=>TRUE],
                'prb_logo'=>     ['type'=>'TEXT','null'=>TRUE],
                'enabled'=>      ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>'1'],
                
	];
        
        /**
         * Returns array with brand(s) data filtered by filters
         * 
         * @param array        $filters
         * @param array|string $orderby
         * @param bool|int     $paginate
         * @param string|int   $logeduseraccess
         * @param bool         $Validation
         * @return array
         */
        function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
        {
            return $this->getView('vw_products_brands_withupdates')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
        }
        
        /**
         * Returns array with brand(s) data for given supplier
         * 
         * @param string $supp
         * @param string $field
         * 
         * @return array
         */
        function getBrandsDataForSupplier(string $supp,string $field=null)
        {
            $arr=[];
            foreach($this->filtered(['prb_supp %'=>$supp])->find() as $rec)
            {
                if (array_key_exists($field, $rec))
                {
                    $arr[]=$rec[$field];
                }else
                {
                    $arr[]=$rec;
                }
            }
            return $arr;
        }
        
        /**
         * Update brand(s) data from file (CSV)
         * 
         * @param string $file
         * @param string $updateMode
         * @param string $notifyEmail
         * @param bool   $addTask
         * 
         * @return boolean
         */
        function updateFromFile(string $file,string $updateMode,string $notifyEmail,bool $addTask=FALSE)
        {
            if ($addTask)
            {
                $action=
                [
                    'controller'=>'Products/BrandModel',
                    'action'=>'updateFromFile',
                    'args'=>[$file,$updateMode,$notifyEmail]
                ];
                return $this->getModel('Tasks/Task')->addNew('Update Brand(s) Data',$action,'upload_brandsdata');
            }
            $fileName= parsePath($file,TRUE);
            $result=FALSE;
            if (file_exists($fileName))
            {
                $filemapper=$this->getModel('Settings')->getUploadDriverData($updateMode,FALSE,TRUE);
                if (!is_array($filemapper))
                {
                    return FALSE;
                }
                $file = fopen($fileName, "r");
                $sqls=[];
                $rowind=0;
                $this->db->transStart();
                while (($raw_string = fgets($file)) !== false)
                {
                    if ($rowind > 0)
                    {
                        $row = str_getcsv($raw_string);
                        $sql=$this;
                        foreach ($filemapper['filemap'] as $column=>$ind)
                        {
                            if (in_array($column, $this->allowedFields))
                            {
                                $sqls[$column]=$row[$ind];
                            }
                        }
                        $sql=$sql->builder()->set($sqls);
                        $sqls=[];
                        if (Arr::KeysExists(['prb_name','prb_desc','prb_logo','enabled'], $filemapper['filemap']))
                        {
                            if ($this->count(['prb_name'=>$row[$filemapper['filemap']['prb_name']]]) > 0)
                            {
                                $sqls[]=$sql->where('prb_name',$filemapper['filemap']['prb_name'])->update();
                            }else
                            {
                                $sqls[]=$sql->insert();
                            } 
                        }
                        if (Arr::KeysExists(['prb_name','lastupdt','nextupdt'], $filemapper['filemap']))
                        {
                            if (strlen($row[$filemapper['filemap']['lastupdt']]) > 0 && validateDate($row[$filemapper['filemap']['lastupdt']]))
                            {
                                $sqls[]=$this->getModel('BrandUpdate')->builder()->set(['prbu_name'=>$row[$filemapper['filemap']['prb_name']],'prbu_updt'=>$row[$filemapper['filemap']['lastupdt']]])->insert();
                            }
                            if (strlen($row[$filemapper['filemap']['nextupdt']]) > 0 && validateDate($row[$filemapper['filemap']['nextupdt']]))
                            {
                                $sqls[]=$this->getModel('BrandUpdate')->builder()->set(['prbu_name'=>$row[$filemapper['filemap']['prb_name']],'prbu_updt'=>$row[$filemapper['filemap']['nextupdt']]])->insert();
                            }
                        }
                    }
                    $rowind++;
                }
                fclose($file);
                unlink($fileName);
                $this->db->transComplete();
                if (Str::isValidEmail($notifyEmail))
                {
                    $tpl=$this->getModel('Settings')->get('system.upload_upload_file_notification');
                    $tpl=$this->getModel('Documents/Report')->parseEmailTemplate($tpl,['name'=>$name,'file'=>Str::afterLast($fileName, '/')]);
                    if (is_array($tpl) && Arr::KeysExists(['mailbox','subject','body'], $tpl))
                    {
                        $tpl=$this->getModel('Emails/Mailbox')
                                ->getMailbox($tpl['mailbox'])
                                ->sendEmail($notifyEmail,$tpl['subject'],$tpl['body']);
                    }
                }
            }
            return TRUE;
        }
        
        /**
         * Returns array with brands data upload modes for form
         * 
         * @return array
         */
        function getUploadModes()
        {
            $settings=$this->getModel('Settings')->get('products.*');
            return 
            [
                $settings['products_branduploadtpl']=>lang('products.import_brandmode_details'),
                $settings['products_brandupdtuploadtpl']=>lang('products.import_brandmode_update')
            ];
        }
        
        /**
         * Returns array with edit fields for form
         * 
         * @param array $record
         * 
         * @return array
         */
        function getFieldsForForm(array $record) 
        {
            $arr=parent::getFieldsForForm($record);
            
            $arr['enabled']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('YesNoField',$arr['enabled']);
            
            $arr['prb_desc']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('TextAreaField',$arr['prb_desc']);
            
            $arr['prb_supp']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField::createField($arr['prb_supp'])
                    ->setInputField($this->getModel('Supplier')->getAccountList('sup_code',TRUE),TRUE);
            
            $arr['prb_logo']= \EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker::createField($arr['prb_logo'])
                    ->setImagePreview(TRUE)
                    ->setFormat('images')
                    ->setAutoSize(250)
                    ->setJustFileNameOption()
                    ->setAsWizard()
                    ->setUploadDir('@storage/files/images/');
            return $arr;
        }
        
         /**
         * Returns array with brands names
         * 
         * @param  bool $addEmpty
         * @param  bool $basic
         * 
         * @return array
         */
        function getBrands($addEmpty=FALSE,$basic=FALSE)
        {
           $arr=[];
           
           if ($addEmpty)
           {
               $arr['*']=lang('products.allbrands'); 
           }
           
           foreach ($this->filtered(['enabled'=>1])->find() as $record)
           {
              $arr[$record['prb_name']]=$record['prb_name']; 
           }
           return $arr;
        }
        
        /**
         * Returns array with brand(s) order usage
         * 
         * @param string $brand
         * 
         * @return array
         */
        function getBrandsOrderUsage(string $brand=null)
        {
            $arr=[];
            $brand=$brand==null ? [] : ['brand'=>$brand];
            $brands=$this->getView('vw_products_brands_orderusage')->filtered($brand)->orderby('usage DESC')->limit(10)->find();
            foreach($brands as $brand)
            {
                $arr[$brand['brand']]=$brand['usage'];
            }
            return $arr;
        }
        
        /**
         * Returns array with brands download statistics
         * 
         * @param bool  $topBrandsOnly
         * @param array $filters
         * 
         * @return array
         */
        function getBrandsYearDownloadUsage(bool $topBrandsOnly=TRUE,array $filters=[])
        {
            $view=$this->getView('vw_products_brandsdownload')->filtered($filters);
            $view->select('`mhto` as `brand`,count(`mhto`) as `usage`')->groupby('mhto');
            $view=$topBrandsOnly ? $view->limit(10) : $view;
            $arr=[];
            foreach($view->find() as $brand)
            {
                $arr[$brand['brand']]=$brand['usage'];
            }
            return $arr;
        }
        
        /**
         * Returns brands data with updates info as array
         * 
         * @param array $filters
         * @param type $orderby
         * @param type $paginate
         * @return array
         */
        function getBrandsWithUpdates(array $filters=[],$orderby = null, $paginate = null)
        {
            return $this->getView('vw_products_brands_withupdates')->filtered($filters, $orderby, $paginate, null, TRUE);
        }
        
        function installstorage() 
        {
            parent::installstorage();
            if ($this->existsInStorage())
            {
                $this->setView('vw_products_brands_withupdates', "select `pb`.*,
(select `lupd`.`prbu_updt` from `products_brands_updates` as `lupd` where `lupd`.`prbu_name` = `pb`.`prb_name` and `lupd`.`prbu_updt` <= convert(date_format(curdate(),'%Y%m%d') using utf8) order by `lupd`.`prbu_updt` desc limit 1) AS `lastupdt`,
(select `nupd`.`prbu_updt` from `products_brands_updates` as `nupd` where `nupd`.`prbu_name` = `pb`.`prb_name` and  `nupd`.`prbu_updt` > convert(date_format(curdate(),'%Y%m%d') using utf8) order by `nupd`.`prbu_updt` limit 1) AS `nextupdt` 
from `products_brands` as `pb`");
            }
        }
        
}/*select `pb`.*,
(select `lupd`.`prbu_updt` from `products_brands_updates` as `lupd` where `lupd`.`prbu_name` = `pb`.`prb_name` and `lupd`.`prbu_updt` <= convert(date_format(curdate(),'%Y%m%d') order by `lupd`.`prbu_updt` desc limit 1) AS `lastupdt`,
(select `nupd`.`prbu_updt` from `products_brands_updates` as `nupd` where `nupd`.`prbu_name` = `pb`.`prb_name` and  `nupd`.`prbu_updt` > convert(date_format(curdate(),'%Y%m%d') order by `nupd`.`prbu_updt` limit 1) AS `nextupdt` 
from `products_brands` as `pb*/