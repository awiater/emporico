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

class LabelField extends HtmlItem
{
    public $_viewname;

    static function create()
    {
        return new LabelField();
    }  
    
    function __construct() 
    {
        $this->_args['labelText']='Label';
        $this->_args['for']='';
        $this->addClass('label');
    }
    
    /**
     * Set field text
     * 
     * @param string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\LabelField
     */
    function setValue($value) 
    {
        $this->_args['labelText']=lang($value);
        return $this;
    }
    
    /**
     * Set item parameters
     * 
     * @param array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\LabelField
     */
    function setArgs(array $args) 
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='value')
            {
                $this->setValue($value);
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
        unset($args['for']);
        unset($args['labelText']);
        return form_label($this->_args['labelText'],$this->getArgs('for'),$args);
    }
}