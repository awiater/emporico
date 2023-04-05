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

class CodeEditor extends HtmlItem
{
    public $_viewname;
    
    private $_view=FALSE;
    
    private $_theme='@vendor/codemirror/theme/blackboard.css';
    
    private $_mode='xml';
    
    static function create()
    {
        return new CodeEditor();
    }  
    
    function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * Set scripts in given view
     * 
     * @param View $view
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setScripts(&$view)
    {
        if (!file_exists(parsePath($this->_theme,TRUE)))
        {
            $this->_theme='@vendor/codemirror/theme/blackboard.css';
        }
        $_theme=$this->getThemeName();
        $this->_view=TRUE;
        
        $script='CodeMirror.fromTextArea(document.getElementById("';
        $script.=$this->getArgs('id');
        $script.='"), {lineNumbers: true,mode : "';
        $script.=$this->_mode;
        $script.='",htmlMode: true,theme:"';
        $script.=$_theme;
        $script.='"';
        if ($this->isReadOnly())
        {
            $script.=',readOnly: true';
        }
        $script.='});';
        $view->addScript('codemirror_js','@vendor/codemirror/lib/codemirror.js')
             ->addCss('codemirror_css','@vendor/codemirror/lib/codemirror.css')
             ->addCss('codemirror_theme_css',$this->_theme)
             ->addScript('codemirror_mode_js','@vendor/codemirror/mode/'.($this->_mode).'/'.($this->_mode).'.js')
	     ->addCustomScript('_codeeditor_script',$script,TRUE);
        return $this;
    }
    
    /**
     * Set editor value
     * 
     * @param string $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
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
     * Set editor theme
     * 
     * @param string $name
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setTheme($name)
    {
        $name=Str::endsWith($name, '.css') ? $name : $name.'.css';
        $exists=FALSE;
        if (file_exists(parsePath($name,TRUE)))
        {
            $exists=true;
        }
        
        if (file_exists(parsePath('@vendor/codemirror/theme/'.$name,TRUE)))
        {
            $name='@vendor/codemirror/theme/'.$name;
            $exists=true;
        }
        if (!$exists)
        {
            return $this;
        }
        $this->_theme=$name;
        return $this;
    }
    
    /**
     * Set editor mode to CSS
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setModeAsCss()
    {
        $this->_mode='css';
        return $this;
    }
    
    /**
     * Set editor mode to XML
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setModeAsXml()
    {
        $this->_mode='xml';
        return $this;
    }
    
    /**
     * Set editor mode to Twig
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setModeAsTwig()
    {
        $this->_mode='twig';
        return $this;
    }
    
    /**
     * Set editor mode to PHP
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setModeAsPHP()
    {
        $this->_mode='php';
        return $this;
    }
    
    /**
     * Set editor mode to XML
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\CodeEditor
     */
    function setModeAsView()
    {
        return $this->setModeAsXml();
    }
    
    /**
     * Returns array with available editor modes
     * 
     * @return array
     */
    function getAvaliableModes()
    {
        return ['xml','php','view','css','twig'];
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
            if ($key=='theme')
            {
                $this->setTheme($value);
            }else
            if ($key=='mode' && is_string($value))
            {
               $this->setModeFromString($value);
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
        return TextAreaField::create()->setArgs($this->getArgs())->render();
    }
    
    /**
     * Set editor mode from given string
     * 
     * @param string $mode
     */
    private function setModeFromString($mode)
    {
        $mode= strtolower($mode);
        if (in_array($mode,$this->getAvaliableModes()))
        {
            if ($mode=='view')
            {
                $this->setModeAsView();
            } else 
            {
              $this->_mode=$mode;  
            }
            
        }
    }
    
    /**
     * Returns current theme name
     * 
     * @return string
     */
    private function getThemeName()
    {
        $theme=Str::afterLast($this->_theme, '/');
        $theme=Str::before($theme, '.');
        return $theme;
    }
}