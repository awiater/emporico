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
 
namespace EMPORIKO\Models\Customers;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class TicketModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='tickets';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'tiid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['tck_subject','tck_desc','tck_extrafields','tck_user','tck_status'
                                  ,'tck_priority','tck_type','tck_iscust','tck_account'
                                  ,'tck_addedon','tck_target'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'tiid'=>                ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'tck_subject'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'tck_desc'=>		['type'=>'TEXT','null'=>FALSE],
                'tck_extrafields'=>	['type'=>'TEXT','null'=>TRUE],
                'tck_user'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'tck_account'=>         ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'tck_iscust'=>          ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
                'tck_status'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
                'tck_priority'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		'tck_type'=>            ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'default'=>0],
                'tck_addedon'=>         ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'tck_target'=>          ['type'=>'TEXT','null'=>TRUE],
	];
        
        /**
         * Returns array with ticket target groups
         * 
         * @return array
         */
        function getTargetGroups(bool $forForm=FALSE)
        {
            $arr=[];
            foreach($this->getModel('Auth/UserGroup')->getForForm('ugid','ugname', FALSE, null,['ugiscust'=>0]) as $key=>$value)
            {
                $arr['#'.$key]=$value;
            }
            $arr[':system_support']=lang('tickets.settings_systemsupport');
            return $arr;
        }
        
        /**
         * Returns array with available tickets statuses
         * 
         * @param Int $status
         * @param bool $forButton
         * 
         * @return type
         */
        function getTicketsStatuses(Int $status=-1,bool $forButton=FALSE)
        {
            $arr=[];
            foreach($this->getModel('Settings')->get('tickets.tickets_status_*',FALSE,'*') as $ticket)
            {
                if ($forButton)
                {
                    $arr[$ticket['value']]= site_url();
                }else
                {
                    $key= str_replace('tickets_status_', '', $ticket['param']);
                    $arr[$key]=lang($ticket['value']);
                }
                
            }
            return array_key_exists($status, $arr) ? $arr[$status] : $arr;
        }
        
        function getLiveTickets(array $filters=[])
        {
            return $this->getView('vw_tickets')->filtered($filters)->whereNotIn('tck_status',$this->getModel('Settings')->get('tickets.tickets_ticketnonpendingstatus',TRUE))->find();
        }
        
        /**
         * Get actions (history) for given ticket
         * 
         * @param Int $ticketID
         * 
         * @return array
         */
        function getTicketMovements(Int $ticketID)
        {
            $data=$this->getView('vw_movements')
                       ->orderBy('mhdate DESC')
                       ->filtered(['mhref'=>$ticketID,'type'=>'tickets'])->find();
            return $data;
        }
        /**
         * Returns array with available tickets types
         * 
         * @param bool    $forTiles
         * @param bool    $withDesc
         * 
         * @return array
         */
        function getTicketsTypes(bool $forTiles=FALSE,bool $withDesc=TRUE)
        {
            $arr=[];
            foreach($this->getModel('TicketType')->find() as $type)
            {
                if ($forTiles)
                {
                    $arr[$type['tit_code']]=
                    [
                        'type'=>$type['tit_type'],
                        'icon'=>$type['tit_icon'],
                        'text_color'=>$type['tit_textcolor'],
                        'sort'=>$type['tit_order'],
                    ];
                }else
                {
                    if ($withDesc)
                    {
                      $arr[$type['tit_code']]=$type['tit_name'].'=>'.$type['tit_desc'];  
                    }else
                    {
                       $arr[$type['tit_code']]=$type['tit_name']; 
                    }
                    
                }
            }
            return $arr;
        }
        
        /**
         * Returns array with ticket priority
         * 
         * @return array
         */
        function getTicketPriorities()
        {
            return lang('tickets.tck_priority_list');
        }
        
        /**
         * Get contact name for email template
         * 
         * @param string $name
         * 
         * @return string
         */
        function getContactName($name=null)
        {
            if ($name==null || ($name!=null && strlen($name) < 1))
            {
                $name=$this->getModel('Settings')->get('tickets.tickets_supportteamname');
            }
            if (Str::startsWith($name, '@'))
            {
                $name= loged_user(substr($name,1));
                $name= is_array($name) ? loged_user('name') : $name;
            }
            return $name;
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            //iscustomer customer  iscustomer
            if (intval($record['iscustomer'])==1)
            {
                if (intval(loged_user('iscustomer'))==0)
                {
                    $arr['tck_account']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('tck_account')
                        ->setID('tck_account')
                        ->setText('tck_account')
                        ->setOptions($this->getModel('Customer')->getForForm('code','name'))
                        ->setAsAdvanced()
                        ->setTab('general');
                }else
                {
                    $arr['tck_account']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('tck_account')
                        ->setID('tck_account')
                        ->setTab('general');
                }
            }
            $arr['tck_subject']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName('tck_subject')
                        ->setID('tck_subject')
                        ->setText('tck_subject')
                        ->setMaxLength(150)
                        ->setTab('general');
            
            $arr['tck_type']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('tck_type')
                        ->setID('tck_type')
                        ->setTab('general');
            
            $arr['tck_priority']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('tck_priority')
                        ->setID('tck_priority')
                        ->setText('tck_priority')
                        ->setTab('general')
                        ->setOptions($this->getTicketPriorities());
            
            
            
            if (array_key_exists('tck_target', $record))
            {
                $arr['tck_target']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('tck_target')
                        ->setID('tck_target')
                        ->setText('tck_target')
                        ->setTab('general');
            }
            
            if (is_array($record['tck_extrafields']))
            {
                foreach($record['tck_extrafields'] as $key=>$field)
                {
                    if (is_array($field) && Arr::KeysExists(['field','label'], $field))
                    {
                        $arr[$key]= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField($field['field']);
                        $arr[$key]->setName("tck_extrafields[$key][value]")
                                  ->setText('='.$field['label']);
                        $arr[]= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                            ->setName("tck_extrafields[$key][field]")
                            ->setID("id_tck_extrafields_label_$key")
                            ->setValue(base64_encode($field['field']));
                    }
                }
               
            }
            
            $arr['tck_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()
                        ->setName('tck_desc')
                        ->setID('tck_desc')
                        ->setText('tck_desc')
                        ->setTab('general')
                        ->setBasicToolbar();
            if (array_key_exists('editabledesc', $record) && !$record['editabledesc'])
            {
                $arr['tck_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::createField($arr['tck_desc']);
            }
            
           return $arr;
        }
       
}