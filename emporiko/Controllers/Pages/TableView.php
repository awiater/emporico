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

  
namespace EMPORIKO\Controllers\Pages;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\AccessLevel;

class TableView extends View
{
	/**
	 * Model class
	 * @var \VLMS\Models\BaseModel
	 */
	private $_model;
        
        /**
         * Array with disabled records ID values
         * @var array
         */
        private $_disbled_records=[];
	
	/**
	 * Array with table data
	 * @var array
	 */
	 private $_data=[];
	 
	 /**
	  * Table class
	  * @var \CodeIgniter\View\Table
	  */
	  private $_table;
	  
	  /**
	   * Table columns names (headers) when in mobile viewport
	   * @var array
	   */
	   private $_tbl_cols_mobile=[];
	   
	   /**
	    * Database table columns names
	    * @var Array
	    */
	    private $_data_cols=[];
	   
	   /**
	    * Table columns names (headers)
	    * @var
	    */
	    private $_tbl_cols=[];
		
            /**
	    * Table columns tooltips
	    * @var
	    */
	    private $_tbl_cols_tooltips=[];
		
		/**
		 * Defines button(s) for edit column
		 * @var array
		 */
		 private $_edit_column=[];
		 
		 /**
		  * Determines if enable button is visible
		  * @var bool/string
		  */
		  private $_enable_btn=FALSE;
		  
		  /**
		  * Determines if remove button is visible
		  * @var bool/string
		  */
		  private $_del_btn=FALSE;
		  
		  /**
		   * Filters fields
		   * @var Array
		   */  
		   private $_filters=[];
		   
		   /**
		    * Filters predefinded fields
		    * @var array
		    */
		   private $_filters_fixed=[];
                   
                   /**
                    * Determines if filters values are not null
                    * @var bool
                    */
                   private $_is_filtering=FALSE;
                   
                   /**
                    * Determines if filters message will be overridden
                    * @var bool
                    */
                   private $_is_filtering_over_msg=FALSE;
                   
                   /**
                    * Currently applied filters
                    * @var array
                    */
                   private $_curr_filters=[];
		   
		   /**
		    * Table tag class
		    * @var Array
		    */
		    private $_table_class=[];
			
			/**
			 * Data primary key name
			 * @var string
			 */
			private $_dataPrimaryKey=null;
			
			/**
			 * Determines if is datable is enabled
			 * @var bool
			 */
			private $_datatable=FALSE;
			
			/**
			 * Determines if sorting of columns is enabled
			 * @var bool
			 */
			 private $_sorting=FALSE;
			
			
	
	public function __construct($controller,$iscached)
	{
            parent::__construct($controller,$iscached);
            $this->_table=new \CodeIgniter\View\Table();
            $this->setStrippedTable();
            $this->addData('_tableview_enable',0);
            $this->addData('_tableview_btns_routes',[]);
            $this->addData(' _tableview_custom',FALSE);
            $this->setFile('System/table');
	}
	
	
	/**
	 * Get raw table data
	 * 
	 * @return Array
	 */
	function getTableData()
	{
		return $this->_data;
	}
	
	/**
	 * Set table as bootstrap datatable
	 * 
	 * @param  array $options Optional datatable options
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	function setAsDataTable(array $options=[])
	{
		if (!array_key_exists('searching', $options))
		{
			$options["'searching'"]='true';
		}
		if (!array_key_exists('ordering', $options))
		{
			$options["'ordering'"]='false';
		}
		if (!array_key_exists('paging', $options))
		{
			$options["'paging'"]='true';
		}
		if (!array_key_exists('dom', $options))
		{
			$options["dom"]="Bfrtip";
		}
		
		if (!Str::contains($options['dom'],"'"))
		{
			$options['dom']="'".$options['dom']."'";
		}
		
		return $this->addDataTableScript()
					->addData('_tableview_datatable',$options)
					->addData('_tableview_datatable_filter',array_key_exists('filter', $_GET) ? $_GET['filter'] : null);
	}
	
	/**
	 * Set table class as stripped (default, table-stripped)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function setStrippedTable()
	 {
	 	$this->_table_class['table_class'][]='table-striped';
		return $this;
	 }
	 
	/**
	 * Set table (or just header) class as dark (table-dark)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function setDarkTable(bool $onlyHeaders=FALSE)
	 {
            if (!$onlyHeaders)
            {
                $this->_table_class['table_class'][]='table-dark';
            } else 
            {
                $this->addData('table_head_class', 'thead-dark');
            }
            return $this;
	 }
	 
	/**
	 * Set table as bordered (table-bordered)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function setBorderedTable()
	 {
	 	$this->_table_class['table_class'][]='table-bordered';
		return $this;
	 }
	 
	 /**
	 * Enable tick boxes against table rows
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function addTickBox($hide=FALSE)
	 {
	 	$this->_enable_btn=TRUE;
		return $this;
	 }
	 
         /**
          * Add records ID value(s) which will be disabled (not able to delete or change status)
          * 
          * @param mixed $records
          * 
          * @return \EMPORIKO\Controllers\Pages\TableView
          */
         public function addDisabledRecords($records)
         {
             if (is_array($records))
             {
                 $this->_disbled_records=$records;
             }else
             {
                 $this->_disbled_records[]=$records;
             }
             return $this;
         }
         
	 /**
	  * Sets table title
	  * 
	  * @param string $title
	  * 
	  * @return \EMPORIKO\Controllers\Pages\TableView
	  */
	 function setTableTitle($title)
	 {
	 	return $this->addData('_tableview_card_title',lang($title));
	 }
         
