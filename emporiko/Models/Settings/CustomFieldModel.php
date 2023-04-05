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

class CustomFieldModel extends \EMPORIKO\Models\BaseModel 
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
        protected $primaryKey = 'ctid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['type','value','target'];
	
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
		'ctid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'type'=>	['type'=>'INT','constraint'=>'36','null'=>FALSE],
		'target'=>	['type'=>'TEXT','null'=>FALSE],
                'value'=>	['type'=>'TEXT','null'=>FALSE],            
	];
	
        function getFieldsByTarget($target,$access,array &$arr=[])
        {
            $records=$this->getView('vw_custom_fields')->filtered(['access'=>$access,'module'=>$target])->find();
            foreach (is_array($records) ? $records : [] as $record)
            {
                if (!array_key_exists($record['name'], $arr))
                {
                    $arr[$record['name']]=
                    [
                        'args'=>['name'=>'customfields['.$record['name'].'][value]','tooltip'=>$record['tooltip'],'tab_name'=>$record['tab']],
                        'type'=>$record['type'],
                        'value'=>$record['value'],
                        'label'=>$record['text'],
                    ];
                    if ($record['type']=='DropDown')
                    {
                        $arr[$record['name']]['args']['options']= json_decode($record['options'],TRUE);
                    }
                    if (array_key_exists('required', $record) && intval($record['required'])==1)
                    {
                        $arr[$record['name']]['args']['required']=TRUE;
                    }
                    $arr[$record['name'].'_type']=
                    [
                        'args'=>['name'=>'customfields['.$record['name'].'][type]'],
                        'type'=>'Hidden',
                        'value'=>$record['cftid'],
                    ];
                }
            }
        }
}

