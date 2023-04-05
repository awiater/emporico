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
 
namespace EMPORIKO\Models\Pages;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class PageModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='pages';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'pgid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['pg_title','pg_name','pg_action','pg_type','pg_desc'
                                  ,'pg_cfg','pg_edit','pg_remove','pg_restricted'
                                  ,'pg_order','access','enabled'];
	
	protected $validationRules =
	[
		'pg_name'=>'required|is_unique[pages.pg_name,pgid,{pgid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'pgid'=>            ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
                'pg_title'=>        ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'pg_name'=>         ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'pg_desc'=>         ['type'=>'TEXT','null'=>TRUE],
                'pg_action'=>       ['type'=>'LONGTEXT','null'=>TRUE],
                'pg_cfg'=>          ['type'=>'LONGTEXT','null'=>TRUE],
		'pg_type'=>         ['type'=>'VARCHAR','constraint'=>'50','default'=>'static','null'=>FALSE],
                'pg_edit'=>         ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
                'pg_remove'=>       ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
		'enabled'=>         ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
                'pg_restricted'=>   ['type'=>'INT','default'=>'0','constraint'=>'11','null'=>FALSE],
                'pg_order'=>        ['type'=>'INT','constraint'=>'11','null'=>FALSE],
                'access'=>          ['type'=>'VARCHAR','constraint'=>'150','default'=>'customer','null'=>FALSE],
	];
        
        /**
         * Returns array with pages data which are used by customer portal
         *  
         * @return type
         */
        function getPagesForMenu()
        {
            $arr=[];
            if (auth()->isLoged())
            {
                $arr['logout']=
                        [
                            'url'=>url('/logout',null,[],['refurl'=>current_url(FALSE,TRUE)]),
                            'title'=>lang('system.auth.logout')
                        ];
            }
            foreach($this->filtered(['enabled'=>1,'access <>'=>'0'])->orderBy('pg_order')->find() as $page)
            {
                $arr[$page['pg_name']]=
                        [
                            'url'=>url('/pages/'.$page['pg_name'].'.html'),
                            'title'=>$page['pg_title']
                        ];
            }
            return $arr;
        }
        
        /**
         * Returns array with pages order values
         * 
         * @param bool   $isNew
         * @param string $valueField
         * 
         * @return array
         */
        function getPagesOrder(bool $isNew=FALSE,string $valueField='pgid')
        {
            $valueField= in_array($valueField, $this->allowedFields) ? $valueField : 'pgid';
            $arr=[];
            foreach($this->orderBy('pg_order')->find() as $page)
            {
                $arr[$page[$valueField]]=$page['pg_title'];
            }
            if ($isNew)
            {
                $arr[0]=lang('documents.pages_order_this');
            }
            return $arr;
        }
        
        function getNotRemovablePages()
        {
            $arr=[];
            foreach($this->where('pg_remove',0)->find() as $page)
            {
                 $arr['pg_remove']=$page['pgid'];
            }
            return $arr;
        }
        
        
        /**
         * Update pages order
         * 
         * @param array $data
         * 
         * @return boolean
         */
        function updateOrder(array $data)
        {
            foreach($data as $key=>$val)
            {
                $data[$key]=['pg_order'=>$key,'pgid'=>$val];
            }
            if (count($data) < 1)
            {
                return FALSE;
            }
            return $this->updateBatch($data,'pgid');
        }
        
        public function getFieldsForForm(array $record) 
        {
            /*'','','','',''
                                  ,'access','enabled'*/
            $arr=[];
            
            $arr['pg_title']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('pg_title')
                    ->setID('pg_title')
                    ->setText('pg_title')
                    ->setMaxLength(150)
                    ->setAsRequired()
                    ->setTab('general');
            
            $arr['pg_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('pg_desc')
                    ->setID('pg_desc')
                    ->setText('pg_desc')
                    ->setRows(3)
                    ->setTab('general');
            
            
            
            $arr['access']= \EMPORIKO\Controllers\Pages\HtmlItems\AcccessField::create()
                    ->setName('access')
                    ->setID('access')
                    ->setText('pg_access')
                    ->setAsRequired()
                    ->addGroupsOptions()
                    
                    ->setTab('general'); 
            
            if (array_key_exists('allowguest', $record) && intval($record['allowguest'])==1)
            {
                $arr['access']->addVisitorLevel();
            }
            
            /*$arr['pg_restricted']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                    ->setName('pg_restricted')
                    ->setID('pg_restricted')
                    ->setText('pg_restricted')
                    //->setAsRequired()
                    ->setTab('general');*/  
            
            $arr['pg_order']= \EMPORIKO\Controllers\Pages\HtmlItems\OrderField::create()
                    ->setName('pg_order')
                    ->setID('pg_order')
                    ->setText('pg_order')
                    ->setOptions($this->getPagesOrder(!is_numeric($record['pgid'])))
                    ->setTab('general');
            
            $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                    ->setName('enabled')
                    ->setID('enabled')
                    ->setText('pg_enabled')
                    ->setAsRequired()
                    ->setTab('general');
            
            
            
            if (array_key_exists('editable', $record) && !$record['editable'])
            {
               unset($arr['pg_restricted']);
               unset($arr['enabled']);
            }
            
            if ($record['acc_cfg'])
            {
                $arr['pg_edit']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                        ->setName('pg_edit')
                        ->setID('pg_edit')
                        ->setText('pg_edit')
                        ->setTab('general');
                  
                 $arr['pg_remove']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                        ->setName('pg_remove')
                        ->setID('pg_remove')
                        ->setText('pg_remove')
                        ->setTab('general');
            }
            
            if (Arr::KeysExists(['pg_type'], $record) && $record['pg_type']=='static')
            {
                $arr['pg_action']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('pg_action')
                        ->setID('pg_action')
                        ->setTab('general')
                        ->setValue('static');
                
                $arr['pg_cfg']= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()
                        ->setName('pg_cfg')
                        ->setID('pg_cfg')
                        ->setText('pg_cfg')
                        ->setTab('static')
                        ->setFullToolbar();
            }else
            if (Arr::KeysExists(['pg_type'], $record) && $record['pg_type']=='contact')
            {
                $arr['pg_action']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('pg_action')
                        ->setID('pg_action')
                        ->setTab('general')
                        ->setValue('contact');
                
                $arr['pg_cfg']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                        ->setName('pg_cfg')
                        ->setID('pg_cfg')
                        ->setText('pg_cfg_contact_email')
                        ->setInputField(
                                \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                                ->setName('pg_cfg_mailbox_input')
                                ->setID('id_pg_cfg_input')
                                ->setOptions($this->getModel('System/Contact')->getSystemUsersEmailsForForm())
                                ->setAsAdvanced()
                                )
                        ->setTab('contact');
            }
            
            return $arr;
            //pg_action,'pg_cfg','pg_edit','pg_remove' 
        }
}