         /**
          * Sets table id
          * 
          * @param string  $id
          * 
          * @return \EMPORIKO\Controllers\Pages\TableView
          */
         function setTableID(string $id)
	 {
            $this->addData('table_view_datatable_id',$id);
            return $this;
	 }
         
         /**
          * Set message which will be shown when there is not data in table
          * 
          * @param string  $msg
          * @param boolean $overrideFilterMsg
          * 
          * @return \EMPORIKO\Controllers\Pages\TableView
          */
	 function setNoDataMessage(string $msg,bool $overrideFilterMsg=FALSE)
	 {
            $this->addData('no_data_message',lang($msg));
            $this->_is_filtering_over_msg=$overrideFilterMsg;
            return $this;
	 }
	
	function setPageTitle($title,$tags=[])
	{
		$tags=is_array($tags) ? $tags : [$tags];
		$title=lang($title,$tags);
		$this->viewData['_vars']['pagetitle']=$title;
		return $this->setTableTitle($title);
	} 
	 
	/**
	 * Set table as row hovered (table-hover)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function setHoverRowsTable()
	 {
	 	$this->_table_class['table_class'][]='table-hover';
		return $this;
	 }
	 
	 /**
	 * Set table as small (table-sm)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function setSmallTable()
	 {
	 	$this->_table_class['table_class'][]='table-sm';
		return $this;
	 }
	 
	/**
	 * Set custom tags and class on table elements (see codeignitier table class)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 public function setCustomTable(array $settings=[])
	 {
	 	$this->_table_class=$settings;
		return $this;
	 }
	
	/**
	 * Determine if custom view is used
	 * 
	 * @param  bool $enabled
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	function setCustomViewEnable($enabled=TRUE)
	{
		return $this->addData('_tableview_custom',$enabled);
	}
	
	/**
	 * Determines if tick box against each line (row) is visible
	 * 
	 * @param  bool   $value
	 * @param  string $key
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	function setTickBox($value=TRUE,$key=null)
	{
		$this->_enable_btn=$value;
		$this->_dataPrimaryKey=$key;
		return $this;
	}
	
        /**
         * Sets data primary field (key) name
         * 
         * @param  string $field
         * @param  string $model
         * 
         * @return \EMPORIKO\Controllers\Pages\TableView
         */
        function setDataIDField($field,string $model='')
        {
            $this->_dataPrimaryKey=$field;
            if (strlen($model) > 0)
            {
                $this->addData('_tableview_model',$model);
            }
            return $this;
        }
        
