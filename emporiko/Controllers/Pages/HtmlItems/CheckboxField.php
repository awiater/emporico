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

class CheckboxField extends HtmlItem
{
    public $_viewname='System/Elements/select';
    
    
    static function create()
    {
        return new CheckboxField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->setClass('custom-control-input');
        $this->addArg('field_label', '');
    }
    
    /**
     * Set field type as radio button
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckboxField
     */
    function setAsRadio()
    {
        $this->addArg('type', 'radio');
        return $this;
    }
    
    /**
     * Determines if field is radio button
     * 
     * @return bool
     */
    function isRadio()
    {
        return $this->isArgExists('type') && $this->_args['type']=='radio';
    }
    
    /**
     * Set field type as checkbox button
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckboxField
     */
    function setAsCheck()
    {
        $this->addArg('type', 'checkbox');
        return $this;
    }
    
    /**
     * Determines if field is checkbox button
     * 
     * @return bool
     */
    function isCheck()
    {
        return $this->isArgExists('type') && $this->_args['type']=='checkbox';
    }
    
    /**
     * Set field checked state
     * 
     * @param mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckboxField
     */
    function setState($value)
    {
        $value=$value==1 || $value=='1' || $value ? TRUE : FALSE;
        return  $this->addArg('checked', $value,FALSE);
    }
    
    /**
     * Determines if field is in checked state
     * 
     * @return bool
     */
    function isChecked()
    {
        return $this->isArgExists('checked') && $this->_args['checked'];
    }
    
    /**
     * Set field label
     * 
     * @param  string $label
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckboxField
     */
    function setLabel($label)
    {
        return $this->addArg('field_label', lang($label));
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
            if ($key=='type' && $value=='radio')
            {
                $this->setAsRadio();
            }else
            if ($key=='type' &&($value=='checkbox' || $value=='check'))
            {
                $this->setAsRadio();
            }else
            if ($key=='checked')
            {
                $this->setState($value);
            }else
            if ($key=='label')
            {
                $this->setLabel($value);
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
        $html='<div class="custom-control custom-'.$this->getArgs('type').'">';
        $html.=$this->isRadio() ? form_radio($this->_args) : form_checkbox($this->_args);
        $html.=form_label($this->_args['field_label'],$this->_args['id'],['class'=>'custom-control-label']);
        $html.='</div>';
        return $html;
    }
    
}