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

class FilePicker extends HtmlItem
{
    public $_viewname='System/Elements/file_picker';
    
    private $_source=[];
    
    private $_format='*';
    
    static function create()
    {
        return new FilePicker();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->showAllFiles();
    }

    
    /**
     * Set images format
     * 
     * @param  string $format
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\FilePicker
     */
    function setFileFormat($format)
    {
        if ($format=='*')
        {
            $format=[];
            goto set_format;
        }
        $arr=config('Mimes')::$mimes;
        if (Str::contains($format, '.') || array_key_exists($format, $arr))
        {
            if (array_key_exists($format, $arr))
            {
                $format=$arr[$format];
            }
        }else
        {
            $format=[];
        }
        set_format:
        return $this->_format=$format;
    }
    
    /**
     * Set files format to all files
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\FilePicker
     */
    function showAllFiles()
    {
        return $this->setFileFormat('*');
    }
    
    /**
     * Set files format to images files
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\FilePicker
     */
    function showImages()
    {
        return $this->setFileFormat('images');
    }
    
    /**
     * Set dir(files) list source
     * 
     * @param string|array $source
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\FilePicker
     */
    function setSource($source)
    {
        if (is_string($source) && file_exists(parsePath($source,TRUE)))
        {
            $this->_source=parsePath($source,TRUE);
        }else
        if (is_array($source))
        {
            $this->_source=$source;
        }
        return $this;
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
            if ($key=='format')
            {
                $this->setFileFormat($value);
            }else
            if ($key=='source')
            {
                $this->setSource($value);
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
        if (is_string($this->_source) && file_exists($this->_source))
        {
            //$this->_source= $this->directory_map($this->_source,$this->_format);
        }
        $this->_source=[];
        $this->addArg('dir_list', $this->_source);
        return view($this->_viewname,['args'=>$this->_args]);
    }
    
    function directory_map(string $sourceDir,array $filters, int $directoryDepth = 0, bool $hidden = false)
    {
		try
		{
			$fp = opendir($sourceDir);

			$fileData  = [];
			$newDepth  = $directoryDepth - 1;
			$sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

			while (false !== ($file = readdir($fp)))
			{
				// Remove '.', '..', and hidden files [optional]
				if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.'))
				{
					continue;
				}

				is_dir($sourceDir . $file) && $file .= DIRECTORY_SEPARATOR;
                                $mime=mime_content_type($sourceDir.$file);
                                if ($mime=='directory' || (count($filters) < 1) || (count($filters) > 0 && in_array($mime,$filters)))
                                {
				if (($directoryDepth < 1 || $newDepth > 0) && is_dir($sourceDir . $file))
				{
					$fileData[$file] = $this->directory_map($sourceDir . $file,$filters, $newDepth, $hidden);
				}
				else
				{
					$fileData[] = ['file'=>$file,'path'=>$sourceDir.$file];
				}
                                }
			}

			closedir($fp);
			return $fileData;
		}
		catch (Throwable $e)
		{
			return [];
		}
	}
    
    
}