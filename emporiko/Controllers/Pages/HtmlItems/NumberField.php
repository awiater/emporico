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

class NumberField extends HtmlItem
{
    public $_viewname;
    
    
    static function create()
    {
        return new NumberField();
    }
   
    function __construct() 
    {
        parent::__construct();
        $this->addArg('type', 'number');
        $this->addArg('value', '0');
    }
    
    /**
     * Set field value
     * 
     * @param  type $value
     * @return EMPORIKO\Controllers\Pages\HtmlItems\NumberField
     */
    function setValue($value)
    {
        $value=!is_numeric($value) ? '' : $value;
        return parent::setValue(strval($value));
    }
    
    /**
     * Set field max value
     * 
     * @param number $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\NumberField
     */
    function setMax($value)
    {
        $value=!is_numeric($value) ? '' : $value;
        return $this->addArg('max', $value,FALSE);
    }
    
    /**
     * Set field min value
     * 
     * @param number $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\NumberField
     */
    function setMin($value)
    {
        $value=!is_numeric($value) ? '' : $value;
        return $this->addArg('min', $value,FALSE);
    }
    
    /**
     * Set step parameter for field
     * 
     * @param number $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\NumberField
     */
    function setStep($value)
    {
        $value=!is_numeric($value) ? '' : $value;
        return $this->addArg('step', $value,FALSE);
    }
    
    /**
     * Determines if user can type values in field
     * 
     * @param bool EMPORIKO\Controllers\Pages\HtmlItems\NumberField
     */
    function setTypeStrict(bool $value=TRUE)
    {
        if ($value)
        {
            $this->addArg('onkeydown', 'return;');
            if ($this->isArgExists('style'))
            {
                $this->_args['style'].='caret-color: transparent;';
            } else 
            {
               $this->addArg('style', 'caret-color: transparent;'); 
            }
        }else
        {
            if ($this->isArgExists('onkeydown'))
            {
               unset($this->_args['onkeydown']);
            }
            if ($this->isArgExists('style'))
            {
                $this->_args['style']= str_replace('caret-color: transparent;', '', $this->_args['style']);
                if (strlen($this->_args['style']) < 2)
                {
                    unset($this->_args['style']);
                }
            }
        }
        return $this;
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
            if ($key=='type_strict' && is_bool($value))
            {
                $this->setTypeStrict($value);
            }else
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='step')
            {
                $this->setStep($value);
            }else
            if ($key=='max')
            {
                $this->setMax($value);
            }else
            if ($key=='min')
            {
                $this->setMin($value);
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
        if ($this->isReadOnly())
        {
            return form_input($this->getArgs('name'), $this->getArgs('value'), $this->getArgs());
        }
        return form_input($this->getArgs('name'), $this->getArgs('value'), $this->_args,'number');
    }
}