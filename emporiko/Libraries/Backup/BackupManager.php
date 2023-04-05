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
 
namespace EMPORIKO\Libraries\Backup;

use EMPORIKO\Helpers\Strings as Str;

class BackupManager
{
    private $_db;
    
    private $_options=[];
    
    private $_includedTables=[];
    
    private $_excludedTables=[];
    
    function __construct()
    {
        $this->_db=\Config\Database::connect();
    }
    
    static function init()
    {
        return new BackupManager();
    }
    
    
    /**
     * Enable tables structure dump
     * 
     * @return $this
     */
    function getTablesStructure()
    {
        $this->_options[1]='_getTablesStructure';
        return $this;
    }
    
    /**
     * Enable views structure dump
     * 
     * @return $this
     */
    function getViewsStructure()
    {
        $this->_options[2]='_getViewsStructure';
        return $this;
    }
    
    /**
     * Enable procedures structure dump
     * 
     * @return $this
     */
    function getProceduresStructure()
    {
        $this->_options[3]='_getProceduresStructure';
        return $this;
    }
    
    /**
     * Enable functions structure dump
     * 
     * @return $this
     */
    function getFunctionsStructure()
    {
        $this->_options[4]='_getFunctionsStructure';
        return $this;
    }
    
    /**
     * Enable data values dump
     * 
     * @param array $tablesToInclude
     * @param array $tablesToExclude
     * 
     * @return $this
     */
    function getData(array $tablesToInclude=[],array $tablesToExclude=[])
    {
        $this->_options[5]=['_getData',[$tablesToInclude,$tablesToExclude]];
        return $this;
    }
    
    /**
     * Disable Foreign Keys Check
     * 
     * @return $this
     */
    function disableForeignKeysCheck()
    {
        $this->_options['FOREIGN_KEY_CHECKS']=0;
        return $this;
    }
    
    /**
     * Enable Foreign Keys Check
     * 
     * @return $this
     */
    function enableForeignKeysCheck()
    {
        $this->_options['FOREIGN_KEY_CHECKS']=1;
        return $this;
    }
    
    function enableTransaction()
    {
        $this->_options['USE_TRANSACTIONS']=TRUE;
        return $this;
    }
    
    /**
     * Dump table to string
     * 
     * @return string
     */
    function dump()
    {
        $content='-- BackupManager SQL Dump'.PHP_EOL;
        $content.='-- version 1.0'.PHP_EOL;
        $content.='--'.PHP_EOL;
        $content.='-- Generation Time:'. formatDate('now','d M Y @ H:i').PHP_EOL;
        $content.='-- PHP Version: '. phpversion().PHP_EOL.PHP_EOL;;
        if (array_key_exists('FOREIGN_KEY_CHECKS',$this->_options))
        {
            $content.='SET FOREIGN_KEY_CHECKS='.$this->_options['FOREIGN_KEY_CHECKS'].';'.PHP_EOL;
        }
        
        if (array_key_exists('USE_TRANSACTIONS',$this->_options))
        {
            $content.='SET AUTOCOMMIT = 0;'.PHP_EOL;
            $content.='START TRANSACTION;'.PHP_EOL;
        }
        $content.=PHP_EOL.'--'.PHP_EOL.'-- Database: '.$this->_getSchemaInfo().PHP_EOL.'--'.PHP_EOL.PHP_EOL;
        foreach($this->_options as $option)
        {
            if (is_string($option) && method_exists($this, $option))
            {
                $content.=$this->{$option}();
            }else
            if (is_array($option) && method_exists($this, $option[0]))
            {
                $content.= call_user_func_array([$this,$option[0]], $option[1]);
            } 
        }
        if (array_key_exists('USE_TRANSACTIONS',$this->_options))
        {
            $content.='COMMIT;'.PHP_EOL;
        }
        return $content;
    }
    
