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

class InputLockedField extends InputField
{
    public $_viewname;
    
    private $_icon='...';
    
    private $_button=[];
    
    private $_field=null;
    
    static function create()
    {
        return new InputLockedField();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_button['class']='btn btn-outline-danger';
    }
    
    static function createField($field, $args = null) 
    {
        $field=parent::createField($field, $args);
        
        if ($field->isArgExists('options'))
        {
            $field->setInputField($field->getArgs('options'));
        }
        return $field;
    }
    
    /**
     * Set button class
     * 
     * @param  string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputLockedField
     */
    function setButtonClass($class)
    {
        $class= is_array($class) ? implode(' ',$class) : $class;
        $this->_button['class']=$class;
        return $this;
    }
    
    /**
     * Set button custom parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputLockedField
     */
    function setButtonArgs(array $args)
    {
        $this->_button['args']=[];
        foreach($args as $key=>$value)
        {
            
            if ($key=='tooltip')
            {
                if (is_array($value) && Arr::KeysExists(['placement','title'], $value))
                {
                    $this->_button['args']['data-placement']=$value['placement'];
                    $this->_button['args']['title']=lang($value['title']);
                    $this->_button['args']['data-toggle']='tooltip';
                }else
                if (is_string($value))
                {
                    $this->_button['args']['data-placement']='top';
                    $this->_button['args']['title']=lang($value);
                    $this->_button['args']['data-toggle']='tooltip';
                } 
            }else
            {
               $this->_button['args'][$key]=$value; 
            }
        }
        return $this;
    }
    
    
    /**
     * Set input field
     * @param  mixed $field
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputLockedField
     */
    function setInputField($field)
    {
        if (is_a($field, '\EMPORIKO\Controllers\Pages\HtmlItems\InputField') || is_a($field, '\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField'))
        {
            $this->_field=$field->render();
        }else
        if (is_array($field))
        {
            $this->_field= DropDownField::create()
                    ->setName($this->getArgs('name'))
                    ->setID($this->getArgs('id'))
                    ->setOptions($field)
                    ->setAsAdvanced();
        }else
        {
            $this->_field= is_string($field) ? $field : null;
        }
        return $this;
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputLockedField
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
            if ($key=='button_class')
            {
                $this->setButtonClass($value);
            }else
            if ($key=='button_args' && is_array($value))
            {
                $this->setButtonArgs($value);
            }else
            if ($key=='input_field')
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
        $this->_button['args']['name']=$this->_args['name'].'_button';
        $this->_button['args']['id']=$this->_args['id'].'_button';
        if (!array_key_exists('class',  $this->_button['args']))
        {
            $this->_button['args']['class']=$this->_button['class'];
        }
        if ($this->isReadOnly())
        {
            $this->_icon='<i class="fas fa-lock"></i>';
            $this->_button['args']['onclick']=$this->_args['id']."_check()";
        } else 
        {
            $this->_icon='<i class="fas fa-lock-open"></i>';
            $this->_button['args']['onclick']=$this->_args['id']."_check()";
        }
        
        if ($this->_field==null)
        {
            $this->_field=form_input($this->_args);
        }
        
        if (!is_string($this->_field))
        {
            if ($this->isReadOnly())
            {
                $this->_field->setReadOnly();
            }
            
            $this->_field=$this->_field->render();
        }
        
        $this->_button['args']['content']=$this->_icon;
        $this->_button['args']['type']='button';
        
        return view('System/Elements/inputlocked_field',['field'=>$this->_field,'args'=>$this->_args,'button_args'=>$this->_button['args']]);
    }
}