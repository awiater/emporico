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

class TicketTypeModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='tickets_types';
	
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
	protected $allowedFields=['tit_code','tit_name','tit_desc','tit_editable'
                                  ,'tit_type','tit_icon','tit_textcolor','tit_canconvert'
                                  ,'tit_order'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'titid'=>               ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'tit_code'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'tit_name'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'tit_desc'=>            ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'tit_editable'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
                'tit_type'=>		['type'=>'TEXT','null'=>TRUE],
                'tit_icon'=>            ['type'=>'TEXT','null'=>TRUE],
                'tit_textcolor'=>       ['type'=>'TEXT','null'=>TRUE],
                'tit_canconvert'=>	['type'=>'TEXT','null'=>TRUE],
                'tit_order'=>           ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
	];
        
        /**
         * Returns array with tiles colours
         * 
         * @return array
         */
        function getColorsList()
        {
            return array_combine(['warning','primary','success','danger','info'], lang('tickets.tit_type_list'));
        }
        
        /**
         * Returns array with tile text colours
         * 
         * @return array
         */
        function getTextColorList()
        {
            return array_combine(['#ffc107','#007bff','#28a745','#dc3545','#17a2b8','#000','#FFF'], lang('tickets.tit_textcolor_list'));
        }
        
        /**
         * Returns array with not editable types ID`s
         * 
         * @return type
         */
        function getNotEditable()
        {
            $arr=[];
            foreach($this->filtered(['tit_editable'=>0])->find() as $record)
            {
                $arr[]=$record[$this->primaryKey];
            }
            return $arr;
        }
        
        /**
         * Returns ticket type data
         * 
         * @param string $code
         * @param string $field
         * 
         * @return mixed
         */
        function getTicketData(string $code,string $field='*')
        {
            $arr=[];
            $arr=$this->filtered(['tit_code'=>$code])->first();
            $arr= is_array($arr) ? $arr : [];
            return $field!='*' ? (array_key_exists($field, $arr) ? $arr[$field] : null) : $arr;
        }
        
        /**
         * Get array with types order values
         * 
         * @return array
         */
        function getOrderList()
        {
            $arr=[];
            
            foreach($this->orderBy('tit_order')->find() as $type)
            {
                $arr[$type['tit_code']]=$type['tit_name'];
            }
            return $arr;
        }
        
        /**
         * Set type items presentation order
         * 
         * @param array  $data
         * @param string $orderField
         * @param string $whereField
         * 
         * @return bool
         */
        function setOrder(array $data, string $orderField = null, string $whereField = null) 
        {
            $orderField=$orderField==null ? 'tit_order' : $orderField;
            $whereField=$whereField==null ? 'tit_code' : $whereField;
            return parent::setOrder($data, $orderField, $whereField);
        }
        
        function getConversionPath(string $code,string $caseid)
        {
            $code=$this->filtered(['tit_code'=>$code])->first();
            if (!is_array($code))
            {
                return FALSE;
            }
            $code=$this->geTypeConversionPaths($code['tit_canconvert'],TRUE,FALSE);
            if ($code==null)
            {
                return FALSE;
            }
            if (is_array($code))
            {
                $code= url_from_array($code);
            }else
            if (is_string($code) && Str::contains($code, '::'))
            {
                $code= url_from_string($code);
            }else
            {
                $code=FALSE;
            }
            
            if (is_string($code))
            {
                $code= str_replace('-id-', $caseid, $code);
                $code.=(Str::contains($code, '?') ? '&refurl=' : '?refurl=').current_url(FALSE,TRUE);
            }
            return $code==null ? FALSE : $code;
        }
        
        /**
         * Returns array with available type conversion paths
         * 
         * @param string $name
         * @param bool   $getValue
         * @param bool   $includeBlank
         * 
         * @return array
         */
        function geTypeConversionPaths(string $name='*',bool $getValue=FALSE,bool $includeBlank=TRUE)
        {
            $name=Str::startsWith($name, 'tickets_type_conversion_') ? Str::afterLast($name, '_') : $name;
            
            if ($includeBlank)
            {
                $arr=[lang('tickets.type_blankconv')];
            }else
            {
                $arr=[];
            }
            foreach($this->getModel('Settings')->get('tickets.tickets_type_conversion_*',FALSE,'*') as $type)
            {
                $type['param']=Str::afterLast($type['param'], '_');
                if ($getValue)
                {
                    $arr[$type['param']]=json_decode($type['value'],TRUE);
                    if (!is_array($arr[$type['param']]))
                    {
                        $arr[$type['param']]=$type['value'];
                    }
                }else
                {
                    $arr[$type['param']]= lang($type['tooltip']);
                }
                
            }
            return array_key_exists($name, $arr) ? $arr[$name] : ($name!='*' ? null : $arr);
        }
        
        /**
         * Set type conversion path
         * 
         * @param string $name
         * @param mixed  $value
         * @param string $tootlip
         * 
         * @return boolean
         */
        function setTypeConversionPath(string $name,$value,string $tootlip)
        {
            $name='tickets_type_conversion_'.$name;
            if (is_array($value))
            {
                $value= json_encode($value);
            }
            if (!is_string($value))
            {
                return FALSE;
            }
            return $this->getModel('Settings')->add('tickets',$name, $value,'text', $tooltip);
        }


        function getFieldsForForm(array $record)
        {
            $arr=[];
            $arr['tit_name']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('tit_name')
                    ->setID('tit_name')
                    ->setText('tit_name')
                    ->setMaxLength('50')
                    ->setAsRequired();
            
            $arr['tit_desc']=\EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('tit_desc')
                    ->setID('tit_desc')
                    ->setText('tit_desc')
                    ->setMaxLength('150');
            
            $arr['tit_type']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('tit_type')
                    ->setID('tit_type')
                    ->setText('tit_type')
                    ->setOptions($this->getColorsList())
                    ->setAsRequired();
            
            $arr['tit_textcolor']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('tit_textcolor')
                    ->setID('tit_textcolor')
                    ->setText('tit_textcolor')
                    ->setOptions($this->getTextColorList())
                    ->setAsRequired();
            
            $arr['tit_icon']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('tit_icon')
                    ->setID('tit_icon')
                    ->setText('tit_icon')
                    ->setAsAdvanced()
                    ->setOptions($this->getModel('Settings')->getAvaliableIconsForForm())
                    ->setAsRequired();
            
            $arr['tit_order']=\EMPORIKO\Controllers\Pages\HtmlItems\OrderField::create()
                    ->setName('tit_order')
                    ->setID('tit_order')
                    ->setText('tit_order')
                    ->setOptions($this->getOrderList());
            
            $arr['tit_editable']=\EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                    ->setName('tit_editable')
                    ->setID('tit_editable')
                    ->setText('tit_editable');
            
            $arr['tit_canconvert']=\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                    ->setName('tit_canconvert')
                    ->setID('tit_canconvert')
                    ->setText('tit_canconvert')
                    ->setOptions($this->geTypeConversionPaths());
            
            return $arr;
        }
        
}