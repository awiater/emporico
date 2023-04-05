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

class MovementsDataField extends HtmlItem 
{
    public $_viewname='System/movement';
    
    public $_pagination=FALSE;
    
    public $_filters=[];
    
    public $_columns=[];
    
    static function create()
    {
        return new MovementsDataField();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->setTitle('system.movements.card_title');
        $this->setEditable(FALSE);
        $this->addArg('viewmode', 'list');
        $this->setValue(null);
        $this->setNoDataMsg('system.movements.no_data_msg');
    }
    
    /**
     * Set default filter (mhtype or mhtype_desc field)
     * 
     * @param mixed $value
     * 
     * @return $this
     */
    function setDefaultFilter($value)
    {
        if (is_numeric($value))
        {
            $this->_filters['mhtype']=$value;
        }else
        if (is_string($value))
        {
            $this->_filters['mhtype_desc']=$value;
        }
        return $this;
    }
    
    /**
     * Set reference field filter
     * 
     * @param string $value
     * 
     * @return $this
     */
    function setReferenceFilter(string $value)
    {
        return $this->addCustomFilter('mhref', $value);
    }
    
    /**
     * Set user field filter
     * 
     * @param string $value
     * 
     * @return $this
     */
    function setUserFilter(string $value)
    {
        return $this->addCustomFilter('mhuser', $value);
    }
    
    /**
     * Set date range filter
     * 
     * @param string $startDate
     * 
     * @param string $endDate
     * 
     * @return $this
     */
    function setDateRangeFilter(string $startDate,string $endDate)
    {
        $this->_filters['( mhdate >=']=$startDate;
        $this->_filters['mhdate <= )']=$endDate;
        return $this;
    }
    
    /**
     * Add custom filter
     * 
     * @param string $field
     * @param string $value
     * 
     * @return $this
     */
    function addCustomFilter(string $field,string $value)
    {
        if (in_array($field, model('System/MovementsModel')->allowedFields))
        {
            $this->_filters[$field]=$value;
        }
        return $this;
    }
    
    /**
     * Set to show only n (10 by default) logs
     * 
     * @param int $limit
     * 
     * @return $this
     */
    function showOnlyTop(int $limit=10)
    {
        $this->_filters['@limit']=$limit;
        return $this;
    }
    
    /**
     * Determines if pagination is enabled (if auto from model use TRUE)
     * 
     * @param bool $enabled
     * 
     * @return $this
     */
    function setPagination($data=TRUE)
    {
        $this->_pagination=$data;
        return $this;
    }
    
    /**
     * Add field title
     * 
     * @param string $title
     * 
     * @return $this
     */
    function setTitle(string $title)
    {
        return $this->addArg('title', $title);
    }
    
    /**
     * Set item text (label for input fields, alternative text for buttons)
     * 
     * @param  string $text
     * 
     * @return $this
     */
    function setText($text) 
    {
        parent::setText($text);
        return $this->setTitle($text);
    }
    
    /**
     * Add table mode columns
     * 
     * @param string      $column
     * @param null|string $text
     * 
     * @return $this
     */
    function addTableColumn(string $column,$text=null)
    {
        $columns=['mhtype_name','mhdate','mhuser','mhfrom','mhto','mhref','mhinfo'];
        if ($column=='*' || $column=='all')
        {
            foreach($columns as $column)
            {
                $this->addTableColumn($column);
            }
            return $this;
        }
        if (!in_array($column,$columns))
        {
            return $this;
        }
        $text= is_string($text) ? $text : 'system.movements.'.($column=='mhtype_name' ? 'mhtype' : $column);
        $this->_columns[$column]=lang($text);
        return $this;
    }
    
    /**
     * Set date format visible by user
     * 
     * @param string $format
     * 
     * @return $this
     */
    function setDateFormat(string $format)
    {
        return $this->addArg('date_format', $format);
    }
    /**
     * Determines if logs could be deleted by user
     * 
     * @param bool $value
     * 
     * @return $this
     */
    function setEditable(bool $value=TRUE)
    {
        if ($value)
        {
            $this->addArg('movements_del_url', url('Home','deletesingle',['mov','-id-'],['refurl'=> current_url(FALSE,TRUE)]));
        }
        return $this->addArg('cfg_acc', $value);
    }
    
