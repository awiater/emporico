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

class TimePicker extends HtmlItem
{
    public $_viewname='System/Elements/timepicker';
    
    static function create()
    {
        return new TimePicker();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_args['icon']='far fa-clock';
    }
    /**
     * Set button icon
     * 
     * @param string $icon
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TimePicker
     */
    function setButtonIcon($icon)
    {
        if (Str::startsWith($icon, 'fa'))
        {
            $this->_args['icon']=$icon;
        }
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
            if (strtolower($key)=='icon')
            {
               $this->setButtonIcon($value); 
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
    function render($aferLoad=TRUE) 
    {
        $this->getFlatClass(TRUE);
        if ($this->isArgExists('value') && strlen($this->_args['value']) > 1)
        {
            $this->_args['_timevalue']=convertDate($this->_args['value'], 'db', 'H:i');           
        }else
        {
            $this->_args['value']=formatDate('now','Hi');
            $this->_args['_timevalue']=convertDate($this->_args['value'], 'Hi', 'H:i');
        }
        $this->_args['value_view']= convertDate($this->_args['value'], 'Hi', 'H:i');
        $this->_args['_timevalue']=explode(':',$this->_args['_timevalue']);
        return view($this->_viewname,$this->getArgs());
    }
}