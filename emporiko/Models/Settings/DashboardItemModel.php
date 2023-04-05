<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 * @version: 1.1					
 * @author Artur W				
 * @copyright Copyright (c) 2022 All Rights Reserved				
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Models\Settings;


class DashboardItemModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Settings table name
	 * 
	 * @var string
	 */
	protected $table='dashboards_tiles';
	
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
	protected $allowedFields=['name','data','icon','enabled'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'dtid'=>        ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>        ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'desc'=>        ['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'data'=>        ['type'=>'TEXT','null'=>TRUE],
                'icon'=>        ['type'=>'TEXT','null'=>TRUE],
                'enabled'=>     ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		
	];
        
        /**
         * Returns array with tiles data
         * 
         * @return type
         */
        function getTiles()
        {
            $arr=[];
            foreach ($this->filtered(['enabled'=>1])->find() as $tile)
            {
                $arr[$tile['name']]=$tile;
            }
            return $arr;
        }
        
        
}