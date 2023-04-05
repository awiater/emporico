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

namespace EMPORIKO\Libraries\CookiesManager;

use Config\Services;

class Manager
{
    /**
     * Array with settings
     * 
     * @var array
     */
    private $_settings=[];
    
    /**
     * Array with allowed scopes
     * @var array
     */
    private $_allowedScopes=[];
    
    function __construct() 
    {
        $this->_settings=json_decode(json_encode(Config('Cookies')));
        $this->_allowedScopes=$this->getAllowedScopes();
    }
    
    /**
     * Returns array with allowed scopes (cookies)
     * 
     * @return array
     */
    function getAllowedScopes()
    {
        $scopes=$this->_settings->scopes;
        $arr=[];
        if ($this->isCookie())
        {
            $scopes_cookie= $this->_getCookie();
            if (is_string($scopes_cookie))
            {
                $scopes_cookie= json_decode($scopes_cookie,TRUE);
                if (is_array($scopes_cookie))
                {
                    $scopes=$scopes_cookie;
                }
            }
        }
        foreach($scopes as $scope)
        {
            if (is_string($scope) && property_exists($this->_settings->scopes,$scope))
            {
                $arr[]=$scope;
            }else
            if (is_object($scope) && property_exists ($scope, 'required') && $scope->required)
            {
                $arr[]=$scope->name;
            }
        }
        return $arr;
    }
    
    /**
     * Determines if given scope is allowed
     * 
     * @param string $scope
     * 
     * @return bool
     */
    function isAllowed(string $scope)
    {
        return in_array($scope, $this->getAllowedScopes());
    }
    
    /**
     * Determines if cookie is set (by default GDPR cookie)
     * 
     * @param string $name
     * 
     * @return bool
     */
    function isCookie(string $name=null)
    {
        $name=$name==null ? $this->_settings->cookieName : $name;
        return isset($_COOKIE[$name]);
    }
    
    
    /**
     * Returns default GDPR cookie value
     * 
     * @return string
     */
    private function _getCookie()
    {
        return $_COOKIE[$this->_settings->cookieName];
    }
    
    
}