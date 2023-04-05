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
 
namespace EMPORIKO\Models\Documents;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class FormSubModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='forms_subs';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'fsid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['formid','createdby','createdon','field','value','subid','formattype'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'fsid'=>                ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'formid'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'createdby'=>           ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
                'createdon'=>           ['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
                'field'=>               ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'value'=>               ['type'=>'TEXT','null'=>TRUE],
                'subid'=>               ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'formattype'=>          ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
	];
        
        /**
         * Returns array with given form responses
         * 
         * @param  Int $formid
         * @return Array
         */
        function getResult($formid)
        {
            $formid=$this->where('formid',$formid)
                        //->groupby('field')
                        ->orderby('formattype')
                        ->find();
            
            if (!is_array($formid))
            {
                return [];
            }
            $arr=[];
            foreach($formid as $field)
            {
                if (!array_key_exists($field['subid'], $arr))
                {
                    $arr[$field['subid']]=[];
                }
                $arr[$field['subid']]['createdby']=$field['createdby'];
                $arr[$field['subid']]['createdon']= convertDate($field['createdon'], 'DB', 'd M Y H:i');
                $arr[$field['subid']][$field['field']]=$field['value'];
            }
            
            return array_values($arr);
        }
        
        /**
         * Returns array with form fields data
         * 
         * @param  Int $formid
         * @return array
         */
        function getForEditForm($formid)
        {
            $formid=$this->select('field,formattype,value')
                        ->where('formid',$formid)
                        //->groupby('field')
                        ->orderby('formattype')
                        ->find();
            if (!is_array($formid))
            {
                return null;
            }
            $arr=[];
            foreach($formid as $field)
            {
                if (strlen($field['value']) > 0)
                {
                if (!array_key_exists($field['field'], $arr))
                {
                    $arr[$field['field']]=['data'=>[],'colors'=>[]];
                }
                if (!array_key_exists($field['value'], $arr[$field['field']]['data']))
                {
                    $arr[$field['field']]['data'][$field['value']]=1;
                } else 
                {
                    $arr[$field['field']]['data'][$field['value']]+=1;
                }
                $arr[$field['field']]['colors'][$field['value']]='rgb('.rand(0,255).','.rand(0,255).','.rand(0,255).')';
                $arr[$field['field']]['type']=$field['formattype'];
                }
            }
            return $arr;
        }

}