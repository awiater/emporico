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
use \EMPORIKO\Helpers\MovementType;

class TaskModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='tasks';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'tskid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['tsk_isauto','tsk_descs','tsk_addon','tsk_actionon',
                                  'tsk_ref','tsk_actionedon','tsk_addby','tsk_actionedby',
                                  'tsk_action','enabled'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'tskid'=>           ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
                'tsk_descs'=>       ['type'=>'VARCHAR','constraint'=>'250','null'=>TRUE],
                'tsk_ref'=>         ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
		'tsk_isauto'=>      ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		'tsk_addon'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'tsk_actionon'=>    ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
                'tsk_actionedon'=>  ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'tsk_addby'=>       ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
		'tsk_actionedby'=>  ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
		'tsk_action'=>      ['type'=>'TEXT','null'=>TRUE],
		'enabled'=>         ['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	
        /**
         *  Add new tasks
         * 
         * @param string       $desc
         * @param string|array $action
         * @param string       $ref
         * @param string       $actionon
         * @param bool         $isauto
         * @param string       $addBy
         * 
         * @return boolean
         */
	function addNew(string $desc,$action,string $ref,string $actionon='now',bool $isauto=TRUE,string $addBy=null)
	{
            if (is_array($action) && Arr::KeysExists(['controller','action'], $action))
            {
                $action= json_encode($action);
            }
            $addBy=$addBy==null ? loged_user('username') : $addBy;
            if (!is_string($action))
            {
                return FALSE;
            }
            return $this->save(
                [
                    'tsk_isauto'=>$isauto ? 1 :0,
                    'tsk_descs'=>$desc,
                    'tsk_addon'=> formatDate(),
                    'tsk_addby'=>$addBy,
                    'tsk_action'=>$action,
                    'tsk_actionon'=>$actionon,
                    'tsk_ref'=>$ref,
                    'enabled'=>1
	 	]);
	}
        
        function actionEnabled($date='now')
        {
           $tasks=$this->filtered(['( tsk_actionon <'=>$date,'|| tsk_actionon )'=>'now','enabled'=>1])->find();
           foreach(is_array($tasks) ? $tasks : [] as $task)
           {
               if (Arr::KeysExists(['tsk_action'], $task))
               {
                   $task['tsk_action']= json_decode($task['tsk_action'],TRUE);
                   if (is_array($task['tsk_action']))
                   {
                       $this->save(['tsk_actionedon'=>formatDate(),'tsk_actionedby'=>'auto','enabled'=>0,'tskid'=>$task['tskid']]);
                       loadModuleFromArray($task['tsk_action']);
                   }
               }
           }    
        }
	 
}