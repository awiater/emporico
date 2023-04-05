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
namespace EMPORIKO\Models\System;

use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Arrays as Arr;

class RedirectModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='redirects';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'rdid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['rd_code','rd_target','rd_ref','rd_info','enabled'];
    
    protected $validationRules =
    [
        
    ];
    
    protected $validationMessages = 
    [
    ];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'rdid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
	'rd_code'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'unique'=>TRUE],
        'rd_target'=>           ['type'=>'TEXT','null'=>TRUE],
        'rd_ref'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'unique'=>TRUE],
        'rd_info'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'unique'=>TRUE],
        'enabled'=>             ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
    ];
    
    /**
     * Add new redirect link
     * 
     * @param string|array $target
     * @param string       $ref
     * @param string       $info
     * @param string       $code
     * @param bool         $createUrl
     * 
     * @return string
     */
    function addLink($target,string $ref,string $info,$code=null,bool $createUrl=FALSE)
    {
        $length=4;
        $code=$code==null ? substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length) : $code;
        $code= strtolower($code);
        if (is_array($target))
        {
            if (count($target) < 2)
            {
                return FALSE;
            }
            $target= array_values($target);
            $aTarget=['controller'=>$target[0],'action'=>$target[1]];
            if (count($target) > 2)
            {
                $aTarget['args']=$target[2];
            }
            $target= json_encode($aTarget);
        }
        if ($this->count(['rd_code'=>$code]) < 1)
        {
            $this->save(['rd_code'=>$code,'rd_target'=>$target,'rd_ref'=>$ref,'rd_info'=>$info]);
        }
        return $createUrl ? url('Pages','redirect.html',[],['link'=>$code]) : $code;
    }
    
    /**
     * Returns redirect link data by code
     * 
     * @param string $code
     * 
     * @return array|null
     */
    function getByCode(string $code)
    {
        return $this->filtered(['rd_code'=>strtolower($code),'enabled'=>1])->first();
    }
    
}