	/**
	 * Sets table data
	 * 
	 * @param  mixed  $model
	 * @param  string $filters
	 * @param  string $orderBy
	 * @param  mixed  $pagination Could be Integer or bool (False for no pagination, True for default)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function setData($model,$orderBy=null,$pagination=FALSE,$groupby=null,$defFilters=[])
	{
		$model_action='filtered';
		if (is_subclass_of($model,'\EMPORICO\Models\BaseModel'))
		{
			$this->_model=$model;	
		}else
		if (is_string($model))
		{
                    if (Str::contains($model, '::'))
                    {
                        $model_action=explode('::',$model);
                        $model=model($model_action[0]);
                        if (count($model_action) > 2)
                        {
                            $defFilters['_model_args']=$model_action[2];
                        }
                        
                        if (is_subclass_of($model, '\CodeIgniter\Model'))
                        {
                         $model_action=$model_action[1];
                          goto model_set;  
                        }else
                        {
                            $model=$model_action[0];
                        }
                        $model_action=$model_action[1];
                    }
                    
                    if(!array_key_exists($model, $this->controller->assocModels))
                    {
                        goto error_model;
                    }
                    $this->addData('_tableview_model',$model);
                    $model=$this->controller->assocModels[$model];
                    $model=Str::endsWith($model,'Model') ? $model : $model.'Model';                   
                    $this->_model=model($model);
                    $model=$this->_model;	
		}else
		if (!is_array($model))
		{
                    goto error_model;
		}else
		{
			$model=array_values($model);
		}
		
                model_set:
		$this->_model=$model;
		$filters=[];
                
		if (array_key_exists('filtered', $_GET))
                {
                    $filters=$_GET['filtered'];
                    $this->_is_filtering=TRUE;
                }
               
		if ($filters!=null || is_array($defFilters))
		{
			if ($filters!=null)
			{
				$filters=json_decode(base64url_decode($filters),TRUE);
                                
				$this->addData('_tableview_filter_value',$filters['-value-']);
				unset($filters['-value-']);
			}else
			{
				$filters=[];
			}
                        $filters=is_array($filters) ? $filters : [];
                        $defFilters=is_array($defFilters) ? $defFilters : [];
			$filters=$filters+$defFilters;
                        
			if (!is_array($model))
			{
                                $model=$model->{$model_action}($filters);
			}else
			{
                                $origModel=$model;
				$filters_keys=array_keys($filters);
				$filters=array_values($filters);
				if (is_array($filters_keys) && count($filters_keys) >0)
				{
                                    $results=[];
                                    foreach($filters as $key=>$filter)
                                    {
                                        $result=Arr::searchMulti($model, str_replace([' ','%','>','<','(',')'], '', $filters_keys[$key]),$filters[$key]);
                                        if ($result!=FALSE)
                                        {
                                            $results= array_merge($results,$result);
                                        }
                                    }
                                    if (count($results) > 0)
                                    {
					$model=$results;
                                    }else
                                    {
                                        $model=[];//[array_combine(array_keys($model[0]), array_fill(0, count($model[0]), ' '))];
                                    }
				}
			}
		}
                if (!is_array($model))
		{
			$orderBy=(is_array($_GET) && array_key_exists('orderby', $_GET)) ? $_GET['orderby'] : $orderBy;
                       
			if ($model==null)
			{
				error_model:
				$this->addData('_tableview_model','');
				throw new \Exception('Invalid model name');
			}
			if (is_array($orderBy))
			{
				foreach ($orderBy as $value) 
				{
                                    $model=$model->orderBy($value);
				}
			}else
			{
                            $orderBy=$orderBy==null ? $model->primaryKey : $orderBy;
                            if ($orderBy!=':id:')
                            {
                                $model=$model->orderBy($orderBy);
                            }
			}
		
			if ($groupby!=null && array_key_exists($groupby, $model->allowedFields))
			{
				$model->groupBy($groupby);
			}
		}else
                {
                    if ($orderBy!=null)
                    {
                        $sorter=[];
                        $orderBy= is_array($orderBy) ? $orderBy : [$orderBy];
                        foreach ($orderBy as $order)
                        {
                            $order=explode(' ',$order);
                            if (count($order) > 1 && strlen($order[1]) > 0 && strtolower($order[1])=='desc')
                            {
                                $sorter[$order[0]]=SORT_DESC;
                            }else
                            {
                                $sorter[$order[0]]=SORT_ASC;
                            }
                        }
                        if (count($sorter)>0)
                        {
                            array_sort_by_multiple_keys($model,$sorter);
                        }
                    }
                }
		
		if (!is_numeric($pagination) && $pagination==TRUE)
		{
			$pagination=config('Pager')->perPage;
		}
		
		
		if ($pagination!=FALSE)
		{
			if (!is_array($model))
			{
				$this->_data=$model->paginate($pagination);
				$this->addData('_tableview_pagination',$model->pager->links());
			}else
			{
				$page=1;
				if (array_key_exists('page', $_GET))
				{
					$page=$_GET['page'];
				}
				$page=$page==null ? 1 : $page;
				$max=$pagination;
				if (count($model) < ($max+$page) && $page>1)
				{
					$max=($max+$page)-count($model);
				}
				$this->_data=array_slice($model, $page-1<0 ? 0 : $page-1, $max);
				$pager = \Config\Services::pager();
				$pager=$pager->makeLinks($page, $pagination, count($model));
				$this->addData('_tableview_pagination',$pager);
			}
		}else
		{
			if(is_array($model))
			{
				$this->_data=$model;
			}else
			if (is_array($filters))
			{
                            $this->_data=$model->find();
			}
			
		}
                
                $this->_curr_filters=$filters;
		$this->_edit_column=[];
                if (is_array($this->_data))
                {
                    if (count($this->_data)>0)
                    {
                        $this->_data_cols=array_key_exists(0, $this->_data) ? array_keys($this->_data[0]) : array_keys($this->_data);
                    } else 
                    {
                        $this->_data_cols= !empty($origModel) && is_array($origModel) && count($origModel) > 0 ? array_keys($origModel[0]) : [];
                    }
                }else
                {
                    $this->_data_cols=$model->getFieldsNames();
                }
                
		return $this;
	}
	
        
        
	/**
	 * Add column to table
	 * 
	 * @param  string $label
	 * @param  string $name
	 * @param  bool   $ismobile Determines if column will be visible in mobile view port
	 * @param  array  $list     Optional array with value labels
	 * @param  string $format   Optional format used to format cell value (if numerical strftime will be used, you can also use len:0 as format to substr value 0 is number of characters)
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addColumn($label,$name,$ismobile=FALSE,$list=[],$format=null,$tooltip=null)
	{
		//
		if ($list=='yesno')
		{
                    $list= array_flip(range(-9,9));
                    $list= array_fill(-9, count($list), lang('system.general.no'));
                    $list[1]=lang('system.general.yes');
		}else
		if ($list=='access')
		{
			$list=$this->controller->model_Auth_UserGroup->getAccessForForm();
		}
		if (!is_array($list))
		{
			$list=[];	
		}
		$label=lang($label);
		if (count($this->_data_cols) > 0 && !in_array($name,$this->_data_cols))
		{
			throw new \Exception($name." is not valid column name", 1);		
		}
		$this->_tbl_cols[$name]=['label'=>$label,'list'=>$list,'format'=>$format,'','tooltip'=>$tooltip];
		if ($ismobile)
		{
			$this->_tbl_cols_mobile[$name]=$this->_tbl_cols[$name];
		}
		return $this;
	}
	
	/**
	 * Enable filters
	 * 
	 * @param  string $baseUrl
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	 
	public function addFilters($method)
	{
		$method=$method=='index' ? null : $method;
		$this->addData('_tableview_filters_url',url($this->controller,$method));
		$this->_sorting=TRUE;
		$this->_filters['-value-']='%value%'; 		
		return $this;
	} 
	
        /**
         * Add filters list divider line
         * 
         * @return \EMPORIKO\Controllers\Pages\TableView
         */
        public function addFixedFilterListDivider(string $text=null)
        {
           $this->_filters_fixed[]='#'.($text!=null ? lang($text) : $text); 
           return $this;
        }
        
        
        /**
         * Add clear all filters option
         * 
         * @return \EMPORIKO\Controllers\Pages\TableView
         */
        public function addClearFilter()
        {
            if (is_array($this->_filters_fixed) && count($this->_filters_fixed) > 0)
            {
                $this->addFixedFilterListDivider();
            }
            
            $this->_filters_fixed['system.buttons.clear_filters']='*='; 
            return $this;
        }
        
