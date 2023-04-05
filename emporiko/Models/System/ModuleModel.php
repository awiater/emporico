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
namespace EMPORIKO\Models\System;

use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Arrays as Arr;

class ModuleModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='modules';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'mid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['mname','mdesc','route','theme','cfgmth','menuenabled','acc_view','acc_state',
                              'acc_modify','acc_edit','acc_create','acc_delete','acc_settings','enabled'
                              ,'reportsenabled','report_source'];
    
    protected $validationRules =[];
    
    protected $validationMessages = [];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'mid'=>			['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
	'mname'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
        'mdesc'=>               ['type'=>'TEXT','null'=>TRUE],
        'theme'=>   		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
        'route'=>		['type'=>'TEXT','null'=>TRUE],
        'cfgmth'=>   		['type'=>'TEXT','null'=>TRUE],
        'menuenabled'=>         ['type'=>'INT','constraint'=>'11','default'=>0,'null'=>FALSE],
        'enabled'=>             ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
        'acc_view'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
	'acc_state'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
	'acc_modify'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
	'acc_edit'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
	'acc_create'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
	'acc_delete'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
	'acc_settings'=>	['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE,'foreignkey'=>['users_groups','ugref','CASCADE','CASCADE']],
        'report_source'=>   	['type'=>'TEXT','null'=>TRUE],
        'reportsenabled'=>      ['type'=>'INT','constraint'=>'11','default'=>0,'null'=>FALSE],
    ];
    
    function getViewsForModule($modulename)
    {
        $arr=[];
        $sql="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME LIKE 'vw_{name}%'";
        $sql=$this->query(str_replace('{name}', strtolower($modulename), $sql))->getResultArray();
        if (is_array($sql) && count($sql)>0)
        {
            foreach($sql as $view)
            {
                $arr[]=$view['TABLE_NAME'];
            }
        }
        return $arr;
    }
    
    /**
     * Determines if given module exists
     * 
     * @param  string $name
     * @return bool
     */
    function exists($name)
    {
        return $this->count(['mname'=>$name,'enabled'=>1])>0;
    }
    
    /**
     * Returns modules names and info for profile editor
     * 
     * @return array
     */
    function getForProfileForm()
    {
        $arr=[];
	//getMenuItemsData($value=null,$justItems=FALSE)
        foreach ($this->where('menuenabled',1)->find() as $value)
        {
            $afunc= loadModule(ucwords($value['mname']),'getMenuItemsData',[null,TRUE]);
            foreach($afunc as $key=>$func)
            {
                $nvalue=
                [
                    'controller'=>ucwords($value['mname']),
                    'action'=>is_numeric($key) ? $func : $key
                ];
                $arr[base64_encode(json_encode($nvalue))]= ucwords($value['mname']).'->'.(ucwords($func));
            }
        }
	return $arr;
    }
    
    /**
     * Returns module name / desc array for access field
     * 
     * @return array
     */
    function getForAccessField()
    {
        $arr=[];
	//getMenuItemsData($value=null,$justItems=FALSE)
        foreach ($this->where('enabled',1)->find() as $value)
        {
            $arr[$value['mname']]=ucwords($value['mname']).'=>'.$value['mdesc'];
        }
	return $arr;
    }
    
    /**
     * Get fields array for form
     * 
     * @return array
     */
    function getFieldsForForm(array $record)
    {
        $theme=$this->getModel('Settings')->getTheme();
        $theme= array_key_exists('views', $theme) && is_array($theme['views'])? $theme['views'] :[];
        $theme=$theme+['Index'=>'index'];
        $fields=['mname','mdesc','theme','enabled','acc_view','acc_state','acc_modify','acc_edit','acc_create','acc_delete','acc_settings'];
        $arr=[];
        //getGroupAccess
        foreach($fields as $field)
        {
            $name='modules['.$field.']';
            
            if ($field=='mdesc')
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()->setReadOnly();
            }else
            if ($field=='theme1')
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setOptions(array_flip($theme))
                        ->setAsAdvanced();
            }else
            if ($field=='enabled')
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create();
            }else
            if (Str::startsWith($field, 'acc_'))
            {
                unset($arr[$field]);goto end_loop;
                /*$arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\AcccessField::create()
                        ->setTab('access');*/
            }
            else
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create();
            }
            
            if ($field=='mname')
            {
                $arr[$field]->setReadOnly();
            }
            
            $arr[$field]->setName($name)
                        ->setid($name)
                        ->setText($field);
            end_loop:
        }
        $arr['access_groups']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                ->setName('access_groups')
                ->setid('access_groups')
                ->setText('')
                ->setTab('access');
        return $arr;
        
        foreach ($this->fieldsTypes as $key=>$value)
        {
            $label=$key;
            $key='modules['.$key.']';
            $arr[$key]=$value;
            $arr[$key]['label']='system.settings.modules_'.$label;
            $arr[$key]['dataKey']=$label;
            if (Str::contains($key,'acc_'))
            {
                $arr[$key]['type']='Acccess';
                $arr[$key]['args']=['tab_name'=>'access'];
            }           
        }
        $arr['modules[route]']['type']='InputField';
        $arr['modules[theme]']['type']='DropDown';
        $arr['modules[theme]']['args']['options']= array_flip($theme);
        $arr['modules[mid]']['type']='hidden';
        //unset();
        
        unset($arr['modules[cfgmth]']);
        unset($arr['modules[route]']);
        
        if (array_key_exists('params',$record) && is_array($record['params']))
        {
           foreach($record['params'] as  $key=>$value)
           {
              $arr['modules['.$key.']']=[
                  'type'=>$value['fieldtype'],
                  'value'=>$value['value'],
                  'args'=>['tab_name'=>'params'],
                  'label'=>$value['paramsgroups'].'.'.$value['param']
              ]; 
           }
        }
        
        return $arr;
    }
    
    function getModuleMenuItemsData($value,$name=null)
    {
        $arr=[];
        $filters=['enabled'=>1,'access'=>'@loged_user','menuenabled'=>1];
        if ($name!=null)
        {
            $filters['mname']=$name;
        }
        foreach($this->filtered($filters)->find() as $record)
        {
            $name= ucwords($record['mname']);
            $record= loadModule($name);
            if (method_exists($record, 'getMenuItemsData'))
            {
                $arr[$name]= base64_encode($record->getMenuItemsData($value));
            }    
        }
        return $arr;
    }
    
    function getAccessForUser(string $userid)
    {
        return $this->getModel('Auth/AuthAccess')->getCustomAccess($userid);
    }
    
    function setAccessForUser(string $userid,array $data)
    {
        $this->getModel('Auth/AuthAccess')->setCustomAccess($userid,$data);
    }
    
    
    function getGroupAccess(string $module)
    {
        $select=[];
        $acc=$this->getModel('Auth/AuthAccess');
        $model=$this->getModel('Auth/UserGroup');
        $select[]="'$module' as `module`";
        $select[]=$model->table.'.ugname';
        $select[]=$model->table.'.ugref';
        foreach($acc->allowedFields as $field)
        {
            $select[]=$acc->table.'.'.$field;
        }
        return $model->select(implode(',',$select))
                     ->join($acc->table,$acc->table.".`acc_ref`=CONCAT('$module','.',`".$model->table."`.`ugref`)",'LEFT')
                     ->find();
    }

}