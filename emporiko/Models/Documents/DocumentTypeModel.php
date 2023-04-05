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

class DocumentTypeModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='documents_types';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'dtid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','icon','text','desc','route_edit','route_show','route_down','linked','shared'];
        
        protected $validationRules =
	[
		'name'=>'required|is_unique[documents_types.name,dtid,{dtid}]',
	];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'dtid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'name'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'icon'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'text'=>		['type'=>'TEXT','null'=>TRUE],
                'desc'=>		['type'=>'TEXT','null'=>TRUE],
		'route_edit'=>		['type'=>'TEXT','null'=>TRUE],
                'route_show'=>		['type'=>'TEXT','null'=>TRUE],
                'route_down'=>		['type'=>'TEXT','null'=>TRUE],
		'linked'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
                'shared'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
}