	/**
	 * Add filters field to view
	 * 
	 * @param  string $key
	 * @param  string $value
	 * @param  string $filterLabel
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addFilterField($key,$value=null,$filterLabel=null)
	{
		$value=$value==null && $value!=0 && $value!=' ' && $value!=FALSE ? '%value%' : $value;
		$value=$value==null && $value!=FALSE? '%value%' : $value;
                $value=$value=='@0' ? 0 : $value;
                
		$column=explode(' ', $key);
		
		if (Str::startsWith($key,['||','(',')','&&']))
		{
			$column=$column[1];
		}else
		{
			$column=$column[0];
		}
		
		if (count($this->_data_cols) >0 && !in_array($column,$this->_data_cols) && $value!=FALSE)
		{
                    throw new \Exception($column." is not valid column name for filters", 1);	
		}
		
                if ($filterLabel!=null && is_bool($value) && $value==FALSE)
                {
                    $this->_filters_fixed[$filterLabel]=$key;
                }else
		if ($filterLabel!=null && ($value!=null || $value==0))
		{
			$this->_filters_fixed[$filterLabel]=$key.'='.$value;
		}else
		{
			$this->_filters[$key]=$value;
		}
		return $this;
	}
	
        /**
         * Determines if given filter criteria is used by user
         * 
         * @param string $field
         * @param string $value
         * 
         * @return boolean
         */
        function isFilterApplied(string $field,string $value=null)
        {
            if (array_key_exists($field, $this->_curr_filters))
            {
                if ($value==null)
                {
                    return TRUE;
                }
                return strcmp($this->_curr_filters[$field], $value)==0;
            }
            return FALSE;
        }
        
	/**
	 * Turn on/off sorting of columns
	 * 
	 * @param  bool $enable
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	function setSorting($enable=TRUE)
	{
		$this->_sorting=$enable;
		$this->addData('_tableview_filters_url',current_url());
		return $this;
	}
	
	/**
	 * Add disable button to header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addDisableButton($access=AccessLevel::view,$action='enable/null&enable=0')
	{
		if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
		{
			return $this;
		}
		$this->_enable_btn=TRUE;
                $this->addFilterField('enabled','@0','system.general.enable_filt_0');
		return $this->addHeaderButton($action,'id_tableview_btn_disable','button','btn btn-outline-danger btn-sm tableview_def_btns','<i class="fa fa-eye-slash mr-1"></i>',lang('system.auth.profiles.enable_btn_no'),$access,['data-actiontype'=>'disable']);
	}
	
	/**
	 * Add enable button to header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addEnableButton($access=AccessLevel::view,$action='enable/null&enable=1')
	{
		if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
		{
			return $this;
		}
		$this->_enable_btn=TRUE;
                $this->addFilterField('enabled',1,'system.general.enable_filt_1');
		return $this->addHeaderButton($action,'id_tableview_btn_enable','button','btn btn-success btn-sm tableview_def_btns','<i class="fa fa-eye mr-1"></i>',lang('system.auth.profiles.enable_btn'),$access,['data-actiontype'=>'delete']);
	}
	
	/**
	 * Make delete button visible in header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addDeleteButton($access=AccessLevel::delete,$action='delete/null')
	{
		if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
		{
			return $this;
		}
		$this->_del_btn=TRUE;
		return $this->addHeaderButton($action,'id_tableview_btn_del','button','btn btn-danger btn-sm tableview_def_btns','<i class="fa fa-trash mr-1"></i>',lang('system.auth.profiles.del_btn'),$access,['data-actiontype'=>'delete']);
	}
	
	/**
	 * Make delete button visible in header
	 * 
	 * @param  string $action
	 * @param  Int    $access
         * @param  array  $access
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addNewButton($action='new/new',$access=AccessLevel::create,array $args=[])
	{
		if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
		{
			return $this;
		}
		$args['data-actiontype']='new';
                if (!array_key_exists('class', $args))
                {
                    $args['class']='btn btn-dark btn-sm';
                }
                if (is_array($action))
                {
                    $action=HtmlItems\ToolbarButton::createDropDownButton('plus','dark',lang('system.buttons.new'),$action,null,['mode'=>'dropleft']);
                    return $this->addCustomHeaderButton($action, $access);
                }
                if (!array_key_exists('id', $args))
                {
                    $args['id']='id_tableview_btn_new';
                }
                if (!array_key_exists('tooltip', $args))
                {
                    $args['tooltip']=lang('system.buttons.new');
                }
		return $this->addHeaderButton($action,$args['id'],'link',$args['class'],'<i class="fa fa-plus mr-1"></i>',$args['tooltip'],$access,$args);
	}
	
        /**
         * Add columns edit button to form 
         * 
         * @param  array $columns
         * @param  Int   $access
         * 
         * @return \EMPORIKO\Controllers\Pages\TableView
         */
        public function addColumnsEditButton(array $setCols,array $allCols,$param,$access=AccessLevel::settings)
        {
            if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
            {
                return $this;
            }
            if (count($setCols) < 1)
            {
                return $this;
            }
            
            if ($param==null)
            {
                return $this;
            }
            
            if (model('Settings/SettingsModel')->get($param,FALSE,'value',FALSE)==null)
            {
                return $this;
            }
            $param=Str::afterLast($param, '.');
            $form=new FormView($this->controller);
            $form->addCustomElementsListField('', 'settings['.$param.'][value]', $setCols, ['input_type'=>$allCols])
                 ->addHiddenField('settings['.$param.'][param]', $param);
            return $this->addHeaderButton(null,'id_tableview_btn_coledit','button','btn btn-secondary btn-sm','<i class="fas fa-columns"></i>',lang('system.buttons.columns'),$access,['data-actiontype'=>'columnedit'])
                        ->addData('_columns_to_edit',view('System/form_fields',$form->getViewData()))
                        ->addData('_columns_to_edit_url',url('Settings','savesettings',[],['refurl'=>current_url(FALSE,TRUE)]));
        }
        
