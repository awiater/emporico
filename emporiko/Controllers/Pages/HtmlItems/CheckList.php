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
use EMPORIKO\Helpers\Strings as Str;

class CheckList extends HtmlItem
{
    public $_viewname='System/Elements/check_list';
    
    
    static function create()
    {
        return new CheckList();
    }
    
    function __construct() 
    {
        $this->_args['class']=[];
        $this->setOptions([],TRUE);
        $this->_args['label_class']=[];
        $this->_args['box_class']=[];
    }
    
    /**
     * Set list elements array
     * 
     * @param array $options
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckList
     */
    function setOptions(array $options)
    {
        return $this->addArg('options', $options);
    }
    
    /**
     * Set field name
     * 
     * @param string $name
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckList
     */
    function setName($name)
    {
        if (!Str::endsWith($name, '[]'))
        {
            $name.='[]';
        }
        return parent::setName($name);
    }
    
    /**
     * Set option label class
     * 
     * @param mixed $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckList
     */
    function setOptionLabelClass($class)
    {
        $class= is_array($class) ? $class : [$class];
        foreach($class as $item)
        {
            $this->_args['label_class'][]=$item;
        }
        return $this;
    }
    
    /**
     * Set option checkbox input class
     * 
     * @param mixed $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CheckList
     */
    function setOptionCheckBoxClass($class)
    {
        $class= is_array($class) ? $class : [$class];
        foreach($class as $item)
        {
            $this->_args['box_class'][]=$item;
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
            if ($key=='options' && is_array($value) && count($value) > 0)
            {
                $this->setOptions($value);
            }else
            if ($key=='label_class')
            {
                $this->setOptionLabelClass($value);
            }else
            if ($key=='box_class')
            {
                $this->setOptionCheckBoxClass($value);
            }else
            if ($key=='name')
            {
                $this->setName($value);
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
        $this->_args['label_class']=implode(' ',$this->_args['label_class']);
        $this->_args['box_class']=implode(' ',$this->_args['box_class']);
        $div=[];
        foreach ($this->_args as $key => $value) 
        {
            if (!in_array($key, ['box_class','label_class','options','value']) && is_string($value))
            {
                $div[]=$key.'="'.$value.'"';
            }
        }
        return view($this->_viewname,
                [
                    'div_args'=>' '.implode(' ',$div),
                    'name'=>$this->_args['name'],
                    'value'=>$this->_args['value'],
                    'chb'=>$this->_args['box_class'],
                    'chb_label'=>$this->_args['label_class'],
                    'items'=>$this->_args['options']
                ]);
    }
    
}