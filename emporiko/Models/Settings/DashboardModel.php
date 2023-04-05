<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Models\Settings;


class DashboardModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Settings table name
	 * 
	 * @var string
	 */
	protected $table='dashboards';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'did';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','desc','data','data_edit_html','data_edit_css','enabled'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'did'=>             ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>            ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'desc'=>            ['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'data'=>            ['type'=>'LONGTEXT','null'=>TRUE],
                'data_edit_html'=>  ['type'=>'LONGTEXT','null'=>TRUE],
                'data_edit_css'=>   ['type'=>'LONGTEXT','null'=>TRUE],
                'enabled'=>         ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		
	];
        
        /**
         * Returns array with dashboards names for drop down
         * 
         * @return string
         */
        function getDashboardsForForm()
        {
            $arr=[];
            foreach($this->filtered(['enabled'=>1])->find() as $dash)
            {
                $arr[$dash['name']]=$dash['name'].'=>'.$dash['desc'];
            }
            return $arr;
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            $arr['name']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName('name')
                        ->setID('name')
                        ->setText('name')
                        ->setMaxLength(50)
                        ->setTab('general');
            
            $arr['desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                        ->setName('desc')
                        ->setID('desc')
                        ->setText('desc')
                        ->setTab('general');
            
            $arr['editor']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                        ->setName('editor')
                        ->setID('editor')
                        ->setText('')
                        ->setTab('editor');
            
            $arr[]= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('data_edit_html')
                    ->setID('id_data_edit_html')
                    ->setText('')
                    ->setTab('editor')
                    ->addClass('d-none');
            
            $arr[]= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('data_edit_css')
                    ->setID('id_data_edit_css')
                    ->setText('')
                    ->setTab('editor')
                    ->addClass('d-none');
            return $arr;
        }
}