	private function parseAction($action)
	{
		if (is_array($this->_model))
		{
			$keys=array_keys($this->_model);
		}else
		{
			$keys=$this->_model->allowedFields;
		}
		$keys=Arr::ParsePatern($keys,'-value-');
		$url=Arr::toObject(['controller'=>'','action'=>'','args'=>[],'get'=>[]]);
		$url->controller=$this->controller;
		
		
		if (is_array($action) && array_key_exists('action',$action))
		{
			if (array_key_exists('get',$action) && is_array($action['get']))
			{
				$url->get=$action['get'];
			}
			$url->controller=array_key_exists('controller',$action) ? $action['controller'] : $url->controller;
			$url->args=array_key_exists('params',$action) ? $action['params'] : $url->args;
			$url->action=$action['action'];
			
			goto url_parse;
		}
		
		if (Str::contains($action,'&'))
		{
			$action=explode('&', $action);
			foreach($action as $key => $value)
			{
				if ($key>0)
				{
					$value=explode('=',$value);
					$url->get[$value[0]]=$value[1];
				}
			}
			$action=$action[0];
		}
		
		if (Str::contains($action,'::'))
		{
			$action=explode('::', $action);
			$url->controller=$action[0];
			$action=$action[1];
		}
		
		if (Str::contains($action,'/'))
		{
			$url->args=explode('/', $action);
			$action=$url->args[0];
			unset($url->args[0]);
			foreach ($url->args as $key => $value) 
			{
				if ($value=='null' || $value==null)
				{
					unset($url->args[$key]);
				}
			}
			
		}
		
		$url->action=$action;
                    
		url_parse:
		$url->get['refurl']=current_url(FALSE,TRUE);
		return url($url->controller,$url->action,$url->args,$url->get);
	}
        
        /**
         * Add upload button to table toolbar
         * 
         * @param string      $driver
         * @param string      $id
         * @param string      $tooltip
         * @param string      $icon
         * @param string      $class
         * @param string      $access
         * @param array       $btnArgs
         * 
         * @return \EMPORIKO\Controllers\Pages\TableView
         */
	public function addUploadButton(string $driver=null,string $id=null,string $tooltip=null,string $icon=null,string $class=null,$access=AccessLevel::edit,array $btnArgs=[])
        {
            if ($driver==null)
            {
                $_tableview_model=$this->getViewData('_tableview_model');
                if ($_tableview_model!=null)
                {
                    $driver=$_tableview_model;
                }
            }
            
            if (!is_string($driver))
            {
                return $this;
            }
            
            if (!Str::contains($driver, ['/','::']))
            {
                $driver='Settings::upload/'.$driver;
            }
            
            $id=$id==null ? 'id_table_buttons_upload' : $id;
            $tooltip=$tooltip== null ? 'system.buttons.upload' : $tooltip;
            $icon=$icon==null ? 'fas fa-cloud-upload-alt' : $icon;
            $class=$class==null ? 'btn btn-sm btn-dark ml-3' : $class;
            if (!Str::startsWith($class, 'btn btn-sm'))
            {
                $class='btn '.$class;
            }
            if (!array_key_exists('title', $btnArgs))
            {
                $btnArgs['title']=$tooltip;
            }
            $icon= html_fontawesome($icon);
            //$this->addData('_uploadform', ['driver'=>$driver,'button_id'=>$id,'title'=>$btnArgs['title']]);
            unset($btnArgs['title']);
            return $this->addHeaderButton($driver,$id,'link',$class,$icon,$tooltip,$access,$btnArgs);
        }
        
        public function addModuleSettingsButton($tooltip='system.buttons.module_settings',$access=AccessLevel::settings,array $btnArgs=[])
        {
            $tooltip=$tooltip==null || strlen($tooltip) < 2 ? 'system.buttons.module_settings' : $tooltip;
            $access=$access==null ? AccessLevel::settings : $access;
            if(!array_key_exists('id', $btnArgs))
            {
                $id=null;
            }else
            {
                $id=$btnArgs['id'];
            }
            
            if(!array_key_exists('class', $btnArgs))
            {
                $class='btn btn-secondary btn-sm';
            }else
            {
                $class='btn btn-sm btn-'.$btnArgs['class'];
            }
            
            if(array_key_exists('margin', $btnArgs))
            {
                $class.=' '.$btnArgs['margin'];
                unset($btnArgs['margin']);
            }
            
            if(!array_key_exists('icon', $btnArgs))
            {
                $icon='<i class="fa fa-fas fa-cogs"></i>';
            }else
            {
                if (!Str::startsWith($btnArgs['icon'],'<i class'))
                {
                    $icon='<i class="'.$btnArgs['icon'].'"></i>';
                }
            }
            
            $btnArgs['data-url']=$this->controller->getModuleSettingsUrl(array_key_exists('tabName', $btnArgs) ? $btnArgs['tabName'] : 'cfg');
            
            return $this->addHeaderButton(null, $id,'button',$class,$icon,$tooltip,AccessLevel::settings,$btnArgs);
        }        
        
