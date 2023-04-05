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

class TinyEditor extends HtmlItem
{
    public $_viewname='System/Elements/editor_field';
    
    private $_placeholders=[];
    
    private $_buttons=[];
    
    static function create()
    {
        return new TinyEditor();
    }  
    
    function __construct() 
    {
        parent::__construct();
        $this->setBasicToolbar();
    }
    
    
    
    /**
     * Set scripts in given view
     * 
     * @param View $view
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function getScripts()
    {
        return view('System/tinymce',
                [
                    'id'=>$this->getArgs('editortag'),
                    'tinytoolbar'=>$this->getArgs('toolbar'),
                    'height'=>$this->getArgs('height'),
                    'language'=>config('APP')->defaultLocale,
                    'args'=>$this->_args
                ]);
    }
    
    /**
     * Set editor tag used for init
     * 
     * @param string $tag
     * @param bool $override
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setEditorTag($tag,$override=TRUE)
    {
        return $this->addArg('editortag', $tag,$override);
    }
    
    /**
     * Set editor toolbar as simple
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setSimpleToolbar()
    {
        return $this->addArg('toolbar','simple');
    }
    
    function addCustomButton(string $name,$text,$icon,string $action,bool $isTextAct=TRUE,string $tooltip=null,bool $enabled=TRUE)
    {
       $button=[];
       if ($text!=null && strlen($text) > 0)
       {
           $button['text']=$text;
       }
       
       if ($icon!=null && strlen($icon) > 0)
       {
           $button['icon']=$icon;
       }
       
       if ($tooltip!=null && strlen($tooltip) > 0)
       {
           $button['tooltip']=$tooltip;
       }
       if ($action!=null && strlen($action) > 0)
       {
           if ($isTextAct)
           {
               $button['action_text']="'$action'";
           } else 
           {
               $button['action']=$action;
           }
           
       }
       $this->_buttons[]=$button;
       return $this;
    }
    
    /**
     * Set editor toolbar as email or emailext
     * 
     * @param bool $extended
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setEmailToolbar($extended=FALSE)
    {
        if ($extended)
        {
            return $this->addArg('toolbar','emailext');
        }else
        {
            return $this->addArg('toolbar','email');
        }
        
    }
    
    /**
     * Set editor toolbar as full
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setFullToolbar()
    {
        return $this->addArg('toolbar','full');
    }
    
    /**
     * Set editor toolbar as basic
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setBasicToolbar()
    {
        return $this->addArg('toolbar','basic');
    }
    
    /**
     * Set editor height
     * 
     * @param Int $size
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setHeight($size)
    {
        $size= is_numeric($size) ? $size : '200';
        return $this->addArg('height', $size);
    }
    
    /**
     * Set editor field id
     * 
     * @param string $id
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setID($id) 
    {
        parent::setID($id);
        $this->setEditorTag('#'.$this->getArgs('id'),FALSE);
        return $this;
    }
    
    /**
     * Set editor value
     * 
     * @param string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setValue($value)
    {
        if (is_string($value) && file_exists(parsePath($value,TRUE)))
        {
            $value= file_get_contents(parsePath($value,TRUE));
        }
        return parent::setValue($value);
    }
    
    /**
     * Add placeholder to editor placeholder list
     * 
     * @param string $name
     * @param string $toolip
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function addEditPlaceHolder(string $name,string $toolip)
    {
        return $this->addCustomButton('cusInsBtn'.(count($this->_buttons)), $toolip, 'sharpen', $name);
    }
    
    /**
     * Set editor placeholder list
     * 
     * @param array $placeholders
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor
     */
    function setEditPlaceHolders(array $placeholders)
    {
        foreach($placeholders as $key=>$value)
        {
            $key=!Str::contains($key, '{') ? '{'.$key.'}' : $key;
            $this->addCustomButton('cusInsBtn'.(count($this->_buttons)), $value, 'sharpen', $key);
        }
        return $this;
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
            $key= strtolower($key);
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='placeholders' && is_array($value))
            {
                $this->setEditPlaceHolders($value);
            }else
            if ($key=='editortag')
            {
                $this->setEditorTag($value);
            }else
            if ($key=='toolbar' && is_string($value))
            {
               $this->setToolbarFromString($value);
            }else
            if ($key=='toolbar_buttons' && is_array($value))
            {
                if (array_key_exists('action_text', $value))
                {
                    if (Str::startsWith($value['action_text'], '#'))
                    {
                        $value['action_text']="atob('".substr($value['action_text'],1)."')";
                    }else
                    {
                        $value['action_text']="'".$value['action_text']."'";
                    }
                }
               $this->addArg('tinytoolbar_buttons',$value);
            }else    
            if ($key=='height')
            {
                $this->setHeight($value);
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
    function render($renderScript=TRUE) 
    {
        $this->getFlatClass(TRUE);
        if (count($this->_buttons) > 0)
        {
            $this->addArg('tinytoolbar_buttons',$this->_buttons);
        }
        return view($this->_viewname,['args'=>$this->getArgs(),'placeholders'=>$this->_placeholders,'scripts'=>$this->getScripts()]);
    }
    
    /**
     * Set editor mode from given string
     * 
     * @param string $mode
     */
    private function setToolbarFromString($mode)
    {
        $mode= strtolower($mode);
        if (in_array($mode,['simple','email','emailext','full','basic']))
        {
            $this->addArg('toolbar', $mode);            
        }
    }
    
}