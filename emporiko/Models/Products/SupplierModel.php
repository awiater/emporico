<?php
/*
 *  This file is part of EMPORIKO ERP
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

class SupplierModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products_suppliers';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'supid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['sup_code','sup_name','sup_brand','sup_contactnr','sup_orderdays','sup_minorderval',
                                  'sup_ordernote','sup_ordermode','sup_invoicenote','sup_bookingnote','sup_currency',
                                  'sup_leadtime','sup_isifa','sup_rebate','sup_orderemail','enabled'];
	
	protected $validationRules =
	 [
	 	'sup_code'=>'required|is_unique[products_suppliers.sup_code,supid,{supid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'supid'=>               ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'sup_code'=>            ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'unique'=>TRUE],
                'sup_name'=>            ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'sup_brand'=>           ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'sup_contactnr'=>       ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
                'sup_orderdays'=>       ['type'=>'TEXT','null'=>TRUE],
                'sup_minorderval'=>     ['type'=>'DOUBLE','null'=>TRUE],
                'sup_ordernote'=>       ['type'=>'TEXT','null'=>TRUE],
                'sup_ordermode'=>       ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'sup_orderemail'=>      ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'sup_invoicenote'=>     ['type'=>'TEXT','null'=>TRUE],
                'sup_bookingnote'=>     ['type'=>'TEXT','null'=>TRUE],
                'sup_currency'=>        ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'sup_leadtime'=>        ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
                'sup_isifa'=>           ['type'=>'INT','constraint'=>'11','default'=>'0','null'=>FALSE],
                'sup_rebate'=>          ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
                'enabled'=>             ['type'=>'INT','constraint'=>'11','default'=>'1','null'=>FALSE],
	];
        
        /**
         * Get activity data for supplier
         * 
         * @param string $customer
         * 
         * @return array
         */
        function getMovements($code)
        {
            $data=$this->getView('vw_movements')
                       ->orderBy('mhdate DESC')
                       ->limit(10)
                       ->filtered(['mhref'=>$code])->find();
            return $data;
        }
        
        /**
         * Returns array with available currency from suppliers data
         * 
         * @return array 
         */
        function getAvailableCurrency()
        {
            $arr=[];
            foreach($this->groupBy('sup_currency')->find() as $row)
            {
                $arr[$row['sup_currency']]=$row['sup_currency'];
            }
            return $arr;
        }
        
        /**
         * Returns array with suppliers accounts details for form
         * 
         * @param type $field
         * @param type $onlyEnabled
         * 
         * @return array
         */
        function getAccountList($field='sup_code',$onlyEnabled=FALSE)
        {
            $arr=[];
            $filters=[];
            if ($onlyEnabled)
            {
                $filters['enabled']=1;
            }
            foreach($this->filtered($filters)->orderBy('sup_name')->find() as $row)
            {
                $arr[$row[$field]]=$row['sup_name'].' - '.$row['sup_code'];
            }
            return $arr;
        }
        
        /**
         * Return array with order modes
         * 
         * @return array
         */
        function getOrderModes()
        {
            $arr=[];
            foreach($this->groupby('sup_ordermode')->find() as $record)
            {
                $arr[$record['sup_ordermode']]=$record['sup_ordermode'];
            }
            return $arr;
        }
        
        /**
         * Determines if order is email enabled
         * 
         * @param type $record
         * 
         * @return boolean
         */
        function isEmailEnabled($record)
        {
            if (is_numeric($record))
            {
                $record=$this->find($record);
                if (!is_array($record))
                {
                    return FALSE;
                }
                $record=$record['sup_ordermode'];
            }
            if (is_string($record))
            {
                $settings=$this->getModel('Settings')->get('products.products_suppemailmodes',TRUE);
                if (is_array($settings))
                {
                    return array_key_exists($record, $settings);
                }
            }
            return FALSE;
            
        }
        
        /**
         * Returns array with edit fields for form
         * 
         * @param array $record
         * 
         * @return type
         */
        function getFieldsForForm(array $record) 
        {
            $arr=parent::getFieldsForForm($record);
            /*$arr['sup_brand']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::createField($arr['sup_brand'])
                               ->setOptions($this->getModel('Brand')->getForForm('prb_name','prb_name'))
                               ->setAsAdvanced();
             */
            unset($arr['sup_brand']);
            foreach(['sup_ordernote','sup_invoicenote','sup_bookingnote'] as $field)
            {
                if ($field=='sup_ordernote')
                {
                    $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::createField($arr[$field]);
                }
                $arr[$field]->setTab('ordersnotes');
            }
            
            //'','sup_sendorderinemail'
            $arr['sup_ordermode']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::createField($arr['sup_ordermode'])
                                ->setTab('ordersnotes')
                                ->setOptions($this->getOrderModes());
            
            $arr['sup_orderemail']= \EMPORIKO\Controllers\Pages\HtmlItems\EmailField::createField($arr['sup_orderemail'])
                                ->setTab('ordersnotes');
            
            
            foreach(['sup_minorderval','sup_leadtime','sup_rebate'] as $field)
            {
                $arr[$field]->setTab('other');
            }
            $arr['sup_rebate']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::createField($arr['sup_rebate']);
            $arr['sup_isifa']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::createField($arr['sup_isifa'])->setTab('other');
            
            $arr['sup_currency']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::createField($arr['sup_currency'])
                                    ->setOptions($this->getAvailableCurrency())
                                    ->setAsAdvanced();
            
            $arr['sup_orderdays']= \EMPORIKO\Controllers\Pages\HtmlItems\CheckList::createField($arr['sup_orderdays'])
                                    ->setOptions(array_combine(['ADHOC','MON','TUE','WED','THU','FRI','SAT','SUN'], lang('products.sup_orderdays_list')))
                                    ->setTab('other');
            
            $arr['sup_contactnr']= \EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField::createField($arr['sup_contactnr'])
                                        ->setAsPhoneFormat();
            return $arr;
        }
        
       
}