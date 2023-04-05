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

class CustomElementsListField extends HtmlItem
{
    public $_viewname='System/Elements/custom_elements_list';
    
    private $_inputtype='InputField';
    
    
    static function create()
    {
        return new CustomElementsListField();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->setItemsColor('secondary');
        $this->setItemsTooltip(FALSE);
    }
    
    /**
     * Set item value
     * 
     * @param  string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField
     */
    public function setValue($value) 
    {
        if (is_string($value) && Str::isJson($value))
        {
            $value= json_decode($value,TRUE);
        }
        $value= is_array($value) ? $value : (Str::contains($value, ',') ? explode(',', $value) : (strlen($value) > 0 ? [$value] : []));
        $this->_args['value']=$value;
        return $this;
    }
    
    /**
     * Set input field
     * 
     * @param mixed $field
     * @param bool  $advancedlist
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField
     */
    public function setInputField($field,bool $advancedlist=FALSE)
    {
        if (is_a($field, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
        {
            $this->_inputtype=$field;
            return $this;
        }
        if (is_string($field) && $field=='@email_field')
        {
            $this->_inputtype= EmailField::create()
                    ->setName($this->_args['name'].'_input')
                    ->setID($this->_args['id'].'_input')
                    ->setValue('')
                    ->addArg('placeholder', 'email@email.com');
        }else
        if (is_array($field))
        {
           $this->_inputtype= DropDownField::create()
                   ->setName($this->_args['name'].'_input')
                   ->setID($this->_args['name'].'_input')
                   ->setOptions($field);
           if ($advancedlist)
           {
               $this->_inputtype->setAsAdvanced();
           }
        }else
        {
            $this->_inputtype= InputField::create()
                    ->setName($this->_args['name'].'_input')
                    ->setID($this->_args['id'].'_input')
                    ->setValue('');
        }
        return $this;
    }
    
    /**
     * Set item (badge) colour
     * 
     * @param string $color
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField
     */
    function setItemsColor(string $color)
    {
        if (in_array(strtolower($color), ['primary','secondary','success','danger','warning','info','light','dark']))
        {
            $this->_args['data-badge']=$color;
        }
        return $this;
    }
    
    /**
     * Determines if items (badges) tool tips are enabled
     * 
     * @param bool $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField
     */
    function setItemsTooltip(bool $value=TRUE)
    {
        return $this->addArg('data-badgetip', $value);
    }
    
    /**
     * Determines what function will be triggered when user click on add button
     * @param string $func
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField
     */
    function setAddBtnAction(string $func)
    {
        return $this->addArg('btnonclick', $func);
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='addbtn_action' && is_string($value))
            {
                $this->setAddBtnAction($value);
            }else
            if ($key=='item_tooltip')
            {
                $this->setItemsTooltip($value);
            }else
            if ($key=='item_color')
            {
                $this->setItemsColor($value);
            }else
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='input_type' || $key=='input_field')
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
            $this->_inputtype->setClass($this->getArgs('class'));
        }
        
        if ($this->isArgExists('advanced_list'))
        {
            $this->_inputtype->setAsAdvanced();
            unset($this->_args['advanced_list']);
        }
        if (!$this->isArgExists('btnonclick'))
        {
            $this->addArg('btnonclick',$this->_args['id'].'_listadd()');
        }
        return view($this->_viewname,['input_field'=>$this->_inputtype,'name'=>$this->_args['name'],'items'=>$this->_args['value'],'args'=>$this->_args]);
    }
}