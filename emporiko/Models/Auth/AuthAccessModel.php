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

class AuthAccessModel extends \EMPORIKO\Models\BaseModel {

    /**
     * Users table name
     * 
     * @var string
     */
    protected $table = 'auth_perms';

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
    protected $allowedFields = ['acc_ref','acc_view','acc_modify','acc_edit','acc_create','acc_delete','acc_settings'];
    

    /**
     * Fields types declarations for forge
     * @var array
     */
    protected $fieldsTypes = 
    [
        'accid' =>          ['type' => 'INT', 'constraint' => '36', 'auto_increment' => TRUE, 'null' => FALSE],
        'acc_ref' =>        ['type' => 'VARCHAR', 'constraint' => '255', 'null' => FALSE, 'unique' => TRUE],
        'acc_view' =>       ['type' => 'TINYINT', 'constraint' => '1', 'null' => FALSE, 'default' => 1],
        'acc_modify' =>     ['type' => 'TINYINT', 'constraint' => '1', 'null' => FALSE, 'default' => 0],
        'acc_edit' =>       ['type' => 'TINYINT', 'constraint' => '1', 'null' => FALSE, 'default' => 0],
        'acc_create' =>     ['type' => 'TINYINT', 'constraint' => '1', 'null' => FALSE, 'default' => 0],
        'acc_delete' =>     ['type' => 'TINYINT', 'constraint' => '1', 'null' => FALSE, 'default' => 0],
        'acc_settings' =>   ['type' => 'TINYINT', 'constraint' => '1', 'null' => FALSE, 'default' => 0],
    ];
    
    function getCustomAccess(string $ref)
    {
        $select=[];
        foreach($this->allowedFields as $field)
        {
            if ($field=='acc_ref')
            {
                $select[]="(REPLACE(`acc_ref`,'.$ref','')) as $field"; 
            }else{
               $select[]=$field; 
            }
            
        }
        return $this->select(implode(',',$select))
                     ->like('acc_ref','%.'.$ref)->find();
    }
    
    function setCustomAccess(string $ref,array $data)
    {
        $levels=\EMPORIKO\Helpers\AccessLevel::Levels;
        if ($ref!=null && is_string($ref) && strlen($ref) > 0)
        {
            $ref=".$ref";
        } else {
            $ref=null;
        }
        foreach($data as $key=>$value)
        {
            $arr=['acc_ref'=>$key.$ref];
            foreach($levels as $level)
            {
                if (array_key_exists($level, $value))
                {
                    $arr['acc_'.$level]=$value[$level];
                }else
                {
                    $arr['acc_'.$level]=0;
                }
            }
            $this->builder()->set($arr)->where('acc_ref',$arr['acc_ref'])->update();
            if ($this->db()->affectedRows() < 1 && $this->count(['acc_ref'=>$arr['acc_ref']]) < 1)
            {
                $this->builder()->set($arr)->insert();
            }
        }
    }
    
    
}
