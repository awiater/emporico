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

class ToolbarButton extends HtmlItem
{
    public $_viewname;
    
    private $_type;
    
    private $_icon;
    
    private $_tooltip;
    
    
    static function createPrintButton(string $id,string $container,$title,$tooltip,array $args=[])
    {
        $btn=self::create('fas fa-print', 'secondary', $tooltip, $id, null, $args);
        $title=is_array($title) ? lang($title[0],$title[1]) : lang($title);
        $btn->addArg('_print_btn', 
        [
            'button_id'=>$id,
            'container_id'=>$container,
            'title'=> $title,
            'header'=> array_key_exists('header', $args) ? $args['header'] : $title,
        ]);
        return $btn;
    }
    
    static function createDataUrlButton($icon,$color,$href,$tooltip=null,$id=null,array $args=[])
    {
        $href= is_array($href) ? url_from_array($href) : $href;
        $args['data-url']=$href;
        return self::create($icon,$color,$tooltip,$id,null,$args);
    }
     
    static function createModuleSettingsButton($href,$icon=null, $color=null,$tooltip=null)
    {
        $icon=$icon==null ? 'fas fa-cogs' : $icon;
        $color=$color==null ? 'secondary' : $color;
        $tooltip=$tooltip==null ? 'system.buttons.module_settings' : $tooltip;
        if ($href instanceof \EMPORIKO\Controllers\BaseController)
        {
            $href=$href->getModuleSettingsUrl();
        }
        return self::createDataUrlButton($icon,$color,$href,$tooltip);
    }
    
    static function createBackButton($href,string $tooltip='system.buttons.back',$color=null,array $args=[])
    {
        $color=$color==null ? 'dark' : $color;
        return self::create('fas fa-arrow-alt-circle-left',$color,$tooltip,null,$href,$args);
    }
    
    static function createModalStarter($modal_id,$icon=null,$color=null,$tooltip=null,$id=null,array $args=[])
    {
        $args['onclick']="$('#".$modal_id."').modal('show');";
        return self::create($icon, $color, $tooltip, $id, null, $args);
    }
    
    static function createNewButton($href,$id=null,$tooltip=null,array $args=[])
    {
        if (is_array($href) && array_key_exists(0, $href)&& is_a($href[0], '\EMPORIKO\Controllers\BaseController'))
        {
            $href=url($href[0],$href[1],['new'],['refurl'=> current_url(FALSE,TRUE)]);
        }
        return self::create('fa fa-plus', 'dark', $tooltip==null ? 'system.buttons.new':$tooltip, $id, $href, $args);
    }
    
    static function createEditButton($href,$id=null,$tooltip=null,array $args=[])
    {
        if (is_array($href) && array_key_exists(0, $href) && count($href)>2 && is_a($href[0], '\EMPORIKO\Controllers\BaseController'))
        {
            $href[2]= is_array($href[2]) ? $href[2] : [$href[2]];
            $href=url($href[0],$href[1],$href[2],['refurl'=> current_url(FALSE,TRUE)]);
        }
        return self::create('fa fa-edit', 'primary', $tooltip==null ? 'system.buttons.edit_details':$tooltip, $id, $href, $args);
    }
    
    static function createDropDownButton(string $icon,string $color,string $tooltip,array $items,$id=null,array $args=[])
    {
        $btn=new ToolbarButton();
        $btn->setAsDropDownButton($items);
        
        if ($icon!=null)
        {
            $btn->addArg('icon',$icon);
        }
        
        if ($color!=null)
        {
            $btn->setButtonColor($color);
        }
        
        if ($tooltip!=null)
        {
            $btn->setTooltip($tooltip);
        }
        
        if ($id==null)
        {
            $id='id_toolbarbtn_'.(rand(1,31101987));
        }
        $btn->setID($id);
        
        if (count($args)>0)
        {
            $btn->setArgs($args);
        }
        return $btn;
    }
    
    static function createFromArray(array $args)
    {
        return _self::create(
                array_key_exists('icon',$args) ? $args['icon'] : null,
                array_key_exists('color',$args) ? $args['color'] : null,
                array_key_exists('tooltip',$args) ? $args['tooltip'] : null,
                array_key_exists('id',$args) ? $args['id'] : null,
                array_key_exists('href',$args) ? $args['href'] : null,
                array_key_exists('args',$args) ? $args['args'] : []
        );
    }
    