        function addCustomHeaderButton($button,$access= AccessLevel::view)
        {
            if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
            {
                return $this;
            }
            
            if (is_subclass_of($button, '\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem'))
            {
                $button=$button->render();
            }
            if (!is_string($button))
            {
                return $this;
            }
            $_tableview_btns=$this->getViewData('_tableview_btns');
            $_tableview_btns[]=$button;
            $this->addData('_tableview_btns',$_tableview_btns); 
            return $this;
        }
        
	/**
	 * Add new button to header section
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addHeaderButton($action,$id=null,$type='button',$class='btn btn-secondary',$icon='button_icon',$text='button_text',$access=AccessLevel::view,array $btnArgs=[])
	{
		if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
		{
			return $this;
		}
		$text=lang($text);
		$_tableview_btns=$this->getViewData('_tableview_btns');
		$_tableview_btns=is_array($_tableview_btns) ? $_tableview_btns :[];
		$icon=Str::startsWith($icon, 'fa') ? html_fontawesome($icon) : $icon;
		$_tableview_btns_routes=$this->getViewData('_tableview_btns_routes');
		$_tableview_btns_routes=is_array($_tableview_btns_routes) ? $_tableview_btns_routes :[];
		$name='header_'.count($_tableview_btns);
		if (Str::contains($class, 'tableview_def_btns'))
                {
                    $this->_enable_btn=TRUE;
                }
                
		$args=
		[
			'class'=>(strlen($class) > 0 ? ' ':'').$class,
			'id'=>$id==null ? 'id_tableview_btn_'.$name : $id,
                        'title'=>$text,
                        'data-toggle'=>'tooltip',
                        'data-placement'=>'top',
                        'data-tblheadbtn'=>'true',
		];
                if (array_key_exists('data-delmsg', $btnArgs))
                {
                    $btnArgs['data-delmsg']= base64_encode(lang($btnArgs['data-delmsg']));
                }
                $args=$args+$btnArgs;
                
                $icon= str_replace('mr-1', '', $icon);
		$args['content']=$icon.($this->ismobile() ? null :null);
                
		if ($this->ismobile())
                {
                    $args['class']= str_replace(['-success','-danger','-info','-warning','-light','-dark','-secondary','-primary'], '-light', $args['class']);
                    $args['class']= str_replace('ml-','noclass-',$args['class']);
                    $args['class'].=' w-100 text-left';
                    $args['content']=$args['content'].'<b class="ml-1">'.$args['title'].'</b>';
                }
		if (is_array($action))
		{
			$dropdownitems=[];
			foreach ($action as $key => $value) 
			{
				$dropdownitems[]=
				[
					'text'=>lang($key),
					'href'=>$this->parseAction($value)
				];	 
			}
			$args['text']=$text;
			$args['dropdownitems']=$dropdownitems;
			$_tableview_btns[]='<div class="btn">'.view('System/Elements/dropdown',$args).'</div>';
			$_tableview_btns_routes[$id]=$args;
		}else
		{
			if ($action!=null)
			{
				$action=$this->parseAction($action);
			}
			
			
			if ($type=='button')
			{
				if ($action!=null)
				{
					$args['data-action']=$action;
				}
			
				$args['name']=$name;
				$type=form_button($args);
			
			}else
			if ($type=='link')
			{
				$type=url_tag($action,$args['content'],$args);
			}else
			if ($type=='link_newtab')
			{
				$args['target']='_blank';
				$type=url_tag($action,$args['content'],$args);
			}
			$args['route']=$action;
			if ($id!=null && strlen($id)>0)
			{
				$_tableview_btns_routes[$id]=$args;
				$_tableview_btns[$id]=$type;
			}else
			{
				$_tableview_btns[]=$type;
				$_tableview_btns_routes[]=$args;
			}
			
		}
		$this->addData('_tableview_btns',$_tableview_btns); 
		$this->addData('_tableview_btns_routes',$_tableview_btns_routes); 
		return $this;
	}
	
	/**
	 * Remove all header buttons
	 * 
	 * @param  bool $clearRoutes Determines if buttons routes will be deleted
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	function clearHeadersButtons($clearRoutes=TRUE)
	{
		$this->addData('_tableview_btns',[]);
		if ($clearRoutes)
		{
			$this->addData('_tableview_btns_routes',[]); 
		} 
		return $this;
	}
	
	/**
	 * Returns given header button details
	 * 
	 * @param  mixed $id Button id
	 * 
	 * @return string
	 */
	function getHeaderButton($id)
	{
		return $this->getViewData('_tableview_btns');
	}

/**
	 * Returns route for given header button
	 * 
	 * @param  mixed $id Button id
	 * 
	 * @return string
	 */
	function getHeaderButtonRoute($id)
	{
		return $this->getViewData('_tableview_btns_routes.'.$id);
	}
	
	/**
	 * Determines if  route for given header button exists
	 * 
	 * @param  mixed $id Button id
	 * 
	 * @return string
	 */
	function isHeaderButtonRoute($id)
	{
		$id=$this->getViewData('_tableview_btns_routes.'.$id);
		return !is_array($id) && strlen($id)>0;
	}
	
