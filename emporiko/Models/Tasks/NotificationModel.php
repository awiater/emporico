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
 
namespace EMPORIKO\Models\Tasks;

use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;


class NotificationModel extends \EMPORIKO\Models\BaseModel  
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='notifications';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'nid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['createdon','createdby','type','text','priority','assignon','assignby','enabled'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[rules.name,rid,{rid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'nid'=>			['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'createdon'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'createdby'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'type'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'text'=>		['type'=>'TEXT','null'=>FALSE],
		'assignon'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'assignby'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'priority'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	/**
	 * Add notification for mobile interface
	 * 
	 * @param  String $text
	 * @param  String $user
	 * @param  Int $priority
	 */
	function addForMobile($text,$user=null,$priority=5)
	{
		$user=$user==null ? loged_user('username') : $user;
		$this->builder()->set(
		[
			'createdon'=>formatDate(),
			'createdby'=>$user,
			'type'=>'mobile',
			'enabled'=>1,
			'text'=>$text,
			'priority'=>$priority
		])->Insert();
	}
	
	/**
	 * Returns array with all active notifications for mobile
	 * 
	 * @param  Array $filters
	 * 
	 * @return Array
	 */
	function getForMobile(array $filters=[])
	{
		$filters['enabled']=1;
		$filters['type']='mobile';
		return $this->filtered($filters)->find();
	}

	/**
	 * Returns array with all active notifications messages for mobile
	 * 
	 * @param  Array $filters
	 * 
	 * @return Array
	 */
	function getMessagesForMobile(array $filters=[])
	{
		$arr=[];
		foreach ($this->getForMobile($filters) as  $value) 
		{
			$arr[]=$value['text'];
		}
		return $arr;
	}
	
	/**
	 * Change record status
	 * 
	 * @param Array $filters
	 * @param Int $enabled
	 */
	function changeStatus(array $filters,$enabled)
	{
		$this->filtered($filters)->builder()->set(['enabled'=>$enabled,'assignon'=>formatDate(),'assignby'=>loged_user('username')])->Update();
	}
}