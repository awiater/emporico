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

  
namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;

class Reports extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
            'index'=>           AccessLevel::view,
            'templates'=>       AccessLevel::view,
            'template'=>        AccessLevel::edit,
	];
        
        protected $no_access=[];
        
        /**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
            'reports'=>'Documents/Report',
	];
	
        /**
	 * Array with controller method remaps ($key is fake function name and $value is actual function name)
	 */
	public $remaps=
        [
            'index'=>'reports',
        ];
        
        /**
	 * Array with function names which are excluded from routes actions
	 * @var Array
	 */
	protected $routerexlude=[];
        
        /**
         * Array with available menu items (keys as function names and values as description)
         */
        public $availablemenuitems = 
        [
            'list','templates'
        ];
        
        function reports($record=null,$type=1)
        {
            if ($record!=null)
            {
                return $this->report($record,$type);
            }
            $this->setTableView()
                        ->setData('reports',null,TRUE,null,['rtype'=>1])
                        ->setPageTitle('reports.mainmenu_list')
                        ->addFilters('reports')
                        ->addFilterField('rname %')
                        ->addColumn('reports.rtitle','rtitle',TRUE,[],null,'rdesc')
                        ->addColumn('reports.rname','rname',FALSE,[])
   
                        ->addBreadcrumb('reports.mainmenu',url($this))
                    
                        ->addEditButton('reports.rep_runreport','reports/-rid-/run',null,'btn-success edtBtn','fas fa-play-circle',[])
                        ->addEditButton('workers.titles_editbtn','reports',null,'btn-primary edtBtn','fa fa-edit',[])
                        
                        ->addNewButton(
                                [
                                    'reports.rep_newbtn_rep'=>url($this,'reports',['new'],['refurl'=>current_url(FALSE,TRUE)]),
                                    'reports.rep_newbtn_chart'=>url($this,'reports',['new','2'],['refurl'=>current_url(FALSE,TRUE)]),
                                ],AccessLevel::create)
                        ->addDeleteButton(AccessLevel::create)
                        ->addEnableButton(AccessLevel::create)
                        ->addDisableButton(AccessLevel::create);
                        
            if ($this->hasAccess(AccessLevel::create))
            {
                $this->view->addColumn('reports.access','access',FALSE,'access')
                           ->addColumn('reports.enabled','enabled',FALSE,'yesno');
            }
            return $this->view->render();
        }
        
        function report($record,$type=1)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            if (is_numeric($record))
            {
                $record=$this->model_Reports->find($record);
            }else
            {
		$record=null;
            }
            if ($type=='run')
            {
                return $this->run($record);
            }
            
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError(true);
                }
                $isnew=TRUE;
		$record=$this->model_Reports->getNewRecordData(TRUE);
                $record['rtype']=intval($type) < 1 ? 1 : $type;
                $record['rsql']='';
                $record['rcolumns']= []; 
                $record['rfilters']=[];
                $record['enabled']=1;
            }else
            {
               $record['rcolumns']= json_decode($record['rcolumns'],TRUE); 
               $record['rfilters']= json_decode($record['rfilters'],TRUE);
               if (is_array($record['rcolumns']))
               {
                  if (array_key_exists('table', $record['rcolumns']))
                  {
                      $record['rcolumns_table']=$record['rcolumns']['table'];
                  }
                  if (array_key_exists('rconfig', $record))
                  {
                      $record['rcolumns_source']= json_decode($record['rconfig'],TRUE);
                  }
               }
               //dump($record);exit;
            }
            
            
            $record['edit_acc']=$this->hasAccess(AccessLevel::settings);
            $this->setFormView('Reports/Reports/report_edittbl')
                ->setFormTitle('reports.'.( $isnew ? 'tpl_neweditform' : 'tpl_editform'))
		->setPageTitle('reports.'.( $isnew ? 'tpl_neweditform' : 'tpl_editform'))
		->setFormAction($this,'save',['reports'],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],
                            [
                                $this->model_Reports->primaryKey=>$record[$this->model_Reports->primaryKey],
                                'rtype'=>$record['rtype'],
                                'rname'=>$record['rname']
                            ],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('reports.mainmenu',url($this,'reports'))
                ->addBreadcrumb($isnew ? 'reports.tpl_neweditform' : $record['rname'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info') 
                ->addFieldsFromModel($this->model_Reports->getReportFieldsForForm($record),$record,'reports.rep_-key-')
                ->setTab('cfg','reports.tab_cfg');
            if (intval($type)==2 && !$isnew)
            {
                $this->view->setTab('chart','reports.tab_chart');
            }
            return $this->view->render();
        }
        
        
        function api($command=null,array $post=[])
        {
            $result=null;

            if ($command=='getfieldsforsource')
            {
                if (!array_key_exists('source', $post))
                {
                    return null;
                }
                $post= json_decode(base64_decode($post['source']),TRUE);
                if (!is_array($post) || (is_array($post) && !Arr::KeysExists(['source','lang'], $post)))
                {
                    return ['error'=>'Invalid source'];
                }
                
                $result=['fields'=>$this->model_Reports->getFieldsForSource($post['source'],$post['lang']),'table'=>$post['source']];
            }
            end_func:
            return $result;
        }
        
        private function run($record)
        {
            if (!is_array($record))
            {
                error:
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('reports.error_reportfile', 'danger'));
            }
            if (!Arr::KeysExists(array_keys($this->model_Reports->getNewRecordData(TRUE)), $record))
            {
                goto error;
            }
            if (!array_key_exists('rtables', $record))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('reports.error_reportfile','warning'));
            }
            
            $report=$this->model_Reports->getReportModel($record['rtables']);
            $filters=FALSE;
            
            if (array_key_exists('rfilters', $record))
            {
                $post=$this->request->getPost();
                $filters=TRUE;
                $record['rfilters']= json_decode($record['rfilters'],TRUE);
                if (is_array($record['rfilters']) && count($record['rfilters'])>0)
                {
                    if (count($post) > 0)
                    {
                        foreach($post as $key=>$val)
                        {
                            if (array_key_exists($key, $record['rfilters']))
                            {
                                $report->where($key.$record['rfilters'][$key]['formula'],$val);
                            }
                        }
                        goto after_filter_form;
                    }
                    $form=new Pages\FormView($this);
                    $form->setFormTitle(lang('reports.rep_filtersform',[$record['rname']]))
                         ->setPageTitle(lang('reports.rep_filtersform',[$record['rname']]))
                         ->setFormAction($this,'reports',[$record['rid'],'run'],['refurl'=>base64url_encode($this->getRefUrl())])
                         ->setFormArgs(['autocomplete'=>'off'],[],['class'=>'col-12'])
                         ->setFormCancelUrl($this->getRefUrl())
                         ->setFile('Reports/view')
                         ->addBreadcrumb('reports.mainmenu',url($this,'reports'))
                         ->addBreadcrumb($record['rtitle'],'/')
                         ->addData('filtersform',TRUE);
                    foreach($record['rfilters'] as $key=>$val)
                    {
                        if (strlen($val['text']) > 0)
                        {//
                            if (in_array($val['field'], ['customersList','brandsList','usersList','suppsList']))
                            {
                                $options=[];
                                if ($val['field']=='suppsList')
                                {
                                    $options=$this->model_Products_Supplier->getForForm('sup_code','sup_name');
                                }else
                                if ($val['field']=='customersList')
                                {
                                    $options=$this->model_Customers_Customer->getForForm('code','name');
                                }else
                                if ($val['field']=='brandsList')
                                {
                                    $options=$this->model_Products_Brand->getBrands();
                                }
                                $form->addDropDownField($val['text'], $key, $options, null, ['advanced'=>TRUE])->addSelect2();
                            }else
                            if ($val['field']=='DatePicker')
                            {
                                $form->addDatePicker($val['text'], $key, $val['formula']);
                            }else
                            {
                                $form->addInputField($val['text'], $key, $val['formula']);
                            }
                        }
                    }
                    return $form->render();
                    //$record['rfilters']=view('System/form_fields',$form->getViewData());
                }
            }
            after_filter_form:
               
            
            if (array_key_exists('rcolumns', $record))
            {
                $record['rcolumns']= json_decode($record['rcolumns'],TRUE);
            }
            $select=[];
            $table_cols=[];
            
            if (is_array($record['rcolumns']) && array_key_exists('fields', $record['rcolumns']) && is_array($record['rcolumns']['fields']) )
            {
                foreach($record['rcolumns']['fields'] as $field)
                {
                    if ($field['visible']==1)
                    {
                        if ($field['format']=='yesno')
                        {
                            $table_cols[]=[$field['text'],$field['text'],FALSE,'yesno',''];
                        }else
                        if ($field['format']=='date')
                        {
                            $table_cols[]=[$field['text'],$field['text'],FALSE,[],'d M Y'];
                        }else
                        {
                            $table_cols[]=[$field['text'],$field['text'],FALSE,[],$field['format']];
                        }
                        $select[]=$field['field']." as '".$field['text']."'";
                    }
                }
                $report->select(implode(',',$select));
            }
            $data=$report->find();
            $this->setTableView('Reports/view')
                    ->setData($data,'',TRUE,null,[])
                    ->setBorderedTable()
                    ->setNoDataMessage(lang('reports.msg_nodata_report').'<br>'. url_tag(url($this),'system.buttons.back'),TRUE)
                    ->setSmallTable()
                    ->setAsDataTable()
                    ->addBreadcrumb('reports.mainmenu',url($this,'reports'))
                    ->addBreadcrumb($record['rtitle'],'/')
                    ->addData('report',$record)
                    ->addData('is_fileters',$filters);
            foreach(is_array($data) && count($data) >0 ? $table_cols : [] as $col)
            {
                $this->view->addColumn($col[0],$col[1],$col[2],$col[3],$col[4]);
            }
            return $this->view->render();
        }
        
        function templates($record=null)
        {
            if ($record!=null)
            {
                return $this->template($record);
            }
             return $this->setTableView()
                        ->setData('reports','rtitle',TRUE,null,['rtype'=>0])
                        ->setPageTitle('reports.tpl_list')
                        ->addFilters('templates')
                        ->addFilterField('rname %')
                        ->addColumn('reports.tpl_rtitle','rtitle',TRUE)
                        ->addColumn('reports.rdesc','rdesc',FALSE,[],'len:150')
                        ->addColumn('reports.enabled','enabled',FALSE,'yesno')
			
                        ->addBreadcrumbSubSettings()
                        ->addBreadcrumb('reports.tpl_mainmenu',current_url())
   
                        ->addEditButton('reports.edit_page','templates',null,'btn-primary edtBtn','fa fa-edit',[])

                        ->addNewButton('templates/new')
                        ->addDeleteButton()
                        ->addEnableButton()
                        ->addDisableButton()
                        ->addModuleSettingsButton(null,null,['margin'=>'ml-3'])
                     
                        ->addDisabledRecords($this->model_Reports->getSystemEmailsTemplatesIDs())
                        ->render();   
        }
        
        function parsetemplate($record,$parseData=null,$fullpage=FALSE,$autoprint=FALSE)
        {
            if (is_array($parseData) && count($parseData)>0)
            {
                if (is_numeric($record))
                {
                    $record=$this->model_Reports->find($record);              
                }else
                {
                    $record=$this->model_Reports->where('rname',$record)->first();
                }
                if (!is_array($record) && array_key_exists('report_file', $parseData))
                {
                    $parseData['report_file']=Str::endsWith(strtolower($parseData['report_file']), '.php') ? $parseData['report_file'] : $parseData['report_file'].'.php';
                    $parseData['report_file']= parsePath('@views/'.$parseData['report_file'],TRUE);
                    if (!file_exists($parseData['report_file']))
                    {
                        goto record_error;
                    }
                    $record['rsql']= file_get_contents($parseData['report_file']);
                    $record['rfilters']=$this->view->getViewData('css');
                    if (is_array($record['rfilters']) && Arr::KeysExists(['systemcss','template'], $record['rfilters']))
                    {
                        $record['rfilters']= file_get_contents($record['rfilters']['systemcss']).file_get_contents($record['rfilters']['template']);
                    }else
                    {
                        $record['rfilters']='';
                    }
                }else
                if (!is_array($record))
                {
                    record_error:
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('reports.error_tplfile'));
                }
                $pattern='#<field field="text" formatin=\"(.*?)\" formatout=\"(.*?)\" indata="no" id=\"(.*?)\">{(.+?)}</field>#s';
                $replacement='<p id="$3"><?= $4?></p>';
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $pattern='#<field field="date" formatin=\"(.*?)\" formatout=\"(.*?)\" indata="no" id=\"(.*?)\">{(.+?)}</field>#s';
                $replacement='<p id="$3">'."<?= convertDate($4,'$1','$2') ?></p>";
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $pattern='#<field field="lang" formatin=\"(.*?)\" formatout=\"(.*?)\" indata="no" id=\"(.*?)\">(.+?)</field>#s';
                $replacement='<p id="$3">'."<?= lang('$4')?></p>";
                $replacement= str_replace('#', "'", $replacement);
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
    
                $pattern='#<div type="datacontainer" id=\"(.*?)\">(.+?)</div>#s';
                $replacement='<div id="$1"><?php foreach($data as $record):?>$2<?php endforeach ?></div>';
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
               
                $pattern='#<tbody type="datacontainer">(.+?)</tbody>#s';
                $replacement='<tbody><?php foreach($data as $record):?>$1<?php endforeach ?></tbody>';
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $pattern='#<field field="text" formatin=\"(.*?)\" formatout=\"(.*?)\" indata="yes" id=\"(.*?)\">{\$(.+?)}</field>#s';
                $replacement='<p id="$3"><?=array_key_exists("$4", $record) ? $record["$4"] : null ?></p>';
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $pattern='#<field field="date" formatin=\"(.*?)\" formatout=\"(.*?)\" indata="yes" id=\"(.*?)\">{\$(.+?)}</field>#s';
                $replacement='<p id="$3"><?= array_key_exists(#$4#, $record)  ? convertDate($record[#$4#],#$1#,#$2#) : null?></p>';
                $replacement= str_replace('#', "'", $replacement);
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $pattern='#<field field="lang" formatin=\"(.*?)\" formatout=\"(.*?)\" indata="yes" id=\"(.*?)\">(.+?)</field>#s';
                $replacement='<p id="$3"><?= !array_key_exists(#$4#, $record) ? lang($record[#$4#]) : null?></p>';
                $replacement= str_replace('#', "'", $replacement);
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $pattern='#{(.+?)}#s';
                $replacement='<?= $1 ?>';
                $record['rsql']=preg_replace($pattern, $replacement, $record['rsql']);
                
                $record['rsql']= str_replace('src="/', 'src="'. site_url(),  $record['rsql']);
                $record['rsql']= str_replace(['[now]','&#039;'], ["'".formatDate()."'","'"],  $record['rsql']);
                /*$record['rsql']= str_replace(['<?','?>'], ['[#','#]'],  $record['rsql']);/**/
                if ($fullpage)
                {
                    $parseData['autoprint']=$autoprint;
                    //$parseData['css_url']=$this->view->getCSS(['template','systemcss','bootstrap']);
                    $record['rsql']= str_replace(['<body>','</body>'], '', $record['rsql']);
                    return view('Reports/page_skeleton',['style'=>$record['rfilters'],'content'=>view('#'.$record['rsql'],$parseData)]);
                }
                
                $parseData['theme_logo']=parsePath($this->model_Settings->get('system.theme_logo'),'rel');
                if ($autoprint)
                {
                    $record['rsql'].='<script>window.onload = function (){window.print();}</script>';
                }
                return view('#<style>'.$record['rfilters'].'</style>'.$record['rsql'],$parseData);exit;
            }
        }
        
        function template($record)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            if (is_numeric($record) || (is_string($record) && $record!='new'))
            {
                $record=$this->model_Reports->where(is_numeric($record) ? 'rid' : 'rname',$record)->first();              
            }else
            {
		$record=null;
            }
            
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError(true);
                }
                $isnew=TRUE;
		$record=$this->model_Reports->getNewRecordData(TRUE);
                $record['rtype']=0;
                $record['rsql']='';
            }
            
            $record['edit_acc']=$this->hasAccess(AccessLevel::settings);
            $this->setFormView()//'Reports/tpl_edit')
                ->setFormTitle('reports.'.( $isnew ? 'tpl_neweditform' : 'tpl_editform'))
		->setPageTitle('reports.'.( $isnew ? 'tpl_neweditform' : 'tpl_editform'))
		->setFormAction($this,'save',['reports'],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],
                            [
                                $this->model_Reports->primaryKey=>$record[$this->model_Reports->primaryKey],
                                'rtype'=>0,
                                'rsql'=> base64_encode($record['rsql']),
                                'rfilters'=>base64_encode($record['rfilters']),
                                'rname'=>$record['rname']
                            ],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
		
                ->addBreadcrumbSubSettings()
		->addBreadcrumb('reports.tpl_bread',url($this,'templates'))
                ->addBreadcrumb($isnew ? 'reports.tpl_neweditform' : $record['rname'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info') 
                ->addFieldsFromModel($this->model_Reports->getTemplateFieldsForForm($record),$record,'reports.tpl_-key-')
                ->setTab('editor','reports.tab_editor')
                ->addGrapesJSLibrary()
                ->addCss('grape_news','https://unpkg.com/grapesjs-preset-newsletter/dist/grapesjs-preset-newsletter.css')
                ->addScript('grape_news','https://unpkg.com/grapesjs-preset-newsletter')
                ->addData('replacements',$this->model_Reports->getBlocksFields())
                ->addData('blocks',directory_map(parsePath('@views/Reports/Templates',TRUE)))
                ->addData('layouts_blocks',$this->model_Reports->getLayoutTemplates())    
                ->addData('theme_logo',$this->model_Settings->get('system.theme_logo'))
                ->addEditorScript()
                ->addData('blocks_images',
                        [
                            'layouts'=>'fas fa-file-invoice',
                            'block'=>'far fa-square',
                            'block_logo'=>'fas fa-building',
                            'block_image'=>'far fa-image',
                            'block_table_one'=>'far fa-square',
                            'block_table_two'=>'fas fa-columns',
                            'block_table_three'=>'fas fa-grip-horizontal',
                            'block_lang'=>'fas fa-language',
                            'block_row'=>'fas fa-grip-lines',
                            'block_datatable'=>'fas fa-database',
                            'block_datacontainer'=>'fas fa-database',
                        ]); 
            return $this->view->render();
        }
        
        function save($type, $post = null) 
        {
            $post=$post==null ? $this->request->getPost() : $post;
            
            if ($type=='reports')
            {
                
                if (Arr::KeysExists(['rtype','rcolumns'], $post) && $post['rtype']!=0 && is_array($post['rcolumns']))
                {
                   $post['rcolumns']= json_encode($post['rcolumns']);
                }
                if (Arr::KeysExists(['rtype','rfilters'], $post) && $post['rtype']!=0 && is_array($post['rfilters']))
                {
                   $post['rfilters']= json_encode($post['rfilters']);
                }
                
                if (Arr::KeysExists(['rid','rname','rtitle'], $post) && !is_numeric($post['rid']) && strlen($post['rname']) < 1)
                {
                   $post['rname']= strtolower(mb_url_title($post['rtitle']));
                }
                //dump($post);exit;
                if (array_key_exists('rsql', $post) && $post['rsql']!=null && strlen( $post['rsql']) > 1)
                {
                    //$post['rsql']= base64_decode($post['rsql']);
                }
            
                if (Arr::KeysExists(['rconfig','rtype'], $post) && $post['rconfig']!=null && strlen( $post['rconfig']) > 1 && $post['rtype']!=0)
                {
                    //$post['rconfig']= base64_encode($post['rconfig']);
                }
            
                if (Arr::KeysExists(['rtype','rfilters'], $post) && $post['rtype']==0 && $post['rfilters']!=null && strlen( $post['rfilters']) > 1)
                {
                    $post['rfilters']= base64_decode($post['rfilters']);
                }
            }
           
            return parent::save($type, $post);
        }
}