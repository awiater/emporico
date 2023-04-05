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


class RuleModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='rules';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'rulid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['rulname','ruldesc','rultrigger','rulaction','rulaction_args','rulaction_args_editable','access','enabled'];
	
	protected $validationRules =
	 [
	 	'rulname'=>'required|is_unique[rules.rulname,rulid,{rulid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'rulid'=>                   ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'rulname'=>                 ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'ruldesc'=>                 ['type'=>'VARCHAR','constraint'=>'250','null'=>TRUE],
		'rultrigger'=>              ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'rulaction'=>               ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'rulaction_args'=>          ['type'=>'LONGTEXT','null'=>FALSE],
                'rulaction_args_editable'=> ['type'=>'LONGTEXT','null'=>FALSE],
		'access'=>                  ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'enabled'=>                 ['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
        
        /**
         * Returns array with available triggers
         * 
         * @return array
         */
        function getTriggers()
        {
            return $this->getModel('Settings')->getListSettings('system.rules_trigger_');
        }
        
        /**
         * Add new trigger
         * 
         * @param string $name
         * @param string $text
         * 
         * @return bool
         */
        function addTrigger(string $name,string $text)
        {
            return $this->getModel('Settings')->add('system','rules_trigger_'.$name, $name,'textlong',$text); 
        }
        
        /**
         * Returns array with available actions
         * 
         * @return array
         */
        function getActions(bool $getCommand=FALSE)
        {
            $arr=[];
            /*foreach($this->getModel('Settings')->get('system.rules_actions_notify_*',FALSE,'*') as $act)
            {
                $act['param']= str_replace('rules_actions_notify_', '', $act['param']);
                $arr[$act['param']]=lang($act['tooltip']);
            }*/
            
            $arr['send_email']=$getCommand ? 'Settings::sendNotification@{theme},{data},{email}' : lang('system.tasks.actions_send_email');
            $arr['notify_user']=$getCommand ? 'Settings::sendNotification@{theme},{data},{user}' :lang('system.tasks.actions_notify_user');
            $arr['notify_cust']=$getCommand ? 'Customers::sendNotification@{theme},{data},cust' :lang('system.tasks.actions_notify_cust');
            $arr['command']=$getCommand ? '{rulaction_args}' :lang('system.tasks.rules_actioncustom');
            return $arr;
        }
        
        /**
         * Get rule action command template
         * 
         * @param string $name
         * 
         * @return string
         */
        function getActionTemplate(string $name)
        {
            $actions=$this->getActions(TRUE);
            return array_key_exists($name, $actions) ?$actions[$name] : null;
        }
        
        /**
         * Add new action
         * 
         * @param string $name
         * @param string $text
         * @param array  $action
         * @param array  $args
         * 
         * @return bool
         */
        function addAction(string $name,string $text,array $action,array $args)
        {
            foreach($args as $key=>$value)
            {
                if (is_a($value, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
                {
                    $args[$key]=$value->serialize();
                }
            }
            $args= json_encode(['args'=>$args,'action'=>$action]);
            return $this->getModel('Settings')->add('system','rules_actions_'.$name, $args,'textlong',$text);
        }
        
        /**
         * Action rule by trigger
         * 
         * @param string $trigger
         * @param \EMPORIKO\Controllers\BaseController $controller
         */
        function actionRuleByTrigger(string $trigger,array $args,\EMPORIKO\Controllers\BaseController $controller=null)
        {
            $rules=$this->filtered(['rultrigger'=>$trigger,'enabled'=>1,'access'=>'@loged_user'])->find();
            if (is_array($rules) && count($rules) > 0)
            {
                foreach($rules as $rule)
                {
                    $action=$this->getActionTemplate($rule['rulaction']);
                    if ($action!=null)
                    {
                        if (Str::isJson($action))
                        {
                            $action= json_decode($action,TRUE);
                        }else
                        if (Str::contains($action, '::'))
                        {
                            $action=explode('::',$action);
                            $action=['controller'=>$action[0],'action'=>$action[1]];
                            if (Str::contains($action['action'], '@'))
                            {
                                $action['action']=explode('@',$action['action']);
                                $action['args']=$action['action'][1];
                                $action['action']=$action['action'][0];
                                if (Str::contains($action['args'], ','))
                                {
                                    $action['args']=explode(',',$action['args']);
                                }
                            }
                        }
                        if (!is_array($action))
                        {
                            goto end_loop;
                        }
                        $rule= json_decode($rule['rulaction_args'],TRUE);
                        foreach($rule as $key=>$value)
                        {
                            $rule['{'.$key.'}']= is_array($value) ? implode(';',$value) : $value;
                            unset($rule[$key]);
                        }
                        if (array_key_exists('args', $action))
                        {
                            foreach($action['args'] as $key=>$value)
                            {
                                $action['args'][$key]= str_replace(array_keys($rule), $rule, $action['args'][$key]);
                                if ($action['args'][$key]=='{data}')
                                {
                                    $action['args'][$key]=$args;
                                }
                                if ($action['args'][$key]=='{user}')
                                {
                                    unset($action['args'][$key]);
                                }
                                
                            }
                        }
                        if (is_array($action) && Arr::KeysExists(['controller','action'], $action))
                        {
                            loadModuleFromArray($action);
                        }
                    }
                    end_loop:
                }
            }
            
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            
            $arr['rulname']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('rulname')
                    ->setID('rulname')
                    ->setMaxLength('50')
                    ->setText('rulname')
                    ->addClass('w-75')
                    ->setTab('general')
                    ->setAsRequired();
            
            $arr['ruldesc']=\EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('ruldesc')
                    ->setID('ruldesc')
                    ->setMaxLength('250')
                    ->setText('ruldesc')
                    ->setTab('general');
            
            $arr['access']=\EMPORIKO\Controllers\Pages\HtmlItems\AcccessField::create()
                    ->setName('access')
                    ->setID('access')
                    ->setTab('general')
                    ->setText('access')
                    ->setAsRequired();
            
            $arr['enabled']=\EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                    ->setName('enabled')
                    ->setID('enabled')
                    ->setTab('general')
                    ->setText('enabled')
                    ->setAsRequired();
            
            $arr['rultrigger']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('rultrigger')
                    ->setID('rultrigger')
                    ->setText('rultrigger')
                    ->setOptions($this->getTriggers())
                    ->setTab('cfgtab')
                    ->setAsRequired();
            
            $arr['rulaction']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('rulaction')
                    ->setID('rulaction')
                    ->setText('rulaction')
                    ->setOptions($this->getActions())
                    ->setTab('cfgtab')
                    ->setAsRequired();
            
            if (array_key_exists('rulaction_args', $record))
            {
                if (Str::isJson($record['rulaction_args']))
                {
                    $record['rulaction_args']= json_decode($record['rulaction_args'],TRUE);
                } 
            }
            
            $fields='<label id="action_args_email_label">'.lang('system.tasks.actions_notify_label_email').'</label>'.\EMPORIKO\Controllers\Pages\HtmlItems\EmailField::create()
                    ->setName('action_args[email]')
                    ->setID('action_args_email')
                    ->setValue(is_array($record['rulaction_args']) && array_key_exists('email', $record['rulaction_args']) ? $record['rulaction_args']['email'] :'')
                    ->render();
            
            $fields.='<label id="action_args_theme_label_user" class="mt-2">'.lang('system.tasks.actions_notify_label_user').'</label>'. \EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField::create()
                    ->setName('action_args[user]')
                    ->setID('action_args_user')
                    ->setInputField($this->getModel('Auth/User')->getUsersEmails(TRUE,'email','name'))
                    ->setValue(is_array($record['rulaction_args']) && array_key_exists('user', $record['rulaction_args']) ? $record['rulaction_args']['user'] :'')
                    ->render();
            
            $fields.='<label id="action_args_theme_label" class="mt-2">'.lang('system.tasks.actions_notify_label').'</label>'. \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('action_args[theme]')
                    ->setID('action_args_theme')
                    ->setAsAdvanced()
                    ->setOptions($this->getModel('Documents/ReportModel')->getTemplatesForForm())
                    ->setValue(is_array($record['rulaction_args']) && array_key_exists('theme', $record['rulaction_args']) ? $record['rulaction_args']['theme'] :'')
                    ->render();
            
            $fields.='<label id="action_args_command_label" class="mt-2">'.lang('system.tasks.rules_actioncustom').'</label>'. \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('action_args[command]')
                    ->setID('action_args_command')
                    ->setValue(is_string($record['rulaction_args']) ? $record['rulaction_args'] :'')
                    ->render();
            $arr['rulaction_args_field']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                    ->setName('rulaction_args_field')
                    ->setID('rulaction_args_field')
                    ->setText('rulaction_args')
                    ->setTab('cfgtab')
                    ->addClass('p-3')
                    ->setValue($fields);
            
            return $arr;
        }
}