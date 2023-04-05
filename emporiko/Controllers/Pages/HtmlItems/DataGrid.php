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

class DataGrid extends HtmlItem 
{
    public $_viewname='System/Elements/datagrid';
    
    public $_pagination=FALSE;
    
    static function create()
    {
        return new DataGrid();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->_args['_columns']=[];
    }
    
    /**
     * Add column configuration
     * 
     * @param string $name
     * @param string $title
     * @param bool   $editable
     * @param array  $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addColumn(string $name,string $title,bool $editable=TRUE,array $args=[])
    {
        if (!array_key_exists('field', $args))
        {
            $args['field']=InputField::create();
        }
        
        if (!$this->isArgExists('value') || ($this->isArgExists('value') && !is_array($this->_args['value'])))
        {
            $args['field']=$args['field']->setName($this->_args['name'].'[#rowid]['.$name.']')->setValue('0')->render();
        }
        
        $this->_args['_columns'][$name]=
        [
            'name'=>$name,
            'title'=>$title,
            'editable'=>$editable,
            'args'=>$args,
            
        ];
        return $this;
    }
    
    /**
     * Add columns configuration
     * 
     * @param array $columns
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addColumns(array $columns)
    {
        foreach($columns as $key=>$column)
        {
            if (is_array($column) && Arr::KeysExists(['name','title'], $column))
            {
                if ($column['name']=='_action')
                {
                    $this->addRemoveRowColumn($column['title']);
                }else
                {
                    $this->addColumn($column['name'],$column['title'], array_key_exists('editable', $column)? $column['editable'] : TRUE,array_key_exists('args', $column)? $column['args'] : []);
                }
            }
        }
        return $this;
    }
    
    /**
     * Add number column configuration
     * 
     * @param string $name
     * @param string $title
     * @param int    $min
     * @param int    $max
     * @param array  $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addNumberColumn(string $name,string $title,int $min,int $max,array $args=[])
    {
        $args['number']=TRUE;
        $args['field']= NumberField::create()
                ->setMax($max)
                ->setMin($min)
                ->setArgs(array_key_exists('field', $args) ? $args['field'] : []);
        return $this->addColumn($name, $title,TRUE,$args);
    }
    
    /**
     * Add list column configuration
     * 
     * @param string $name
     * @param string $title
     * @param array  $list
     * @param bool   $isAdvanced
     * @param bool   $editable
     * @param array  $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addListColumn(string $name,string $title,array $list,bool $isAdvanced=TRUE,bool $editable=TRUE,array $args=[])
    {
        $args['list']=$list;
        $args['field']= DropDownField::create()
                ->setOptions($list)
                 ->setArgs(array_key_exists('field', $args) ? $args['field'] : []);
        if ($isAdvanced)
        {
           $args['field']->setAsAdvanced(); 
        }
        return $this->addColumn($name, $title,$editable,$args);
    }
    
    /**
     * Add Yes/No column configuration
     * 
     * @param string $name
     * @param string $title
     * @param array  $list
     * @param bool   $editable
     * @param array  $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addYesNoColumn(string $name,string $title,array $list,bool $editable=TRUE,array $args=[])
    {
        $args['list']=$list;
        $args['field']= DropDownField::create()
                ->setOptions($list)
                ->setArgs(array_key_exists('field', $args) ? $args['field'] : []);
        return $this->addColumn($name,$title,$editable,$args);
    }
    
    /**
     * Add money column configuration
     * 
     * @param string $name
     * @param string $title
     * @param string $icon
     * @param bool   $editable
     * @param array  $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addMoneyColumn(string $name,string $title,string $icon,bool $editable=TRUE,array $args=[])
    {
        $args['field']= InputButtonField::create()
                ->setAsCurrFormat($icon)
                ->setArgs(array_key_exists('field', $args) ? $args['field'] : []);
        $args['money']=Str::startsWith($icon, 'fa') ? html_fontawesome($icon.' mr-auto'): $icon;
        $args['style']='text-align:right';
        return $this->addColumn($name, $title,$editable,$args);
    }
    
    /**
     * Set column edit state
     * 
     * @param type $name
     * @param bool $editable
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function setColumnEditable($name,bool $editable=TRUE)
    {
        $name= is_string($name) && $name=='*' ? array_keys($this->_args['_columns']) : $name;
        $name= is_array($name) ? $name : [$name];
        foreach($this->_args['_columns'] as $key=>$val)
        {
            if (in_array($key,$name))
            {
               $this->_args['_columns'][$key]['editable']=$editable; 
            }
        }
        return $this;
    }
    
    /**
     * Add total column configuration
     * 
     * @param string $title
     * @param string $method
     * @param array  $columns
     * @param array  $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addTotalColumn(string $title,string $method,array $columns,array $args=[])
    {
        if (!in_array($method, ['sum','times','x']))
        {
            $method='sum';
        }
        $method=$method=='times' ? 'x' : $method;
        $args['total']=
        [
            'method'=>$method,
            'columns'=>$columns,
        ];
        $this->addArg('_totals', []);
        return $this->addColumn('_total', $title,FALSE,$args);
    }
    
    /**
     * Remove column configuration
     * 
     * @param type $name
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function removeColumn($name)
    {
        $name= is_array($name) ? $name : [$name];
        foreach($this->_args['_columns'] as $key=>$val)
        {
            if (in_array($key,$name))
            {
               unset($this->_args['_columns'][$key]);
            }
        }
        return $this;
    }
    
    /**
     * Add action column
     * 
     * @param string $title
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addRemoveRowColumn(string $title='')
    {
        return $this->addColumn('_action',$title,FALSE,['style'=>'text-align:right']);
    }
    
    /**
     * Add totals row on the bottom of grid
     * 
     * @param array $columns
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function addTotalRow(array $columns)
    {
        return $this->addArg('_footer', TRUE)->addArg('_total_row', $columns);
    }
    
    /**
     * Set grid header class
     * 
     * @param mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function setHeaderClass($value)
    {
        $value= is_array($value) ? implode(' ',$value) : $value;
        if (is_string($value))
        {
           $this->_args['_header']=$value; 
        }
        return $this;
    }
    
    /**
     * Set/unset grid as read only
     * 
     * @param bool $value
     * @param bool $whiteBackground
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\DataGrid
     */
    function setReadOnly(bool $value = TRUE, bool $whiteBackground = FALSE) 
    {
        parent::setReadOnly($value, $whiteBackground);
        return $this->setColumnEditable(array_keys($this->_args['_columns']),FALSE);
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
     * Set field value
     * 
     * @param array $value
     * 
     * @return type
     */
    function setValue($value) 
    {
        if (is_bool($this->_pagination) && $this->_pagination && is_array($value) && array_key_exists('links', $value))
        {
            $this->addArg('_pagination', $value['links'])->addArg('_footer', TRUE);
            $value=$value['data'];
        }
        return parent::setValue($value);
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
            if (strtolower($key)=='columns' && is_array($value))
            {
               $this->_args['_columns']=$value; 
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

        return view($this->_viewname,$this->getArgs());
    }
}