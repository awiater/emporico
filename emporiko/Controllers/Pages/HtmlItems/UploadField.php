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

class UploadField extends HtmlItem
{
    public $_viewname='System/Elements/image_picker';
    
    static function create()
    {
        return new UploadField();
    }
    
    function __construct() 
    {
        parent::__construct();
        $this->addArg('protect', FALSE);
        $this->addArg('value', null);
        $this->setUploadDir('@upload');
    }
    
    /**
     * Set upload file format as CSV
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setAsCSV()
    {
        return $this->setFormat('.csv');
    }
    
    /**
     * Set upload file format as Excel (xlsx)
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setAsEXCEL()
    {
        return $this->setFormat('.xlsx');
    }
    
    /**
     * Set upload file format as PDF
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setAsPDF()
    {
        return $this->setFormat('.pdf');
    }
    
    /**
     * Set upload file format
     * 
     * @param  string $format
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setFormat($format)
    {
        $mimes=config('Mimes')::$mimes;
        
        if (is_string($format) && strtolower($format)=='csv')
        {
            return $this->addArg('accept', '.csv',FALSE);
        }else
        if (is_string($format) && strtolower($format)=='pdf')
        {
            return $this->addArg('accept', '.pdf',FALSE);
        }
            
        if (is_string($format) && (Str::contains($format, '.') || array_key_exists($format, $mimes)))
        {
            if (array_key_exists($format, $mimes))
            {
                $format=$mimes[$format];
            }else
            {
                $format=[$format];
            }
        }
        if (is_array($format))
        {
            $arr=[];
            foreach ($format as $value)
            {
                if (array_key_exists($value, $mimes))
                {
                   $arr[]= is_array($mimes[$value]) ? implode(',',$mimes[$value]) : $mimes[$value];
                }else
                if (Str::startsWith($value, '.'))
                {
                    $arr[]=$value;
                }
            }
            $format=implode(',', $arr);
        }
        return $this->addArg('accept', $format,FALSE);
    }
    
    
    /**
     * Set item value
     * 
     * @param type $value
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setValue($value)
    {
        if (Str::isJson($value))
        {
            $value= json_decode($value,TRUE);
            if (is_array($value))
            {
                $value=$value[0];
            }else
            {
                $value=null;
            }
        }
        return parent::setValue($value);
    }
    
    /**
     * Set custom file name for uploaded file
     * 
     * @param string $fileName
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setFileName(string $fileName)
    {
        return $this->addArg('_upload_filename', $fileName);
    }
    
    /**
     * Set picker to use original name for uploads
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setFileNameAsOriginalName()
    {
        return $this->addArg('_upload_filename', '@');
    }
    
    /**
     * Set custom upload dir
     * 
     * @param string $path
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setUploadDir(string $path)
    {
        
        if (is_dir(parsePath($path,TRUE)))
        {
            $this->addArg('_uploads_dir', $path);
        }
        return $this;
    }
    
    /**
     * Set upload data driver
     * 
     * @param string $driver
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadButton
     */
    function setDriver(string $driver)
    {
        if (strlen($driver) > 0)
        {
            $this->_args['_driver']=$driver;
            $this->_args['_uploadurl']=model('Settings/SettingsModel')->getUploadDriverData($driver,TRUE);
        }
        return $this;
    }
    
    /**
     * Set storage engine
     * 
     * @param type $engine
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setStorageEngine($engine)
    {
        return $this->addArg('_storage_engine', $engine);
    }
    
    /**
     * Set storage engine as local file system
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setLocalStorageEngine()
    {
        return $this->addArg('_storage_engine', 'local');
    }
    
    /**
     * Set storage engine as one drive
     * 
     * @param string $token
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setOneDriveStorageEngine(string $token=null)
    {
        return $this->addArg('_storage_engine',new \OneDrive\StorageEngine($token));
    }
    
    /**
     * Set upload value to be just filename
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setAsJustFileName()
    {
        return $this->addArg('_export_justname', TRUE);
    }
    
    /**
     * Set item parameters
     * 
     * @param  array $args
     * 
     * @return EMPORIKO\Controllers\Pages\HtmlItems\UploadField
     */
    function setArgs(array $args)
    {
        foreach ($args as $key => $value) 
        {
            if ($key=='class')
            {
                $this->addClass($value);
            }else
            if ($key=='export_justname')
            {
                $this->setAsJustFileName();
            }else
            if ($key=='storage_engine')
            {
                if (is_string($value))
                {
                    if (Str::startsWith($value, 'onedrive'))
                    {
                        $this->setOneDriveStorageEngine(Str::contains($value, ':') ? Str::afterLast($value, ':'): null);
                    }else
                    {
                        $this->setStorageEngine($value);
                    }
                }
            }else
            if ($key=='upload_driver')
            {
                $this->setDriver($value);
            }else
            if ($key=='upload_dir')
            {
                $this->setUploadDir($value);
            }else
            if ($key=='filename')
            {
                $this->setFileName($value);
            }else
            if ($key=='accept')
            {
                $this->setFormat($value);
            }else
            {
                $this->addArg($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Render picker to HTML tag
     * 
     * @return string
     */
    function render()
    {
       $this->getFlatClass(TRUE);
       return view('System/Elements/upload',['name'=>$this->_args['name'],'items'=>$this->_args['value'],'args'=>$this->_args]);
    }
}