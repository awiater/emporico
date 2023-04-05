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

class CustomTextField extends HtmlItem
{
    public $_viewname;
    
    public $_value;
    
    static function create()
    {
        return new CustomTextField();
    }  
    
    function __construct() 
    {
        $this->setClass('border p-2');
    }
    
    /**
     * Set item value
     * 
     * @param mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField
     */
    function setValue($value)
    {
        if (is_string($value))
        {
            $this->_value=$value;
        }else
        {
            $this->_value='';   
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
        if (!is_string($this->_value) && is_a($this->_value, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
        {
            if ($this->isReadOnly())
            {
                $this->_value->setReadOnly();
            }
            $this->_value=$this->_value->render();
        }
        
        $html='<div';
        foreach ($this->_args as $key => $value) 
        {
            if ($key!='value' && is_string($value))
            {
                if (!in_array($key, ['id','class']))
                {
                        $key='data-'.$key;
                }
                $html.=' '.$key.'="'.$value.'"';
            }
        }
        $html.='>'.($this->_value).'</div>';
        
        return $html;
    }
}