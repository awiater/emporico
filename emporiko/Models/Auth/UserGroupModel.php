<?php

/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Models\Auth;

use \EMPORIKO\Helpers\Strings as Str;

class UserGroupModel extends \EMPORIKO\Models\BaseModel {

    /**
     * Users table name
     * 
     * @var string
     */
    protected $table = 'users_groups';

    /**
     * Table primary key
     * 
     * @var string
     */
    protected $primaryKey = 'ugid';

    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields = ['ugname', 'ugdesc', 'enabled', 'ugref'];
    
    protected $validationRules = [
        'ugname' => 'required|is_unique[users_groups.ugname,ugid,{ugid}]',
    ];
    
    protected $validationMessages = [
        'ugname' => [
            'is_unique' => 'system.auth.groups_error_unique',
        ]
    ];

    /**
     * Fields types declarations for forge
     * @var array
     */
    protected $fieldsTypes = 
    [
        'ugid' =>       ['type' => 'INT', 'constraint' => '36', 'auto_increment' => TRUE, 'null' => FALSE],
        'ugname' =>     ['type' => 'VARCHAR', 'constraint' => '150', 'null' => FALSE, 'unique' => TRUE],
        'ugdesc' =>     ['type' => 'TEXT', 'null' => FALSE],
        'enabled' =>    ['type' => 'INT','constraint' => '11', 'null' => FALSE],
        'ugref' =>      ['type' => 'VARCHAR', 'constraint' => '36'],
        'ugperms' =>    ['TEXT', 'null' => FALSE],
        'ugiscust'=>    ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugview' =>     ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugstate' =>    ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugmodify' =>   ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugedit' =>     ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugcreate' =>   ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugdelete' =>   ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
        'ugsettings' => ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 0],
    ];
    
    /**
     * Returns group data for profile form as array
     * 
     * @return type
     */
    function getForProfile() 
    {
        $filters = [];
        if (!Str::contains(loged_user('accessgroups'), $this->getSuperAdminsGroup())) 
        {
            $filters = ['ugref In' => explode(',', loged_user('accessgroups'))];
        }
        return $this->getForForm(
                        'ugref',
                        null,
                        FALSE,
                        null,
                        $filters
        ); 
    }
    
    /**
     * Get group access data by group id
     * 
     * @param Int $record
     * 
     * @return array
     */
    function getDataByID($record)
    {
        $accTbl=$this->getModel('AuthAccess')->table;
        return $this->where($this->primaryKey,$record)
                    ->join($accTbl,'acc_ref=ugref','Left')
                    ->first();
    }
    
    /**
     * Return array with group access levels only with positive (1) values
     * 
     * @param mixed $record
     * @param bool $asArray
     * 
     * @return array
     */
    function getGroupAccess($record,bool $asArray=FALSE,bool $includeGroupRef=FALSE)
    {
        $arr=[];
        $acc=$this->getModel('AuthAccess')->table;
        $acc=$this->where('ugref',$record)
                    ->join($acc,'acc_ref=ugref','Left')
                    ->first();
        foreach(is_array($acc) ? $acc : [] as $key=>$val)
        {
            if (Str::startsWith($key, 'acc_') && ($val==1 || $val=='1'))
            {
                $arr[]= substr($key, 4);
            }    
        }
        if ($includeGroupRef && is_array($acc) && array_key_exists('ugref', $acc))
        {
            $arr[]=$acc['ugref'];
        }
        return $asArray ? $arr : implode(';',$arr);
    }
    
    /**
     * Returns array with access levels and theirs labels
     * 
     * @return array
     */
    function getAccessLevels()
    {
        $arr=[];
        foreach(\EMPORIKO\Helpers\AccessLevel::Levels as $level)
        {
            $arr[$level]=lang('system.auth.groups_acc_'.$level);
        }
        return $arr;
    }
    
    /**
     * Returns array with groups data (default reference) which have given level value
     * 
     * @param type   $levels
     * @param bool   $levelValue
     * @param string $retValue
     * 
     * @return array
     */
    function getGroupsWithLevel($levels,bool $levelValue=TRUE,string $retValue='ugref')
    {
        $levelValue=$levelValue ? 1 : 0;
        if ($retValue!='*' && !in_array($retValue, $this->allowedFields))
        {
            $retValue='ugref';
        }
        if (!is_string($levels) && !is_array($levels))
        {
            return [];
        }
        $arr=[];
        $acc=$this->getModel('AuthAccess')->table;
        $acc=$this->join($acc,'acc_ref=ugref','Left');
        foreach(is_array($levels) ? $levels : [$levels] as $level)
        {
            $acc->orWhere('acc_'.$level,$levelValue);
        }
        foreach($acc->find() as $rec)
        {
            if ($retValue=='*')
            {
                $arr[]=$rec;
            }else
            if (array_key_exists($retValue, $rec))
            {
                $arr[]=$rec[$retValue];
            }
        }
        
        return $arr;
    }
    
    /**
     * Returns data of super admin group
     * 
     * @param  boolean $allinfo
     * @return array
     */
    function getSuperAdminsGroup($allinfo = FALSE) 
    {
        $acc = '#superadmin';
        return $allinfo ? $this->where('ugref', $acc)->first() : $acc;
    }

    /**
     * Return Array with grups data to populate dropdown in form
     * 
     * @param  string $field	Value field name (saved to db)
     * @param  string $value    Text field name (showed to end user)
     * @param  bool   $addEmpty Determine if empty field will be added
     * @param  string $defValue Default value field name if $value is null or not exists in allowed fields array
     * @return Array
     */
    function getForForm($field = null, $value = null, $addEmpty = FALSE, $defValue = null, array $filters = []) 
    {
        $defValue = $defValue == null ? 'ugname' : $defValue;
        $field = $field == null ? $this->primaryKey : $field;
        $field = in_array($field, $this->allowedFields) ? $field : $this->primaryKey;
        $value = $value == null ? $defValue : $value;
        $value = in_array($value, $this->allowedFields) ? $value : $defValue;
        $this->parseFilters($filters, $this, $this->allowedFields);
        $result = [];
        if ($addEmpty) 
        {
            $result[] = '';
        }

        foreach ($this->find() as $record) 
        {
            $result[$record[$field]] = $record[$value];
        }

        return $result;
    }
    
    /**
     * Returns array with access levels for form
     *  
     * @return array
     */
    function getAccessForForm() 
    {
        $options=[lang('Auth.access_no_level')];
        $options=$options+array_combine(\EMPORIKO\Helpers\AccessLevel::Levels, lang('auth.access_levels'));
        $options=$options+$this->getForForm('ugref');
        return $options;
    }
    
    /**
     * Get emails of linked users to given group
     * 
     * @param Int $groupID
     * 
     * @return array
     */
    function getUserEmailsForGroup(Int $groupID)
    {
        $groupID=$this->find($groupID);
        if (is_array($groupID) && array_key_exists('ugref', $groupID))
        {
            $groupID=$this->getModel('User')->like('accessgroups',$groupID['ugref'])->find();
            $arr=[];
            if (is_array($groupID))
            {
                foreach($groupID as $email)
                {
                    if (array_key_exists('email', $email))
                    {
                       $arr[$email['email']]=$email['email'];  
                    }
                }
                return count($arr) > 0 ? $arr : null;
            }else
            {
                return null;
            }
        }else
        {
            return null;
        }
        return $groupID;
    }
    
    /**
     * Returns array with fields data for edit form
     * 
     * @param  array $record
     * @param  mixed $fields
     * @return array
     */
    function getFieldsForForm($record,$fields=null)
    {
        $arr=[];
        $fields=!is_array($fields) ? $this->allowedFields : $fields;
        $fields=$fields=='basic' ? ['ugname', 'ugdesc'] : $fields;
        
        if (in_array($this->primaryKey, $fields))
        {
            $fields= array_diff($fields,[$this->primaryKey]);
        }
        
        foreach($fields as $field)
        {
            if ($field!='ugref')
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName($field)
                        ->setID('id_'.$field)
                        ->setText($field)
                        ->setTab('general');
                if ($field=='ugdesc')
                {
                    $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::createField($arr[$field]);
                }
                
                if ($field=='enabled')
                {
                    $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::createField($arr[$field]);
                }
            }   
        }
        
        $arr['access_groups_field']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                ->setName('access_groups_field')
                ->setID('access_groups_field')
                ->setText('')
                ->setTab('access');
        
        return $arr;
    }
    
    /**
     * Returns array with group access data
     * 
     * @param string $groupRef
     * 
     * @return array
     */
    function getAccess(string $groupRef)
    {
        if ($groupRef=='@new')
        {
            $groupRef=$this->getModel('Auth/AuthAccess')->getNewRecordData(TRUE);
            $groupRef['acc_ref']='@new';
            return $groupRef;
        }
        return $this->getModel('Auth/AuthAccess')->where('acc_ref',$groupRef)->first();
    }
    
    /**
     * Set group access data
     * 
     * @param array $data
     */
    function setAccess(array $data)
    {
        $this->getModel('Auth/AuthAccess')->setCustomAccess('',$data);
    }
    
    /**
     * Install model table and insert data to storage (db)
     * 
     * @return bool
     */
    public function installstorage() 
    {
        if (!parent::installstorage()) 
        {
            return FALSE;
        }
        return $this->insertBatch(
               [
                    [
                        'ugname' => lang('system.auth.groups_sagname'), 
                        'ugdesc' => lang('system.auth.groups_sagdesc'), 
                        'enabled' => '1',
                        'ugref'=> 'NjEyZGU3ZmNhMzk3Yg', 
                        'ugperms'=> '1', 
                        'ugview'=> '1', 
                        'ugstate'=> '1', 
                        'ugmodify'=> '1', 
                        'ugedit'=> '1', 
                        'ugcreate'=> '1', 
                        'ugdelete'=> '1', 
                        'ugsettings'=> '1'
                     ]
               ]);
    }

}
