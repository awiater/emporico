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

class Toolbar extends HtmlItem
{
    public $_viewname='System/Elements/toolbar';
    
    private $_buttons;
    
   
    static function create(string $name,array $items=[],string $background='',array $args=[])
    {
        $btn=new Toolbar();
        
        if (!array_key_exists('background', $args))
        {
            $args['background']=$background;
        }
        
        if (!array_key_exists('items', $args))
        {
            $btn->setItems($items);
        }
        if (!array_key_exists('name', $args))
        {
            $args['name']=$name;
        }
        
        if (count($args)>0)
        {
            $btn->setArgs($args);
        }
        return $btn;
    }
   
    function __construct() 
    {
        $this->setClass('btn btn-sm btn-dark');
    }
    
    /**
     * Add new item to toolbar
     * 
     * @param \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem $item
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\Toolbar
     */
    function addItem(\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem $item)
    {
        $this->_buttons[]=$item;
        return $this;
    }
    
    /**
     * Set toolbar items using array elements
     * 
     * @param array $items
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\Toolbar
     */
    function setItems(array $items)
    {
        $this->_buttons=[];
        foreach ($items as $item)
        {
            if (is_subclass_of($item,'\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
            {
                $this->addItem($item);
            }
        }
        return $this;
    }
    
    /**
     * Set toolbar background colour
     * 
     * @param string $background
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\Toolbar
     */
    function setBackground(string $background)
    {
        if (!in_array($background, ['dark','white','danger','warning','purple','info','light']))
        {
           $background='white'; 
        }
        return $this->addArg('background', $background);
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
            if ($key=='items' && is_array($value))
            {
                $this->setItems($value);
            }else
            if ($key=='background' && is_string($value))
            {
                $this->setBackground($value);
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
        if ($this->isReadOnly())
        {
            return 'null';
        }
        $this->addArg('buttons', $this->_buttons);
        return view($this->_viewname,$this->getArgs());
    }
}