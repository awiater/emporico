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

class EmailField extends HtmlItem
{
    public $_viewname;
    
    
    static function create()
    {
        return new EmailField();
    }
   
    function __construct() 
    {
        parent::__construct();
        $this->addArg('type', 'email');
    }
    
    /**
     * Set field value
     * 
     * @param  type $value
     * @return EMPORIKO\Controllers\Pages\HtmlItems\EmailField
     */
    function setValue($value)
    {
        $value=!is_string($value) ? '' : $value;
        if (Str::isValidEmail($value))
        {
            return parent::setValue($value);
        }
        return parent::setValue('');
    }
    
    /**
     * Render to HTML tag
     * 
     * @return string
     */
    function render() 
    {
        $this->getFlatClass(TRUE);
        if ($this->getArgs('value')==null)
        {
            $this->addArg('value','');
        }
        if ($this->isReadOnly())
        {
            return form_input($this->getArgs('name'), $this->getArgs('value'), $this->getArgs());
        }
        return form_input($this->getArgs('name'), $this->getArgs('value'), $this->_args,'email');
    }
}