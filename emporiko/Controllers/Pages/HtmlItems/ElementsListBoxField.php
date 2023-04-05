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

class ElementsListBoxField extends HtmlItem
{
    public $_viewname;
    
    public $_value;
    
    private $_inputtype='InputField';
    
    private $_addnewitemact=null;
    
    static function create()
    {
        return new ElementsListBoxField();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_args['list_height']='350px';
        $this->setEnableDeletionOfItems();
    }
    
    /**
     * Set item value
     * 
     * @param mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField
     */
    function setValue($value)
    {
        if (is_string($value) && Str::isJson($value))
        {
            $value= json_decode($value,TRUE);
        }else
        if ($value==null || (is_string($value) && strlen($value) < 1))
        {
            $this->_value=[];
            return $this;
        }
        $value= is_array($value) ? $value : [$value];
        $this->_value=$value;
        return $this;
    }
    
    /**
     * Set values list height 
     * 
     * @param Int $size
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField
     */
    function setListHeight($size)
    {
        $size= is_numeric($size) ? $size.'px' : $size;
        return $this->addArg('list_height', $size);
    }
    
    /**
     * Enable items deletions from list
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField
     */
    function setEnableDeletionOfItems()
    {
        return $this->addArg('item_delete', true);
    }
    
     /**
     * Set input field
     * 
     * @param mixed $field
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField
     */
    public function setInputField($field)
    {
        if (is_a($field, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
        {
            $this->_inputtype=$field;
            return $this;
        }
        if (is_array($field))
        {
           $this->_inputtype= DropDownField::create()
                   ->setName($this->_args['name'].'_input')
                   ->setID($this->_args['name'].'_input')
                   ->setOptions($field);
           $this->addArg('input_options', $field);
        }else
        {
            $this->_inputtype= InputField::create()
                    ->setName($this->_args['name'].'_input')
                    ->setID($this->_args['name'].'_input')
                    ->setValue('');
        }
        return $this;
    }
    
    /**
     * Set new item function name
     * 
     * @param string $name
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField
     */
    function setNewItemFunction(string $name)
    {
        $this->_addnewitemact=$name;
        return $this;
    }
    
    /**
     * Set message showed to end user if same item is added to list
     * 
     * @param string $msg
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField
     */
    function setItemExistsErrorMsg(string $msg)
    {
        return $this->addArg('_list_item_exists_error', lang($msg));
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
            $key= strtolower($key);
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if (strtolower($key)=='newitemfunction')
            {
                $this->setNewItemFunction($value);
            }else
            if ($key=='listheight')
            {
                $this->setListHeight($value);
            }else
            if ($key=='item_delete' && $value)
            {
                $this->setEnableDeletionOfItems($value);
            }else
            if ($key=='input_type' || $key='input_field')
            {
                $this->setInputField($value);
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
        
        if ($this->_inputtype=='InputField')
        {
            $this->setInputField('InputField');
        }
        if (is_a($this->_inputtype, '\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField'))
        {
            $this->addArg('input_options',$this->_inputtype->getArgs('options'));
        }
        
        if (!$this->isArgExists('_list_item_exists_error'))
        {
            $this->_args['_list_item_exists_error']=lang('system.errors.list_item_exists');
        }
        
        if ($this->isReadOnly())
        {
           $this->_inputtype->setReadOnly();
           $this->addArg('item_delete', false);
        }
        return view('System/Elements/elements_listbox',['addnewfunc'=>$this->_addnewitemact,'input_field'=>$this->_inputtype,'name'=>$this->getArgs('name'),'items'=>$this->_value,'args'=>$this->getArgs()]);
    }
}