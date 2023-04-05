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
 
namespace EMPORIKO\Models\Documents;

use EMPORIKO\Helpers\Strings as Str;

class FolderModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='documents_cat';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'dcid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','text','desc','category','createdby'
                                  ,'createdon','tags','modby','modon','enabled','access'];
        
        protected $validationRules =
	[
		'name'=>'required|is_unique[documents_cat.name,dcid,{dcid}]',
	];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'dcid'=>                ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'text'=>                ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'desc'=>		['type'=>'TEXT','null'=>TRUE],
                'category'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'default'=>'.'],
                'createdby'=>           ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
                'createdon'=>           ['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
                'modby'=>               ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
                'modon'=>               ['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
                'tags'=>                ['type'=>'TEXT','null'=>TRUE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
        
        /**
         * Return array with folders from top location (/)
         * 
         * @param  array $filters
         * @return array
         */
        function getTopFolders(array $filters=[])
        {
            $filters['( category']='/';
            $filters['|| category )']='.';
            $filters['access']='@loged_user';
            $documents=model('Documents/DocumentModel')->table;
            $select=[$this->table.'.*'];
            $select[]='(SELECT COUNT(category) FROM '.$this->table.' WHERE category='.$this->table.'.name) as folders';
            $select[]='(SELECT COUNT(name) FROM '.$documents.' WHERE '.$documents.'.dcategory='.$this->table.'.name) as files';
            $select[]="'dir' as `type`";
            
            return $this->filtered($filters)
                        ->select(implode(',',$select))
                        ->find();
        }
        
        /**
         * Returns array with folder
         * 
         * @param  array $filters
         * @return array
         */
        function getFolders(array $filters=[])
        {
            $filters['access']='@loged_user';
            $documents=model('Documents/DocumentModel')->table;
            $select=[$this->table.'.*'];
            $select[]='(SELECT COUNT(category) FROM '.$this->table.' WHERE category='.$this->table.'.name) as folders';
            $select[]='(SELECT COUNT(name) FROM '.$documents.' WHERE '.$documents.'.category='.$this->table.'.name) as files';
            $select[]="'dir' as `type`";
            return $this->filtered($filters)
                        ->select(implode(',',$select))
                        ->find();
        }
        
        /**
         * Returns array with fields for folder edit form
         * 
         * @return array
         */
        function getFieldsForForm(array $record)
        {
            $arr=$this->fieldsTypes;
            if (is_numeric($record[$this->primaryKey]))
            {
               unset($arr['createdby']);
               unset($arr['createdon']); 
               $arr['modby']['type']='hidden';
               $arr['modby']['null']=FALSE;
            }else
            {
               $arr['createdby']['type']='hidden';
               $arr['createdby']['null']=FALSE;
               $arr['createdon']['type']='hidden';
               $arr['createdon']['null']=FALSE;
                unset($arr['modby']);
                unset($arr['modon']);
            }
            $arr['tags']['type']='CustomElementsList';
            
            $arr['modon']['type']='hidden';
            $arr['modon']['null']=FALSE;
            
            $arr['name']['type']='hidden';
            $arr['name']['null']=FALSE;
            
            $arr['category']=$this->fieldsTypes['category'];
            $arr['category']['type']='DropDown';
            $arr['category']['args']['options']=$this->getPathsForForm(array_key_exists('category', $record) ? $record['category'] : null);
            $arr['category']['args']['selectwithicons']=TRUE;
            $arr['category']['args']['searchlist']=TRUE;
            unset($arr['dcid']);
            return $arr;
        }
        
        function setDocumentCat($oldCat,$newCat)
        {
            $oldCat= str_replace('//', '/', $oldCat);
            $newCat= str_replace('//', '/', $newCat);
            $db=$this->builder()->db();
            $oldCat=$db->escapeString($oldCat);
            $newCat=$db->escapeString($newCat);
            
            $db->simpleQuery("UPDATE ".$this->table." SET category=REPLACE(category,'".$oldCat."','".$newCat."') WHERE category LIKE '".$oldCat."%'");
            //$this->builder()->set('category',$newCat)->like('category',$oldCat)->update();
        }
        
        /**
         * Removes directory from storage
         * 
         * @param Int $id
         */
        function deleteFolder($id)
        {
            $path=parsePath('@storage/files/documents/'.$id,TRUE);
            if (file_exists($path))
            {
              deleteDir($path);  
            }
        }
        
        /**
         * Create new folder in storage
         * 
         * @param string $id
         */
        function createFolder($id)
        {
            $id=parsePath('@storage/files/documents/'.$id,TRUE);
            if (!file_exists($id))
            {
                mkdir($id);
            }
        }
        
        /**
         * Returns array with folders paths
         * 
         * @return array
         */
        function getPathsForForm($category=null)
        {
            $arr=['/'=>'<i class="fas fa-long-arrow-alt-right ml-1 mr-1"></i>'];
            $arr['/']=['value'=>'/','text'=>'/'];
            $names=[];
            foreach ($this->orderby('category','name')->orderby('category')->find() as $key => $value)
            {
                if ($category==null || ($category!=null && $category!=$value['category']))
                {
                    $names[$value['name']]=$value['text'];
                    $key=$value['category'].'/'.$value['name'];
                    $key= str_replace('//', '/', $key);
                    $path=str_replace(array_keys($names), $names, $key);
                    $path=str_replace('/', '<i class="fas fa-long-arrow-alt-right ml-1 mr-1"></i>', $path);
                    $arr[$key]=['value'=>$key,'text'=>$path,'search'=>substr($key,1)];
                }
            }

            return $arr;
        }
        
        function getFoldersNames($field='name',$text='text')
        {
            $arr=[];
            foreach ($this->orderby('category','name')->orderby('category')->find() as $key => $value)
            {
                $arr[$value[$field]]=$text==null || $text=='*' ? $value : $value[$text];
            }
            return $arr;
        }
        
        /**
         * Returns name of folder by path
         * 
         * @param  string $path
         * @return array
         */
        function getFolderNameByPath($path)
        {
            if ($path=='/')
            {
                return null;
            }
            $path=$this->where('name',Str::afterLast($path, '/'))->first();
            if (is_array($path) && array_key_exists('name', $path))
            {
                return ['name'=>$path['name'],'id'=>$path[$this->primaryKey]];
            }
            return null;
        }
}
?>