    static function create($icon=null,$color=null,$tooltip=null,$id=null,$href=null,array $args=[])
    {
        $btn=new ToolbarButton();
        if ($href=='label:' || $href=='-')
        {
            $btn->setAsLabel();
        }else
        if ($href!=null)
        {
            $btn->setHref($href);
        }else
        {
            $btn->setAsButton();
        }
        
        if ($icon!=null)
        {
            $btn->setButtonIcon($icon);
        }
        
        if ($color!=null)
        {
            $btn->setButtonColor($color);
        }
        
        if ($tooltip!=null)
        {
            $btn->setTooltip($tooltip);
        }
        
        if ($id==null)
        {
            $id='id_toolbarbtn_'.(rand(1,31101987));
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
        $this->addArg('content','',TRUE); 
    }
    
    /**
     * Set item as button
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setAsButton()
    {
        $this->_type='button';
        return $this;
    }
    
    /**
     * Set item as drop down button
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setAsDropDownButton(array $items)
    {
        $this->_type=$items;
        return $this;
    }
    
    /**
     * Set item as link
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setAsLink()
    {
        $this->_type='link';
        return $this;
    }
    
    /**
     * Set item as label
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setAsLabel()
    {
        $this->_type='label';
        return $this;
    }
    
    /**
     * Set item tool tip
     * 
     * @param  array|string $data
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setTooltip($data)
    {
        if ($this->_type=='label' && is_string($data))
        {
            return $this->addArg('label_text', $data);
        }
        
        if (is_array($data) && Arr::KeysExists(['placement','title'], $data))
        {
            $this->_tooltip=[];
            $this->_args['data-placement']=$data['placement'];
            $this->_tooltip['data-placement']=$data['placement'];
            $this->_args['title']=lang($data['title']);
            $this->_tooltip['title']=$data['title'];
            $this->_args['data-toggle']='tooltip';
            $this->_tooltip['data-toggle']=$data['tooltip'];
        }else
        if (is_string($data))
        {
            $this->_tooltip=[];
            $this->_args['data-placement']='top';
            $this->_args['title']=lang($data);
            $this->_args['data-toggle']='tooltip';
            $this->_tooltip['data-placement']='top';
            $this->_tooltip['title']=lang($data);
            $this->_tooltip['data-toggle']='tooltip';
        } 
        
        return $this;
    }
    
    /**
     * Set button class (ie. danger, warning etc)
     * 
     * @param string $color
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
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
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setButtonIcon($icon)
    {
        $this->_icon= html_fontawesome($icon);
        return $this;
    }
    
    /**
     * Set item link target argument
     * 
     * @param mixed $href
     * @param bool  $addToButton
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setHref($href,bool $addToButton=FALSE)
    {
        $href= is_array($href) ? url_from_array($href) : $href;
        if ($addToButton)
        {
           $this->setAsButton();
           $this->addArg('data-url', $href,TRUE); 
        } else 
        {
            $this->setAsLink();
            $this->addArg('href', $href,TRUE);
        }
        return $this;
    }
    
    /**
     * Set modal body if modal starter button type
     * 
     * @param array|string $body
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton
     */
    function setModalBody($body)
    {
        if (is_array($body) && Arr::KeysExists(['file','data'], $body))
        {
            $body=view($body['file'],$body['data']);
        }
        
        if (is_string($body))
        {
            $this->addArg('_modal_body', $body);
        }
        return $this;
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
            if ($key=='modal_body')
            {
                $this->setModalBody($value);
            }else
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
     * Render to HTML tag
     * 
     * @return string
     */
    function render() 
    {
       $this->getFlatClass(TRUE);
        if ($this->isReadOnly())
        {
            return 'null';
        }
        if (html_isMobile())
        {
            $html=$this->_icon;
            $html.='&nbsp;&nbsp;';
            $html.=lang($this->getArgs('title'));
            return form_label($html);
        }else
        if (is_array($this->_type))
        {
            if (!$this->isArgExists('mode'))
            {
                $this->addArg('mode', 'dropdown');
            }
            $this->addArg('icon', html_fontawesome($this->getArgs('icon'), is_array($this->_tooltip) ? $this->_tooltip : []));
            return view('System/Elements/button_dropdown',['args'=>$this->_args,'items'=>$this->_type]);
        }else
        if ($this->_type=='label')
        {
            return form_label(lang($this->getArgs('label_text')));
        }else
        if ($this->_type=='button')
        {
            $this->addArg('type', 'button',TRUE);
            $this->_args['content']=$this->_icon;
            if (array_key_exists('text', $this->_args))
            {
               $this->_args['content'].=lang($this->_args['text']); 
            }
            $html='';
            if ($this->isArgExists('_print_btn'))
            {
                $html=view('System/print_script',$this->_args['_print_btn']);
                unset($this->_args['_print_btn']);
            }
            if ($this->isArgExists('_modal_body'))
            {
                $html.=$this->_args['_modal_body'];
                unset($this->_args['_modal_body']);
            }
            return $html.form_button($this->getArgs());
        }else
        {
            $this->_args['content']=$this->_icon;
            if (array_key_exists('text', $this->_args))
            {
               $this->_args['content'].=lang($this->_args['text']); 
            }
            return url_tag($this->getArgs('href'),$this->_args['content'],$this->getArgs());
        }
    }
}