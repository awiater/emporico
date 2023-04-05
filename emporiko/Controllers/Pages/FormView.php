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

  
namespace EMPORIKO\Controllers\Pages;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class FormView extends View
{
	private $_field_accessA=[];
	
	private $_namePrefix=null; 
	
        function __construct($controller, $iscached = TRUE) 
        {
            parent::__construct($controller, $iscached);
            $this->setFile('System/form');
            $this->addCustomScript('number_field_check', str_replace('#', "'", '$(#input[type="number"]#).on("change",function(){if(parseInt($(this).val())>parseInt($(this).attr(#max#))){$(this).val($(this).attr(#max#));}if(parseInt($(this).val())<parseInt($(this).attr(#min#))){$(this).val($(this).attr(#min#));}});'),TRUE);	
        }
	/**
	 * Add form action url
	 * 
 	 * @param  string $controller
 	 * @param  string $action
 	 * @param  array  $params
 	 * @param  array  $get
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setFormAction($controller,$action=null,array $params=[],array $get=[])
	{
		$this->setFormArgs(['id'=>'edit-form']);
		$this->addScript('jquery.inputmask.min','@vendor/jquery/jquery.mask.min.js');
                $this->addCustomScript('jquery_mask_2','$.applyDataMask();',TRUE);
		$this->setFormSaveUrl(null);	
		return $this->addData('_formview_action',url($controller,$action,$params,$get));
	}
       
        
        /**
	 * Sets form arguments (ie. class)
	 * 
	 * @param  array $formArgs
	 * @param  array $hiddenFields
	 * @param  array $formCardArgs
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setFormArgs(array $formArgs,array $hiddenFields=[],array $formCardArgs=[])
	{
		if (!array_key_exists('class', $formCardArgs))
		{
			$formCardArgs['class']='col-xs-12 col-md-8';
		}
		
		if (!array_key_exists('id', $formCardArgs))
		{
			$formCardArgs['id']='form_container';
		}
		$cargs=$this->getViewData('_formview_action_attr');
		$formArgs=array_merge(is_array($cargs) ? $cargs : [],$formArgs);
                 if (array_key_exists('_formview_action_hidden', $this->viewData) && is_array($this->viewData['_formview_action_hidden']))
                {
                     $hiddenFields=array_merge($hiddenFields,$this->viewData['_formview_action_hidden']);
                }
		$hiddenFields['movements_logger']=base64_encode(json_encode([]));
		$this->addCustomScript('movement_function',view('System/Elements/movement_function'),FALSE);
		return $this->addData('_formview_action_attr',$formArgs)
					->addData('_formview_action_hidden',$hiddenFields)
					->addData('_formview_card_attr',$formCardArgs);
	}
	
        /**
         * Set flag to parse array value of fields to json
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function parseArrayFields()
        {
           
            $this->viewData['_formview_action_hidden']['_check_array_fields']=TRUE;
            return $this;
        }
        
        
        /**
         * Add simple validation to form fields (check if required fields are filled)
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addSimpleValidation()
        {
            return $this->addValidation('simple');
        }
        
        /**
         * Add validation to form fields
         * 
         * @param string $model
         * @param array  $fields
         * @param bool   $addLiveCheck
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addValidation(string $model)
        {
            if ($model=='no_validaton')
            {
                return $this->setFormArgs(['novalidate'=>'true']);
            }else
            if ($model=='simple')
            {
                $model='simple';
            }else
            {
                $model=['url'=>url($this->controller,'validatefield',[$model])];
            }
            return $this->addData('_formvalidation',$model);
        }
        
	/**
	 * Determine if custom view is used
	 * 
	 * @param  bool $enabled
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setCustomViewEnable($enabled=TRUE)
	{
		return $this->addData('_formview_custom',$enabled);
	}
	
        
        
        /**
         * Set env variable for array values parsing to JSON when saving to DB
         * 
         * @param  mixed $field
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function setArrayValuesAsJSON($field=null)
        {
           if ($field!=null)
           {
               $field=is_array($field) ? $field : [$field];
               $field=json_encode($field);
           }
          return $this->addHiddenField('_check_array_fields',$field);
        }
        
	/**
	 * Sets cancel url
	 * 
 	 * @param  string $controller
 	 * @param  string $action
 	 * @param  array  $params
 	 * @param  array  $get
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setFormCancelUrl($controller,$action=null,$params=[],$get=[])
	{
		return $this->addData('_formview_urlcancel',url($controller,$action,$params,$get));
	}
	
	/**
	 * Sets save button
	 * 
	 * @param  mixed $data
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setFormSaveUrl($data)
	{
		$data=$data==null ? [] : $data;
                if (is_string($data))
                {
                    $data=['text'=>$data];
                }
		if (is_array($data))
		{
			if (!array_key_exists('text', $data))
			{
                            $data['text']='system.buttons.save';
			}
			if (!array_key_exists('type', $data))
			{
				$data['type']='submit';
			}
			if (!array_key_exists('icon', $data))
			{
				$data['icon']='far fa-save';
			}
			if (!array_key_exists('class', $data))
			{
				$data['class']='btn btn-success';
			}
			if (!array_key_exists('id', $data))
			{
				$data['id']='id_formview_submit';
			}
		}
		return $this->addData('_formview_savebtn',$data);
	}
	
	/**
	 * Sets form (card header) title
	 * 
	 * @param  string $title
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setFormTitle($title,$tags=[])
	{
		$tags=is_array($tags) ? $tags : [$tags];
		return $this->addData('_formview_title',lang($title,$tags));
	} 
	
	/**
	 * Sets custom save button
	 * 
	 * @param  string $text
	 * @param  string $icon
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setCustomSaveButton($text,$icon=null)
	{
		$icon=$icon==null ? null : $icon.' mr-1';
		return $this->addData('_formview_custom_save',[$icon,lang($text)]);
	}

	/**
	 * Determines if form is tabbed
	 * 
	 * @return Bool
	 */
	function isTabbed()
	{
		$tabs=$this->getViewData('_fieldstabs');
		return is_array($tabs) && count($tabs) >0;
	}
	
