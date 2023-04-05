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
 
namespace EMPORIKO\Models\Products;

use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\Strings as Str;

class PriceFilePartModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products_pricefiles_parts';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'prpid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['prf_name','prf_ourpart','prf_price','enabled'];
	
	protected $validationRules =
	 [
	 	'prf_name'=>'required|is_unique[products_pricefiles_parts.prf_name,prpid,{prpid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'prpid'=>        ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'prf_name'=>     ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
                'prf_ourpart'=>  ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE,'foreignkey'=>['products','prd_apdpartnumber','CASCADE','CASCADE']],
                'prf_price'=>    ['type'=>'DOUBLE','default'=>'0','null'=>FALSE]
                
	];
        
        /**
         * Returns price value for given part and customer
         * 
         * @param string $customer
         * @param string $part
         * 
         * @return mixed
         */
        function getPartPriceForCustomer(string $customer,string $part)
        {
            $customer=$this->getModel('Customers/Customer')->filtered(['code'=>$customer,'|| cid'=>$customer])->first();
            if (!is_array($customer))
            {
                return 0;
            }
            $part=$this->filtered(['prf_name'=>$customer['terms_price'],'prf_ourpart'=>$part])->first();
            if (!is_array($part))
            {
                return 0;
            }
            return $part['prf_price'];
        }
        
        /**
         * Returns price of part from given price file
         * 
         * @param string $name
         * @param string $part
         * 
         * @return array
         */
        function getPartPriceForPriceFiles(string $name,string $part)
        {
            $part=['prf_ourpart'=>$part,'enabled'=>1,'ppf_source'=>'*','ppf_pricingmode'=>'db'];
            if ($name!='*')
            {
                $part['prf_name']=$name;
            }
            $arr=[];
            //dump($this->filtered($part)->find());exit;
            foreach ($this->getView('vw_products_forpricefile')->filtered($part)->find() as $file)
            {
                $arr[$file['prf_name']]=['prf_price'=>$file['prf_price'],'ppf_curr'=>$file['ppf_curr'],'ppf_updated'=>$file['ppf_updated']];
            }
            return $arr;
        }
        
        function generatePartsFromBrands(string $priceFileName,array $brands)
        {
            $select=
            [
                "'".$priceFileName."' as `prf_name`",
                '`prd_apdpartnumber`',
                "'0' as `prf_price`",
                "'1' as `enabled`"
            ];
            $select=$this->getModel('Product')->filtered(['prd_brand In'=>$brands])->select(implode(',',$select))->getCompiledSelect();
           
            $select='INSERT INTO `'.$this->table.'` (`prf_name`,`prf_ourpart`,`prf_price`,`enabled`) ('.$select.')';
            $this->query($select);
        }
        
        function updateFromFile(string $name,string $file,string $updateMode,string $notifyEmail,string $UploadTpl='#products.products_pricinguploadtpl')
        {  
            $fileName= parsePath($file,TRUE);
            $result=FALSE;
            if (file_exists($fileName))
            {
                $sql=[];
                $sql_tpl=$this->builder()->set(['prf_price'=>'%prf_price%','enabled'=>1])->where(['prf_name'=>$name,'prf_ourpart'=>'%prf_ourpart%'])->getCompiledUpdate();
                $sql_tpl="UPDATE `".$this->table."` SET `prf_price` = '%prf_price%', `enabled` = 1 WHERE `prf_name` = '".$name."' AND `prf_ourpart` = '%prf_ourpart%'";
                $filemapper=$this->getModel('Settings')->getUploadDriverFileMap($UploadTpl);
                $file = fopen($fileName, "r");
                if (!is_array($filemapper))
                {
                    return FALSE;
                }
                $this->db->transStart();
                if ($updateMode!='none')
                {
                    $this->builder()->set(['enabled'=>0])->where(['prf_name'=>$name])->update();
                }
                while (($raw_string = fgets($file)) !== false)
                {
                    $row = str_getcsv($raw_string);
                    if (is_array($row) && count($row) > 1 && is_numeric($row[$filemapper['price']]))
                    {
                        //$sql[]= str_replace(['%prf_ourpart%','%prf_price%'], [$row[$filemapper['part']],$row[$filemapper['price']]], $sql_tpl);
                        $sql_tpl="UPDATE `".$this->table."` SET `prf_price` = '".$row[$filemapper['price']]."', `enabled` = 1 WHERE `prf_name` = '".$name."' AND `prf_ourpart` = '".$row[$filemapper['part']]."'";
                        $this->db->query($sql_tpl);
                    }
                }
                fclose($file);
                unlink($fileName);
                if ($updateMode=='delete')
                {
                    $this->builder()->where(['prf_name'=>$name,'enabled'=>0])->delete();
                }
                $this->getModel('PriceFile')->builder()->set(['ppf_updated'=> formatDate()])->where(['ppf_name'=>$name])->update();
                $this->db->transComplete();
                if (Str::isValidEmail($notifyEmail))
                {
                    $tpl=$this->getModel('Settings')->get('products.products_pricinguploadnotifytpl');
                    $tpl=$this->getModel('Documents/Report')->parseEmailTemplate($tpl,['name'=>$name,'file'=>Str::afterLast($fileName, '/')]);
                    if (is_array($tpl) && Arr::KeysExists(['mailbox','subject','body'], $tpl))
                    {
                        $tpl=$this->getModel('Emails/Mailbox')
                                ->getMailbox($tpl['mailbox'])
                                ->sendEmail($notifyEmail,$tpl['subject'],$tpl['body']);
                    }
                }
                return $sql;
            }
            return $fileName;
        }
        
        
        
        function installstorage() 
        {
            parent::installstorage();
            if ($this->getModel('PriceFile')->existsInStorage())
            {
                $this->setView('vw_products_forpricefile', "
                SELECT  `parts`.*,`files`.`ppf_curr`,`files`.`ppf_updated` 
                FROM `products_pricefiles_parts` as `parts`
                LEFT JOIN `products_pricefiles` as `files` ON `parts`.`prf_name`=`files`.`ppf_name`
                ");
            }
        }
     
}