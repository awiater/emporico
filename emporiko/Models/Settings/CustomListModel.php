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

class CustomListModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='custom_listdata';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'trmid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['trm_name','trm_grp','trm_value'];
	
	protected $validationRules =
	 [
	 	'trm_name'=>'required|is_unique[custom_listdata.trm_name,trmid,{trmid}]',
	 	'trm_grp'=>'required',
	 	'trm_value'=>'required',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'trmid'=>               ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'trm_name'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'trm_grp'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'trm_value'=>		['type'=>'TEXT','null'=>TRUE],                	
	];
        
        /**
         * Add new record
         * 
         * @param string $group
         * @param string $name
         * @param string $value
         * 
         * @return bool
         */
        function add($group,$name,$value)
        {
            return $this->save(['trm_name'=>$name,'trm_grp'=>$group,'trm_value'=>$value]);
        }
        
        /**
         * Returns array with list data by group
         * 
         * @param string $group
         * @param bool   $advList
         * 
         * @return array
         */
        function getByGroup($group,$advList=FALSE)  
        {
            $arr=[];
            foreach($this->where('trm_grp',$group)->orderby('trm_name')->find() as $value)
            {
                if ($advList)
                {
                  $arr[$value['trm_name']]=['text'=>$value['trm_value'],'value'=>$value['trm_name']];  
                } else 
                {
                    $arr[$value['trm_name']]=$value['trm_value'];
                }
                
            }
            return $arr;
        }
}