	/**
	 * Add button to edit edit column
	 * 
	 * @param  string $label
	 * @param  string $action Button action, if you want change controller use format controller::action, if you want to use different column as id add it to action prefixed by \
	 * @param  string $id
	 * @param  string $type
	 * @param  string $icon
	 * @param  array $args
	 * 
	 * @return \EMPORIKO\Controllers\Pages\TableView
	 */
	public function addEditButton($label,$action,$id=null,$type="btn-primary",$icon='fa fa-edit',array $args=[],$access=AccessLevel::edit)
	{
		if (!$this->controller->auth->hasAccess($this->controller->getModuleAccess($access)))
		{
			return $this;
		}
                if (!Str::contains($type, 'edtBtn'))
                {
                    $type.=' edtBtn';
                }
		$label=lang($label);
		$name='edit_'.count($this->_edit_column);
		if (is_array($this->_model))
		{
                    if (count($this->_model) > 0)
                    {
                        $key=array_keys($this->_model[0]);
			$urlid='-'.$key[0].'-';
                    }else
                    {
                        $urlid='';
                    }
			
		}else
		{
			$urlid='-'.$this->_model->primaryKey.'-';
		}
		
		$controller=$this->controller;
		if (Str::contains($action,'::'))
		{
			$action=explode('::', $action);
			$controller=$action[0];
			$action=$action[1];
		}
		
		if (Str::contains($action,'\\'))
		{
			$action=explode('\\', $action);
			$urlid='-'.$action[1].'-';
			$action=$action[0];
		}
		if ($action!=null && Str::startsWith($action,'@'))
                {
                    $href=url(substr($action, 1));
                }else
		if ($action!=null && Str::startsWith($action,'http'))
		{
			$href=$action;
		}else
		if ($action!=null)
		{
                    $params=[$urlid];
                    if (Str::contains($action,['/',$urlid,'-id-']))
                    {
                        $action= str_replace('-id-', $urlid, $action, $count);
                        $params= explode('/', $action);
                        $action=$params[0];
                        unset($params[0]);
                    }
                    
                    $href=url($controller,$action,$params,['refurl'=>current_url(FALSE,TRUE)]);    	
		}else
		{
			$href='#';
		}
		$args=is_array($args) ? $args : [];
		$args['name']=$name;
		$type=!Str::contains($type,'btn-sm') && !Str::contains($type,'btn-lg') ? $type.' btn-sm' : $type;
		$args['class']='btn '.$type;
		$args['id']=$id==null ? 'id_tableview_btn_'.$name : $id;
		$args['data-toggle']='tooltip';
		$args['data-placement']='left';
		$args['title']=$label;
		$args['data-urlid']=$urlid;
                $args['data-url']=$href;
		//$this->_edit_column[]=$action=='#' ? form_button('','<i class="'.$icon.'"></i>',$args) : url_tag($href,'<i class="'.$icon.'"></i>',$args);
                $this->_edit_column[]=form_button('','<i class="'.$icon.'"></i>',$args);
		return $this;
	}
	/**
	 * Render view
	 * 
	 * @param string $mode do not use in this view
	 * 
	 * @return string
	 */
	public function render($mode='HTML',$stop = true)
        {
            if (is_array($this->_data) && count($this->_data) < 1 && $this->_is_filtering && !$this->_is_filtering_over_msg)
            {
                $this->setNoDataMessage('system.general.msg_filter_no_data');
            }
            if ($this->getViewData('no_data_message')==null)
            {
                $this->setNoDataMessage('system.general.msg_no_data');
            }
            
            if ($this->getViewData('table_view_datatable_id')==null)
            {
                $this->addData('table_view_datatable_id','table_view_datatable');
            }
            
            if (!in_array('table', $this->_table_class['table_class']))
            {
                array_unshift($this->_table_class['table_class'],'table');
            }
            if (strlen($this->getViewData('_tableview_filters_url')) > 0)
            {
                $this->addClearFilter();
            }
            $primaryKey=0;
            if (is_array($this->_model))
            {
                if (count($this->_model) > 0)
                {
                    $key=array_keys($this->_model[0]);
                    $primaryKey=$key[0];
                } else 
                {
                    $primaryKey=null;
                }
                
            }else
            {
                $primaryKey=$this->_model->primaryKey;
            }
            $_record_key=$this->_dataPrimaryKey==null ? $primaryKey : $this->_dataPrimaryKey;
            if (is_array($this->_disbled_records) && count($this->_disbled_records)>0)
            {
                foreach($this->_disbled_records as $key=>$value)
                {
                    if (is_string($value))
                    {
                        $this->_disbled_records[$key]='input[name="'.$_record_key.'[]"][value="'.$value.'"]';
                    } else 
                    {
                        unset($this->_disbled_records[$key]);
                    }
                }
                $this->addData('_disabled_records',implode(',', $this->_disbled_records));
            }
            
            $this->addData('_table_class',implode(' ', $this->_table_class['table_class']));
            $this->addData('_tableview_refurl',$this->controller->getRefUrl(null));
            $this->addData('_tableview_data',$this->_data);
            $this->addData('_tableview_filters_fixed',$this->_filters_fixed);
            if (count($this->_filters)>0)
            {
                $this->addData('_tableview_filters',base64_encode(json_encode($this->_filters)));
            }
            $this->addData('_data_cols',$this->ismobile() ? $this->_tbl_cols_mobile : $this->_tbl_cols);
            $this->addData('_data_cols_keys', array_keys($this->ismobile() ? $this->_tbl_cols_mobile : $this->_tbl_cols));
            $this->addData('_data_sorting',$this->ismobile() ? false : $this->_sorting);
            $this->addData('_edit_column',$this->_edit_column);
            
            $this->addData('_record_key',$_record_key);
            $this->addData('_multiedit_column',$this->_enable_btn || $this->_del_btn);
            if ($mode=='justview')
            {
                if (is_array($this->_data) && count($this->_data) < 1)
                {
                    return null;
                }
                return view($this->getFile(),$this->getViewData());
            }
            return  parent::render($mode,$stop);
        }
        
