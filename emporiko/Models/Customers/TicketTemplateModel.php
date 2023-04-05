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
 
namespace EMPORIKO\Models\Customers;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class TicketTemplateModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='tickets_templates';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'titid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','desc','iscustomer','targetgrp','descfield'
                                  ,'title','subjectfield','extrafields','tcktype'
                                  ,'editabledesc','template','enabled'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[tickets_templates.name,titid,{titid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'titid'=>               ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'title'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'desc'=>		['type'=>'TEXT','null'=>FALSE],
                'editabledesc'=>        ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
                'iscustomer'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
                'targetgrp'=>		['type'=>'TEXT','null'=>TRUE],
                'descfield'=>		['type'=>'TEXT','null'=>TRUE],
                'subjectfield'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'extrafields'=>		['type'=>'TEXT','null'=>TRUE],
                'template'=>		['type'=>'TEXT','null'=>TRUE],
                'tcktype'=>             ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
        
        /**
         * Returns array with cases templates data available only for customers
         * 
         * @return array
         */
        function getTilesForCustomer(array $filters=[])
        {
            $filters['enabled']=1;
            $filters['iscustomer']='1';
            return $this->getTiles($filters);
        }
        
        /**
         * Returns array with cases templates
         * 
         * @param array $filters
         * 
         * @return array
         */
        function getTiles(array $filters=[])
        {
            $arr=[];
            $url_args=['tpl'=>'-tpl-','refurl'=> current_url(FALSE,TRUE)];
            if (array_key_exists('acc', $filters))
            {
                $url_args['acc']=$filters['acc'];
                unset($filters['acc']);
            }
            if (array_key_exists('refurl', $filters))
            {
                $url_args['refurl']=$filters['refurl'];
                unset($filters['refurl']);
            }
            $url=url('Tickets','cases',['new'],$url_args);
            $types=$this->getModel('Ticket')->getTicketsTypes(TRUE);
            foreach($this->filtered($filters)->find() as $key=>$tpl)
            {
                $key++;
                if (array_key_exists($tpl['tcktype'], $types))
                {
                    $arr[$key]=$types[$tpl['tcktype']];
                }
                $arr[$key]['title']=$tpl['title'];
                $arr[$key]['desc']=$tpl['desc'];
                //$arr[$key]['sort']=$tpl['tcktype'];
                if (!array_key_exists('type', $arr[$key]))
                {
                    $arr[$key]['type']='warning';
                }
                if (!array_key_exists('icon', $arr[$key]))
                {
                    $arr[$key]['icon']='fas fa-exclamation-triangle';
                }
                
                if (!array_key_exists('text_color', $arr[$key]))
                {
                    $arr[$key]['text_color']='#FFF';
                }
                $arr[$key]['url']= str_replace('-tpl-', $tpl['name'], $url);
            }
            
            array_sort_by_multiple_keys($arr,['sort'=>SORT_ASC]);
            return $arr;
        }
        
        /**
         * Returns array with cases templates data
         * 
         * @return array
         */
        function getTilesForEmployee()
        {
            return $this->getTiles(['enabled'=>1]);
        }
        
        /**
         * Returns array with template info or fill record array if provided
         * 
         * @param string $name
         * @param array $record
         * 
         * @return array
         */
        function getByName(string $name,array $record=[])
        {
            $name=$this->filtered(['name'=>$name])->first();
            if (is_array($name) && count($record) > 0)
            {
                $record['tck_subject']=$name['subjectfield']; 
                $record['tck_desc']=$name['descfield']; 
                $record['tck_type']=$name['tcktype']; 
                $record['tck_extrafields']=$name['extrafields'];
                $record['tck_target']= base64_encode($name['name']);
                $record['editabledesc']=$name['editabledesc'];
                $record['iscustomer']=$name['iscustomer'];
            } else 
            {
                $name=[];
            }
            return count($record) > 0 ? $record : $name;
        }
        
        /**
         * Returns array with templates data for drop down field on form
         * 
         * @param bool $isAdvanced
         * 
         * @return array
         */
        function getForDropDown(bool $isAdvanced=TRUE)
        {
            $arr=[];
            foreach($this->filtered(['enabled'=>1])->find() as $tpl)
            {
                if ($isAdvanced)
                {
                  $arr[$tpl['name']]=$tpl['title'].'=>'.$tpl['desc'];  
                }else
                {
                   $arr[$tpl['name']]=$tpl['title'];
                } 
            }
            return $arr;
        }
        
        function getExtraFields(array $filterFields=[],bool $forNewCase=FALSE)
        {
            if (count($filterFields) > 0)
            {
                $filterFields=Arr::Prefix($filterFields, null, 'tickets_tplextrafield_');
                $filterFields =$this->getModel('Settings')->filtered(['param In'=>$filterFields])->find();
            }else
            {
                $filterFields=$this->getModel('Settings')->get('tickets.tickets_tplextrafield_*',FALSE,'*');
            }
            
            $arr=[];
            foreach(is_array($filterFields) ? $filterFields : [] as $ticket)
            {
                $key= str_replace('tickets_tplextrafield_', '', $ticket['param']);
                if ($forNewCase)
                {
                   $arr[$key]=['field'=>$ticket['value'],'label'=>$ticket['tooltip']]; 
                }else
                {
                    $arr[$key]=lang($ticket['tooltip']);
                }
                
            }
            return $arr;
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            $arr['title']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                         ->setName('title')
                         ->setID('title')
                         ->setText('title')
                         ->setTab('general')
                         ->setMaxLength(50)
                         ->setAsRequired();
            
            $arr['desc']=\EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                         ->setName('desc')
                         ->setID('desc')
                         ->setText('desc')
                         ->setTab('general');
            
            $arr['targetgrp']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField::create()
                         ->setName('targetgrp')
                         ->setID('targetgrp')
                         ->setText('targetgrp')
                         ->setTab('general')
                         ->setInputField(\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()->setArgs(
                                 [
                                     'name'=>'targetgrp_input',
                                     'id'=>'id_targetgrp_input',
                                     'options'=>$this->getModel('Ticket')->getTargetGroups()
                                 ]));
            
            $arr['tcktype']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                         ->setName('tcktype')
                         ->setID('tcktype')
                         ->setText('tcktype')
                         ->setTab('general')
                         ->setOptions($this->getModel('Ticket')->getTicketsTypes())
                         ->setAsAdvanced();
            
            $arr['iscustomer']=\EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                         ->setName('iscustomer')
                         ->setID('iscustomer')
                         ->setText('iscustomer')
                         ->setTab('general');
            
            $arr['enabled']=\EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                         ->setName('enabled')
                         ->setID('enabled')
                         ->setText('enabled')
                         ->setTab('general');
            
            $arr['subjectfield']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                         ->setName('subjectfield')
                         ->setID('subjectfield')
                         ->setText('subjectfield')
                         ->setTab('fields')
                         ->setMaxLength(150)
                         ->setAsRequired();
            
            $arr['descfield']= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()
                         ->setName('descfield')
                         ->setID('descfield')
                         ->setText('descfield')
                         ->setTab('fields')
                         ->setBasicToolbar();
            
            $arr['editabledesc']=\EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                         ->setName('editabledesc')
                         ->setID('editabledesc')
                         ->setText('editabledesc')
                         ->setTab('fields');
            
            $arr['extrafields']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                         ->setName('extrafields')
                         ->setID('extrafields')
                         ->setText('extrafields')
                         ->setTab('fields')
                         ->setInputField($this->getExtraFields());/*\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                                 ->setName('extrafields_input')
                                 ->setID('extrafields_input')
                                 ->setOptions($this->getExtraFields()));*/
            
            $arr['template']= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()
                         ->setName('template')
                         ->setID('template')
                         ->setText('')
                         ->setTab('tpl')
                         ->setBasicToolbar();
            return $arr;
        }
        
        
}