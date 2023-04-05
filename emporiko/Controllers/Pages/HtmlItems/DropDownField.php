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

use EMPORIKO\Helpers\Arrays as Arr;

class DropDownField extends HtmlItem
{
    public $_viewname='';
    
    public $_advanced=FALSE;
    
    
    static function create()
    {
        return new DropDownField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->setOptions([],TRUE);
    }
    
    /**
     * Set list elements array
     * 
     * @param array $options
     * @param bool  $addBlank
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownField
     */
    function setOptions(array $options,$addBlank=FALSE)
    {
        if ($addBlank!=FALSE)
        {
            if (!is_array($addBlank))
            {
                $addBlank=[''=>$addBlank];
            }
            $keys= array_keys($options);
            if (count($keys) > 0)
            {
               Arr::InsertBefore($options, $keys[0], $addBlank); 
            }
            
        }
        return $this->addArg('options', $options);
    }
    
    /**
     * Set item mode as read only
     * 
     * @param bool $value
     * @param bool $whiteBackground
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownField
     */
    function setReadOnly(bool $value = true, bool $whiteBackground = false) 
    {
        if ($whiteBackground && $value)
        {
            $this->addArg('style','background-color:#FFF!important');
        }
        return $value ? $this->addArg('disabled', $value,FALSE) : $this;
    }
    
    /**
     * Determine if item is in read only mode
     * 
     * @return bool
     */
    function isReadOnly(): bool {
        return $this->isArgExists('disabled') && $this->getArgs('disabled');
    }
    
    /**
     * Set drop down list as select2 list (Select2 library must be added to view)
     * 
     * @param string $mode
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownField
     */
    function setAsAdvanced(string $mode='select2')
    {
        $this->addClass($mode);
        $this->_advanced=TRUE;
        return $this;
    }
    
    /**
     * Determines if drop down using select2 library
     * 
     * @return bool
     */
    function isAdvanced()
    {
        return $this->_advanced;
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
            if (($key=='selectwithicons' || $key=='advanced') && $value)
            {
                $this->setAsAdvanced();
            }else
            if ($key=='options' && is_string($value) && \EMPORIKO\Helpers\Strings::contains($value, '::'))
            {
               $this->setOptions(loadModuleFromString($value)); 
            }else
            if ($key=='options' && is_array($value) && count($value) > 0)
            {
                $this->setOptions($value);
            }else
            if ($key=='readonly')
            {
                $this->setReadOnly($value);
            }else
            {
                $this->addArg($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Render item to HTML tag
     * 
     * @return string
     */
    public function render()
    {
        $this->getFlatClass(TRUE);
        $args=$this->_args;
        unset($args['options']);
        $value=$this->getArgs('value');
        $value=$value==null ? '' : $value;
        if ($this->isReadOnly())
        {
            return form_dropdown($args['name'].'_list', $this->_args['options'],[$value], $args).form_hidden($this->getArgs('name'),$value);
        }else
        {
            return form_dropdown($args['name'], $this->_args['options'],[$value], $args);
        }
    }
    
}