<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Libraries\StorageEngine;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class StorageEngine implements EngineInterface
{
    private $_file;
    
    public static function init($storageEngine=null,$engineOptions=null)
    {
        $storageEngine=$storageEngine==null ? config('Storage')->storageEngine :$storageEngine;
        if ($storageEngine=='local')
        {
            local_engine:
            return new StorageEngine();
        }
        if (!Str::startsWith($storageEngine,'\\'))
        {
            $storageEngine='\\'.$storageEngine;
        }
        
        if (!class_exists($storageEngine))
        {
            goto local_engine;
        }
        $storageEngine=new $storageEngine($engineOptions);
        if (!is_subclass_of($storageEngine, '\EMPORIKO\Libraries\StorageEngine\EngineInterface'))
        {
             goto local_engine;
        }
        return $storageEngine;
    }
    
    /**
     * Determines if default(or from given path) file exists
     * 
     * @param  string $from
     * @return bool
     */
    public function fileExists($from=null)
    {
        $from=$this->getFrom($from,FALSE);
        return file_exists($from);
    }
    
    /**
     * Copy default(or from given path) file to specified location 
     * 
     * @param  string $to
     * @param  string $from
     * @return boolean
     */
    public function copy($to, $from = null) 
    {
        $from=$this->getFrom($from,TRUE);
        $to= parsePath($to,TRUE);
        if (is_dir($to))
        {
            return copy($from, $to);
        }
        return FALSE;
    }
    
    /**
     * Create new dir in location
     * 
     * @param  string $to
     * @return $this
     * @throws \Exception
     */
    public function createDir($to) 
    {
        if (!is_string($to))
        {
            throw new \Exception('Invalid path');
        }
        mkdir($to);
        return $this;
    }
    
    /**
     * Delete default(or from given path) file or dir
     * 
     * @param type $from
     * @return boolean
     */
    public function delete($from = null) 
    {
        $from=$this->getFrom($from,TRUE);
        
        if (is_dir($from))
        {
            $from=$this->_file->getRealPath();
            delete_files($from, true);
            rmdir($from);
            return TRUE;
        }else
        if (file_exists($from))
        {
           return unlink($from);
        }
        return FALSE;
    }
    
    /**
     * Move default(or from given path) file to new location
     * 
     * @param  mixed $to
     * @param  mixed $from
     * @return bool
     */
    public function move($to, $from = null) 
    {
        if ($from!=null)
        {
            $from=$this->set($from);
        }
        $to=$this->getFrom($to,TRUE);
        return $this->_file->move($to);
    }
    
    /**
     * Upload default(or from given path) file to storage 
     * 
     * @param  string $uploads_dir
     * @param  string $newFileName
     * @param  string $from
     * @return boolean
     */
    public function upload($uploads_dir,$from,$newFileName = null,array $uploadOptions=[]) 
    {
        $uploads_dir=parsePath($uploads_dir . DIRECTORY_SEPARATOR, TRUE);
        
        $from=$from==null ? $this->_file : $from;
        if (is_bool($newFileName) && $newFileName==TRUE)
        {
            $newFileName = $from->getRandomName();
        }else
        if ($newFileName==null)
        {
            $newFileName = $from->getClientName();
        }
        $oFilePath =parsePath('@upload/'.$newFileName,TRUE);
        
        $from->store('.', $newFileName);
        
        if (!file_exists($oFilePath))
        {
            return FALSE;
        }
        if ($uploads_dir==parsePath('@upload',TRUE))
        {
            goto setfile;
        }
        if ($this->setFile($oFilePath)->move($uploads_dir)) 
        {
            setfile:
            $nfilePath = $uploads_dir. ($newFileName);
            $this->setFile($nfilePath);
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Returns instance of \CodeIgniter\Files\File
     * 
     * @param  mixed $from
     * @return \CodeIgniter\Files\File
     */
    public function getFile($from=null) 
    {
        if ($from!=null)
        {
            $from=$this->setFile($from);
        }
        return $this->_file;
    }
    
    /**
     * Download file
     * 
     * @param string $from
     */
    public function DownloadFile($from=null,string $refurl=null)
    {
        header('Content-Type: application/octet-stream');
        ob_clean();
        flush();
        readfile($this->getFile()->getPath());
    }
    
    /**
     * Set default file
     * 
     * @param  mixed $file
     * @return $this
     * @throws \Exception
     */
    public function setFile($file) 
    {
        if (is_string($file))
        {
            if (!file_exists(parsePath($file,TRUE)))
            {
                return FALSE;
            }
            $this->_file=new \CodeIgniter\Files\File(parsePath($file,TRUE));
            return $this;
        }
        if (is_subclass_of($file, '\CodeIgniter\HTTP\Files\UploadedFile')||is_subclass_of($file, '\CodeIgniter\Files\File'))
        {
            $this->_file=$file;
            return $this;
        }
        
        throw new \Exception('Invalid file type');
    }
    
    /**
     * Returns relative path to default(or from given path) file
     * @param  type $from
     * @return type
     */
    public function getRelativePath($from = null) 
    {
        if ($from==null)
        {
            $from=$this->getFrom($from,TRUE);
        }
        return str_replace(FCPATH, '@/', $this->_file->getRealPath());    
    }
    
    public function mapDir($dir = null) 
    {
        $dir=$dir==null ? config('Storage')->deffolderid : $dir;
        if (!is_dir(parsePath($dir,TRUE)))
        {
            $dir='@';
        }
        $arr=[];
        foreach(directory_map(parsePath($dir,TRUE),1) as $subdir)
        {
           if (Str::endsWith($subdir, '/'))
           {
               $arr[$dir.'/'.$subdir]=Str::before($subdir, '/');
           }
        }
        return $arr;
    }
    
    private function getFrom($from,$checkExists=FALSE)
    {
        if ($from!=null && !is_string($from))
        {
            $this->set($from);
        }
        
        if ($from==null)
        {
            if ($this->_file==null)
            {
                return null;
            }
            $from=$this->_file->getRealPath();
        }
        
        $from=parsePath($from,TRUE);
        
        if ($checkExists && !file_exists($from))
        {
            throw new Exception('File not exists');
        }
        return $from;
    }

    

}

