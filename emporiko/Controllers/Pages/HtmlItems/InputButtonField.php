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

class InputButtonField extends InputField
{
    public $_viewname;
    
    private $_icon='...';
    
    private $_button=[];
    
    private $_mode='after';
    
    private $_field=null;
    
    static function createCurrButton($icon='£')
    {
        return (new InputButtonField())
                ->setAsCurrFormat($icon)
                ->addArg('dir', 'rtl');
                
    }
    
    static function createPhoneButton($format='£')
    {
        return (new InputButtonField())->setAsPhoneFormat($format);              
    }
    
    static function createDropDownFieldButton($name,array $options=[],string $icon='..',array $buttonsArgs=[],array $fieldArgs=[])
    {
        $btn=new InputButtonField();
        $btn->setButtonAfter();
        $btn->setName($name);
        $btn->setInputField(DropDownField::create()
                    ->setArgs($fieldArgs)
                    ->setName($name)
                    ->setOptions($options));
        $btn->setID($name);
        $btn->setButtonArgs($buttonsArgs);
        $btn->setButtonIcon($icon);
        return $btn;
    }
    
    static function create()
    {
        return new InputButtonField();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_button['class']='btn btn-primary';
        $this->_button['args']=[];
    }
    
    /**
     * Set current field as currency field
     * 
     * @param string $icon
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setAsCurrFormat($icon)
    {
        return $this->setAsCustomMaskedField('$',$icon);
    }
    
    /**
     * Set current field as telephone number field
     * 
     * @param string $format
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setAsPhoneFormat($format='00000 000000')
    {
        return $this->setAsCustomMaskedField($format,'fas fa-phone-alt');
    }
    
    /**
     * Set current field as custom mask field with icon before input
     * 
     * @param string $format
     * @param string $icon
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setAsCustomMaskedField($format,$icon)
    {
        return $this->setButtonbefore()
                    ->setButtonIcon($icon,FALSE)
                    ->setMask($format)
                    ->setButtonClass('input-group-text font-weight-bold border-right-0')
                    ->setButtonArgs(['style'=>'cursor:default']);
    }
    
    /**
     * Set button icon / text
     * 
     * @param string $icon
     * @param bool   $keepFromArgs
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setButtonIcon($icon,$keepFromArgs=FALSE)
    {
        if (Str::startsWith($icon, 'fa'))
        {
            $icon= html_fontawesome($icon);
        }
        if ($keepFromArgs && $this->_icon!=null)
        {
            return $this;
        }
        $this->_icon=$icon;
        return $this;
    }
    
    /**
     * Set button class
     * 
     * @param  string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setButtonClass($class)
    {
        $class= is_array($class) ? implode(' ',$class) : $class;
        $this->_button['class']=$class;
        return $this;
    }
    
    /**
     * Set button onClick action
     * 
     * @param mixed $action
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setButtonAction($action)
    {
        if (is_string($action))
        {
            if (Str::startsWith(strtolower($action), 'http'))
            {
                $this->_button['args']['data-url']=$action;
            } else 
            {
                $this->_button['args']['onclick']=$action;
            }
        }
        return $this; 
    }
    
    /**
     * Set button custom parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setButtonArgs(array $args)
    {
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
     * Set button after input
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setButtonAfter()
    {
        $this->_mode='after';
        return $this;
    }
    
    /**
     * Set button before input
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setButtonbefore()
    {
        $this->_mode='before';
        return $this;
    }
    
    /**
     * Set input field
     * @param  mixed $field
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
     */
    function setInputField($field)
    {
        if (is_a($field, '\EMPORIKO\Controllers\Pages\HtmlItems\InputField') || is_a($field, '\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField'))
        {
            $this->_field=$field->render();
        }else
        {
            $this->_field=$field;
        }
        return $this;
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField
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
            if ($key=='button_icon')
            {
                $this->setButtonIcon($value);
            }else
            if ($key=='input_field')
            {
                $this->setInputField($value); 
            }else
            if ($key=='button_class')
            {
                $this->setButtonClass($value);
            }else
            if ($key=='button_args' && is_array($value))
            {
                $this->setButtonArgs($value);
            }else
            if ($key=='button_pos')
            {
                if ($value=='before')
                {
                    $this->setButtonbefore();
                } else 
                {
                    $this->setButtonAfter();
                }
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
        if (!$this->isArgExists('id'))
        {
            $this->_args['id']= str_replace(['-',' ','$','!'],'_', $this->_args['name']);
        }
        $this->_button['args']['id']=$this->_args['id'].'_button';
        if (!array_key_exists('class',  $this->_button['args']))
        {
            $this->_button['args']['class']=$this->_button['class'];
        }
        $this->_button['args']['content']=$this->_icon;
        $this->_button['args']['type']='button';
        
        if (is_array($this->_field))
        {
            $this->_field= form_dropdown($this->_args['name'], $this->_field, [], ['class'=>'form-control','id'=>$this->_args['id']]);
        }else
        if ($this->_field==null)
        {
            $this->_field=form_input($this->_args);
        }
        if ($this->_mode=='before')
        {
            $field='<div class="input-group mb-3">';
            $field.='<div class="input-group-append">';
            $field.=form_button($this->_button['args']);
            $field.='</div>';
            $field.=$this->_field.'</div>';
            
        }else
        {
            $field='<div class="input-group mb-3">';
            $field.=$this->_field;
            $field.='<div class="input-group-append">';
            $field.=form_button($this->_button['args']);
            $field.='</div></div>';
        }
        return $field;
    }
}