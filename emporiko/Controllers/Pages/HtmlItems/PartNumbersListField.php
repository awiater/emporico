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

class PartNumbersListField extends HtmlItem
{
    public $_viewname;
    
    public $_value;
    
    private $_inputtype='InputField';
    
    static function create()
    {
        return new PartNumbersListField();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_args['list_height']='350px';
        $this->setEnableDeletionOfItems(TRUE);
        $this->setValueFieldVisibility(FALSE);
        $this->setTotalsVisibility();
        $this->setDefValueCurrency('');
        $this->setListFields(['prd_brand','prd_tecdocpart']);
    }
    
    /**
     * Set item value
     * 
     * @param mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setValue($value)
    {
        //$value= is_array($value) ? $value : [$value];
        $this->_value=$value;
        return $this;
    }
    
    /**
     * Set values list height 
     * 
     * @param Int $size
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setListHeight($size)
    {
        $size= is_numeric($size) ? $size.'px' : $size;
        return $this->addArg('list_height', $size);
    }
    
    /**
     * Enable items deletions from list
     * 
     * @param bool $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setEnableDeletionOfItems(bool $value=FALSE)
    {
        return $this->addArg('item_delete', $value);
    }
    
    /**
     * Determines if value field is visible
     * 
     * @param bool $visbility
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setValueFieldVisibility(bool $visbility=TRUE)
    {
        return $this->addArg('field_value', $visbility);
    }
    
    /**
     * Determines if totals line is visible
     * 
     * @param bool $visbility
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setTotalsVisibility(bool $visbility=FALSE)
    {
        return $this->addArg('totals_values', $visbility)->addArg('items_totals', []);
    }
    
    /**
     * Determines default part value currency
     * 
     * @param string $currency
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setDefValueCurrency(string $currency)
    {
        return $this->addArg('def_curr', $currency);
    }
    
    /**
     * Determines if value field is visible
     * 
     * @return boolean
     */
    function isValueFieldVisible()
    {
        if (!$this->isArgExists('field_value'))
        {
            return FALSE;
        }
        return $this->_args['field_value'];
    }
    
    /**
     * Determines what products data fields are visible in search and result list
     * 
     * @param array $fields
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\PartNumbersListField
     */
    function setListFields(array $fields)
    {
        return $this->addArg('list_fields', $fields);
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
            $key= strtolower($key);
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='listheight')
            {
                $this->setListHeight($value);
            }else
            if ($key=='item_delete' && $value)
            {
                $this->setEnableDeletionOfItems($value);
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
           $this->addArg('item_delete', false);
        }
        return view('System/Elements/partnumbers_list',['name'=>$this->getArgs('name'),'items'=>$this->_value,'args'=>$this->getArgs()]);
    }
}