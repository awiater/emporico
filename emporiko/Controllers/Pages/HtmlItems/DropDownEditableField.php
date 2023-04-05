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

class DropDownEditableField extends DropDownField
{
    public $_viewname;
    
    private $_mode='after';
    
    static function create()
    {
        return new DropDownEditableField();
    }  
    
    function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * Set item edit button
     * 
     * @param type   $url
     * @param string $tooltip
     * @param string $icon
     * @param string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField
     */
    function setEditButton($url,string $tooltip='system.buttons.edit',string $icon='fas fa-edit',string $class='btn btn-primary')
    {
        if (is_array($url))
        {
            $url= url_from_array($url);
        }
        if (!is_string($url))
        {
            return $this;
        }
        $this->addArg('_buttonsEdit',
        [
            'class'=>$class,
            'content'=>Str::startsWith($icon, 'fa') ? '<i class="'.$icon.'"></i>' : $icon,
            'data-placement'=>'top',
            'title'=>lang($tooltip),
            'data-toggle'=>'tooltip',
            'onclick'=>"window.location='".$url."'.replace('-id-',$('#".$this->_args['id']."_list').val());"
        ]);
        return $this;
    }
    
    /**
     * Set new item button
     * 
     * @param type   $url
     * @param string $tooltip
     * @param string $icon
     * @param string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField
     */
    function setNewButton($url,string $tooltip='system.buttons.new',string $icon='fas fa-plus',string $class='btn btn-dark')
    {
        if (is_array($url))
        {
            $url= url_from_array($url);
        }
        if (!is_string($url))
        {
            return $this;
        }
        $this->addArg('_buttonsNew',
        [
            'class'=>$class,
            'content'=>Str::startsWith($icon, 'fa') ? '<i class="'.$icon.'"></i>' : $icon,
            'data-placement'=>'top',
            'title'=>lang($tooltip),
            'data-toggle'=>'tooltip',
            'data-url'=>$url
        ]);
        return $this;
    }
    
    /**
     * Set button after input
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField
     */
    function setButtonsAfter()
    {
        $this->_mode='after';
        return $this;
    }
    
    /**
     * Set button before input
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField
     */
    function setButtonsbefore()
    {
        $this->_mode='before';
        return $this;
    }
   
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField
     */
    function setArgs(array $args)
    {
        parent::setArgs($args);
        foreach ($args as $key => $value) 
        {
            if ($key=='url')
            {
                $this->setNewButton(str_replace('-id-', 'new', $value));
                $this->setEditButton($value);
            }else
            if ($key=='edit_url')
            {
                $this->setEditButton($value);
            }else
            if ($key=='new_url')
            {
                $this->setNewButton($value);
            }else
            if ($key=='buttonsNew' && is_array($value))
            {
                $this->addArg('_buttonsNew', $value);
            }else
            if ($key=='buttonsEdit' && is_array($value))
            {
                $this->addArg('_buttonsEdit', $value);
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
        $field=null;
        $args=$this->getArgs();
        $buttonEdit='';
       
        if (array_key_exists('_buttonsEdit', $args))
        {
            $buttonEdit=form_button($args['_buttonsEdit']);
            unset($args['_buttonsEdit']);
        }
        $buttonNew='';
        if (array_key_exists('_buttonsNew', $args))
        {
            $buttonNew=form_button($args['_buttonsNew']);
            unset($args['_buttonsNew']);
        }
        $input='';
        if ($this->isArgExists('options'))
        {
            $options=$this->getArgs('options');
            unset($args['options']);
            $value=$this->getArgs('value');
            
            if (!array_key_exists('class', $args))
            {
                $args['class']='form-control';
            }
            $args['id'].='_list';
            $input= form_dropdown($args['name'].'_list',$options,[$args['value']], $args);
        }
          
        if ($this->_mode=='before')
        {
            $field='<div class="input-group">';
            $field.='<div class="input-group-append">';
            $field.=$buttonEdit;
            $field.=$buttonNew;
            $field.='</div>';
            $field.=$input.'</div>';
            
        }else
        {
            $field='<div class="input-group">';
            $field.=$input;
            $field.='<div class="input-group-append">';
            $field.=$buttonEdit;
            $field.=$buttonNew;
            $field.='</div></div>';
        }
        return $field;
    }
}