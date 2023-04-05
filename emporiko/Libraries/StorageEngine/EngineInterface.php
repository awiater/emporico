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
interface EngineInterface
{
    public function move($to,$from=null);
    /**
     * Copy default(or from given path) file to specified location 
     * 
     * @param  string $to
     * @param  string $from
     * @return boolean
     */
    public function copy($to,$from=null);
    public function delete($from=null);
    public function upload($uploads_dir,$file,$newFileName = null,array $uploadOptions=[]);
    public function createDir($to);
    public function setFile($file);
    public function getFile($path);
    public function fileExists($from=null);
    public function getRelativePath($from=null);
    public function mapDir($dir=null);
    public function DownloadFile($from=null,string $refurl=null);
}
