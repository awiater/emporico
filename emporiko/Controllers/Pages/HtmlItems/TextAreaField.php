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

class TextAreaField extends HtmlItem
{
    public $_viewname;
    
    
    static function create()
    {
        return new TextAreaField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->setRows(10)->setCols(40);
    }
    
    /**
     * Sets qty of field rows
     * 
     * @param int $value
     * 
     * @return \EMPORIKO\Controllers\Pages\TextAreaField
     */
    function setRows(int $value)
    {
        $value=$value < 1 ? 1 :$value;
        return $this->addArg('rows', $value);
    }
    
    /**
     * Sets qty of field columns
     * 
     * @param int $value
     * 
     * @return \EMPORIKO\Controllers\Pages\TextAreaField
     */
    function setCols(int $value)
    {
        $value=$value < 1 ? 1 :$value;
        return $this->addArg('cols', $value);
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
            $this->_args['readonly']='TRUE';
            return form_textarea($this->_args);
        }
        return form_textarea($this->_args);
    }
}