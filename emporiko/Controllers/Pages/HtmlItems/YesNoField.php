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

class YesNoField extends HtmlItem
{
    public $_viewname='System/Elements/yesno';
    
    
    static function create()
    {
        return new YesNoField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->addClass('col-xs-12 col-md-2');
        $this->addArg('options', [lang('system.general.no'),lang('system.general.yes')]);
        $this->addArg('value', 1);
    }
    
    /**
     * Set field Yes value text
     * 
     * @param string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\YesNoField
     */
    function setYesText($value)
    {
        $this->_args['options'][1]=lang($value);
        return $this;
    }
    
    /**
     * Set field No value text
     * 
     * @param string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\YesNoField
     */
    function setNoText($value)
    {
        $this->_args['options'][0]=lang($value);
        return $this;
    }
    
    /**
     * Add wrapped argument to field (do not add empty line on the top)
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\YesNoField
     */
    function setAsWrapped()
    {
        return $this->addArg('wrapped', TRUE);
    }
    
    /**
     * Render to HTML tag
     * 
     * @return string
     */
    function render() 
    {
        $this->getFlatClass(TRUE);
        $args=$this->_args;
        unset($args['options']);
        if ($this->isReadOnly())
        {
            $options=$this->_args['options'];
            $key=$this->getArgs('value');
            //return form_input($this->getArgs('name'), array_key_exists($key, $options) ?$options[$key] : $key , $args);
        } 
        $args=$this->_args;
        unset($args['options']);
        $value=$this->getArgs('value');
        
        return view($this->_viewname,['args'=>$args,'value'=>$value==null ? '' : $value,'options'=>$this->_args['options']]);
    }
}