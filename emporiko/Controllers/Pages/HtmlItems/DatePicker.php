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

class DatePicker extends HtmlItem
{
    public $_viewname='System/Elements/datetimepicker';
      
    private $_noweekends=FALSE;
    
    private $_yearpicker=FALSE;
    
    private $_monthpicker=FALSE;
    
    private $_mode=[];
    
    private $_nodays=[];
    
    static function create()
    {
        return new DatePicker();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_args['_dbwformat']='yymmdd';
        $this->_args['_viewformat']='d M yy';
        $this->_args['icon']='far fa-calendar-alt';
    }
    
    /**
     * Set item value
     * 
     * @param mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setValue($value)
    {
        $value=$value==null ? formatDate() : $value;
        return parent::setValue($value);
    }
    
    /**
     * Set format of date viewer (java script date format)
     * 
     * @param string $format
     * @param bool $override
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setViewFormat($format,string $phpVers='')
    {
        $this->_args['_viewformat']=$format;
        $this->_args['_viewformat_php']=$phpVers;
        return $this;
    }
    
    /**
     * Set minimum date of picker (in DB format)
     * 
     * @param string $date
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setMinDate($date)
    {
        if (strlen($date)==12)
        {
            $date= convertDate($date, null,$this->_args['_viewformat']);
        }
        $this->_args['minDate']=$date;
        return $this->addMode('date');
    }
    
    /**
     * Set maximum date of picker (in DB format)
     * 
     * @param string $date
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setMaxDate($date)
    {
        if (strlen($date)==12)
        {
            $date= convertDate($date, null,$this->_args['_viewformat']);
        }
        $this->_args['maxDate']=$date;
        return $this->addMode('date');
    }
    
    /**
     * Set disabled days
     * 
     * @param mixed $days
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setDisabledDays($days)
    {
        $days= is_array($days) ? $days : [$days];
        $this->_nodays=$this->_nodays+$days;
        return $this->addMode('date');
    }
    
    /**
     * Set weekend days as disabled
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setWeekendsDisabled()
    {
        $this->_noweekends=TRUE;
        return $this->addMode('date');
    }
    
    /**
     * Enable Year drop down
     *  
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setYearPicker()
    {
       $this->_yearpicker=TRUE;
       return $this->addMode('date');
    }
    
    /**
     * Enable Month drop down
     *  
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setMonthPicker()
    {
       $this->_monthpicker=TRUE;
       return $this->addMode('date');
    }
    
    /**
     * Enable time picker
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setTimePicker()
    {
        return $this->addMode('time');
    }
    
    /**
     * Determines if time picker is enabled
     * 
     * @return bool
     */
    function isTimePicker()
    {
        return in_array('time', $this->_mode);
    }
    
    /**
     * Set button icon
     * 
     * @param string $icon
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DatePicker
     */
    function setButtonIcon($icon)
    {
        $this->_args['icon']=$icon;
        return $icon;
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
            if (strtolower($key)=='mindate')
            {
                $this->setMinDate($value,TRUE);
            }else
            if (strtolower($key)=='maxdate')
            {
                $this->setMaxDate($value,TRUE);
            }else
            if (strtolower($key)=='dateformat')
            {
                $this->setViewFormat($value);
            }else
            if (strtolower($key)=='noweekends' && $value)
            {
                $this->setWeekendsDisabled();
            }else
            if (strtolower($key)=='yearpicker' && $value)
            {
                $this->setYearPicker();
            }else
            if (strtolower($key)=='monthpicker' && $value)
            {
                $this->setMonthPicker();
            }else
            if (strtolower($key)=='nodays')
            {
               $this->setDisabledDays($value); 
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
            $this->_args['value_view']= convertDate($this->_args['value'],null, $this->_args['_viewformat_php']);
            if ($this->isTimePicker())
            {
                $this->_args['_timevalue']=convertDate($this->_args['value'], 'db', 'H:i');
                $this->_args['_timevalue']=explode(':',$this->_args['_timevalue']);
            }
        }
        if ($this->isTimePicker())
        {
            $this->_args['_viewformat']='d M yy [t]';
        }
        return view($this->_viewname,$this->getArgs());
        
    }
    
    private function addMode($mode)
    {
        if (!in_array($mode, $this->_mode))
        {
            $this->_mode[]=$mode;
        }
        return $this;
    }
}