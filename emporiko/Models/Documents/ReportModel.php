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
use EMPORIKO\Helpers\Arrays as Arr;

class ReportModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='reports';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'rid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['rname','rtitle','rdesc','rtype','rtables','rcolumns','rfilters',
                                   'rconfig','rsql','redit','access','enabled'];
	
	protected $validationRules =
	[
		'rname'=>'required|is_unique[reports.rname,rid,{rid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'rid'=>         ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'rname'=>       ['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
                'rtitle'=>      ['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'rdesc'=>       ['type'=>'TEXT','null'=>TRUE],
		'rtype'=>       ['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'rtables'=> 	['type'=>'TEXT','null'=>FALSE],
		'rcolumns'=> 	['type'=>'TEXT','null'=>TRUE],
		'rfilters'=> 	['type'=>'TEXT','null'=>TRUE],
		'rconfig'=> 	['type'=>'TEXT','null'=>TRUE],
		'rsql'=>        ['type'=>'TEXT','null'=>TRUE],
                'redit'=>       ['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'default'=>1],
		'access'=>      ['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=> 	['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];	
	
	function getTablesForForm()
	{
		$arr=$this->db()->listTables();
		return array_combine(array_values($arr), array_values($arr));
	}
	
        
        function getReportFieldsForForm(array $record) 
        {
            $arr=[];
            $arr['rtitle']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('rtitle')
                    ->setID('rtitle')
                    ->setMaxLength(250)
                    ->setText('reports.rname')
                    ->setTab('general')
                    ->setAsRequired();
            
            $arr['rdesc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('rdesc')
                    ->setID('rdesc')
                    ->setText('reports.rdesc')
                    ->setTab('general');
            
            $arr['access']= \EMPORIKO\Controllers\Pages\HtmlItems\AcccessField::create()
                    ->setName('access')
                    ->setID('access')
                    ->setText('reports.access')
                    ->setTab('general');
            
            $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                    ->setName('enabled')
                    ->setID('enabled')
                    ->setText('reports.enabled')
                    ->setTab('general');
           
            
            $arr['rcolumns_table']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('rcolumns[table]')
                    ->setID('id_rcolumns_list')
                    ->setOptions($this->getAvaliableReportSources())
                    ->setAsAdvanced()
                    ->setText('reports.rtables')
                    ->setTab('cfg');
            if (array_key_exists('rcolumns_source', $record) && is_array($record['rcolumns_source']))
            {
                $arr['rcolumns_table']->setReadOnly();
            }
            
            $arr['rcolumns']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                    ->setName('rcolumns')
                    ->setID('rcolumns')
                    ->setText('reports.rcolumns')
                    ->setTab('cfg')
                    ->setValue('');
            
            if (array_key_exists('rid', $record) && is_numeric($record['rid']))
            {
                //$arr['rtables']['args']['readonly']=TRUE;
            }
            
            return $arr;
        }
        
        /**
         * Returns array with available table names for reports
         * 
         * @return array
         */
        function getAvaliableReportSources()
        {
           $arr=[];
           foreach($this->getModel('Settings')->get('reports.reports_data_source_*',FALSE,'*') as $source)
           {
             $source['value']= base64_encode($source['value']);
             $arr[$source['value']]=lang($source['tooltip']);
           } 
           asort($arr);
           return $arr;
        }
        
        function getFieldsForSource(string $source,string $lang)
        {
            $source=Str::startsWith($source, 'vw_') ? $this->getView($source) : $this->getModel($source);
            $arr=[];
            foreach($source->allowedFields as $field)
            {
                if ($field!=$source->primaryKey && !Str::endsWith($field, 'id'))
                {
                    $lang=Str::contains($lang, '.') ? $lang : $lang.'.';
                    $arr[$field]=lang($lang.$field);
                }               
            }
            
            return $arr;
        }
        
        function getReportModel(string $name)
        {
            $name=Str::startsWith($name, 'vw_') ? $this->getView($name) : $this->getModel($name);
            return $name;
        }
        
        function getSystemEmailsTemplatesIDs()
        {
            $arr=$this->getForForm('rid', 'rid', FALSE, null, ['redit'=>0]);
            return count($arr) > 0 ? array_values(array_flip($arr)) : [];
        }
        
        function getTemplateByName($name,$parse=FALSE)
        {
            $name=$this->filtered(['rname'=>$name,'rtype'=>0])->first();
            if (is_array($parse))
            {
                foreach($parse as $key=>$value)
                {
                    $name['rsql']= str_replace('{'.$key.'}', $value, $name['rsql']);
                }
                
            }
            return $name;
        }
        
        function getTemplateFieldsForForm(array $record) 
        {
           $arr=[];
           $arr['rtitle']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                            ->setName('rtitle')
                            ->setID('rtitle')
                            ->setAsRequired()
                            ->setMaxLength(250)
                            ->setText('rtitle');
           
           $arr['rdesc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                            ->setName('rdesc')
                            ->setID('rdesc')
                            ->setText('reports.rdesc');
           
           if ($record['rtype']==0)
            {
                $arr['rsql']= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create() //CustomTextField
                            ->setArgs(['toolbar'=>'full'])
                            ->setName('rsql')
                            ->setID('rsql')
                            ->setHeight(300)
                            ->setTab('editor');
                
                $arr['rtables']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                                ->setName('rtables')
                                ->setID('rtables')
                                ->setAsRequired()
                                ->setText('subject');
                $arr['rconfig']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                                ->setName('rconfig')
                                ->setID('rconfig')
                                ->setAsRequired()
                                ->setOptions($this->getModel('Emails/Mailbox')->getForForm('emm_name','emm_name',FALSE,null,['enabled'=>1]))
                                ->setText('mailbox')
                                ->setAsAdvanced();
            }
           
            $arr['access']= \EMPORIKO\Controllers\Pages\HtmlItems\AcccessField::create()
                            ->setName('access')
                            ->setID('access')
                            ->setAsRequired()
                            ->setText('reports.access');
            
            $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                            ->setName('enabled')
                            ->setID('enabled')
                            ->setAsRequired()
                            ->setText('reports.enabled')
                            ->setValue(1);
           
            
           return $arr;
        }
        
        function getTemplatesForForm()
        {
            $arr=[];
            foreach($this->filtered(['rtype'=>0])->find() as $record)
            {
                $arr[$record['rname']]=$record['rtitle'].'=>'.$record['rdesc'];
            }
            return $arr;
        }
        
        /**
         * Get Fontawesome icons for leave types
         * 
         * @return array
         */
        function getIcons(array $deficons=[])
        {
            $file= file_get_contents(parsePath('@vendor/fontawesome/icons.json',TRUE));
            $file= json_decode($file,TRUE);
            $arr=[];
            foreach(is_array($file)? $file: [] as $icon)
            {
                if (count($deficons) > 0 && Str::contains($icon,$deficons))
                {
                    $arr[$icon]=['icon'=>$icon,'value'=>$icon,'text'=>$icon];
                } else 
                {
                    $arr[$icon]=['icon'=>$icon,'value'=>$icon,'text'=>$icon];
                }
                
            }
            $arr=[];
            return $arr;
        }
        
        /**
         * Parse email template
         * 
         * @param string $tplName
         * @param array  $tplData
         * 
         * @return array
         */
        function parseEmailTemplate(string $tplName,array $tplData)
        {
            $tplName=$this->where('rname',$tplName)->first();
            $tplData['@site_url']=site_url();
            
            $tplData=Arr::parsePaternOnKeys($tplData, '{key}',TRUE);
            
            if (is_array($tplName))
            {
                if (array_key_exists('{mail_subject}', $tplData))
                {
                    $tplName['rtables']=$tplData['{mail_subject}'];
                }
                $tplSubj=str_replace(array_keys($tplData), $tplData, $tplName['rtables']);
                $tplData=str_replace(array_keys($tplData), $tplData, $tplName['rsql']);
                return ['subject'=>$tplSubj,'body'=>$tplData,'mailbox'=>$tplName['rconfig']];
            }
            return null;
        }
        
        function getLayoutTemplates(array $filters=[])
        {
            $arr=[];
            $filters['enabled']=1;
            $filters['rconfig']=1;
            $filters['rtype']=0;
            foreach($this->filtered($filters)->find() as $record)
            {
                $arr[]=
                    [
                        'name'=>$record['rname'],
                        'tooltip'=>$record['rdesc'],
                        'content'=>'<style>'.$record['rfilters'].'</style>'.$record['rsql'],
                        'icon'=>$record['rcolumns'],
                        'title'=>$record['rtitle']
                    ];
            }
            return $arr;
        }
        
	function getDataForReport($sql,$filtersPost=[],$legacy=FALSE)
	{	
		if ($legacy)
		{
			$sql=str_replace(Arr::ParsePatern(array_keys($filtersPost),'@value'), array_values($filtersPost), $sql);
			return $this->db()->query($sql)->getResultArray();
		}
		
		if (array_key_exists('data_fetch', $sql) && array_key_exists('sql', $sql['data_fetch']))
		{
			$sql=$sql['data_fetch']['sql'];
			$sql=str_replace(array_keys($filtersPost), array_values($filtersPost), $sql);
			return $this->db()->query($sql)->getResultArray();
		}
		
		if (array_key_exists('data_fetch', $sql) && Arr::KeysExists(['controller','action'], $sql['data_fetch']))
		{
			if (array_key_exists('args', $sql['data_fetch']))
			{
				
				$sql['data_fetch']['args']=str_replace(array_keys($filtersPost), array_values($filtersPost), $sql['data_fetch']['args']);
				if (Str::startsWith($sql['data_fetch']['args'],'@'))
				{
					$sql['data_fetch']['args']=substr($sql['data_fetch']['args'], 1);
					if (array_key_exists($sql['data_fetch']['args'], $sql))
					{
						$sql['data_fetch']['args']=$sql[$sql['data_fetch']['args']];
					}else
					{
						$sql['data_fetch']['args']=[];
					}
				}else
				{
					$sql['data_fetch']['args']=explode(',',$sql['data_fetch']['args']);
				}
				
				foreach ($sql['data_fetch']['args'] as $key => $value) 
				{
					if (array_key_exists(substr($value, 1), $filtersPost))
					{
						$sql['data_fetch']['args'][$key]=$filtersPost[substr($value, 1)];
					}
					
					if (array_key_exists(substr($value, 1), $sql))
					{
						$sql['data_fetch']['args'][$key]=$sql[substr($value, 1)];
					}
					
					if (Str::isJSON($value))
					{
						$sql['data_fetch']['args'][$key]=json_decode($value,TRUE);
					}
				}
			}else
			{
				$sql['data_fetch']['args']=[];
			}
			
			$sql=loadModule($sql['data_fetch']['controller'],$sql['data_fetch']['action'],$sql['data_fetch']['args']);
			return is_array($sql) ? array_values($sql) : [];
		}	
		return [];	
		
		if (Str::startsWith($sql,'#'))
		{
			if (!Str::contains($sql,'::'))
			{
				return [];
			}
			$sql=explode('::',$sql);
			$sql[0]=substr($sql[0],1);
			if (Str::contains($sql[1],'@'))
			{
				$sql[2]=explode('@',$sql[1]);
				$sql[1]=$sql[2][0];
				$sql[2]=explode(',',$sql[2][1]);
				foreach ($sql[2] as $key=>$value) 
				{
					if (Str::contains($value,'|'))
					{
						$sql[2][$key]=Arr::fromFlatten($value);
					}
				}
			}
			$sql=loadModule($sql[0],$sql[1],count($sql)>2 ? $sql[2] : null);
			return is_array($sql) ? array_values($sql) : [];
		}
		return $this->db()->query($sql)->getResultArray();
	}
	
        function addBlocksFields($name,array $blocks,$tooltip='')
        {
            $name= str_replace([' ','/'], ['_'], strtolower($name));
            $this->getModel('Settings')->add('reports', 'modelblocks_'.$name, json_encode($blocks),'textlong',$tooltip);
        }
        
        function getBlocksFields() 
        {
            return [];
            $arr=[];
            foreach($this->getModel('Settings')->get('reports.modelblocks_*',FALSE,'*') as $record)
            {
                if (Str::isJson($record['value']))
                {
                    if ($record['tooltip']!=null && strlen($record['tooltip']) >0)
                    {
                        $record['param']=lang($record['tooltip']); 
                    }else
                    {
                        $record['param']= str_replace('modelblocks_', '', $record['param']);
                    }
                    
                    $record['value']= json_decode($record['value'],TRUE);
                    if (is_array($record['value']))
                    {
                        $arr[$record['param']]=$record['value'];
                    }
                }
                
            }
            return $arr;
        }
        
	function runReport1($record,$filtersPost=[])
	{
		if (!is_array($record) || (is_array($record) && !array_key_exists('rsql', $record)))
		{
			return FALSE;
		}
		$record['rcolumns']=str_replace([','.PHP_EOL,', ,'], ',', $record['rcolumns']);
		$filters=[];
		$fields=[];
		foreach (explode(PHP_EOL,$record['rfilters']) as $value) 
		{
			if (!Str::contains($value,'|'))
			{
				continue;
			}
			$value=explode('|', $value);
			
			if (array_key_exists($value[0], $filtersPost))
			{
				$value[1]=$filtersPost[$value[0]]['value'];
			}

			if (Str::contains($value[1],','))
			{
				$value[1]=explode(',', $value[1]);
			} 
			if ($value[1]!=null || (is_string($value[1]) && strlen($value[1]) > 0) )
			{
				$filters[$value[0]]=$value[1];
			}
			
			if (count($value) > 2)
			{
				$fields[]=['name'=>$value[0],'cfid'=>$value[0],'type'=>$value[2],'label'=>count($value) > 3 ? $value[3] : $value[0]];
			}
		}
		//dump($filters);exit;
		$model=model('BaseModel');
		$model->table=$record['rtables'];
		if (count($filters) > 0)
		{
			$model=$model->parseFilters($filters,$model,[],FALSE);
		}
		
		if (Str::contains($record['rcolumns'],'groupBy'))
		{
			$group=Str::after($record['rcolumns'],'groupBy');
			$record['rcolumns']=Str::before($record['rcolumns'],'groupBy');
			$group=explode(' ', $group);
			$group=$group[1];
			$group=explode(',', $group);
			$model->groupBy($group);
		}
		
		$model->select($record['rcolumns']);
		//dump($filtersPost);exit;
		return ['data'=>$model,'fields'=>$fields];
	}
}