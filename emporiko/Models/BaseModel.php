<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Models;

use CodeIgniter\Model;
use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class BaseModel extends Model
{
	
	protected $useAutoIncrement = true;
	
	
	protected $returnType     = 'array';
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=[];
	
        
	/**
	 *  Return all records from table
	 *  
	 * @param  array   $filters  		Array with filters (key is field, value is field value)
	 * @param  string  $orderby  		Order by field name
	 * @param  string  $paginate 		Pagination settings
	 * @param  integer $logeduseraccess     Logged user access level
         * 
	 * @return array
	 */
	public function filtered(array $filters=[],$orderby=null,$paginate=null,$logeduseraccess=null,$Validation=TRUE)
	{
            $allowed=[];
            if (is_array($Validation))
            {
                $allowed=$Validation;
                $Validation=TRUE;
            }
	    $result=$this->parseFilters($filters,$this,$allowed,$Validation);
		
		if ($orderby!=null)
		{
			if (is_string($orderby)&&Str::startsWith($orderby,'groupby:'))
			{
				$orderby=substr($orderby,strlen('groupby:'));
				$result=$result->groupBy($orderby);
			}else
			{
				$orderby=is_array($orderby)?$orderby:[$orderby];
				foreach ($orderby as $orderbyValue) 
				{
					$result=$result->orderBy($orderbyValue);
				}
			}		
		}
                
		if ($paginate!=null&&$paginate!=FALSE)
		{
			if ($paginate==0)
			{
				return $result->find();
			}
                        if (is_bool($paginate) && $paginate)
                        {
                            $paginate=config('Pager')->perPage;
                        }
                        
			$result= $result->paginate($paginate);
		}
		
		return $result;
	}	
	
        /**
         * Returns array with allowed fields names
         * 
         * @return array
         */
        function getFieldsNames()
        {
            return $this->allowedFields;
        }
        
        /**
         * Set table variable
         * 
         * @param string $table
         * 
         * @return $this
         */
        function setTable(string $table)
        {
            $this->table=$table;
            return $this;
        }
        
        /**
         * Set records order field
         * 
         * @param array $data
         * @param string $orderField
         * @param string $whereField
         * 
         * @return boolean
         */
        function setOrder(array $data,string $orderField=null,string $whereField=null)
        {
            $arr=$this->allowedFields;
            $arr[]=$this->primaryKey;
            if (!Arr::KeysExists([$orderField,$whereField], array_flip($arr)))
            {
                return FALSE;
            }
            $arr=[];
            foreach ($data as $key=>$value)
            {
                $arr[]=
                [
                    $whereField=>$value,
                    $orderField=>$key,
                ];
            }
            return $this->builder()->updateBatch($arr,$whereField);
        }
        
        /**
         * Parse filters from array to SQL command (Codeigniter)
         * 
         * @param array   $filters
         * @param string  $model
         * @param array   $allowedfields
         * @param boolean $Validation
         * 
         * @return type
         */
	protected function parseFilters(array $filters,$model,$allowedfields=[],$Validation=TRUE)
	{
		
		$allowedfields=count($allowedfields)<1?$model->allowedFields:$allowedfields;
		if (!in_array($this->primaryKey, $allowedfields))
		{
			$allowedfields[]=$this->primaryKey;
		}
                
                if (array_key_exists($this->primaryKey, $filters) && $filters[$this->primaryKey]=='first')
                {
                    $filters[$this->primaryKey]=$this->getFirstID();
                }
                
		foreach($filters as $key=>$value)
		{
			$prefix=null;
                        if ($key=='@columns')
                        {
                            if (is_array($value))
                            {
                                $model->select(imploade(',',$value));
                            }
                           goto endforloop; 
                        }else
                        if ($key=='@limit')
                        {
                            $model->limit($value);
                            goto endforloop;
                        }else
			if (Str::startsWith($key,'( '))
			{
				$key=str_replace('( ', '', $key);
				$model=$model->groupStart();
			}
			if (Str::startsWith($key,'||( '))
			{
				$key=str_replace('||( ', '', $key);
				$model=$model->orGroupStart();
			}
			$groupend=FALSE;
			if (Str::endsWith($key,' )'))
			{
				$key=str_replace(' )', '', $key);
				$groupend=TRUE;
			}
			
			
			if (Str::contains($key,'.'))
			{
				$prefix=explode('.', $key);
				$key=$prefix[1];
				$prefix=$prefix[0].'.';
			}
			$option='';
                       
			if ($key=='access' && in_array('access',$allowedfields))
			{
				/*$accessgroups=loged_user('accessgroups');
				$accessgroups=is_array($accessgroups) ? null :$accessgroups;
			  	$value=str_replace(['@loged_user','@logeduser'],$accessgroups,$value);
			  	$model=$model->Where("FIND_IN_SET(".$prefix.$key.",'".$value."')>0",null,FALSE);*/
                                if (is_string($value) && Str::contains($value, ['@loged_user','@logeduser']) && loged_user('object')!=null)
                                {
                                    $value=loged_user('object')->getAccess(TRUE);
                                }
                                
                                if (is_array($value) && count($value) > 0)
                                {
                                    $key.=' In';
                                }else
                                {
                                    goto endforloop;
                                }
				
			}else
			if (Str::startsWith($key,'|| '))
			{
				$option='or';
				$key=str_replace('|| ', '', $key);
			}
			
                        if (Str::endsWith(strtolower($key),' len<') || Str::endsWith(strtolower($key),' len>'))
                        {
                           $option.='Where';
                           $kkey=Str::endsWith(strtolower($key),' len<') ? '<' : '>';
                           $key=str_replace([' len<',' len>'], '', $key);
                           
                           $model=$model->{$option}("length(".$prefix.$key.") ".$kkey." ".$value,null,FALSE);
                           goto endforloop;
                        }else
                        if (Str::endsWith($key,' <>')&&($value==null || $value=='null'))
                        {
                            $option.='Where';
                            $key=str_replace(' <>', '', $key);
                            $model=$model->{$option}("length(".$prefix.$key.") > 0",null,FALSE);
                            goto endforloop;
                        }else
			if (Str::endsWith($key,' %'))
			{
				$option.='Like';
				$key=str_replace(' %', '', $key);
			}else
                        if (Str::endsWith($key,' InSet')&&is_array($value))
                        {
                            $key=str_replace(' InSet', '', $key);
                            $model=$model->Where("FIND_IN_SET(".$prefix.$key.",'".implode(',',$value)."')>0",null,FALSE);
                            goto endforloop;
                        }else
			if (Str::endsWith($key,' In')&&is_array($value))
			{
				$option.='whereIn';
				$key=str_replace(' In', '', $key);
			}else
			if (Str::endsWith($key,' notIn')&&is_array($value))
			{
				$option.='whereNotIn';
				$key=str_replace(' notIn', '', $key);
			}else	
			{
				$option.='Where';
			}
			
			$keyA=explode(' ', $key);
			
			if (Str::contains($keyA[0],'.'))
			{
				$keyA[0]=explode('.', $keyA[0]);
				$keyA[0]=$keyA[0][1];
			}			
			if ($Validation && in_array($keyA[0],$allowedfields))
			{
                            if (is_array($value) && !Str::contains(strtolower($option),'wherein'))
                            {
                                $model=$model->groupStart();
                                foreach($value as $vvalue)
                                {
                                    $model=$model->{'or'.$option}($prefix.$key,$vvalue);
                                }
                                $model=$model->groupEnd();
                            } else 
                            {
                                $model=$model->{$option}($prefix.$key,$value);
                            }
				
			}else
			if (!$Validation)
			{
                            if (is_array($value) && !Str::contains(strtolower($option),'wherein'))
                            {
                                $model=$model->groupStart();
                                foreach($value as $vvalue)
                                {
                                    $model=$model->{'or'.$option}($prefix.$key,$vvalue);
                                }
                                $model=$model->groupEnd();
                            } else 
                            {
                                $model=$model->{$option}($prefix.$key,$value);
                            }
			}
			
			if ($groupend)
			{
				$model=$model->groupEnd();
			}
		endforloop:
		}
		return $model;
	}
	
	/**
	 * Count records in table. Could be restricted by filters
	 * 
	 * @param  array $filters Array with filters (key is field, value is field value)
         * 
	 * @return int
	 */
	public function count(array $filters=[])
	{
            $sql="SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME   = '".$this->table."';";
            $sql=$this->db()->query($sql)->getResultArray();
            if (is_array($sql) && count($sql) >0)
            {
                $sql= array_values($sql[0]);
                if (is_numeric($sql[0]) && $sql[0]==1)
                {
                    return 0;
                }
            }
            if (array_key_exists('_limit', $filters))
            {
                $query=$this->limit($filters['_limit']);
                unset($filters['_limit']);
            }else
            {
                $query=$this;
            }
            $query=$this->parseFilters($filters,$query->builder(),$this->allowedFields);
            return $query->countAllResults();
	}
	
        /**
         * Sum records in given column.Could be restricted by filters
         * 
         * @param  array  $filters
         * @param  string $field
         * 
         * @return Int
         */
        public function sum($field,array $filters=[])
	{
		$query=$this->parseFilters($filters,$this->builder(),$this->allowedFields);
		$arr=$query->selectSum($field)->get()->getResultArray();
                $arr=is_array($arr) && count($arr) > 0 ? $arr[0][$field] : 0;
		return $arr==null ? 0 : $arr;
        }
        
         /**
         * Get max value from records in given column.Could be restricted by filters
         * 
         * @param  array  $filters
         * @param  string $field
          * 
         * @return Int
         */
        public function max($field,array $filters=[])
	{
		$query=$this->parseFilters($filters,$this->builder(),$this->allowedFields);
		$arr=$query->selectMax($field)->get()->getResultArray();
		return is_array($arr) && count($arr) > 0 ? $arr[0][$field] : null;
        }
        
        /**
         * Get min value from records in given column.Could be restricted by filters
         * 
         * @param  array  $filters
         * @param  string $field
         * 
         * @return Int
         */
        public function min($field,array $filters=[])
	{
		$query=$this->parseFilters($filters,$this->builder(),$this->allowedFields);
		$arr=$query->selectMin($field)->get()->getResultArray();
		return is_array($arr) && count($arr) > 0 ? $arr[0][$field] : null;
        }
        
        /**
         * Get average value from records in given column.Could be restricted by filters
         * 
         * @param  array  $filters
         * @param  string $field
         * 
         * @return Int
         */
        public function average($field,array $filters=[])
	{
		$query=$this->parseFilters($filters,$this->builder(),$this->allowedFields);
		$arr=$query->selectAvg($field)->get()->getResultArray();
		return is_array($arr) && count($arr) > 0 ? $arr[0][$field] : null;
        }
        
	/**
	 * Get next record primary key value
	 * 
	 * @return Int
	 */
	function getNextID()
	{
		$arr= $this->db->query(str_replace(['#','%table%'], ['"',$this->table], "SELECT AUTO_INCREMENT FROM information_schema.tables where TABLE_NAME='%table%'"))->getResult();
		if (count($arr)>0)
		{
			return $arr[0]->AUTO_INCREMENT;
		}
		return null;
	}
	
	/**
	 * Get last record primary key value
	 * 
	 * @return Int
	 */
	function getLastID()
	{
		$res=$this->select($this->primaryKey)->orderby($this->primaryKey.' DESC')->limit(1)->find();
		if (is_array($res) && count($res) > 0)
		{
			return $res[count($res)-1][$this->primaryKey];
		}
		return null;
	}
	
        /**
	 * Get last record primary key value
	 * 
	 * @return Int
	 */
	function getFirstID()
	{
		$res=$this->select($this->primaryKey)->orderby($this->primaryKey.' ASC')->limit(1)->find();
		if (is_array($res) && count($res) > 0)
		{
			return $res[count($res)-1][$this->primaryKey];
		}
		return null;
	}
        
	/**
	 * Returns array with next and prev record according to given id
	 * 
	 * @param  Int    $id
	 * @param  String $field
	 * 
	 * @return array
	 */
	function getPrevNextID($id,$field=null)
	{
		$field=$this->primaryKey;
		$sql_a=$this->select($field)
				  ->where($field.' >',$id)
				  ->orderBy($this->primaryKey.' ASC')
				  ->limit(1)
				  ->getCompiledSelect();
		$sql_b=$this->select($field)
				  ->where($this->primaryKey.' <',$id)
				  ->orderBy($this->primaryKey.' DESC')
				  ->limit(1)
				  ->getCompiledSelect();
		$sql_b=$this->db->query('('.$sql_a.') UNION ('.$sql_b.')')->getResultArray();
		
		$sql_a=['next'=>$id,'prev'=>$id];
		if (is_array($sql_b) && count($sql_b)>0)
		{
			$sql_a['next']=$sql_b[0][$field];
			if (count($sql_b)>1)
			{
				$sql_a['prev']=$sql_b[1][$field];
			}
		}
		
		return $sql_a;
	}
	
	/**
	 * Enable item by give item id
	 * 
	 * @param  int  $id      Item id
	 * @param  bool $enable  Determine if item is enabled (TRUE,1) or disabled (FALSE,0)
         * 
	 * @return bool
	 */
	public function enableItem($id,$enable)
	{
		if (is_bool($enable))
		{
			$enable=$enable?1:0;
		}
		return $this->save([$this->primaryKey=>$id,'enabled'=>$enable]);
	}
	
        /**
         * Update data in storage
         * 
         * @param type $data
         * 
         * @return bool
         */
	public function updateData($data)
	{
		if (array_key_exists($this->primaryKey, $data))
		{
			return parent::update($data[$this->primaryKey],$data);
		}else
		{
			return parent::insert($data);
		}
	}
	
        /**
         * Update data using different primary key
         * 
         * @param array $data
         * @param bool  $stopOnFailure
         * @param type  $whKey
         * @param bool  $forceInsert
         * 
         * @return array
         */
	public function updateMany(array $data,bool $stopOnFailure=FALSE,$whKey=null,bool $forceInsert=FALSE)
	{
            $whKey=$whKey==null ? $this->primaryKey :  $whKey;
            $primaryKey=$this->primaryKey;
            if ($whKey!=null)
            {
                $this->primaryKey=$whKey;
            }
            /*$columns= array_keys($data[0]);
            $columns_repl=Arr::ParsePatern($columns, '{value}');
            $columns_repl=array_combine($columns,$columns_repl);
            $updt=$this->builder()->set($columns_repl)->where($whKey,'{'.($whKey).'}')->getCompiledUpdate();
            $ins=$this->builder()->set($columns_repl)->getCompiledInsert();
            $ins.=' ON DUPLICATE KEY ';
            $ins.='`'.($whKey).'`=`'.($whKey).'`';*/
            $arr=[];
            $this->db->transStart();
            foreach ($data as  $row) 
            {
                if (array_key_exists($whKey, $row))
		{
                    $sql=$this->builder()->set($row)->getCompiledInsert();
                    if ($forceInsert)
                    {
                        $arr[]=$this->db()->query($sql);
                    }else
                    {
                        $sql.=' ON DUPLICATE KEY ';
                        $sql.=$this->builder()->set($row)->getCompiledUpdate();
                        $sql= str_replace('`'.$this->table.'` SET', '', $sql);
                        $arr[]=$this->db()->query($sql);
                    }
                }
            }
            $this->db->transComplete();
            $this->primaryKey=$primaryKey;
            return $arr;
	}
	
	/**
	 * Return Array with data to populate drop down in form
	 * 
	 * @param  string $field    Value field name (saved to db)
	 * @param  string $value    Text field name (showed to end user)
	 * @param  bool   $addEmpty Determine if empty field will be added
	 * @param  string $defValue Default value field name if $value is null or not exists in allowed fields array
	 * @return Array
	 */
	function getForForm($field=null,$value=null,$addEmpty=FALSE,$defValue=null,array $filters=[])
	{
		$defValue=$defValue==null?$this->allowedFields[0]:$defValue;
		$field=$field==null?$this->primaryKey:$field;
		$field=in_array($field, $this->allowedFields)?$field:$this->primaryKey;
		$value=$value==null?$defValue:$value;
		$value=in_array($value, $this->allowedFields)?$value:($value=='*' ? $value :$defValue);
		
		$result=[];
		if ($addEmpty!=FALSE)
		{
			$result['']= is_bool($addEmpty) ? '' : $addEmpty;
		}
		
		$sql=$this;
		if (in_array('enabled', $this->allowedFields) && !array_key_exists('enabled', $filters))
		{
			$filters['enabled']=1;
		}
		
		if (in_array('access', $this->allowedFields))
		{
			$filters['access']=service('auth')->getLogedUserInfo('accessgroups');
		}
		
		$sql=$sql->filtered($filters,$field,FALSE);
		foreach ($sql->find() as $record) 
		{
                    if ($value=='*')
                    {
                        $result[$record[$field]]=$record;
                    }else
                    if (array_key_exists($value, $record) && array_key_exists($field, $record))
                    {
                        $result[$record[$field]]=$record[$value];
                    }
			
		}
		return $result;
	}
        
        
        /**
         * Returns array with custom tabs
         * 
         * @param string $group
         * @param string $prefix
         * 
         * @return array
         */
	protected function getCustomTabsData($group,$prefix)
	{
		$tabs=model('Settings/SettingsModel')->get($group.'.'.$prefix.'*',FALSE,'value',FALSE);
		if ($tabs==null)
		{
			return [];
		}
		
		$tabs=is_array($tabs) ? $tabs : [$tabs];
		$arr=[]; 
		foreach ($tabs as  $key=>$value) 
		{
			$value=json_decode($value,TRUE);
			if (is_array($value))
			{
				$arr[$key]=$value;
			}
		}
		return $arr;
	}
        
        /**
         * Returns Settings model
         * 
         * @return model
         */
        function getSettingsModel()
        {
            return model('Settings/SettingsModel');
        }
        
        /**
         * Get setting value from settings table
         * 
         * @param type $key
         * @param boolean $parseValue
         * @param string $keyToGet
         * @param boolean $showError
         * 
         * @return mixed
         */
        function getSetting($key,$parseValue=FALSE,$keyToGet='value',$showError=TRUE)
        {
            return model('Settings/SettingsModel')->get($key,$parseValue=FALSE,$keyToGet='value',$showError=TRUE);
        }
        
        /**
         * Returns array with form fields data
         * 
         * @param array $record
         * 
         * @return array
         */
        function getFieldsForForm(array $record)
        {
            $arr=[];
            foreach ($this->fieldsTypes as $key => $value) 
            {
                if ($key!= $this->primaryKey)
                {
                    $arr[$key]='InputField';
                    if ($key=='access')
                    {
                        $arr[$key]='AccessField';
                    }
                    
                     if ($key=='enabled' && array_key_exists('type', $value) && strtolower($value['type'])=='int')
                     {
                         $arr[$key]='YesNoField';
                     }
                     
                     if (array_key_exists('type', $value) && strtolower($value['type'])=='double')
                     {
                         $arr[$key]='InputButtonField';
                     }
                    
                    $arr[$key]=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField($arr[$key]);
                    if ((array_key_exists('_readonly', $record) && $record['_readonly']) || (array_key_exists('edit_acc', $record) && !$record['edit_acc']))
                    {
                        $arr[$key]->setReadOnly();
                    }
                    if (array_key_exists('constraint', $value))
                    {
                        $arr[$key]->setMaxLength($value['constraint']);
                    }
                    
                    if (array_key_exists('null', $value) && !$value['null'])
                    {
                        $arr[$key]->setAsRequired();
                    }
                    
                    $arr[$key]->setTab('general');
                    $arr[$key]->setName($key);
                    $arr[$key]->setID('id_'.$key);
                    $arr[$key]->setText($key);
                    
                    if (array_key_exists('type', $value) && strtolower($value['type'])=='double')
                    {
                         $arr[$key]->setButtonbefore()
                                   ->setButtonIcon('£',FALSE)
                                   ->setMask('$')
                                   ->setButtonClass('input-group-text font-weight-bold border-right-0')
                                   ->setButtonArgs(['style'=>'cursor:default']);
                    }
                }
            }
            
            return $arr;
        }
        
        /**
         * Add custom tab settings to database
         * 
         * @param  type $tabName
         * @param  type $tabAction
         * @param  type $paramGroup
         * @param  type $tabText
         * 
         * @throws \Exception
         */
        protected function addCustomTab($tabName,$tabAction,$paramGroup,$tabText=null)
        {
            $tabText= $tabText==null ? ucwords($tabName) : $tabText;
            if (!is_string($paramGroup))
            {
                throw new \Exception('Invalid $paramGroup value');
            }
            $paramGroup= strtolower($paramGroup);
            $this->getModel('Settings')->add($paramGroup, $paramGroup.'_customtab_'.$tabName, $tabAction, 'textlong', $tabText);
        }
        
        /**
         * Add custom tabs to edit given FormView
         * 
         * @param \EMPORIKO\Controllers\Pages\FormView $view
         * @param type $param
         * @param type $labelsPatern
         * @param array $record
         */
        protected function setCustomTabs(\EMPORIKO\Controllers\Pages\FormView &$view,$paramGroup,$labelsPatern='-key-',array $record=[])
        {
            if (!is_string($paramGroup))
            {
                throw new \Exception('Invalid $paramGroup value');
            }
           $paramGroup= strtolower($paramGroup);
           $paramGroup.='_customtab_';
           foreach($this->getModel('Settings')->like('param',$paramGroup)->find() as $value)
           {
               $value['value']= json_decode($value['value'],TRUE);
               
               if (is_array($value['value']) && Arr::KeysExists(['controller','action'], $value['value']))
               {
                   if (array_key_exists('args', $value['value']) && is_array($value['value']['args']))
                   {
                       array_splice($value['value']['args'], 0,0,$record);
                   }else
                   {
                      $value['value']['args']=[$record]; 
                   }
                   
                   $value['value']= loadModuleFromArray($value['value']);
                   if ($value['value']!=null)
                   {
                      $view->setTab(str_replace($paramGroup, '', $value['param']),$value['tooltip']); 
                   }
                   
                   if (is_array($value['value']))
                   {
                       $view->addFieldsFromModel($value['value'],$record,$labelsPatern);
                   }
                   
                   if (is_string($value['value']))
                   {
                       $view->addCustomTextField('', '', $value['value']);
                   }
               }
               
           }
        }
        
	/**
	 * Install model table in db
	 * 
	 * @return bool
	 */
	public function installstorage()
	{
		$this->initForge();
		if (is_array($this->fieldsTypes)&&count($this->fieldsTypes))
		{
			$this->db->disableForeignKeyChecks();
			$this->forge->dropTable($this->table, true);
			$fields=$this->fieldsTypes;
                        $keys=[];
			foreach ($this->fieldsTypes as $key => $value) 
			{
                            if ($value['type']=='MONEY')
                            {
                                $fields[$key]['type']='DECIMAL';
                                $fields[$key]['constraint']='10,2';
                            }
                            
				if (is_array($value)&&array_key_exists('foreignkey', $value)&&count($value['foreignkey'])>1)
				{
					$value=$value['foreignkey'];
					$keys[$key]=['foreignkey',$value[0],$value[1],count($value)>2?$value[2]:'',count($value)>3?$value[3]:''];
				}
                                
                                if (is_array($value)&&array_key_exists('index', $value) && $value['index'])
                                {
                                    $keys[$key]=['index'];
                                }
			}
                        $this->forge->addField($fields);
                        foreach ($keys as $key => $value) 
                        {
                            if ($value[0]=='index')
                            {
                               $this->forge->addKey($key,FALSE,FALSE); 
                            }else
                            if ($value[0]=='foreignkey')
                            {
                                $this->forge->addForeignKey($key,$value[1],$value[2],$value[3],$value[4]);
                            }
                        }
                        
			$this->forge->addKey($this->primaryKey,TRUE);
			$result= $this->forge->createTable($this->table, TRUE);
			$this->db->enableForeignKeyChecks();
			return $result;
		}
		return FALSE;
	}
	
	/**
	 * Uninstall (remove) model table from db
	 * 
	 * @return bool
	 */
	public function removestorage()
	{
		$this->initForge();
		return $this->forge->dropTable($this->table, false, true);
	}
	
	/**
	 * Returns field(column) setup info
	 * 
	 * @param  string      $field
	 * @param  string/null $attr
	 * 
	 * @return mixed
	 */
	public function getFieldInfo($field,$attr=null)
	{
		if (!array_key_exists($field, $this->fieldsTypes))
		{
			return null;
		}
		
		if ($attr!=null && !array_key_exists($attr, $this->fieldsTypes[$field]))
		{
			return null;
		}
		return $attr==null ? $this->fieldsTypes[$field] : $this->fieldsTypes[$field][$attr];
	}
	
	/**
	 * Returns array with empty fields
	 * 
	 * @param  bool $addPrime
	 * 
	 * @return array
	 */
	public function getNewRecordData($addPrime=FALSE)
	{
		$arr=array_combine($this->allowedFields, array_fill(0, count($this->allowedFields), ''));
		if ($addPrime)
		{
			$arr[$this->primaryKey]='';
		}
                foreach(array_keys($arr) as $key)
                {
                    if (array_key_exists($key, $this->fieldsTypes) && array_key_exists('default', $this->fieldsTypes[$key]))
                    {
                        $arr[$key]=$this->fieldsTypes[$key]['default'];
                    }
                }
		return $arr;
	}
	
        /**
         * Returns model for given view
         * 
         * @param string $name
         * @param string $primaryKey
         * 
         * @return \EMPORIKO\Models\BaseModel
         */
        function getView($name,$primaryKey=':id:')
        {
            $model=new BaseModel();
            $model->table=$name;
            $model->allowedFields=$model->db()->getFieldNames($name);
            $model->primaryKey=$primaryKey;
            return $model;
        }
        
        /**
         * Create new view from command
         * 
         * @param  string $name
         * @param  string $content
         * @return bool
         */
        function setView($name,$content)
        {
            $name=Str::startsWith($name, 'vw_') ? $name : 'vw_'.$name;
            if(is_object($content) && get_class($content)=='CodeIgniter\Database\MySQLi\Builder')
            {
                $content=$content->getCompiledSelect();
            }
            $this->db()->query('DROP VIEW IF EXISTS `'.$name.'`;');
            return $this->db()->query('CREATE VIEW `'.$name.'` AS '.$content.';');
        }
        
        /**
         * Returns given view definition
         * 
         * @param  type $viewName
         * @return boolean/String
         */
        protected function getViewDefinition($viewName)
        {
            $sql="SELECT VIEW_DEFINITION FROM    INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = '".$viewName."'";
            $sql=$this->query($sql)->getResultArray();
            if (is_array($sql) && count($sql)>0 && array_key_exists('VIEW_DEFINITION', $sql[0]))
            {
                return $sql[0]['VIEW_DEFINITION'];
            }
            return FALSE;
        }
        
        
        
        /**
         * Returns model
         * 
         * @param type $name
         * 
         * @return type
         */
        function getModel($name)
        {
            if (strtolower($name)=='movements')
            {
                return model('System/MovementsModel');
            }
            if (strtolower($name)=='settings')
            {
                return model('Settings/SettingsModel');
            }
            $name=ucwords($name);
            if (!Str::endsWith($name,'Model'))
            {
                $name.='Model';
            }
            if (Str::contains($name, '/'))
            {
                return model($name);
            }
            $namespace=new \ReflectionClass(get_class($this));
            $namespace=$namespace->getNamespaceName();
            $namespace=Str::afterLast($namespace, '\\');
            return model($namespace.'/'.$name);
        }
        
        /**
         * Returns fields names for block replacement (docs editor)
         * 
         * @return array
         */
        function getBlocksFields()
        {
            return $this->allowedFields;
        }
        
	/**
	 * Init forge if not set before
	 */
	function initForge()
	{
            if ($this->forge==null)
            {
                $this->forge=\Config\Database::forge();
            }
            return $this->forge;
	}
        
        /**
         * Determines if model table (or given one) exists in database
         * 
         * @param string $table
         * 
         * @return bool
         */
        function existsInStorage(string $table=null)
        {
            $table=$table==null ? $this->table : $table;
            $table="SELECT * FROM information_schema.tables WHERE table_name = '$table' LIMIT 1;";
            $table=$this->db()->query($table)->getResultArray();
            return is_array($table) && count($table) > 0;
        }
	
}
?>