<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

  
namespace EMPORIKO\Helpers;

class MenuBarItemType
{
    const TextField= 'InputField';
     
    const DropDown='DropDown';
     
    const Button='b';
     
    const Link='a';
     
    const Label='label';
    
    private $_field;

    static function TextField() {return new MenuBarItemType(MenuBarItemType::TextField);}
     
    static function DropDown() {return new MenuBarItemType(MenuBarItemType::DropDown);}
    
    static function Button() {return new MenuBarItemType(MenuBarItemType::Button);}
    
    static function Link() {return new MenuBarItemType(MenuBarItemType::Link);}
    
    static function Label() {return new MenuBarItemType(MenuBarItemType::Label);}
    
    static function _instance($field)
    {
        return new MenuBarItemType($field);
    }
    
    function __construct($field) {$this->_field=$field;}
    
    function get() {return $this->_field;}
    
    function is($string){return $this->_field==$string;}
}