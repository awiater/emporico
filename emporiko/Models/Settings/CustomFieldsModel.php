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
 
namespace EMPORIKO\Models\Settings;


class CustomFieldsModel extends \EMPORIKO\Models\BaseModel  
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='custom_fields';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'cfid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['type','target','value','targetid'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'cfid'=>	 ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'type'=>	 ['type'=>'INT','constraint'=>'36','null'=>FALSE],
		'target'=>	 ['type'=>'TEXT','null'=>FALSE],
		'value'=>	 ['type'=>'TEXT','null'=>FALSE],
		'targetid'=> ['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
	
	/**
         * Returns array with fields data
         * 
         * @param array $filters
         * @return type
         */
        function getFields(array $filters=[])
        {
            $arr=[];
            foreach($this->filtered($filters)->find() as $record)
            {
                $arr[$record['type']]=$record;
            }
            return $arr;
        }
	
	
}