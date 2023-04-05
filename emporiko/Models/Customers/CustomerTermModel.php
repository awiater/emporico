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
 
namespace EMPORIKO\Models\Customers;

class CustomerTermModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='customers_terms';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'ctid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','formula','level','pricefile','note','enabled'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[customers_terms.name,ctid,{ctid}]',
	 	'enabled'=>'required',
                'formula'=>'required',
                'level'=>'required',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'ctid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'formula'=>		['type'=>'TEXT','null'=>FALSE],
                'level'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'note'=>                ['type'=>'TEXT','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
                'pricefile'=>           ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
	];
        
        function getFieldsForForm(array $record) 
        {
            $arr=parent::getFieldsForForm($record);
            $arr['note']= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('TextAreaField',$arr['note']);
            $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('YesNoField',$arr['enabled']);
            return $arr;
        }
        
        function getLevels()
        {
           $arr=[];
           foreach($this->groupBy('level')->find() as $level)
           {
               $level=$level['level'];
               $arr[$level]= strtoupper($level);
           }
           return $arr;
        }
	
}