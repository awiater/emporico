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
namespace EMPORIKO\Models\System;

use \EMPORIKO\Helpers\Strings as Str;

class SectionModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='sections';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'secid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['sname','sroute','sconfig','enabled','access'];
    
    protected $validationRules =[];
    
    protected $validationMessages = [];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'secid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
	'sname'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
        'sroute'=>              ['type'=>'TEXT','null'=>TRUE],
        'sconfig'=>   		['type'=>'TEXT','null'=>TRUE],
        'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
        'access'=>   		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
    ];
    
    
    function getSections()
    {
        $arr=[];
        $filters=[];
        $filters['enabled']=1;
        $filters['access']='@loged_user';
        foreach ($this->filtered($filters)->find() as $value) 
        {
           $arr[$value['sname']]= json_decode($value['sroute'],TRUE); 
        }
        return $arr;
    }

}