    function setNewLogButton(array $args=[])
    {
        if (!array_key_exists('ref', $args))
        {
            if (!array_key_exists('mhref', $this->_filters))
            {
                return $this;
            }
            $args['ref']=$this->_filters['mhref'];
        }
        
        if (!array_key_exists('tooltip', $args))
        {
            $args['tooltip']='system.movements.addlog_button';
        }
        if (!array_key_exists('class', $args))
        {
            $args['class']='dark btn-sm';
        }
        
        if (is_array($args['class']))
        {
            $args['class']=implode(' ',$args['class']);
        }
        
        if (!array_key_exists('button', $args) || (array_key_exists('button', $args) && $args['button']))
        {
            $args['button']=ToolbarButton::createModalStarter('movements_addlog_modal', 'fas fa-plus', $args['class'], $args['tooltip'])->render();
        }
        if (!array_key_exists('title', $args))
        {
            $args['title']='system.movements.addlog_title';
        }
        if (!array_key_exists('field_info', $args))
        {
            $args['field_info']='system.movements.addlog_field_info';
        }
        if (!array_key_exists('msgs', $args))
        {
            def_msg:
            $args['msgs']=base64_encode(json_encode(['ok'=>'system.movements.addlog_saveok','error'=>'system.movements.addlog_saveerror']));
        }else
        {
            if (is_array($args['msgs']) && Arr::KeysExists(['ok','error'], $args['msgs']))
            {
                $args['msgs']= base64_encode(json_encode($args['msgs']));
            }else
            {
                goto def_msg;
            }
        }
        $args['action']=url('Home','save',['addlog'],['refurl'=> current_url(FALSE,TRUE)]);
        
        return $this->addArg('addlog', $args);
    }
    
    /**
     * Set view mode as table (not list as default)
     * 
     * @return $this
     */
    function setViewAsTable()
    {
        return $this->addArg('viewmode', 'table');
    }
    
    /**
     * Set message which will be shown when there is no data
     * 
     * @param string $msg
     * 
     * @return $this
     */
    function setNoDataMsg(string $msg)
    {
        return $this->addArg('no_data_msg', $msg);
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
            if ($key=='no_data_msg' && is_string($value))
            {
                $this->setNoDataMsg($value);
            }else
            if ($key=='addlog')
            {
                if (is_array($value))
                {
                    $this->setNewLogButton($value);
                }else
                {
                    $this->setNewLogButton();
                }
            }else
            if ($key=='editable' && is_bool($value))
            {
                $this->setEditable($value);
            }else
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='date_format' && is_string($value))
            {
              $this->setDateFormat($value);  
            }else
            if ($key=='view')
            {
                if (is_string($value) && $value=='table')
                {
                    $this->setViewAsTable();
                }
            }else
            if ($key=='pagination')
            { 
                $this->setPagination($value);
            }else
            if ($key=='showtop')
            {
                $value= is_numeric($value) ? $value : 10;  
                $this->showOnlyTop($value);
            }else
            if ($key=='filter_bydate' && is_array($value) && count($value)==2)
            {
                $value= array_values($value);
                $this->setDateRangeFilter($value[0],$value[1]);
            }else    
            if ($key=='filter_byref' && is_string($value))
            {
                $this->setReferenceFilter($value);
            }else
            if ($key=='filter_byuser' && is_string($value))
            {
                $this->setUserFilter($value);
            }else
            if ($key=='filter' && (is_string($value) || is_numeric ($value)))
            {
                $this->setDefaultFilter($value);
            }else
            if ($key=='filters' && is_array($value))
            {
                foreach($value as $key=>$val)
                {
                    $this->addCustomFilter($key, $val);
                }
            }else
            if ($key=='columns')
            {
                if (is_string($value))
                {
                    $this->addTableColumn($value);
                }else
                if (is_array($value))
                {
                    foreach($value as $key=>$val)
                    {
                        if (is_numeric($key))
                        {
                            $this->addTableColumn($val);
                        }else
                        {
                            $this->addTableColumn($key,$val);
                        }
                    }
                }
                
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
        $value=$this->_args['value'];
        if (is_array($value))
        {
            $this->_args['movements']=$value;
        }else
        {
            $model=model('System/MovementsModel');
            $this->_args['movements']=$model->getData($this->_filters,$this->_pagination);
            if (is_array($this->_args['movements']))
            {
                if (array_key_exists('pagination', $this->_args['movements']))
                {
                    $this->_args['pagination']=$this->_args['movements']['pagination'];
                }
                
                if (array_key_exists('data', $this->_args['movements']))
                {
                    $this->_args['movements']=$this->_args['movements']['data'];
                }
            }
        }
        if (count($this->_columns) < 1)
        {
            $this->addTableColumn('*');
        }
        $this->_args['_table_columns']=$this->_columns;
        return view($this->_viewname,$this->getArgs());
    }
}