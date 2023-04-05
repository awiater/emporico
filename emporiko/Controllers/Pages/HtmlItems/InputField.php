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

class InputField extends HtmlItem
{
    public $_viewname;
    
    
    static function create()
    {
        return new InputField();
    }  
    
    /**
     * Set field value
     * 
     * @param  type $value
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputField
     */
    function setValue($value)
    {
        $value=!is_string($value) ? '' : $value;
        return parent::setValue($value);
    }
    
    /**
     * Add input masks to field (mask script must be added to view)
     * 
     * @param  string $mask
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputField
     */
    function setMask($mask)
    {
        if($mask=='$')
        {
            $this->addArg('data-mask-reverse', 'true');
            $this->addArg('data-mask', '00000000.00');
        }else
        {
            $this->addArg('data-mask',$mask);
        }
        return $this;
    }
    
    /**
     * Determines if mask is set on field
     * 
     * @return bool
     */
    function isMaskSet()
    {
        return $this->isArgExists('data-mask');
    }
    
    /**
     * Set input field as password
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputField
     */
    function setAsPassword()
    {
        $this->_args['type']='password';
        return $this;
    }
    
    /**
     * Determines if field is in password type
     * 
     * @return bool
     */
    function isPassword()
    {
        return $this->isArgExists('type','password');
    }
    
    /**
     * Set input field value placeholder
     * 
     * @param string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputField
     */
    function setPlaceholder($value)
    {
        return $this->addArg('placeholder', lang($value));
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return $this
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='mask')
            {
                $this->setMask($value);
            }else
            {
                $this->addArg($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Render to HTML tag
     * 
     * @return string
     */
    function render() 
    {
        $this->getFlatClass(TRUE);
        $args=$this->_args;
        if (!array_key_exists('type', $this->_args))
        {
            $this->_args['type']='text';
        }
        $value=$this->getArgs('value');
        return form_input($this->getArgs('name'), $value==null ? '' : $value, $args,$this->_args['type']);
    }
}