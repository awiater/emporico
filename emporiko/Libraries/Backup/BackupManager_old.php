<?php
/*
 *  This file is part of VLMS WMS    
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
	/**
	 * Database connection instance
	 * @var CodeIgniter\Database\MySQLi\Connection
	 */
	protected $db;
	
	protected $drop_table='DROP TABLE IF EXISTS `%table%`;';
	
	function __construct()
	{
		$this->db=\Config\Database::connect();
	}
	
	function __destruct()
	{
		//$this->close_connection();
	}
	
	private function close_connection()
	{
		if ($this->db instanceof \CodeIgniter\Database\MySQLi\Connection)
		{
			$this->db->close();
		}
	}
	
	function runBackup($folder,$system=TRUE,$db=TRUE,$tables='*',$systemFolder="public_html")
	{
		$folder=$this->prepareDir($folder);
		if ($db)
		{
			$dbFile=$this->backup_db($folder,$tables);
			if ($dbFile!=FALSE)
			{
				$dbFile=new \CodeIgniter\Files\File($dbFile);
			}
		}
		
		if ($system)
		{
			$file=$folder.$systemFolder.'_'.formatDate().'.zip';
			$files=$this->directoryToArray('../'.$systemFolder,TRUE);
			if ($db)
			{
				$files[]=$dbFile->getRealPath();
			}
			
			if ($this->create_zip($files,$file))
			{
				$file=new \CodeIgniter\Files\File($file);
				$file->move(WRITEPATH.'temp'.DIRECTORY_SEPARATOR,$file->getBasename());
				$file=WRITEPATH.'temp'.DIRECTORY_SEPARATOR.$file->getBasename();
				delete_files($folder,TRUE);
				rmdir($folder);
				return $file;
			}
		}else
		{
			return $dbFile->getRealPath();
		}
		
	}
	
	function create_zip($source,$target,$overwrite=TRUE)
	{
		if (is_string($source)&&is_dir($source))
		{
			$source=$this->directoryToArray($source,TRUE);
		}
		if (!is_array($source))
		{
			return FALSE;
		}
		$valid_files=[];
		foreach($source as $file) 
		{
            if(file_exists($file)) 
            {
                $valid_files[] = $file;
            }
		}
		$zip = new \ZipArchive();
		
		if ($zip->open($target, \ZipArchive::CREATE) === TRUE)
		{
			foreach($valid_files as $file) 
			{
           		if (!$zip->addFile($file,Str::after($file,'/')))
				{
					dump('error');exit;
				}
        	}
			$zip->close();
		}
		
		return file_exists($target);
	}
	
	function backup_db($folder,$tables = '*')
	{
		if($tables == '*')
		{
			$tables = array();
			$query = $this->db->query('SHOW TABLES');
			foreach ($query->getResult('array') as $row)
			{
    			$tables[] = $row;
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}
		
		$return='-- '.config('APP')->APPName.' '.config('APP')->APPVersion.PHP_EOL;
		$return.='-- Generation Time: '.formatDate('now',FALSE,'M d, Y at H:i').PHP_EOL;
		$return.=PHP_EOL;
		$return.='SET FOREIGN_KEY_CHECKS=0;'.PHP_EOL;
		$return.='SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";'.PHP_EOL;
		$return.='START TRANSACTION;'.PHP_EOL;
		$return.=PHP_EOL;
		$return.=PHP_EOL;
		foreach($tables as $table)
		{
			
			$table=array_values($table);
			$table=$table[0];
			$query = $this->db->table($table)->get();
			
			$num_fields = $query->resultID->field_count;
			
			$row2 =$this->db->query('SHOW CREATE TABLE '.$table)->getResult('array');
			if (array_key_exists('Create Table', $row2[0]))
			{
				$return.='--'.PHP_EOL.'--'.PHP_EOL.'-- Table structure for table `'.$table.'`'.PHP_EOL.'--'.PHP_EOL;
				$return.= str_replace('%table%', $table, $this->drop_table).PHP_EOL;
				//$return.='DROP TABLE IF EXISTS `'.$table.'`;'.PHP_EOL;
				$return.= $row2[0]['Create Table'].";".PHP_EOL.PHP_EOL;
				$return.='--'.PHP_EOL.'--'.PHP_EOL.'-- Dumping data for table `'.$table.'`'.PHP_EOL.'--'.PHP_EOL;
				for ($i = 0; $i < $num_fields; $i++) 
				{
					foreach ($query->getResult('array') as $row)					
					{
						$columns=[];
						$values=[];
						foreach ($row as $key => $value) 
						{
							$columns[]='`'.$key.'`';
							$value=addslashes($value);
							$value=str_replace("\n","\\n",$value);
							if (isset($value)) 
							{
								 $values[]= '"'.$value.'"' ; 
							} 
							else 
							{
							 	$values[]= '""'; 
							}
						}
						$return.= 'INSERT INTO '.$table.'('.implode(',',$columns).') VALUES('.implode(',',$values).');'.PHP_EOL;
					
					}
				}
			}else
			if (array_key_exists('Create View', $row2[0]))
			{
				$return.='--'.PHP_EOL.'--'.PHP_EOL.'-- Structure for view `'.$table.'`'.PHP_EOL.'--'.PHP_EOL;
				$return.='DROP VIEW IF EXISTS `'.$table.'`;'.PHP_EOL;
				$return.= $row2[0]['Create View'].";".PHP_EOL.PHP_EOL;
			}
			/**/
		}
		$return.='SET FOREIGN_KEY_CHECKS=1;'.PHP_EOL;
		$return.='COMMIT;'.PHP_EOL;
		
		$file=$folder.'db_'.formatDate().'.sql';
		return write_file($file, $return) ? $file:FALSE;
	}
	
	function prepareDir($folder)
	{
		$folder=WRITEPATH.'temp'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR;
		
		$this->clearDir(WRITEPATH.'temp'.DIRECTORY_SEPARATOR);
		
		if (!file_exists($folder))
		{
			mkdir($folder);
		}
		return $folder;
	}
	
	private function clearDir($directory) 
	{
		
		if (file_exists($directory))
		{
			return delete_files($directory, true);
			$dir = $directory;
			$di = new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
			$ri = new \RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ( $ri as $file ) 
			{
    			$file->isDir() ?  rmdir($file) : unlink($file);
			}
			return true;
		}
	}
	
	private function directoryToArray($directory, $recursive) 
	{
    	$array_items =[];
    	if ($handle = opendir($directory)) 
    	{
        	while (false !== ($file = readdir($handle))) 
        	{
            	if ($file != '.' && $file != '..' && $file != 'Thumbs.db' && $file != 'error_log') 
            	{
                	if (is_dir($directory. '/' . $file)) 
                	{
                    	if($recursive) 
                    	{
                        	$array_items = array_merge($array_items, $this->directoryToArray($directory. '/' . $file, $recursive));
                    	}
                	} else {
                    	$file = $directory . '/' . $file;
                    	$array_items[] = preg_replace('/\/\//si', '/', $file);
                	}
            	}
        	}
        	closedir($handle);
    	}
    	return $array_items;
	}
	
}