    /**
     * Dumps database to given file
     * 
     * @param string $filePath
     * @param bool   $compress
     */
    function dumpToFile(string $filePath,bool $compress=FALSE)
    {
        file_put_contents($filePath,$this->dump());
        if ($compress && file_exists($filePath))
        {
            $dir= dirname($filePath);
            $zipFile=Str::afterLast($filePath, '/');
            $zipFile=$dir.'/'.Str::before($zipFile, '.').'.zip';
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE)
            {
                $zip->addFile($filePath, Str::afterLast($filePath, '/'));
                $zip->close();
            }
            unlink($filePath);
            return $zipFile;
        }
        return $filePath;
    }
    
    /**
     * Returns all tables create SQL command
     * 
     * @param array $tables
     * 
     * @return string
     */
    private function _getTablesStructure(array $tables=[])
    {
        $content='';
        foreach ($this->_getNames('table',$tables) as $row)
        {
            $sql=$this->_getTableCreate($row['name']);
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
            $sql=$this->_getIndexesForTable($row['name']);
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
            
            $sql=$this->_getForeignKeysForTable($row['name']); 
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
        }
      return $content;
    }
    
    /**
     * Returns SQL command for all foreign keys for table
     * 
     * @param string $table
     */
    private function _getForeignKeysForTable(string $table)
    {
        $sql="SELECT REFERENTIAL_CONSTRAINTS.UPDATE_RULE,REFERENTIAL_CONSTRAINTS.DELETE_RULE, KEY_COLUMN_USAGE.COLUMN_NAME,KEY_COLUMN_USAGE.CONSTRAINT_NAME,KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME,KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME";
        $sql.=" FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS ON REFERENTIAL_CONSTRAINTS.CONSTRAINT_NAME=KEY_COLUMN_USAGE.CONSTRAINT_NAME";
        $sql.=" WHERE KEY_COLUMN_USAGE.TABLE_NAME='$table' AND KEY_COLUMN_USAGE.TABLE_SCHEMA='".$this->_getSchemaInfo()."' AND KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME IS NOT NULL";
       
        $sql=$this->_db->query($sql)->getResult('array');
        if (is_array($sql) && count($sql) > 0)
        {
            $content='--'.PHP_EOL;
            $content.="-- Constraints for table `$table`".PHP_EOL;
            $content.='--'.PHP_EOL;
            $content.="ALTER TABLE `$table`".PHP_EOL;
            $max=count($sql)-1;
            foreach($sql as $key=>$index)
            {
            $content.='ADD CONSTRAINT `'.$index['CONSTRAINT_NAME'].'` FOREIGN KEY (`'.$index['COLUMN_NAME'].'`) REFERENCES `'.$index['REFERENCED_TABLE_NAME'].'` (`'.$index['REFERENCED_COLUMN_NAME'].'`) ON DELETE '.$index['DELETE_RULE'].' ON UPDATE '.$index['UPDATE_RULE'].'';
            $content.=$key==$max ? ';' : ',';
            $content.=PHP_EOL;
            }
            return $content;
        }
        return '';
    }
    
    /**
     * Returns SQL command for all indexes for table
     * 
     * @param string $table
     */
    private function _getIndexesForTable(string $table)
    {
        $sql="SELECT COLUMN_NAME,NON_UNIQUE,INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME='$table' AND TABLE_SCHEMA='".$this->_getSchemaInfo()."'";
        $sql=$this->_db->query($sql)->getResult('array');
        if (is_array($sql) && count($sql) > 0)
        {
            $content='--'.PHP_EOL;
            $content.="-- Indexes for table `$table`".PHP_EOL;
            $content.='--'.PHP_EOL;
            $content.="ALTER TABLE `$table`".PHP_EOL;
            $max=count($sql)-1;
            foreach($sql as $key=>$index)
            {
                if ($index['INDEX_NAME']=='PRIMARY')
                {
                    $content.='ADD PRIMARY KEY (`'.$index['COLUMN_NAME'].'`)';
                }else
                if (intval($index['NON_UNIQUE'])==0)
                {
                    $content.='ADD UNIQUE KEY `'.$index['INDEX_NAME'].'` (`'.$index['COLUMN_NAME'].'`)';
                }else
                {
                    $content.='ADD KEY `'.$index['INDEX_NAME'].'` (`'.$index['COLUMN_NAME'].'`)';
                }
                $content.=$key==$max ? ';' : ',';
                $content.=PHP_EOL;
            }
            return $content;
        }
        return '';
    }
    
    /**
     * Returns all views create SQL command
     * 
     * @param array $views
     * 
     * @return string
     */
    private function _getViewsStructure(array $views=[])
    {
        $content='';
        foreach ($this->_getNames('view',$views) as $row)
        {
            $sql=$this->_getViewCreate($row['name'],TRUE,FALSE);
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
        }
        
        foreach ($this->_getNames('view',$views) as $row)
        {
            $sql=$this->_getViewCreate($row['name'],FALSE,TRUE);
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
        }
        
        return $content;
    }
    
    /**
     * Returns all functions create SQL command
     * 
     * @return string
     */
    private function _getFunctionsStructure()
    {
        $content='';
        foreach ($this->_getNames('function') as $row)
        {
            $sql=$this->_getRoutineCreate($row['name']);
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
        }
      return $content;
    }
    
     /**
     * Returns all procedures create SQL command
     * 
     * @return string
     */
    private function _getProceduresStructure()
    {
        $content='';
        foreach ($this->_getNames('procedure') as $row)
        {
            $sql=$this->_getRoutineCreate($row['name'],FALSE);
            if ($sql!=null)
            {
               $content.=$sql.PHP_EOL.PHP_EOL; 
            }
        }
      return $content;
    }
    
    /**
     * Get data for tables
     * 
     * @param array $includedTables
     * @param array $excludedTables
     * 
     * @return string
     */
    private function _getData(array $includedTables=[],array $excludedTables=[])
    {
        $content='';
        foreach ($this->_getNames('table',$includedTables) as $row)
        {
            if (count($excludedTables)==0 || (count($excludedTables) > 0 && !in_array($row['name'], $excludedTables)))
            {
                $columns=$this->_getTableColumns($row['name']);
                if (count($columns) >0)
                {
                  $content.='--'.PHP_EOL;
                  $content.="-- Data for `".$row['name']."`".PHP_EOL;
                  $content.='--'.PHP_EOL;
                  $data=$this->_db->table($row['name'])->select(implode(',',$columns))->get()->getResult('array');
                  foreach($data as $record)
                  {
                      $content.='INSERT INTO `'.$row['name'].'` (`'. implode('`,`', $columns).'`) VALUES('."'".implode("','", $record)."'".');'.PHP_EOL;
                  }
                } 
            } 
        }
      return $content;
    }
    
    private function _getNames(string $type,array $onlyThis=[])
    {
        if (strtolower($type)=='function')
        {
            $sql="SELECT SPECIFIC_NAME AS name FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_TYPE='FUNCTION' AND ROUTINE_SCHEMA='".$this->_getSchemaInfo()."'";
            $where='SPECIFIC_NAME';
        }else
        if (strtolower($type)=='procedure')
        {
            $sql="SELECT SPECIFIC_NAME AS name FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_TYPE='PROCEDURE' AND ROUTINE_SCHEMA='".$this->_getSchemaInfo()."'";
            $where='SPECIFIC_NAME';
        }else
        if (strtolower($type)=='view')
        {
            $sql="SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='VIEW' AND TABLE_SCHEMA='".$this->_getSchemaInfo()."'";
            $where='TABLE_NAME';
        }else
        {
           $sql="SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='".$this->_getSchemaInfo()."'";
           $where='TABLE_NAME';
        }
        
        if (count($onlyThis) > 0)
        {
            foreach($onlyThis as $key=>$value)
            {
                $onlyThis[$key]=$where."='$value'";
            }
            $sql.=' AND ('.implode(' OR ',$onlyThis).')';
        }
        return $this->_db->query($sql)->getResult('array');
    }
    
    /**
     * Return database schema name
     * 
     * @return string
     */
    private function _getSchemaInfo()
    {
        return $this->_db->database;
    }
    
    /**
     * Returns array with column names for given table
     * 
     * @param string $table
     * 
     * @return array
     */
    private function _getTableColumns(string $table)
    {
        $table="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$this->_getSchemaInfo()."' AND TABLE_NAME = '$table'";
        $arr=[];
        foreach($this->_db->query($table)->getResult('array') as $tableName)
        {
            $arr[]=$tableName['COLUMN_NAME'];
        }
        return $arr;
    }
    
    /**
     * Returns table create SQL command or null if failed
     * 
     * @param string $table
     * @param bool   $addDrop
     * 
     * @return string
     */
    private function _getTableCreate(string $table,bool $addDrop=TRUE)
    {
        $arr=$this->_db->query('SHOW CREATE TABLE '.$table)->getResult('array');
        $content='--'.PHP_EOL;
        $content.="-- Table structure for table `$table`".PHP_EOL;
        $content.='--'.PHP_EOL;
        if ($addDrop)
        {
           $content.="DROP TABLE IF EXISTS `$table`;".PHP_EOL; 
        }
        if (is_array($arr) && count($arr)>0 && array_key_exists('Create Table', $arr['0']))
        {
            return $content.$arr['0']['Create Table'].';';
        }
        return null;
    }
    
    
    /**
     * Returns view create SQL command or null if failed
     * 
     * @param string $view
     * @param bool   $addDrop
     * 
     * @return string
     */
    private function _getViewCreate(string $view,bool $createTable=FALSE,bool $createView=TRUE)
    {
        $arr=$this->_db->query("SELECT VIEW_DEFINITION AS body FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME='$view' AND TABLE_SCHEMA='".$this->_getSchemaInfo()."'")->getResult('array');
        $cols=$this->_db->query("SHOW COLUMNS FROM `$view`")->getResultArray();
        
        if (is_array($arr) && count($arr)>0 && array_key_exists('body', $arr['0']) && is_array($cols) && count($cols) > 0)
        {
            
            if ($createTable)
            {
                $content='--'.PHP_EOL;
                $content.="-- Table structure for table `$view`".PHP_EOL;
                $content.='--'.PHP_EOL;
                foreach($cols as $key=>$value)
                {
                    $cols[$key]='`'.$value['Field'].'` '.strtoupper($value['Type']);
                }
                $content.="DROP TABLE IF EXISTS `$view`;DROP VIEW IF EXISTS `$view`;".PHP_EOL;
                $content.="CREATE TABLE `$view` (".implode(',',$cols).');'.PHP_EOL.PHP_EOL;
            }
            if ($createView)
            {
                $content='--'.PHP_EOL;
                $content.="-- View structure for view `$view`".PHP_EOL;
                $content.='--'.PHP_EOL;
                $arr[0]['body']= str_replace('`'.$this->_getSchemaInfo().'`.', '', $arr[0]['body']);
                $content.="DROP TABLE IF EXISTS `$view`;DROP VIEW IF EXISTS `$view`;".PHP_EOL;
                $content.="CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `$view` AS ".($arr[0]['body']).';'.PHP_EOL.PHP_EOL;
            }
            return $content;
        }
        return null;
    }
    
    /**
     * Returns procedure create SQL command or null if failed
     * 
     * @param string $procedure
     * 
     * @return string
     */
    private function _getRoutineCreate(string $routine,bool $isFunction=TRUE,bool $addDrop=TRUE)
    {
        $isFunction=$isFunction ? 'FUNCTION' : 'PROCEDURE';
        $arr=$this->_db->query("SHOW CREATE $isFunction `$routine`")->getResult('array');
        $content='--'.PHP_EOL;
        $content.="-- ".ucwords($isFunction)." structure for ". strtolower($isFunction)." `$routine`".PHP_EOL;
        $content.='--'.PHP_EOL;
        if ($addDrop)
        {
            $content.="DROP $isFunction IF EXISTS `$routine`;".PHP_EOL;
        }
        $content.='DELIMITER //'.PHP_EOL;;
        
        if (is_array($arr) && count($arr)>0 && array_key_exists('Create '.ucwords(strtolower($isFunction)), $arr['0']))
        {
            $arr= preg_replace('#DEFINER=`(.*?)`@`(.*?)`#s', '', $arr['0']['Create '.ucwords(strtolower($isFunction))]);
            return $content.$arr.'; //'.PHP_EOL;
        }
        return null;
    }
    
    
}