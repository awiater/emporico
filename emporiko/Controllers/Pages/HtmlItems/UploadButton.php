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

class UploadButton extends HtmlItem
{
    public $_viewname;
    
    private $_icon;
    
    private $_driver;
    
    private $_title;
    
    private $_uploadurl='';
    
    static function create($driver,$id=null,$icon=null,$color=null,$tooltip=null,array $args=[])
    {
        $btn=new UploadButton();
        
        $btn->setDriver($driver);
        
        $btn->setButtonIcon($icon==null ? 'fas fa-cloud-upload-alt' : $icon);
        
        $btn->setButtonColor($color==null ? 'info' : $color);
        
        if ($tooltip!=null)
        {
            $btn->setTooltip($tooltip);
        }
        
        if ($id==null)
        {
            $name='id_toolbarbtn_'.(rand(1,31101987));
        }
        $btn->setID($id);
        
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
     * Set button class (ie. danger, warning etc)
     * 
     * @param string $color
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadButton
     */
    function setButtonColor($color)
    {
        $this->setClass('btn btn-sm btn-'.$color);
        return $this;
    }
    
    /**
     * Set button icon
     * 
     * @param string $icon
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadButton
     */
    function setButtonIcon($icon)
    {
        $this->_icon= html_fontawesome($icon);
        return $this;
    }
    
    /**
     * Set upload data driver
     * 
     * @param string $driver
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadButton
     */
    function setDriver(string $driver)
    {
        $this->_driver=$driver;
        $this->_uploadurl=model('Settings/SettingsModel')->getUploadDriverData($driver,TRUE);
        return $this;
    }
    
    /**
     * Set button tool tip and modal title
     * 
     * @param mixed $data
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadButton
     */
    public function setTooltip($data) 
    {
        if (is_string($data))
        {
            $this->_title=$data;
        }
        return parent::setTooltip($data);
    }
    
    /*'type'=>'link','href'=>$refurl,'icon'=>'fas fa-th-list','class'=>'btn btn-sm btn-danger','tooltip'=>'customers.accounts_listbtn','id'=>'id_accview_back'*/
    
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
            if ($key=='driver')
            {
                $this->setDriver($value);
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
            return '';
        }
        
        if (!is_string($this->_driver))
        {
            return '';
        }
        
        $this->addArg('type', 'button',TRUE);
        $this->addArg('content', $this->_icon,TRUE);
        $html=form_button($this->getArgs());
        $args= ['button_id'=>$this->_args['id']];
        foreach(['input_field_id','button_id','input_format','input_name','label','title','use_modal','modal_id','upload_url','form_id'] as $arg)
        {
            if ($this->isArgExists($arg))
            {
                $args[$arg]=$this->_args[$arg];
            }
        }
        $html.=form_dataupload($this->_driver, $this->_title,$args);
        return $html;
    }
}