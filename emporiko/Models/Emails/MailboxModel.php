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
namespace EMPORIKO\Models\Emails;

use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Arrays as Arr;

class MailboxModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='emails_mailboxes';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'emmid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['emm_name','emm_desc','emm_inhost','emm_inuser','emm_inpass','emm_inport'
                              ,'emm_intissl','emm_outhost','emm_outuser','emm_outpass','emm_outport','emm_outissl'
                              ,'emm_fldinbox','emm_flddraft','emm_fldsent','emm_fldspam','emm_fldbin'
                              ,'emm_fldslist','emm_syncedfrom','emm_isdef','enabled'];
        
    protected $validationRules =
    [
        'emm_name' => 'required|is_unique[emails_mailboxes.emm_name,emmid,{emmid}]',
    ];
    
    protected $validationMessages = [];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'emmid'=>           ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
        'emm_name'=>        ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'emm_desc'=>        ['type'=>'TEXT','null'=>TRUE],
        'emm_inhost'=>      ['type'=>'TEXT','null'=>TRUE],
        'emm_inuser'=>      ['type'=>'TEXT','null'=>TRUE],
        'emm_inpass'=>      ['type'=>'TEXT','null'=>TRUE],
        'emm_inport'=>      ['type'=>'INT','constraint'=>'11','null'=>TRUE],
        'emm_intissl'=>     ['type'=>'INT','constraint'=>'11','null'=>TRUE,'default'=>0],
        'emm_outhost'=>     ['type'=>'TEXT','null'=>TRUE],
        'emm_outuser'=>     ['type'=>'TEXT','null'=>TRUE],
        'emm_outpass'=>     ['type'=>'TEXT','null'=>TRUE],
        'emm_outport'=>     ['type'=>'INT','constraint'=>'11','null'=>TRUE],
        'emm_outissl'=>     ['type'=>'INT','constraint'=>'11','null'=>TRUE,'default'=>1],
        'emm_fldinbox'=>    ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'emm_flddraft'=>    ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'emm_fldsent'=>     ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'emm_fldspam'=>     ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'emm_fldbin'=>      ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'emm_fldslist'=>    ['type'=>'TEXT','null'=>TRUE],
        'emm_syncedfrom'=>  ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
        'emm_isdef'=>       ['type'=>'INT','constraint'=>'11','default'=>'0','null'=>FALSE],
        'enabled'=>         ['type'=>'INT','constraint'=>'11','default'=>'1','null'=>FALSE],
        
    ];
    
    /**
     * Returns default mailbox data
     * 
     * @param type $returnArray
     * 
     * @return MailboxData
     */
    function getDefaultMailbox($returnArray=FALSE,$field=null)
    {
        $arr=$this->where('emm_isdef',1)->first();
        return is_array($arr) && count($arr) > 0 ? (array_key_exists($field, $arr) ? $arr[$field]: $arr) : ($returnArray ? [] : null);
    }
    
    /**
     * Returns default sending mailbox data
     * 
     * @param bool   $returnArray
     * @param string $field
     * 
     * @return mixed
     */
    function getDefaultOutMailbox(bool $returnArray=FALSE,string $field=null)
    {
        $arr=$this->where('emm_name',config('Email')->defMailbox)->first();
        return is_array($arr) && count($arr) > 0 ? (array_key_exists($field, $arr) ? $arr[$field]: $arr) : ($returnArray ? [] : null);
    }
    
        /**
     * Get mailbox data
     * @param type $name
     * @param bool $getDefaultIfNotExists
     * 
     * @return MailboxData
     */
    function getMailbox($name=null,bool $getDefaultIfNotExists=FALSE)
    {
        if ($name==null)
        {
            $name=$this->getDefaultMailbox();
        }else
        {
            $name=$this->filtered(['emm_name'=>$name,'|| emmid'=>$name,'|| emm_outuser'=>$name])->first();
        }
        
        if (is_array($name))
        {
            return MailboxData::create($name);
        }
        return $getDefaultIfNotExists ? $this->getDefaultMailbox() : null;
    }
    
    /**
     * Return array with mailboxes data for drop down field 
     * 
     * @param string $field
     * @param string $desc
     * @param bool   $advanced
     * @param bool   $addLogedUserEmail
     * 
     * @return array
     */
    function getDropdDownField(string $field='emm_name',string $desc='emm_desc',bool $advanced=TRUE,bool $addLogedUserEmail=FALSE)
    {
        if ($addLogedUserEmail)
        {
           $arr=[lang('emails.msg_from_field_mailbox')=>[],lang('emails.msg_from_field_loged')=>[]];
        }else
        {
            $arr=[];
        }
        $desc= in_array($desc, $this->allowedFields) ? $desc : 'emm_desc';
        $field= in_array($field, $this->allowedFields) ? $field : 'emm_name';
        foreach($this->filtered(['enabled'=>1])->find() as $value)
        {
            $this->parseFields($value);
            if ($advanced)
            {
                $value['_value']=$value['emm_name'].'=>'.$value[$desc];  
            }else
            {
                $value['_value']=$value[$desc];
            }
            if ($addLogedUserEmail)
            {
                $arr[lang('emails.msg_from_field_mailbox')][$value[$field]]=$value['_value'];
            }else
            {
                $arr[$value[$field]]=$value['_value'];
            }
            
        }
        if ($addLogedUserEmail)
        {
            $arr[lang('emails.msg_from_field_loged')][loged_user('email')]= loged_user('name');
        }
        return $arr;
    }
    

    
    
    /**
     * Returns array with form fields data
     * 
     * @param array $record
     * 
     * @return type
     */
    function getFieldsForForm(array $record) 
    {
        $arr=parent::getFieldsForForm($record);
        foreach(['emm_inuser','emm_outuser'] as $field)
        {
            $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\EmailField::createField($arr[$field])
                    ->setTab($field=='emm_inuser' ? 'tab_inbox' : 'tab_out')
                    ->setAsRequired();
        }
        
        foreach(['emm_inpass','emm_outpass'] as $field)
        {
            $arr[$field]->setAsPassword()->setTab($field=='emm_inpass' ? 'tab_inbox' : 'tab_out')
                        ->setAsRequired();
        }
        
        foreach(['emm_inport','emm_outport'] as $field)
        {
            $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\NumberField::createField($arr[$field])
                    ->setTab($field=='emm_inport' ? 'tab_inbox' : 'tab_out')
                    ->setAsRequired();
        }
        
        foreach(['emm_fldinbox','emm_flddraft','emm_fldsent','emm_fldspam','emm_fldbin','emm_fldslist'] as $field)
        {
            $arr[$field]->setTab('tab_flds')->setAsRequired();
        }
        
        $arr['emm_inhost']->setTab('tab_inbox')->setAsRequired();
        $arr['emm_outhost']->setTab('tab_out')->setAsRequired();
        $arr['emm_name']->setAsRequired();
        $arr['emm_outissl']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::createField($arr['emm_outissl'])
                ->setTab('tab_out')
                ->setAsRequired();
        $arr['emm_intissl']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::createField($arr['emm_intissl'])
                ->setTab('tab_inbox')
                ->setAsRequired();
        $arr['emm_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::createField($arr['emm_desc']);
        $arr['emm_fldslist']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField::createField($arr['emm_fldslist'])
                ->setAsRequired();
        $arr['emm_isdef']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::createField($arr['emm_isdef'])
                ->setAsRequired();
        if (array_key_exists('emm_isdef',$record) && intval($record['emm_isdef'])==1)
        {
            $arr['enabled']->setReadonly();
        }
        $arr['emm_syncedfrom']= \EMPORIKO\Controllers\Pages\HtmlItems\DatePicker::createField($arr['emm_syncedfrom'])
                ->setMaxDate(formatDate());
        return $arr;
    }
    
    /**
     * Fetches the row of database
     * 
     * @param array|integer|string|null $id
     * 
     * @return array|object|null
     */
    function find($id = null) 
    {
        if ($id!=null && is_numeric($id))
        {
            $id=parent::find($id);
            $this->parseFields($id);
            return $id;
        }
        return  parent::find($id);
    }
    
    /**
     * Returns the first row of the result set.
     * 
     * @return array|object|null
     */
    function first()
    {
        $record=parent::first();
        if (is_array($record))
        {
            $this->parseFields($record);
        }
        return $record;
    }

    /**
     * A convenience method that will attempt to determine whether the
     * data should be inserted or updated. Will work with either
     * an array or object. When using with custom class objects,
     * you must ensure that the class will provide access to the class
     * variables, even if through a magic method.
     * 
     * @param array|object $data
     * 
     * @return bool
     * 
     * @throws ReflectionException
     */
    function save($data): bool 
    {
        if (is_array($data))
        {
            $this->parseFields($data,FALSE);
            if (array_key_exists('emm_isdef', $data) && intval($data['emm_isdef'])==1)
            {
                $this->builder()->set('emm_isdef',0)->update();
                $data['enabled']=1;
            }
        }
        return parent::save($data);
    }
    
    /**
     * Encrypt/Decrypt user fields and parse folders fields to/from string
     * 
     * @param array $data
     * 
     * @param bool $decrypt
     */
    private function parseFields(array &$data,bool $decrypt=TRUE)
    {
        if (is_array($data) && Arr::KeysExists(['emm_inuser','emm_inpass','emm_outuser','emm_outpass'], $data))
        {
            
            $encrypter = \Config\Services::encrypter();
            if ($decrypt)
            {
                $data['emm_inuser']= $encrypter->decrypt(base64_decode($data['emm_inuser']));
                $data['emm_inpass']= $encrypter->decrypt(base64_decode($data['emm_inpass']));
                $data['emm_outuser']= $encrypter->decrypt(base64_decode($data['emm_outuser']));
                $data['emm_outpass']= $encrypter->decrypt(base64_decode($data['emm_outpass']));
                if (array_key_exists('emm_fldslist', $data))
                {
                   $data['emm_fldslist']=json_decode($data['emm_fldslist'],TRUE); 
                }
                
            }else
            {
                $data['emm_inuser']= base64_encode($encrypter->encrypt($data['emm_inuser']));
                $data['emm_inpass']= base64_encode($encrypter->encrypt($data['emm_inpass']));
                $data['emm_outuser']= base64_encode($encrypter->encrypt($data['emm_outuser']));
                $data['emm_outpass']= base64_encode($encrypter->encrypt($data['emm_outpass']));
                if (array_key_exists('emm_fldslist', $data))
                {
                    $data['emm_fldslist']= is_array($data['emm_fldslist']) ? json_encode($data['emm_fldslist']) : $data['emm_fldslist'];
                }
            }
        }
    }

}