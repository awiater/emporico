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

class ImagePicker extends HtmlItem
{
    public $_viewname='System/Elements/image_picker';
    
    private $_direct=TRUE;
    
    static function create()
    {
        return new ImagePicker();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->addArg('protect', FALSE);
        $this->setFileNameAsOriginalName();
    }
    
    /**
     * Set picker width
     * 
     * @param type $size
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setWidth($size)
    {
        $size= is_numeric($size) ? $size.'px' : $size;
        return $this->addArg('width', $size,FALSE);
    }
    
    /**
     * Set picker width
     * 
     * @param type $size
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setHeight($size)
    {
        $size= is_numeric($size) ? $size.'px' : $size;
        return $this->addArg('height', $size,FALSE);
    }
    
    /**
     * Set picker to upload directly to storage
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setAsDirect()
    {
        $this->_direct=TRUE;
        return $this;
    }
    
    /**
     * Set picker to open image manager before you can upload
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setAsWizard()
    {
        $this->_direct=FALSE;
        return $this;
    }
    
    /**
     * Set width and height of preview image as auto
     * 
     * @param mixed $maxwidth
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setAutoSize($maxwidth=null)
    {
        if ($maxwidth!=null)
        {
            $maxwidth= is_numeric($maxwidth) ? $maxwidth.'px' : $maxwidth;
            $this->addArg('maxwidth', $maxwidth);
        }
        return $this->setWidth('auto')->setHeight('auto');
    }
    
    /**
     * Set picker to return just path to file
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setJustFileNameOption()
    {
        return $this->addArg('_export_justname', TRUE);
    }
    
    /**
     * Specify name of file after upload
     * 
     * @param string $fileName
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setFileName($fileName)
    {
        return $this->addArg('_upload_filename', $fileName);
    }
    
    /**
     * Set picker to use original name for uploads
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setFileNameAsOriginalName()
    {
        return $this->addArg('_upload_filename', '@');
    }
    
    /**
     * Specify upload dir path
     * 
     * @param string $path
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setUploadDir($path)
    {
        return $this->addArg('_uploads_dir', $path);
    }
    
    /**
     * Set images format
     * 
     * @param  string $format
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setFormat($format)
    {
        $arr=config('Mimes')::$mimes;
        if (Str::contains($format, '.') || array_key_exists($format, $arr))
        {
            if (array_key_exists($format, $arr))
            {
                $format=$arr[$format];
            }
        }else
        {
            $format=$arr['images'];
        }
        return $this->addArg('format', $format,FALSE);
    }
    
    /**
     * Determines if image preview is visible
     * 
     * @param  type $visible
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setImagePreview($visible)
    {
        return $this->addArg('noImage',!$visible,FALSE);
    }
    
    
    /**
     * Determines if image viewer is available
     * 
     * @param  type $visible
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setImageViewer($visible)
    {
        return $this->addArg('viewer',$visible,FALSE);
    }
    
    /**
     * Set ImageViewer options
     * 
     * @param  bool $print
     * @param  bool $download
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setImageViewerOptions($print=TRUE,$download=TRUE)
    {
        return $this->addArg('viewer_print', $print,FALSE)->addArg('viewer_download', $download,FALSE);
    }
    
    /**
     * Determines if image viewer is avaliable
     * 
     * @return bool
     */
    function istImageViewer()
    {
        return $this->isArgExists('viewer');
    }
    
    /**
     * Changing image to base64 string
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setImagePathAsObscure()
    {
        return $this->addArg('', TRUE);
    }  
    
    /**
     * Set item value
     * 
     * @param type $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setValue($value)
    {
        if ($value==null)
        {
            $this->setImagePreview(FALSE);
        }
        return parent::setValue($value);
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='protect' && $value)
            {
                $this->setImagePathAsObscure();
            }else
            {
                $this->addArg($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Render picker to html
     * 
     * @return string
     */
    function render()
    {
        $this->getFlatClass(TRUE);
        if (!$this->isArgExists('viewer'))
        {
            $this->setImageViewer(FALSE);
        }
        $this->_args['format']= is_array($this->_args['format']) ? implode(',',$this->_args['format']) : $this->_args['format'];
        if (array_key_exists('protect', $this->_args) && $this->_args['protect'])
        {
            $this->_args['value']= Str::resourceToBase64($this->_args['value']);
        }else
        {
            $this->_args['value']=parsePath($this->_args['value']);
        }
        $this->_args['_direct']=$this->_direct;
        if (!$this->isArgExists('_uploads_dir'))
        {
            $this->_args['_uploads_dir']='@storage/files/images';
        }
        $this->_args['_start_dir']=dirname($this->_args['_uploads_dir']);
        $this->_args['_files']=mapDir($this->_args['_uploads_dir'],['.jpg','.jpeg','.bmp','.png','.ico','.gif']);
        return view($this->_viewname,$this->_args);
    }
    
    
}