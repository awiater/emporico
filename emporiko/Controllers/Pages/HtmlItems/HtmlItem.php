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

use \EMPORIKO\Helpers\Strings as Str;

class HtmlItem
{
    /**
     * Array with item parameters
     * @var array
     */
    protected array $_args=[];
    
    /**
     * Name of item view
     * @var type
     */
    protected $_viewname;
    
    
    static function createField($field,$args=null)
    {
        if (is_string($field) && Str::isJson($field))
        {
            $field= json_decode($field,TRUE);
            $args=$field['args'];
            $field=$field['field'];
            goto set_args;
        }else
        if (is_a($field, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem') && $args==null)
        {
            $args=$field;
            $field= get_called_class();    
        }else
        if (!is_string($field))
        {
            throw new \Exception('Invalid class');
        }
        set_args:  
        if (is_string($field))
        {
            $field=!Str::startsWith($field, 'EMPORIKO\Controllers\Pages\HtmlItems') ? 'EMPORIKO\Controllers\Pages\HtmlItems\\'.$field : $field;   
        } 
        
        if (class_exists($field))
        {
            $field=new $field();
            if ($args!=null && is_a($args, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
            {
                $field->setArgs($args->getArgs());
            }else
            if (is_array($args))
            {
                $field->setArgs($args);
            }
            return $field;
        }
        throw new \Exception('Invalid class');
    }
    
    
    function __construct() 
    {
        $this->addClass('form-control'); 
    }
    
    /**
     * Serialize item to string
     * 
     * @return string
     */
    function serialize()
    {
        return json_encode(['field'=>get_class($this),'args'=>$this->_args]);
    }
    
    /**
     * Add item parameter
     * 
     * @param string $key
     * @param string $value
     * @param bool   $override
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function addArg($key,$value,$override=TRUE)
    {
        if (strtolower($key)=='readonly' && is_bool($value))
        {
            return $this->setReadOnly($value);
        }
        if (array_key_exists($key, $this->_args) && !$override)
        {
            return $this;
        }
        if (is_string($key) && $key=='optional')
        {
            $key='data-optional';
        }
        $this->_args[$key]=$value;
        return $this;
    }
    
    /**
     * Set field as optional (collapse around)
     * 
     * @param bool $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setAsOptional(bool $value=TRUE)
    {
        $this->_args['data-optional']=$value;
        return $this;
    }
    
    /**
     * Determine if given parameter exists in array
     * 
     * @param  string $name
     * @param  mixed  $value
     * 
     * @return bool
     */
    function isArgExists($name,$value=null)
    {
        $name= is_array($name) && count($name) > 0 ? $name[0] : $name;
        $name=array_key_exists($name, $this->_args);
        if ($value!=null && $name)
        {
            return $this->_args[$name]==$value;
        }
        return $name;
    }
    
    /**
     * Get array (or null) with item parameters (or parameter value if name is specified)
     * 
     * @param string $name
     * 
     * @return mixed
     */
    function getArgs($name=null)
    {
        if ($name==null)
        {
            return $this->_args;
        }
        if ($this->isArgExists($name))
        {
           return $this->_args[$name]; 
        }
        return null;
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            {
                $this->addArg($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Set item value
     * 
     * @param  mixed $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setValue($value)
    {
        $this->_args['value']=$value;
        return $this;
    }
    
    /**
     * Set item mode as read only
     * 
     * @param bool $value
     * @param bool $whiteBackground
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setReadOnly(bool $value=TRUE,bool $whiteBackground=FALSE)
    {
        if ($whiteBackground && $value)
        {
            $this->addArg('style','background-color:#FFF!important');
        }
        if ($value)
        {
            $this->_args['readonly']=TRUE;
        }
        return  $this;
    }
    
    /**
     * Set item as required
     * 
     * @param bool $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setAsRequired($value=TRUE)
    {
        return $this->addArg('required', $value,FALSE);
    }
    
    /**
     * Determine if item is in read only mode
     * 
     * @return bool
     */
    function isReadOnly()
    {
        return $this->isArgExists('readonly') && $this->getArgs('readonly');
    }
    
    /**
     * Set item class
     * 
     * @param  array|string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setClass($class)
    {
        $class= !is_array($class) ? [$class] : $class;
        $this->_args['class']=$class;
        return $this;
    }
    
    /**
     * Add tags to item class
     * 
     * @param array|string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function addClass($class)
    {
        $class= !is_array($class) ? [$class] : $class;
        if (!array_key_exists('class', $this->_args))
        {
            $this->_args['class']=[];
        }
        foreach($class as $item)
        {
            if (!in_array($item, $this->_args['class']))
            {
                $this->_args['class'][]=$item;
            }
        }
        return $this;
    }
    
    /**
     * Set item name
     * 
     * @param string  $name
     * @param boolean $override
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
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
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setID($id)
    {
        if ($this->isArgExists('name') && $this->_args['name']==$id)
        {
            $id=str_replace(['[',']'], ['_',null], $this->_args['name']);
            $id='id_'.$id;
            return $this->addArg('id', $id,FALSE);
        }
        $id=str_replace(['[',']'], ['_',''], $id);
        $this->addArg('id', $id,FALSE);
        return $this;
    }
    
    /**
     * Set item text (label for input fields, alternative text for buttons)
     * 
     * @param  string $text
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setText($text)
    {
        $this->_args['label']=$text;
        return $this;
    }
    
    /**
     * Set field tab in form
     * 
     * @param string $tabName
     * 
     * @return type
     */
    function setTab($tabName)
    {
        $this->_args['tab_name']=$tabName;
        return $this;
    }
    
    /**
     * Set item tooltip
     * 
     * @param  array|string $data
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setTooltip($data)
    {
        if ($data==null)
        {
            return $this;
        }
        
        if (is_array($data) && Arr::KeysExists(['placement','title'], $data))
        {
            $this->_args['data-placement']=$data['placement'];
            $this->_args['title']=lang($data['title']);
            $this->_args['data-toggle']='tooltip';
        }else
        if (is_string($data))
        {
            $this->_args['data-placement']='top';
            $this->_args['title']=lang($data);
            $this->_args['data-toggle']='tooltip';
        } 
        
        return $this;
    }
    
    /**
     * Set field max length
     * 
     * @param int $size
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem
     */
    function setMaxLength(int $size)
    {
        return $this->addArg('maxlength', $size,FALSE);
    }
    
    /**
     * Determines if item is valid given type
     * 
     * @param string $type
     * 
     * @return boolean
     */
    function isTypeOf($type)
    {
        if (!is_string($type))
        {
            return FALSE;
        }
        $type=!Str::startsWith($type, 'EMPORIKO\Controllers\Pages\HtmlItems') ? 'EMPORIKO\Controllers\Pages\HtmlItems\\'.$type : $type;
        return is_a($this,$type);
    }
    
    /**
     * Render item to HTML tag
     * 
     * @return string
     */
    public function render()
    {
        $this->getFlatClass(TRUE);
        if ($this->isReadOnly())
        {
            return form_input($this->getArgs('name'), $this->getArgs('value'), $this->getArgs());
        }
        return view($this->_viewname,$this->_args);
    }
    
    protected function getFlatClass($changeOriginal=FALSE)
    {
        $class=is_array($this->_args['class']) ? implode(' ',$this->_args['class']): $this->_args['class'];
        if ($changeOriginal)
        {
            $this->_args['class']=$class;
        }
        return $class;
    }
}

