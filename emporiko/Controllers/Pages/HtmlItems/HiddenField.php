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

  
namespace EMPORIKO\Controllers\Pages\HtmlItems;

use EMPORIKO\Helpers\Strings as Str;

class HiddenField extends HtmlItem
{
    public $_viewname;
    
    
    static function create()
    {
        return new HiddenField();
    }
   
    function __construct() 
    {
        parent::__construct();
        $this->addArg('type', 'hidden');
    }
    
    /**
     * Set field value
     * 
     * @param  type $value
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HiddenField
     */
    function setValue($value)
    {
        $value=!is_string($value) ? '' : $value;
        return parent::setValue($value);
    }
    
    /**
     * Render to HTML tag
     * 
     * @return string
     */
    function render() 
    {
        unset($this->_args['class']);
        $value=$this->getArgs('value');
        return form_input($this->getArgs('name'), $value==null ? '':$value, $this->_args,'hidden');
    }
}