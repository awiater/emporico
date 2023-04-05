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

namespace EMPORIKO\Libraries\Auth\Entities;


abstract class UserEntity extends \CodeIgniter\Entity
{
    
    /**
     * Custom convert handlers
     *
     * @var array<string, string>
     * @phpstan-var array<string, class-string>
     */
    protected $castHandlers = [
        'date_str' => \EMPORIKO\Helpers\Cast\StringDateCast::class,
        'int_bool' => \EMPORIKO\Helpers\Cast\IntBoolCast::class,
    ];
    
    protected $casts = [
        'lastlogin'=> 'date_str',
        'autologoff'=>'int_bool',
        'enabled'=>'int_bool',
    ];
    
    public function lastLogin()
    {
        return $this->lastlogin;
    }
    
    function isActive()
    {
        return $this->enabled;
    }

}