	/**
	 * Sets form fields tabs
	 * 
	 * @param  array $tabs
	 * @param  array  $record
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setTabs(array $tabs,$record=null,array $accesForTabs=[])
	{
		if (!$this->isTabbed())
		{
			$this->viewData['_fieldstabs']=[];
		}
		if (is_array($record))
		{
			foreach ($tabs as $key => $value) 
			{
				if (!array_key_exists('cfg_tabaccess_'.$key, $accesForTabs) || (array_key_exists('cfg_tabaccess_'.$key, $accesForTabs) && $this->auth->hasAccess($accesForTabs['cfg_tabaccess_'.$key])))
				{ 
                                    
					if (is_array($value) && count($value) == 2)
					{
						if (is_array($value[1]))
						{
							$value[1]['args']=array_key_exists('args', $value[1]) ? $value[1]['args'] : [];
							$value[1]['args'][]=$record;	
							$value[1]=loadModuleFromArray($value[1]);
						}else
						if (is_string($value[1]) && Str::contains($value[1], '::'))
						{
							$value[1]=loadModuleFromString($value[1],$record);
						}else
						{
							$value[1]=null;
						}
						if ($value[1]==null)
						{
							return $this;
						}
						$this->setTab($key,$value[0]);
						$this->setCustomTabContent($key,$value[1]);
					}
				}
			}
		}else
		{
			$this->viewData['_fieldstabs']=$this->viewData['_fieldstabs']+$tabs;
		}
		
		return $this;
	}
	
	/**
	 * Set custom tab content
	 * 
	 * @param  string $tabName
	 * @param  mixed  $content
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setCustomTabContent($tabName,$content)
	{
		if (array_key_exists($tabName, $this->viewData['_fieldstabs']))
		{
			if (is_array($content))
			{
				$this->viewData['fields'][$tabName]=$content;
			}else
			{
				$this->viewData['fields'][$tabName][]=$content;
			}
			
		}
		
		return $this;
	}
	
	/**
	 * Set current tab name
	 * 
	 * @param  string $tabName
	 * @param  string $tabText
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function setTab($tabName,$tabText,$order=null)
	{
		if (!$this->isTabbed())
		{
			$this->viewData['_fieldstabs']=[];
		}
                if (is_numeric($order))
                {
                   $arr=[];
                   $ind=0;
                   foreach($this->viewData['_fieldstabs']as $key=>$value)
                   {
                       if ($ind==$order)
                       {
                           $arr[$tabName]=lang($tabText);
                       }
                       $arr[$key]=$value;
                       $ind++;
                   }
                   $this->viewData['_fieldstabs']=$arr;
                }else
                {
                    $this->viewData['_fieldstabs'][$tabName]=lang($tabText);
                }
		
		return $this;
	}
	
	/**
	 * Returns last set fields tab name
	 * 
	 * @return null/string
	 */
	function getCurrentTab()
	{
		if (!$this->isTabbed())
		{
			return null;
		}
		$tabs=array_keys($this->viewData['_fieldstabs']);
		if (count($tabs)<1)
		{
			return null;
		}
		return $tabs[count($tabs)-1];
	}
	