        function parseValue($value,$column,$dataColumns=[])
        { 
            if (is_string($column))
            {
                $columns=$this->getViewData('_data_cols');
                if (array_key_exists($column, $columns))
                {
                    $column=$columns[$column];
                }else
                {
                    return null;
                }
            }
            if (is_array($column) && array_key_exists('tooltip', $column) && strlen($column['tooltip']) > 0)
            {
                if (!array_key_exists('format', $column) || (array_key_exists('format', $column) && strlen($column['format']) < 1))
                {
                    $column['format']='';
                }
                if (array_key_exists($column['tooltip'], $dataColumns))
                {
                    $column['format'].='tooltip:'.$dataColumns[$column['tooltip']];
                }else
                {
                    $column['format'].='tooltip:'.$column['tooltip'];
                }
            }
            if (is_array($column['list']) && count($column['list'])>0 && array_key_exists($value, $column['list']))
            {
                return $column['list'][$value];
            }else
            if ($column['format']!=null && substr($column['format'],0,8)=='tooltip:')
            {
                $column['format']=substr($column['format'],8);
                return '<div class="p-0 w-100">'.$value.'</div><small class="text-muted">'.$column['format'].'</small>';
            }else
            if($column['format']!=null && substr($column['format'],0,6)=='money:')
            {
                $column['format']=substr($column['format'],6);
                if (Str::startsWith($column['format'],'fa'))
                {
                    $column['format']='<i class="'.$column['format'].' mr-1"></i>';
                }else
                if (Str::startsWith($column['format'], '#'))
                {
                    $column['format']=substr($column['format'],1);
                    if (array_key_exists($column['format'], $dataColumns))
                    {
                        $column['format']='<b class="mr-2">'.$dataColumns[$column['format']].'</b>';
                    }else
                    {
                        $column['format']='';
                    }
                    
                }else
                {
                    $column['format']='<b class="mr-1">'.$column['format'].'</b>';
                }
                if (is_numeric($value))
                {
                    if (is_numeric($value))
                    {
                        $value=round($value,2);
                    } else 
                    {
                        $value='-';
                    }
                    $value=$column['format'].$value;
                }
                return $value;
            }else
            if($column['format']!=null && substr($column['format'],0,6)=='group:')
            {
                $column['format']=substr($column['format'],6);
                $column['format']=$column['format']=='group' ? ',' : $column['format'];
                if (is_string($value) && Str::isJson($value))
                {
                    $value= json_decode($value,TRUE);
                    if (is_array($value))
                    {
                        $value= implode(',', array_values($value));
                    }else
                    {
                        $value='';
                    }
                }
                $value= str_replace(array_keys($column['list']), array_values($column['list']), $value);
                $value= str_replace(',', $column['format'], $value);
                return $value;
            }else
            if ($column['format']!=null && substr($column['format'],0,4)=='rep:')
            {
                $column['format']=substr($column['format'],4);
                $column['format']=Arr::fromFlatten($column['format'], '=');
                return str_replace(array_keys($column['format']), $column['format'], $value);
            }else
            if ($column['format']!=null && substr($column['format'],0,4)=='lang')
            {
                return $value!=null ? lang($value) : '';
            }else
            if ($column['format']!=null && substr($column['format'],0,3)=='img')
            {
                if (Str::startsWith($value, 'http'))
                {
                    return img($value,FALSE,['class'=>'table-img']);
                }
                return file_exists(parsePath($value,TRUE)) ? img(protected_link(parsePath($value,TRUE),TRUE),FALSE,['class'=>'table-img']) : '';
            }else
            if ($column['format']!=null && substr($column['format'],0,4)=='url:')
            {
                $column['format']=url(str_replace('{value}', $value, substr($column['format'],4)));
                return url_tag($column['format'], $column['format'], ['target'=>'_blank','data-noloader'=>'true']);
            }else
            if ($column['format']!=null && substr($column['format'],0,4)=='len:')
            {
                return substr($value,0,substr($column['format'],4));
            }else
            if ($column['format']!=null && substr($column['format'],0,4)=='icon')
            {
                return html_fontawesome($value.' fa-lg');
            }else
            if ($column['format']!=null && substr($column['format'],0,5)=='color')
            {
                if (Str::startsWith($value, '#'))
                {
                    return '<div class="border text-center" style="width:30px"><i class="fas fa-square-full fa-lg " style="color:'.$value.'"></i></div>';
                }else
                {
                    return '<div class="border text-center" style="width:30px"><i class="fas fa-square-full fa-lg text-'.$value.'"></i></div>';
                }
            }else
            if (Str::contains($column['format'], '::') && is_string($value))
            {
                $column['format']= str_replace('$1', $value, $column['format']);
                if (!Str::contains($column['format'], '@'))
                {
                    $column['format'].='@'.$value;
                }
                
                return loadModuleFromString($column['format']);
            }else
            if ($column['format']!=null && $column['format']!=0 && $column['format']!='0')
            {
                $column['format']=$column['format']=='date' ? config('APP')->dateFormat : $column['format'];
                $value=convertDate($value,'DB',$column['format']);
                if ($value==null)
                {
                    return $value;
                }
                return '<i class="far fa-calendar-alt mr-1"></i>'.$value;
            }else
            {
                return $value;
            }
        }

	
}