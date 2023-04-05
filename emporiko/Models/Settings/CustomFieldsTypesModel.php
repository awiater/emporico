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

class CustomFieldsTypesModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='custom_fields_types';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'cftid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','type','text','tooltip','options','tab','target','enabled','access','required'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[custom_fields_types.name,cftid,{cftid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'cftid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'type'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'target'=>	['type'=>'TEXT','null'=>FALSE],
                'text'=>	['type'=>'TEXT','null'=>FALSE],
                'tooltip'=>	['type'=>'TEXT','null'=>FALSE],
		'options'=>	['type'=>'TEXT','null'=>FALSE],
                'tab'=>         ['type'=>'TEXT','null'=>FALSE],
                'required'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>	['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
	];
	
	
	function getFieldTypes($type=null)
	{
		if ($type==null)
		{
			return $this->_fieldsTypes;
		}
		if (array_key_exists($type, $this->_fieldsTypes))
		{
			return $this->_fieldsTypes[$type];
		}else
		{
			return null;
		}
	}
       
        /**
         * Adds custom fields target (module)
         * 
         * @param string $name
         * @param string $text
         * @param array  $tabs
         * 
         * @throws Exception
         */
        public function addTarget($name,$text,array $tabs=[],$override=FALSE)
        {
            $name=strtolower($name);
            if ($this->getModel('Settings')->count(['param'=>'customfields_targets_'.$name]) > 0 && !$override)
            {
                throw new \Exception('Custom fields target already exists');;
            }
            $text=json_encode(['text'=>$text,'tabs'=>$tabs,'type'=>$name]);
            $this->getModel('Settings')->add('general', 'customfields_targets_'.$name, $text);
        }

        public function getTargets() 
        {
            $arr=[];
            $targets = $this->getModel('Settings')->get('general.customfields_targets_*',FALSE, '*', FALSE);
            $targets = is_array($targets) ? $targets : [];
            foreach ($targets as $key => $value) 
            {
                $value= json_decode($value['value'],TRUE);
                if (is_array($value))
                {
                    foreach($value['tabs'] as $tabname=>$tab)
                    {
                       $value['tabs'][$tabname]=lang($tab); 
                    }
                    $key= base64_encode(json_encode($value));
                    $arr[$key] = lang($value['text']);
                }
                
            }
            return $arr;
        }  
    
}