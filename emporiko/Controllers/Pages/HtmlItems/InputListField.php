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

class InputListField extends HtmlItem
{
    public $_viewname;
    
    private $_options=[];
    
    private $_validate=FALSE;


    static function create()
    {
        return new InputListField();
    }  
    
    function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * Set field value
     * 
     * @param  type $value
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputListField
     */
    function setValue($value)
    {
        $value=!is_string($value) ? '' : $value;
        return parent::setValue($value);
    }
    
    
    /**
     * Set list elements array
     * 
     * @param array $options
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputListField
     */
    function setOptions(array $options)
    {
        $this->_options=$options;
        return $this;
    }
    
    /**
     * Determines if input field is validated against options list
     * 
     * @param bool $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputListField
     */
    function setValidation(bool $value=TRUE)
    {
        $this->_validate=$value;
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
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='options' && is_array($value) && count($value) > 0)
            {
                $this->setOptions($value);
            }else
            if ($key=='validation')
            {
                $this->setValidation($value);
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
        $this->_args['list']=$this->_args['name'].'_list';
        $field=form_input($this->_args);
	$field.='<datalist id="'.$this->_args['name'].'_list">';
        foreach($this->_options as $option)
        {
            $field.='<option value="'.$option.'"><i class="'.$option.'"></i></option>';
        }
        $field.='</datalist>';
        if ($this->_validate)
        {
            $field.='<script>';		
            $field.='$("#'.$this->_args['id'].'").on("change",function(){';
            $field.='var loaction=$("#'.$this->_args['list'].'").find("option[value='."'".'" + $(this).val() + "'."'".']");';
            $field.='$(this).removeClass("border-danger");';
            $field.='if($("#'.$this->_args['list'].'").html().length>0 && $(this).val().length>0)';
            $field.='{if(loaction != null && loaction.length > 0){}else{';
            $field.='$(this).addClass("border-danger");$(this).val("");}}});';
            $field.='</script>';
        }
        return $field;
    }
}