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

class SignaturePad extends HtmlItem
{
    public $_viewname='System/Elements/sign_pad';
    
    
    static function create()
    {
        return new SignaturePad();
    }
   
    function __construct() 
    {
        $this->setClass('border d-flex');
    }
    
    /**
     * Set field value
     * 
     * @param  type $value
     * @return EMPORIKO\Controllers\Pages\HtmlItems\SignaturePad
     */
    function setValue($value)
    {
        if ($value!=null && file_exists(parsePath($value,TRUE)))
        {
            $value= file_get_contents(parsePath($value,TRUE));
        }
        if (!is_string($value))
        {
            return $this;
        }
        return parent::setValue($value);
    }
    
    /**
     * Set signature pad background colour
     * 
     * @param  string $color
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\SignaturePad
     */
    function setPadColor($color)
    {
        return $this->addArg('pad_color', $color,FALSE);
    }
    
    /**
     * Enable on change event
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\SignaturePad
     */
    function enableChangeEvent()
    {
        return $this->addArg('changeEvent', TRUE,FALSE);
    }
    
    /**
     * Set clear button class
     * 
     * @param  string|array $data
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\SignaturePad
     */
    function setButton($data)
    {
        $data= is_array($data) ? implode(' ',$data) : $data;
        return $this->addArg('button', $data,FALSE);
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\SignaturePad
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='pad_color')
            {
                $this->setPadColor($value);
            }else
            if ($key=='changeEvent')
            {
                $this->enableChangeEvent();
            }else
            if ($key=='button')
            {
                $this->setButton($value);
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
            //return form_input($this->getArgs('name'), $this->getArgs('value'), $this->getArgs());
        }
        return view($this->_viewname,$this->_args);
    }
}