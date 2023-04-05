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
use EMPORIKO\Helpers\Arrays as Arr;

class AcccessField extends HtmlItem
{
    public $_viewname;
    
    private $_options_mode=['levels'];
    
    static function create()
    {
        return new AcccessField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->setName('access')->setID('id_access');
    }
    
    /**
     * Set item name
     * 
     * @param string $name
     * 
     * @return $this
     */
    function setName($name)
    {
        $this->addArg('name', $name,TRUE);
        return $this;
    }
    
    /**
     * Set item name
     * 
     * @param string $name
     * 
     * @return $this
     */
    function setID($id)
    {
        $id=str_replace(['[',']'], ['_',null], $id);
        $this->addArg('id', $id,TRUE);
        return $this;
    }
    
    /**
     * 
     * @param array $options
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\AcccessField
     */
    function setListOptions(array $options)
    {
        if (count($options) < 1)
        {
            return $this;
        }
        return $this->addArg('options', $options,FALSE);
    }
    
    function addGroupsOptions(bool $onlyGroups=FALSE)
    {
        if ($onlyGroups)
        {
            $this->_options_mode=[];
        }
        $this->_options_mode[]='groups';
        return $this;
    }
    
    function addVisitorLevel()
    {
        $this->_options_mode[]='no_level';
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
        $this->setOptions();
        $args=$this->_args;
        unset($args['options']);
        $value=$this->getArgs('value');
        if ($this->isReadOnly())
        {
            $options=$this->_args['options'];
            $key=$this->getArgs('value');
            return form_input($this->getArgs('name'), array_key_exists($key, $options) ?$options[$key] : $key , $args);
        }
        return form_dropdown($args['name'], $this->_args['options'],[$value], $args);
    }
    
    private function setOptions()
    {
        $options=[];
        
        if (in_array('levels', $this->_options_mode))
        {
            $options=$options+array_combine(\EMPORIKO\Helpers\AccessLevel::Levels, lang('auth.access_levels'));
        }
        
        if (in_array('groups', $this->_options_mode))
        {
            $groups=model('Auth/UserGroupModel')->getForForm('ugref','ugname');
            if (count($options)>0)
            {
               $options=[lang('Auth.access_level_opt_title')=>$options];
               $groups=[lang('Auth.access_group_opt_title')=>$groups];
            }
            
            $options=$options+$groups;
        }
        
        if (in_array('no_level', $this->_options_mode))
        {
            array_unshift($options,lang('Auth.access_no_level'));
        }
        
        $this->addArg('options', $options);
    }
}