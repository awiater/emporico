<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

  
namespace EMPORIKO\Controllers\Pages\HtmlItems;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\MenuBarItemType;

class MenuBarItem
{
    private MenuBarItemType $_type;
    
    private View $_view;
    
    private array $_args=[];
    
    public static function create(MenuBarItemType $type,$name,$tooltip,array $args=[])
    {
        $obj=new MenuBarItem();
        $obj->setType($type)
            ->setTooltip($tooltip)
            ->setName($name)
            ->setArgs($args);
        return $obj;
    }
    
    function __construct() 
    {
        $this->_type=MenuBarItemType::Button();
        $this->setText('MenuBarButton')
             ->setButtonType('dark',TRUE);
    }
    
    /**
     * Set left margin of item
     * 
     * @param int $size
     * 
     * @return $this
     */
    function setInBetweenGap(int $size)
    {
        $set=FALSE;
        foreach($this->_args['class'] as $key=>$class)
        {
            if (Str::startsWith($class, 'ml-'))
            {
                $this->_args['class'][$key]='ml-'.$size;
                $set=TRUE;
            }
        }
        if (!$set)
        {
            $this->addClass('ml-'.$size);
        }
        return $this;
    }
    
    /**
     * Set item class as button
     * 
     * @param string $color
     * @param bool   $isSmall
     * 
     * @return $this
     */
    function setButtonType($color,$isSmall=FALSE)
    {
        $class=['btn','btn-'.$color];
        if ($isSmall)
        {
            $class[]='btn-sm';
        }
        return $this->setClass($class);
    }
    
    /**
     * Set item class
     * 
     * @param  array|string $class
     * 
     * @return $this
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
     * @return $this
     */
    function addClass($class)
    {
        $class= !is_array($class) ? [$class] : $class;
        foreach($class as $item)
        {
            $this->_args['class'][]=$item;
        }
        return $this;
    }
    
    /**
     * Set item name (and element id)
     * 
     * @param string $name
     * @param string $id
     * 
     * @return $this
     */
    function setName($name,$id=null)
    {
        $id=$id==null ? $name : $id;
        $id=str_replace(['[',']'], ['_',null], $id);
        $this->_args['name']=$name;
        $this->_args['id']=$id;
        return $this;
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
        $this->_args['label']=$text;
        return $this;
    }
    
    /**
     * Set item icon (applicable only to buttons)
     * 
     * @param  string $icon
     * 
     * @return $this
     */
    function setIcon($icon)
    {
        if ($icon==null)
        {
            return $this;
        }
        $icon=Str::startsWith($icon, 'fa') ? '<i class="'.$icon.'"></i>' : $icon;
        $this->_args['text']=$icon;
        return $this;
    }
    
    /**
     * Set item type 
     * 
     * @param MenuBarItemType $type
     * 
     * @return EMPORIKO\Controllers\Pages\MenuBarItem
     */
    function setType(MenuBarItemType $type)
    {
        $this->_type=$type;
        if ($type->is(MenuBarItemType::Link))
        {
            $this->setButtonType('link',TRUE);
        }
        return $this;
    }
    
    /**
     * Set item tooltip
     * 
     * @param  array|string $data
     * 
     * @return $this
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
     * Set View object
     * 
     * @param View $view
     * 
     * @return EMPORIKO\Controllers\Pages\MenuBarItem
     */
    function setView(View $view)
    {
        $this->_view=$view;
        return $this;
    }
    
    /**
     * Determine if item is given type
     * 
     * @param MenuBarItemType $type
     * 
     * @return bool
     */
    function isTypeOf($type)
    {
        return $this->_type->is($type);
    }
    
    /**
     * Render object to html
     * 
     * @return string
     */
    function render()
    {
        if ($this->_view->ismobile())
        {
            if (array_key_exists('title', $this->_args))
            {
                $this->_args['text']=$this->_args['title'];
            }
        }
        $this->getFlatClass(TRUE);
        if ($this->_type->is(MenuBarItemType::Button))
        {
            $html= form_button($this->_args['name'],$this->_args['text'],$this->_args);
        }else
        if ($this->_type->is(MenuBarItemType::Link))
        {
            if ($this->_view->ismobile())
            {
                $this->_args['class'].=' w-100 mb-1';
            }
            
            if (array_key_exists('href', $this->_args) && is_array($this->_args['href']))
            {
                $this->_args['href']= url_from_array($this->_args['href']);
            }
            if (!array_key_exists('href', $this->_args))
            {
                $this->_args['href']='#';
            }
            $html=url_tag($this->_args['href'], $this->_args['text'],$this->_args);
        }else
        if ($this->_type->is(MenuBarItemType::Link))
        {
            $html= form_label($this->_args['text'],$this->_args);
        }else
        if (in_array($this->_type->get(), [MenuBarItemType::DropDown,MenuBarItemType::TextField]))
        {
            $form=new FormView($this->_view->controller);
            $this->_args['type']=$this->_type->get();
            $form->addCustomFieldFromData($this->_args,FALSE,"@cfid");
            $html=$form->getViewData('fields.'.$this->_args['name'].'.value');
        }else
        {
            return null;
        }
        return 
        [
            'html'=>$html,
            'args'=>$this->_args
        ];
    }
    
    /**
     * Set item parameters
     * 
     * @param array $args
     */
    function setArgs(array $args)
    {
        foreach($args as $key=>$arg)
        {
            if ($key=='icon')
            {
                $this->setIcon($arg);
            }else
            if ($key=='id')
            {
                $this->_args['id']=str_replace(['[',']'], ['_',null], $arg);
            }else
            if ($key=='class')
            {
                $this->setClass($arg);
            }else
            if ($key=='addclass')
            {
                $this->addClass($arg);
            }else
            if ($key=='type')
            {
                if (is_array($arg) && count($arg) > 1 && is_bool($arg[1]))
                {
                   $this->setButtonType($arg[0],$arg[1]); 
                } else 
                if (is_string($arg))
                {
                    $this->setButtonType($arg);
                }
                
            }else
            if ($key=='margin' && is_numeric($arg))
            {
                $this->setInBetweenGap($arg);
            }else
            {
                $this->_args[$key]=$arg;
            }
        }
    }
}