	/**
	 * Add custom field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Field arguments
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addCustomField($label,$name,$value,$dataField,array $args=[])
	{
             if (array_key_exists('field_name', $args))
             {
                 $name=$args['field_name'];
             }
		if (is_array($this->_field_accessA) && count($this->_field_accessA) > 0)
		{
			$_namePrefix=$this->_namePrefix.$name;
			if (array_key_exists($_namePrefix, $this->_field_accessA))
			{
				$_namePrefix=$this->_field_accessA[$_namePrefix];
				if (!$this->controller->auth->hasAccess($_namePrefix))
				{
					$args['type']='hidden';
					
				}
			}
			
		}
		$required=FALSE;
		if (array_key_exists('required', $args)&&(strtolower($args['required'])=='true'||$args['required']==TRUE))
		{
			$required=TRUE;
		}
		if (array_key_exists('id', $args))
		{
			$args['id']=$this->parseFieldID($args['id']);
		}
		$field=
		[
			'label'=>$label,
			'name'=>$name,
			'value'=>$value,
			'args'=>$args,
			'field'=>$dataField,
			'required'=>$required
		];	
		return $this->addField($field, $args);
	}
	
        private function addField($field,$args=[])
        {
            if ($this->isTabbed())
            {
                $tab=$this->getCurrentTab();
                if (array_key_exists('tab_name', $args))
		{
                    $tab=$args['tab_name'];
		}
		if ($tab==null)
		{
                    $tab='_others';
		}
                if (is_array($field) && array_key_exists('name', $field))
                {
                  $this->viewData['fields'][$tab][$field['name']]=$field;  
                }else
                {
                    $this->viewData['fields'][$tab][]=$field;
                }
            }else
            {
                if (is_array($field) && array_key_exists('name', $field))
                {
                   $this->viewData['fields'][$field['name']]=$field; 
                } else 
                {
                   $this->viewData['fields'][]=$field; 
                }
                
            }
            return $this;
        }
        
        /**
	 * Add form fields from model or array
	 * 
	 * @param  mixed  $model
	 * @param  array  $values
	 * @param  string $label_patern
	 * @param  bool   $readonly
	 * @param  string $patern
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addFieldsFromModel($model,array $values,$label_patern,$readonly=FALSE,$patern="@name")
	{
                if (is_string($model) && array_key_exists(strtolower($model), $this->controller->assocModels))
                {
                    $model= ucwords($model);
                    $model='model_'.$model;
                    $model=$this->controller->{$model};
                }
		if (is_subclass_of($model,'\EMPORIKO\Models\BaseModel'))
		{
                    if (method_exists($model, 'getFieldsForForm'))
                    {
                        $model=$model->getFieldsForForm($values);
                    }else
                    {
                        $model=array_key_exists($model->primaryKey, $model->fieldsTypes) ? array_slice($model->fieldsTypes,1) : $model->fieldsTypes;
                    }
		}
                
		$model=is_array($model) ? $model : [];
                
		foreach ($model as $key => $field)
		{
                   if (is_a($field, 'EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
                   {
                       $name=$field->getArgs('name');
                       $name= str_replace(['[',']'], '', $name);
                       if ($field->isArgExists('label'))
                       {
                           $label=lang($field->getArgs('label'));
                       }else
                       {
                           $label=lang(str_replace('-key-', $field->getArgs('name'), $label_patern));
                       }
                       
                       if (array_key_exists($name, $values)||array_key_exists($key, $values))
                       {
                           $index=array_key_exists($name, $values) ? $name : $key;
                           $field->setValue($values[$index]);
                       }
                       
                       $args=$field->getArgs();
                       $label=$field->getArgs('label');
                       $label=Str::startsWith($label,'=') ? substr($label,1) : lang(str_replace('-key-', $label, $label_patern));
                       if (strlen($field->getArgs('label')) < 1)
                       {
                           $label='';
                       }else
                       if (!$field->isArgExists('tooltip'))
                       {
                           $tooltip=str_replace('-key-', $field->getArgs('label').'_tooltip', $label_patern);
                           $args['tooltip']=lang($tooltip);
                           if ($tooltip==$args['tooltip'])
                           {
                               $args['tooltip']='';
                           }
                       }
                       
                       $this->addCustomField($label,$name , $field->render(), $name,$args);
                       if ($field->isTypeOf('DropDownField') && $field->isAdvanced())
                       {
                           $this->addSelect2('.select2');
                       }else
                       if ($field->isTypeOf('PartNumbersListField') && $field->isValueFieldVisible())
                       {
                           $this->addInputMaskScript();
                           $this->addSelect2('.select2');
                       }else
                       if ($field->isTypeOf('TinyEditor'))
                       {
                           $this->addEditorScript();
                       }else
                       if ($field->isTypeOf('DatePicker') && $field->isTimePicker())
                       {
                           $this->addTimePickerScript('[data-timepicker="true"]');
                       }
                   }        
		}
		return $this;
	}
        
	/**
	 * Add form fields from model or array
	 * 
	 * @param  mixed  $model
	 * @param  array  $values
	 * @param  string $label_patern
	 * @param  bool   $readonly
	 * @param  string $patern
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addFieldsFromModel1($model,array $values,$label_patern,$readonly=FALSE,$patern="@name")
	{
                if (is_string($model) && array_key_exists(strtolower($model), $this->controller->assocModels))
                {
                    $model= ucwords($model);
                    $model='model_'.$model;
                    $model=$this->controller->{$model};
                }
		if (is_subclass_of($model,'\EMPORIKO\Models\BaseModel'))
		{
                    if (method_exists($model, 'getFieldsForForm'))
                    {
                        $model=$model->getFieldsForForm($values);
                    }else
                    {
                        $model=array_key_exists($model->primaryKey, $model->fieldsTypes) ? array_slice($model->fieldsTypes,1) : $model->fieldsTypes;
                    }
		}
                
		$model=is_array($model) ? $model : [];
                
		foreach ($model as $key => $field)
		{
			$nfield=
			[
				'type'=>$this->getFieldTypeFromModelField($field,$key),
				'value'=> array_key_exists('value',$field) ? $field['value'] :(array_key_exists($key, $values) ? $values[$key] : (array_key_exists('dataKey', $field) && array_key_exists($field['dataKey'], $values)? $values[$field['dataKey']] : null)),
				'label'=>array_key_exists('label', $field) ? lang($field['label']==null ? '' : $field['label']) :lang(str_replace('-key-', $key, $label_patern)),
				'name'=>array_key_exists('args', $field) && array_key_exists('name', $field['args']) ? $field['args']['name'] : $key
			];
                        
                        if ($nfield['type']=='DateField' || $nfield['type']=='DatePicker')
                        {
                            
                            if(array_key_exists('args', $field) && array_key_exists('readonly', $field['args']))
                            {
                                if (!array_key_exists('dateFormat', $field['args']))
                                {
                                    $field['args']['dateFormat']='d M yy';
                                }        
                                //$nfield['value']= convertDate($nfield['value'], 'DB',$field['args']['dateFormat']);
                            }
                        }
                        
                        if (array_key_exists('args', $field) && is_array($field['args']))
                        {
                            $nfield['args']=$field['args'];
                            if (array_key_exists('type', $nfield['args']))
                            {
                                $nfield['type']=$nfield['args']['type'];
                                unset($nfield['args']['type']);
                            }
                        }else
                        {
                            $nfield['args']=[];
                        }
                        $field['label']=str_replace('-key-', $key, $label_patern);
                        if (array_key_exists('tooltip', $nfield['args']) && strlen($nfield['args']['tooltip']) >0)
                        {
                            $tooltip=lang($nfield['args']['tooltip']);
                            if ($nfield['args']['tooltip']!=$tooltip)
                            {
                                $nfield['args']['tooltip']=$tooltip;
                            }else
                            {
                                if (!Str::contains($nfield['args']['tooltip'], '.'))
                                {
                                    $nfield['args']['tooltip']=$tooltip;
                                } else 
                                {
                                    $nfield['args']['tooltip']='';
                                }
                                
                            }
                        }else
                        if ($field['label']!=null && lang($field['label'].'_tooltip')!=null && lang($field['label'].'_tooltip')!=$field['label'].'_tooltip')
                        {
                            $nfield['args']['tooltip']=lang($field['label'].'_tooltip');                                
                        }
                       
                       
			if (array_key_exists('constraint', $field))
			{
                            $nfield['args']['maxlength']=$field['constraint'];
			}
                        
			if (array_key_exists('null', $field) && !$field['null'])
			{
				$nfield['required']=TRUE;
			}
			$this->addCustomFieldFromData($nfield,$readonly,$patern);
		}
		return $this;
	}
	private function getFieldTypeFromModelField($field,$name)
	{
            if (!array_key_exists('type',$field)) 
            {
               return null;
            }
                if (method_exists($this,'add'.$field['type'].'Field'))
                {
                    return $field['type'];
                }else
                if (method_exists($this,'add'.$field['type']))
                {
                    return $field['type'];
                }
                $field['type']=strtolower($field['type']);
                if (Str::contains(strtolower($name), 'email') && ($field['type']=='text' || $field['type']=='varchar'))
                {
                    return 'EmailField';
                }else
		if ($field['type']=='text' || $field['type']=='textlong')
		{
			return 'TextArea';
		}else
		if (strtolower($name)=='access')
		{
			return 'AcccessField';
		}else
		if ($field['type']=='varchar' && $field['constraint']==12)
		{
			return 'DateField';
		}else
		if ($field['type']=='int' && $field['constraint']==11)
		{
			return 'YesNo';
		}else
		{
			return 'InputField';
		}
	}
	function addCustomFields(array $fieldsData=[],$readonly=FALSE,$patern="customfields[@cfid][value]")
	{
		foreach ($fieldsData as $key => $field) 
		{
			$this->addCustomFieldFromData($field,$readonly,$patern);
		}
		return $this;
	}

	function addCustomFieldFromData($field,$readonly=FALSE,$patern="customfields[@cfid][value]")
	{
                
		if (is_string($field))
		{
			for ($i=0; $i < 4-substr_count($field,'|') ; $i++) 
			{ 
				$field.='|';
			} 
			$field=array_combine(['name','value','type','label','options'], explode('|', $field));
			$field['cfid']=$field['name'];
		}
                
                
		if (array_key_exists('type', $field))
		{
				if (!array_key_exists('cfid', $field) || (array_key_exists('cfid', $field) && $field['cfid']==null))
				{
					$field['cfid']=$field['name'];
				}
				
				if (!array_key_exists('label', $field))
				{
					$field['label']=$field['name'];
				}
				foreach ($field as $key => $value) 
				{
					if (is_string($key) && is_string($value))
					{
						$patern=str_replace('@'.$key, $value, $patern);
					}
					
				}
                                
				$field['name']=$patern;
                                if (array_key_exists('required', $field) && $field['required'])
				{
					$required=TRUE;
				}else
                                {
                                    $required=FALSE;
                                }
				$field['required']=[];
				if (array_key_exists('args', $field))
				{
                                        if (is_array($field['args']) && array_key_exists('options', $field['args']))
                                        {
                                            $field['options']=$field['args']['options'];
                                            unset($field['args']['options']);
                                        }
                                        $field['required']=$field['args'];
                                        unset($field['args']);
				}
                                if ($required)
                                {
                                    $field['required']['required']='TRUE';
                                }
                                
				
				if ($readonly)
				{
					$field['required'][$field['type']=='YesNo' || $field['type']=='AcccessField' ? 'disabled' : 'readonly']=TRUE;
					$field['required']['class']='bg-light';
				}
				if (array_key_exists('typeid', $field) && array_key_exists('cfid', $field))
				{
					$this->addHiddenField('customfields['.$field['cfid'].'][type]',$field['typeid']);
				}
				if (array_key_exists('target', $field) && array_key_exists('cfid', $field))
				{
					$this->addHiddenField('customfields['.$field['cfid'].'][target]',$field['target']);
				}
                                
				if (array_key_exists('cfid', $field) && is_numeric($field['cfid']))
				{
					$this->addHiddenField('customfields['.$field['cfid'].'][cfid]',is_numeric($field['cfid']) ? $field['cfid'] : null);
				}
				
				if (!array_key_exists('value', $field))
				{
					$field['value']=null;
				}
				if (!array_key_exists('cfid', $field))
				{
					$field['cfid']=$key;
				}
                                if ($field['type']=='Editor')
                                {
                                  $this->addEditor(ucwords($field['label']), $field['name'], $field['value'], array_key_exists('mode', $field['required']) ? $field['required']['mode'] : 'simple', 200, array_key_exists('id', $field['required']) ? $field['required']['id'] : null);
                                }else
                                if ($field['type']=='ImagePicker')
                                {
                                    $this->addImagePicker(ucwords($field['label']), $field['name'], $field['value'],$field['required']);
                                }else
                                if ($field['type']=='ColorPickerField' || $field['type']=='ColorPicker')
                                {
                                    $this->addColorPickerField(ucwords($field['label']), $field['name'], $field['value'],$field['required']);
                                }else
                                if ($field['type']=='CustomTextField')
                                {
                                    $this->addCustomTextField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
                                }else
                                if ($field['type']=='PasswordField' || $field['type']=='password')
                                {
                                    $field['required']['type']='password';
                                    $this->addInputField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
                                }else
                                if ($field['type']=='UploadField' || $field['type']=='Upload')
                                {
                                    $this->addUploadField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
                                }else
                                if (strtolower ($field['type'])=='hidden')
                                {
                                    $this->addHiddenField($field['name'], $field['value'],['id'=>'id_'.$field['name']]);
                                }else
                                if ($field['type']=='Curr' || $field['type']=='CurrField')
				{
                                    $this->addCurrField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
                                }else
				if ($field['type']=='Number' || $field['type']=='NumberField')
				{
                                    $this->addNumberField(ucwords($field['label']),$field['value'],$field['name'], array_key_exists('max', $field['required']) ? $field['required']['max'] : 100,array_key_exists('min', $field['required']) ? $field['required']['min'] : 0,$field['required']);
				}else
				if ($field['type']=='EmailField')
				{
					$this->addEmailField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='InputField')
				{
					$this->addInputField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='TextArea')
				{
					$this->addTextAreaField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='YesNo')
				{
                                    $this->addYesNoField(ucwords($field['label']),$field['value'],$field['name'],$field['required']);
				}else
				if ($field['type']=='AcccessField' || $field['type']=='Acccess')
				{
					$this->addAcccessField(ucwords($field['label']),$field['value'],$field['name'],[],$field['required']);
				}else
				if ($field['type']=='CustomersField')
				{
					$this->addInputListField(ucwords($field['label']),$field['name'],$field['value'],model('Owners/CustomerModel')->getForForm('code','code'),$field['required']);
				}else
				if ($field['type']=='UserField')
				{
					$this->addInputListField(ucwords($field['label']),$field['name'],$field['value'],model('Auth/UserModel')->getForForm('username','username'),$field['required']);
				}else
				if ($field['type']=='DateField' || $field['type']=='DatePicker')
				{
					if (array_key_exists('options', $field) && Str::isJson($field['options']))
					{
						$field['options']=json_decode($field['options'],TRUE);
						$field['required']=$field['required']+$field['options'];
										
					}
					$this->addDatePicker($field['label'],$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='DropDown' && array_key_exists('options', $field) && (is_array($field['options']) || (is_string($field['options']) && strlen($field['options']) > 0 && (Str::contains($field['options'],'::') || Str::isJson($field['options'])))))
				{
					if (is_string($field['options']) && Str::isJson($field['options']))
					{
						$field['options']=json_decode($field['options'],TRUE);					
					}else
                                        if(is_string($field['options']))
					{
						$field['options']=loadModuleFromString($field['options']);
					}else
                                        if (!is_array($field['options']))
                                        {
                                            $field['options']=[];
                                        }
					$this->addDropDownField(ucwords($field['label']),$field['name'],$field['options'],$field['value'],$field['required']);
				}else
				if ($field['type']=='CheckList' && array_key_exists('options', $field) && (is_array($field['options']) || (is_string($field['options']) && strlen($field['options']) > 0 && (Str::contains($field['options'],'::') || Str::isJson($field['options'])))))
				{
					if (is_string($field['options']) && Str::isJson($field['options']))
					{
						$field['options']=json_decode($field['options'],TRUE);					
					}else
                                        if(is_string($field['options']))
					{
						$field['options']=loadModuleFromString($field['options']);
					}else
                                        if (!is_array($field['options']))
                                        {
                                            $field['options']=[];
                                        }
					$this->addCheckList(ucwords($field['label']),$field['name'],$field['value'],$field['options'],$field['required']);
				}else
                                if ($field['type']=='ElementsListBox')
                                {
                                    $this->addElementsListBoxField(ucwords($field['label']), $field['name'], $field['value'], $field['required']);
                                }else
                                if ($field['type']=='CustomElementsList')
                                {
                                   $this->addCustomElementsListField(ucwords($field['label']),$field['name'],$field['value'],$field['required']); 
                                }else
                                if ($field['type']=='InputList' && array_key_exists('options', $field) && (is_array($field['options']) || (is_string($field['options']) && strlen($field['options']) > 0 && (Str::contains($field['options'],'::') || Str::isJson($field['options'])))))
                                {
                                    if (is_string($field['options']) && Str::isJson($field['options']))
                                    {
						$field['options']=json_decode($field['options'],TRUE);					
                                    }else
                                        if(is_string($field['options']))
					{
						$field['options']=loadModuleFromString($field['options']);
					}else
                                        if (!is_array($field['options']))
                                        {
                                            $field['options']=[];
                                        }
                                        $this->addInputListField(ucwords($field['label']),$field['name'],$field['value'],$field['options'],$field['required']);
                                }        
							
				
			}
			return $this;
	}
	
	/**
	 * Add enabled field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  Array   $args       Field arguments
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addYesNoField($label,$value,$name='enabled',array $args=[])
	{
            $field=HtmlItems\YesNoField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            return $this->addCustomField($label, $name,$field->render(), $name, $field->getArgs());
	}
	
	/**
	 * Add number field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  Array   $args       Field arguments
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addNumberField($label,$value,$name,$max=100,$min=0,array $args=[])
	{
            $field= HtmlItems\NumberField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setMax($max)
                    ->setMin($min);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());	
	}
	
	/**
	 * Add hidden field to view
	 * 
	 * @param  String  $name       Field name
	 * @param  String  $value      Field value
	 * @param  Array   $args       Field arguments
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addHiddenField($name,$value,array $args=[])
	{
            $field= HtmlItems\HiddenField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            return $this->addCustomField('@hidden', $name, $field->render(), $name,$field->getArgs());
	}	
	
	/**
	 * Add dropdown field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $options    Dropdown field options
	 * @param  String  $value      Field value
	 * @param  Array   $args       Field arguments
	 * @return \VLMS\Libraries\Pages\FormView
	 */
	function addDropDownField($label,$name,array $options,$value,array $args=[])
	{
            $field= HtmlItems\DropDownField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setOptions($options);
            
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
	
        /**
         * 
         * 
         * @param string $label
         * @param string $name
         * @param array  $options
         * @param string $editUrl
         * @param type   $value
         * @param array  $args
         * 
         * @return \VLMS\Libraries\Pages\FormView
         */
        function addDropDownEditableField(string $label,string $name,array $options,$value,array $args=[])
        {
            $field= HtmlItems\DropDownEditableField::create()
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setOptions($options)
                    ->setArgs($args);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
	function addColorPickerField($label,$name,$value,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$this->addColorPickerScript();
		//$this->addInputField($label,$name,null,$args);
		return $this->addCustomField(
		$label,
		$name,
		view('System/Elements/colorpicker',['args'=>$args,'value'=>$value,'name'=>$name]),
		$name,
		$args);
	}
	
	function addColorPickerScript(array $args=[])
	{
		$this->addScript('bootstrap-colorpicker','@vendor/bootstrap/js/bootstrap-colorpicker.min.js');
		$this->addCss('bootstrap-colorpicker','@vendor/bootstrap/css/bootstrap-colorpicker.min.css');
		if (array_key_exists('id', $args))
		{
			$this->addCustomScript('bootstrap-colorpicker-init','$("#'.$args['id'].'").colorpicker();',TRUE);
		}
		if (array_key_exists('init', $args))
		{
			$this->addCustomScript('bootstrap-colorpicker-init','$("'.$args['init'].'").colorpicker();',TRUE);
		}
		
		return $this;
	}

	function addSignaturePad($label,$name,$value,array $args=[])
        {
             $field= HtmlItems\SignaturePad::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setPadColor('#FFF')
                    ->enableChangeEvent()
                    ->setButton('btn btn-dark btn-sm');
            $this->addSignaturePadLib();
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
	function addDatePicker($label,$name,string $value,array $args=[])
	{
            $field= HtmlItems\DatePicker::create()
                    ->setViewFormat('dd M yy','d M Y')
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
	
        function addTimePicker($label,$name,$value,array $args=[])
        { 
            $field= HtmlItems\TimePicker::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            $this->addTimePickerScript('#'.$field->getArgs('id'));
            return $this->addCustomField($label, $name,$field->render(), $name,$field->getArgs());
        }
        
	/**
	 * Add texarea field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Custom field attributes
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addTextAreaField($label,$name,$value=null,array $args=[])
	{
            $field= HtmlItems\TextAreaField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
	
	/**
	 * Add access dropdown field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field value
	 * @param  Array   $options    Access levels array
	 * @param  Array   $args       Field arguments
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addAcccessField($label,$value,$name='access',array $options=[],array $args=[])
	{
            $field= HtmlItems\AcccessField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setListOptions($options)
                    ->setValue($value);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());//NjEyZGU3ZjkyNDAyYg
	}
	/**
	 * Add email field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	public function addEmailField($label,$name,$value=null,array $args=[])
        {
            $field= HtmlItems\EmailField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
        function addButtonsToolBar($name,array $buttons,array $args=[])
        {
            if (!array_key_exists('position', $args))
            {
                $args['position']='mr';
            }else
            {
                $args['position']=$args['position']=='right' ? 'ml' : 'mr';
            }
            
            if (!array_key_exists('bg', $args) && !array_key_exists('background', $args))
            {
                $args['background']='light';
            }else
            if (array_key_exists('bg', $args))
            {
                $args['background']=$args['bg'];
                unset($args['bg']);
            }
            if (!array_key_exists('padding', $args)|| (array_key_exists('padding', $args) && !is_numeric($args['padding'])))
            {
                $args['padding']=0;
            }
            
            $args['buttons']=$buttons;
            $args['name']=$name;
            return $this->addCustomField(
			'',
			$name,
			view('System/Elements/toolbar',$args),
			null,
                        $args);
        }
        
        /**
         * Add Chart field
         * 
         * @param  string $label
         * @param  string $name
         * @param  array  $data
         * @param  array  $args
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        public function addChartField($label,$name,array $data=[],array $args=[])
        {
            if (!array_key_exists('id', $args))
            {
                $args['id']='id_'.$name;
            }
            $args['id']=$this->parseFieldID($args['id']);
            $args['name']=$name;
            
            if (!array_key_exists('type', $args))
            {
                $args['type']='bar';
            }
            $this->addChartObject($args['type'],$name,$data,$args);
            return $this->addCustomField(
			$label,
			$name,
			$this->getChartObject($name),
			null,
			$args);
        }
        
	/**
	 * Add input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	public function addInputField($label,$name,$value=null,array $args=[])
	{
            $field= HtmlItems\InputField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            if ($field->isMaskSet())
            {
                $this->addInputMaskScript();
            }
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
        
        /**
         * Add data grid field
         * 
         * @param String  $label
         * @param String  $name
         * @param array   $columns
         * @param type    $value
         * @param mixed   $pagination
         * @param array   $args
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addDataGrid(string $label,string $name,array $columns,$value=null,$pagination=TRUE,array $args=[])
        {
            $field= HtmlItems\DataGrid::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setPagination($pagination)
                    ->addColumns($columns);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
        function addMovementsDataField($label,$name,$value=null,$filter=null,array $args=[])
        {
            if (!array_key_exists('pagination', $args))
            {
                $args['pagination']=TRUE;
            }
            $field= HtmlItems\MovementsDataField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            if ($filter!=null)
            {
                if (is_array($filter))
                {
                    foreach ($filter as $key=>$val)
                    {
                        $field->addCustomFilter($key,$val);
                    }
                }else
                {
                    $field->setDefaultFilter($filter);
                }
            }
            
            if ($this->controller->hasAccess(\EMPORIKO\Helpers\AccessLevel::settings))
            {
                $field->setEditable();
            }
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
        function addCustomTextField($label,$name,$value=null,array $args=[])
        {
            if ($label==null)
            {
                return $this->addField($value,$args);
            }
            
           $field= HtmlItems\CustomTextField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
             
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
        function addUploadField($label,$name,$value=null,array $args=[])
        {
            $field= HtmlItems\UploadField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
        /**
         * Add currency field to view
         * 
         * @param type $label
         * @param type $name
         * @param type $value
         * @param array $args
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        public function addCurrField($label,$name,$value=null,array $args=[])
	{
            $field= HtmlItems\InputButtonField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setButtonbefore()
                    ->setButtonIcon('$',TRUE)
                    ->setMask('$')
                    ->setButtonClass('input-group-text font-weight-bold border-right-0')
                    ->setButtonArgs(['style'=>'cursor:default']);
            if ($field->isMaskSet())
            {
                $this->addScript('jquery_mask','@vendor/jquery/jquery.mask.min.js');
                $this->addCustomScript('jquery_mask_scr','$.applyDataMask();',TRUE);
            }
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
	
	/**
	 * Add input field with button to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	public function addInputButtonField($label,$name,$value=null,$action=null,array $args=[])
	{
            $field= HtmlItems\InputButtonField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setButtonAction($action);
            
            if ($field->isArgExists('mask'))
            {
                $this->addScript('jquery_mask','@vendor/jquery/jquery.mask.min.js');
                $this->addCustomScript('jquery_mask_2','$.applyDataMask();',TRUE);
            }
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());  
	}
	
	/**
	 * Return field from view data
	 * 
	 * @param  int $id Id of field (position in field list, null for last)
	 * 
	 * @return string 
	 */
	function getField($id=null)
	{
		$count=is_array($this->viewData['fields']) ? count($this->viewData['fields'])-1 : 0;
                if ($id==null)
                {
                    return $this->viewData['fields'][$count];
                }
                $id= array_deep_search($id, $this->viewData['fields']);
		if ($id!=null)
		{
                    return view('System/form_fields',['fields'=>[$id]]);
		}
		return null;
	}
	
	function checkFieldAccess($name,$field_access=null)
	{
		if ($field_access==null)
		{
			$field_accessA=$this->model_Settings->get('fieldsaccess.*');
			if (!array_key_exists($name, $field_accessA))
			{
				return FALSE;
			}
			$field_access=$field_accessA[$field_access];
		}	
		return $this->controller->auth->hasAccess($field_accessA);
		
	}
	
	function setFieldAccessRule(string $fields_access)//$namePrefix=null
	{
		$this->_namePrefix=str_replace('__', '', $fields_access.'_');
		$fields_access=$this->controller->model_Settings->get('fieldsaccess.'.$fields_access.'*',FALSE,'values',FALSE);
		$this->_field_accessA=is_array($fields_access) ? $fields_access : [$fields_access];
		
		return $this;
	}
	
	
	public function addCheckboxField($label,$text,$name,$value,$checked=TRUE,array $args=[])
	{
            $field= HtmlItems\CheckboxField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setState($checked)
                    ->setText($label)
                    ->setLabel($text);
            return $this->addCustomField($label, $name, $field->render(), $name, $field->getArgs());
	}
	
        /**
         * Add list box fields with removable elements
         * 
         * @param type $label
         * @param type $name
         * @param type $value
         * @param array $args
         * @return type
         */
        function addElementsListBoxField($label,$name,$value=null,array $args=[])
        {
             $field= HtmlItems\ElementsListBoxField::create()
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setArgs($args);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
        
        /**
         * Add list field with new custom element field
         * 
         * @param type $label
         * @param type $name
         * @param type $value
         * @param array $args
         * @return type
         */
        public function addCustomElementsListField($label,$name,$value=null,array $args=[])
        {
             $field= HtmlItems\CustomElementsListField::create()
                    ->setName($name)
                    ->setID($name)
                    ->setArgs($args)
                    ->setValue($value);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs()); 
        }
        
        
        
	/**
	 * Add checkbox list field
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  mixed   $value 	   Field value
	 * @param  Array   $items	   Array with list items (keys as list values, values as list text)
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	public function addCheckList($label,$name,$value=null,array $items=[],array $args=[])
	{
             $field= HtmlItems\CheckList::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setOptions($items);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
        }
	
	/**
	 * Add input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $args       Custom field attributes
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	public function addInputListField($label,$name,$value=null,array $items=[],array $args=[])
	{
             $field= HtmlItems\InputListField::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setOptions($items);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
	
	
	
	/**
	 * Add CSS link and Script link for full calendar
	 * 
	 * @param  Bool $addTimePicker Determines if time picker will be added also
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addCalendarScript($addTimePicker=FALSE)
	{
		if ($addTimePicker)
		{
			$this->addTimePickerScript();
		}
		
		$this->addScript('fullcalendar','@vendor/fullcalendar/main.min.js');
		
		$locale=config('APP')->defaultLocale;
		if ($locale!='en')
		{
			$this->addScript('fullcalendar_lang','@vendor/fullcalendar/lang/locales-all.min.js');
			
		}else
		{
			$locale='en';
		}
		$this->addData('_fullcalendar_locale',$locale);
		return $this->addCss('calendar','@vendor/fullcalendar/main.min.css')
					->addScript('fullcalendar_init','@vendor/fullcalendar/init.js');
	}
	
	/**
	 * Add wyswig editor tag (must be put in script section of view)
	 *
	 * @param  String $label   Field label text 
	 * @param  String $name   Field name 
	 * @param  String $value  Field value 
	 * @param  String $mode   Editor mode (simple,full)
	 * @param  Int    $height Editor height
         * @param  array  $args 
         * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addEditor($label,$name,$value,$mode='simple',$height='200',$id=null,$includeScript=TRUE,array $args=[])
	{
            $args['toolbar']=$mode;
            
             $field= HtmlItems\TinyEditor::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID($name)
                    ->setValue($value)
                    ->setHeight($height);
            $this->addScript('tinymce','@vendor/tinymce/tinymce.min.js');
            return $this->addCustomField($label, $name, $field->render($includeScript), $name,$field->getArgs()); 
	}
        
        /**
         * Add file picker input field to view
         * 
         * @param String       $label
         * @param String       $name
         * @param String       $value
         * @param String|Array $source
         * @param array        $args
         * 
         * @return \EMPORIKO\Libraries\Pages\FormView
         */
        function addFilePicker($label,$name,$value,$source,array $args=[])
        {
            $picker= HtmlItems\FilePicker::create()
                    ->setName($name)
                    ->setArgs($args)
                    ->setID($name)
                    ->setValue($value)
                    ->setSource($source);
            
            return $this->addCustomField(
                        $label,
			$name,
			$picker->render(),
			$name,
			$picker->getArgs());
        }
        
	/**
	 * Add image picker input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Default value of field
	 * @param  String  $dir        File dir name
	 * @param  String  $isPath     Determine if path field is visible
	 * @param  String  $isUpload   Determine if upload button is visible
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \EMPORIKO\Libraries\Pages\FormView
	 */
	function addImagePicker($label,$name,$value,array $args=[])
	{
            $picker=HtmlItems\ImagePicker::create()
                    ->setName($name)
                    ->setArgs($args)
                    ->setID($name)
                    ->setName($name)
                    ->setValue($value)
                    ->setHeight(50)
                    ->setWidth(50)
                    ->setFormat('images')
                    ->setImagePreview(FALSE)
                    ->setImageViewer(FALSE);
            if ($picker->istImageViewer())
            {
                $this->addPrintLibrary();
                $picker->setImageViewerOptions();
            }   
            return $this->addCustomField(
                        $label,
			$name,
			$picker->render(),
			$name,
			$picker->getArgs());
	}
	
	
	/**
	 * Insert access level to view data container
	 * 
	 * @param  Array $data
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function addAccessLevels(array $data=[])
	{
		if (count($data)<1&&!array_key_exists('access_levels', $this->viewData))
		{
			$this->addData('access_levels',count($data)>0?$data:model('Users/LevelsModel')->getLevelsForForm());
		}else
		{
			$this->addData('access_levels',$data);
		}
		return $this;
	}
	
	/**
	 * Adds code editor (codemirror) to view
	 * 
	 * @param  string $editoID     Editor tag id
	 * @param  string $editorTheme Editor theme name
	 * @param  string $mode        Editor mode name
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCodeEditor($label,$name,$value=null,array $args=[])
	{
             $field= HtmlItems\CodeEditor::create()
                    ->setArgs($args)
                    ->setName($name)
                    ->setID('formtpl_editor')
                    ->setValue($value)
                    ->setScripts($this);
            return $this->addCustomField($label, $name, $field->render(), $name,$field->getArgs());
	}
	
	/**
	 * Adds code editor (codemirror) script to view
	 * 
	 * @param  arrays $args Editor tag id
	 * 
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCodeEditorScript(array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='formtpl_editor';
		}
		if (!array_key_exists('theme', $args))
		{
			$args['theme']='blackboard';
		}
		if (!array_key_exists('mode', $args))
		{
			$args['mode']='xml';
		}

		return	$this->addScript('codemirror_js','@vendor/codemirror/lib/codemirror.js')
				 	->addCss('codemirror_css','@vendor/codemirror/lib/codemirror.css')
				 	->addCss('codemirror_theme_css','@vendor/codemirror/theme/'.$args['theme'].'.css')
				 	->addScript('codemirror_mode_js','@vendor/codemirror/mode/'.$args['mode'].'/'.$args['mode'].'.js')
					->addScript('codemirror_mode_js','@vendor/codemirror/starter.js');
	}
	
	
	 
	function addFileManagerLib($dir='.',$isPath=FALSE,$isUpload=FALSE,array $allowedMimes=[])
	{
		$args=[];
		$args['showpath']=$isPath;
		if (count($allowedMimes)>0)
		{
			$args['onlyMimes']=base64_encode(json_encode($allowedMimes));
		}
		
		$args['toolbar']=[['back']];
		if ($isUpload)
		{
			$args['toolbar'][0][]='upload';
		}
		$args['toolbar']=base64_encode(json_encode($args['toolbar']));
		$args['baseURL']=config('App')->baseURL;
		if ($dir!=null&&$dir!='.')
		{
			$args['dir']='l1_'.base64url_encode($dir);
		}
		
		$this->addEditorScript();
		$args['connecturl']=url('Media/MediaAdminController','api');
		$this->addCustomScript('fileeditor_tiny',$this->controller->view('Media/editortiny',$args));
		return $this;
	}
        
        /**
         * Returns form fields view
         * 
         * @param  mixed $params
         * @return string
         */
        function getFields(...$params)
        {
            $fields=$this->getViewData('fields');
            $fields=is_array($fields) ? $fields : [];
            $args=[];
            if (is_array($params) && count($params) > 0)
            {
                if (count($params)==2 && is_numeric($params[0]) && is_numeric($params[1]))
                {
                    $fields= array_slice($fields, $params[0],$params[1]);
                }else
                if (is_string($params[0]) && array_key_exists($params[0], $fields))
                {
                    $fields=[$params[0]=>$fields[$params[0]]];
                }
            }
            $args=['fields'=>$fields];
            foreach($params as $param)
            {
                if (is_array($param))
                {
                    $args=$args+$param;
                }
            }
            
            return $this->includeView('System/form_fields',$args);
        }
	
        private function parseFieldID($id)
        {
            return str_replace(['[',']','/','\\'], ['_',null], $id);
            
        }
}