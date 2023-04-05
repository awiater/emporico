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

class OrderField extends HtmlItem
{
    public $_viewname='System/Elements/order_list';
    
    public $_advanced=FALSE;
    
    
    static function create()
    {
        return new OrderField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->addArg('_showorderpos', FALSE);
    }
    
    /**
     * Set list elements array
     * 
     * @param array $options
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\OrderField
     */
    function setOptions(array $options)
    {
        return $this->addArg('options', $options);
    }
    
    /**
     * Determines if items position is visible
     * 
     * @param bool $visibility
     * 
     * @return  \EMPORIKO\Controllers\Pages\HtmlItems\OrderField
     */
    function setOrderPosVisibility(bool $visibility=TRUE)
    {
        $this->_args['_showorderpos']=$visibility;
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
            if ($key=='showorderpos')
            {
                $this->setOrderPosVisibility($value);
            }else
            if ($key=='options' && is_string($value) && \EMPORIKO\Helpers\Strings::contains($value, '::'))
            {
               $this->setOptions(loadModuleFromString($value)); 
            }else
            if ($key=='options' && is_array($value) && count($value) > 0)
            {
                $this->setOptions($value);
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
        return view($this->_viewname,['args'=>$args,'value'=>$value==null ? '' : $value,'options'=>$this->_args['options']]);
    }
    
}