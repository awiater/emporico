<?php
/*
 *  This file is part of EMPORIKO CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Models\Auth;

use EMPORIKO\Helpers\Strings as Str;

class UserObject extends \EMPORIKO\Libraries\Auth\Entities\UserEntity
{
    public function isActive()
    {
        return $this->enabled==1 || $this->enabled=='1';
    }
    
    function toPublicFieldsArray()
    {
        $arr=[];
        $restricted_fields=['password','apitoken'];
        foreach($this->toArray() as $key=>$field)
        {
            if (!in_array($key, $restricted_fields))
            {
                $arr[$key]=$field;
            }    
        }
        return $arr;
    }
    
    public function isCustomer()
    {
        return $this->iscustomer==1 || $this->iscustomer=='1';
    }
    
    public function getAccess(bool $asArray=FALSE)
    {
        return model('Auth/UserGroupModel')->getGroupAccess($this->accessgroups,$asArray,TRUE);
    }
    
    public function hasAccessLevel(string $level)
    {
        $level=Str::startsWith($level, 'acc_') ? $level : 'acc_'.$level;
        return in_array($level, $this->getAccess(TRUE));
    }
    
    public function getAvatar()
    {
        if (!property_exists($this, 'avatar'))
        {
            return 'data:image/jpeg;base64,'.createDefaultAvatar($this->name);
        }
        return Str::resourceToBase64($this->avatar);
    }
}

   