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

class ToolbarFilterField extends HtmlItem
{
    public $_viewname;
    
    private $_icon;
    
    private $_field;
    
    private $_button=[];
    
    private $_mode='after';
    
    private $_url_filter;
    
    static function create($name,$filterurl,$icon=null,$color=null,$tooltip=null,$id=null,array $args=[])
    {
        $btn=new ToolbarFilterField();
        
        $btn->setName($name);
        
        $btn->setButtonIcon($icon==null ? 'fas fa-filter' : $icon);
        
        $btn->setButtonColor($color==null ? 'secondary' : $color);
        
        if ($tooltip!=null)
        {
            $btn->setTooltip($tooltip);
        }
        
        
        
        if ($id==null)
        {
            $name='id_toolbarbtn_'.(rand(1,31101987));
        }
        $btn->setID($id);
        
        $btn->setFilterUrl($filterurl==null ? current_url(FALSE) : $filterurl);
        
        $btn->setField(null);
        
        if (count($args)>0)
        {
            $btn->setArgs($args);
        }
        
        return $btn;
    }
   
    function __construct() 
    {
        parent::__construct();
        $this->_button['class']='btn btn-primary';
    }
    
    /**
     * Set filter form url
     * 
     * @param mixed $url
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setFilterUrl($url)
    {
        if (is_array($url))
        {
            $url= url_from_array($url);
        }
        $this->_url_filter=$url;
        return $this;
    }
    
    /**
     * Set button icon / text
     * 
     * @param string $icon
     * @param bool   $keepFromArgs
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setButtonIcon($icon,$keepFromArgs=FALSE)
    {
        if (Str::startsWith($icon, 'fa'))
        {
            $icon= html_fontawesome($icon);
        }
        if ($keepFromArgs && $this->_icon!=null)
        {
            return $this;
        }
        $this->_icon=$icon;
        return $this;
    }
    
    /**
     * Set button class
     * 
     * @param  string $class
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setButtonColor($class)
    {
        $class= is_array($class) ? implode(' ',$class) : $class;
        $this->_button['class']='btn btn-sm btn-'.$class;
        return $this;
    }
    
    /**
     * Set button custom parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setButtonArgs(array $args)
    {
        $this->_button['args']=$args;
        return $this;
    }
    
    /**
     * Set button after input
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setButtonAfter()
    {
        $this->_mode='after';
        return $this;
    }
    
    /**
     * Set button before input
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setButtonbefore()
    {
        $this->_mode='before';
        return $this;
    }
    
    /**
     * Set tool tip on button
     * 
     * @param type $data
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setTooltip($data) 
    {
        if ($data==null)
        {
            return $this;
        }
        if (!array_key_exists('args', $this->_button))
        {
            $this->_button['args']=[];
        }
        if (is_array($data) && Arr::KeysExists(['placement','title'], $data))
        {
            $this->_button['args']['data-placement']=$data['placement'];
            $this->_button['args']['title']=lang($data['title']);
            $this->_button['args']['data-toggle']='tooltip';
        }else
        if (is_string($data))
        {
            $this->_button['args']['data-placement']='top';
            $this->_button['args']['title']=lang($data);
            $this->_button['args']['data-toggle']='tooltip';
        } 
        
        return $this;
    }
    
    /**
     * Set input field
     * 
     * @param mixed $field
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setField($field)
    {
        if ($field==null)
        {
            $this->_field= form_input(
                    [
                        'class'=>'form-control form-control-sm',
                        'id'=>$this->getArgs('id').'_input',
                        'name'=>$this->getArgs('name'),
                        'placeholder'=>lang('system.buttons.filter')
                    ]);
        }else
        {
           $this->_field=$field; 
        }
        
        return $this;
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarFilterField
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='field')
            {
                $this->setField($value);
            }else
            if ($key=='button_icon')
            {
                $this->setButtonIcon($value);
            }else
            if ($key=='button_class')
            {
                $this->setButtonClass($value);
            }else
            if ($key=='button_args' && is_array($value))
            {
                $this->setButtonArgs($value);
            }else
            if ($key=='button_pos')
            {
                if ($value=='before')
                {
                    $this->setButtonbefore();
                } else 
                {
                    $this->setButtonAfter();
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
    function render() 
    {
        $this->getFlatClass(TRUE);
        $this->_button['args']['name']=$this->_args['name'].'_button';
        $this->_button['args']['id']=$this->_args['id'].'_button';
        $this->_button['args']['class']=$this->_button['class'];
        $this->_button['args']['content']=$this->_icon;
        $this->_button['args']['type']='button';
        
        $field=form_open($this->_url_filter, ['id'=>$this->_args['id'].'_form'], []);
        $field.='<div class="input-group">';
        if ($this->_mode=='before')
        {
            $field.='<div class="input-group-append">';
            $field.=form_button($this->_button['args']);
            $field.='</div>';
            $field.=($this->_field).'</div>';
            
        }else
        {
            $field.=($this->_field);
            $field.='<div class="input-group-append">';
            $field.=form_button($this->_button['args']);
            $field.='</div></div>';
        }
        $field.="<script>$('#".$this->_button['args']['id']."').on('click',function(){";
        if ($this->isArgExists('pre_submit_func'))
        {
            $field.=$this->_args['pre_submit_func'];
        }
        $field.="$('#".$this->_args['id'].'_form'."').submit()});</script>";
        $field.='</form>';
        return $field;
    }
}