<?php

/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 * 	@author Artur W				
 * 	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Arrays as Arr;

class Settings extends BaseController {

    /**
     * Array with function names and access levels from which they can be accessed
     * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
     * @var Array
     */
    protected $access = [
        'index' =>          AccessLevel::view,
        'customfields' =>   AccessLevel::edit,
        'customfield' =>    AccessLevel::edit,
        'savesettings' =>   AccessLevel::edit,
        'backup' =>         AccessLevel::settings,
        'saveconfig' =>     AccessLevel::edit,
        'params' =>         AccessLevel::settings,
        'modules' =>        AccessLevel::edit,
        'query'=>           AccessLevel::settings,
        'notification'=>    AccessLevel::view,
        'uploaddata'=>      AccessLevel::edit,
        'rules'=>           AccessLevel::edit,
        'systemstat'=>      AccessLevel::settings,
    ];

    /**
     * Array with function names and linked models names
     */
    public $assocModels = [
        'customfield' => 'Settings/CustomFieldsTypes',
        'notify'=>'System/Movements',
        'rules'=>'Tasks/Rule'
    ];
    
    /**
     * Array with controller method remaps ($key is fake function name and $value is actuall function name)
     */
    public $remaps = 
    [
        'content'=>'index',
        'deletelog'=>['deletesingle',['log','$1']]
    ];
    
    public function uploadtpls($record=null,$mode='edit')
    {
        //$this->model_Settings->addUploadDriverSource('products','products.settings_upload_driver_products', get_class($this->model_Products_Product));
        if ($record!=null)
        {
            if (is_string($record) && $record=='gettemplate')
            {
                $record=$this->model_Settings->getUploadDriverData($this->request->getGet('id'));
                if (is_array($record) && Arr::KeysExists(['columns','title','filemap'], $record))
                {
                    $record['filemap']= json_decode($record['filemap'],TRUE);
                    $file= parsePath('@storage/temp/'.$record['title'],TRUE);
                    $arr=[];
                    for($i=0;$i<max(array_column($record['filemap'], 'file_column'))+1;$i++)
                    {
                        $key=Arr::searchMulti($record['filemap'], 'file_column', $i);
                        if ($key!=FALSE)
                        {
                            $key=$key[0]['column'];
                            $arr[]= array_key_exists($key, $record['columns']) ? lang($record['columns'][$key]): 'col_'.$i;
                        }else
                        {
                            $arr[]='col_'.$i;
                        }
                    }
                    $file=Arr::toCSVFile([$arr],$file,FALSE);
                    header('Content-Disposition: attachment; filename="'.$record['title'].'.csv"');
                    $this->response->setHeader('Content-Type','application/octet-stream');
                    ob_clean();
                    flush();
                    readfile($file);
                    unlink($file);exit;
                }else
                {
                     return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.settings.uploadtpls_driverid_error','danger'));
                }
            }
            $isnew=TRUE;
            $tpls=FALSE;
            if ($record!='new')
            {
                $isnew=FALSE;
                $tpls=TRUE;
                $record=$this->model_Settings->getUploadDriverData($record);
                if (!is_array($record))
                {
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.settings.uploadtpls_driverid_error','danger'));
                }
               $record['filemap']= json_decode($record['filemap'],TRUE);
            }else
            {
                $record=$this->model_Settings->getUploadDrivers('_tpl');
                if ($this->request->getGet('tpl')!=null)
                {
                    $record['model']='upload_driversource_'.$this->request->getGet('tpl');
                    $tpls=TRUE;
                    $record['lookupKey']=$this->model_Settings->getUploadDriversSources(FALSE,$record['model']);
                    if (is_array($record['lookupKey']) && Arr::KeysExists(['lookupKey','columns'], $record['lookupKey']))
                    {
                        $record['columns']=$record['lookupKey']['columns'];
                        $record['lookupKey']=$record['lookupKey']['lookupKey'];
                    }else
                    {
                            $record['lookupKey']='';
                    }
                }
            }
            foreach($record['columns'] as $key=>$val)
            {
                $record['columns'][$key]=lang($val);
            }
            
            $record['grid_columns']=
            [
                [
                    'name'=>'column',
                    'title'=>'system.settings.uploadtpls_cols_modelcolumn',
                    'editable'=>FALSE,
                    'args'=>['list'=>$record['columns']]
                ],
                [
                    'name'=>'file_column',
                    'title'=>'system.settings.uploadtpls_cols_filecolumn',
                    'editable'=>FALSE
                ],
                [
                    'name'=>'_action',
                    'title'=>'<button type="button" class="btn btn-sm btn-dark" id="id_filemap_addnew"><i class="fa fa-plus"></i></button>'
                ]
            ];
            $refurl=$this->getRefUrl();
            $this->setFormView('Settings/uploadtpl_edit')
                    ->setFormTitle('system.settings.uploadtpls_edit')
                    ->setPageTitle('system.settings.uploadtpls_edit')
                    ->setFormAction($this,'save',['uploadtpls'],['refurl'=>base64url_encode($refurl)])
                    ->parseArrayFields()
                    ->setFormArgs(['autocomplete'=>'off'],[],['class'=>'col-12'])
                    ->setCustomViewEnable(FALSE)
                    ->setFormCancelUrl($refurl)
		
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('system.settings.uploadtpls_index',url($this,'uploadtpls'))
                    ->addBreadcrumb($isnew ? 'system.general.new' : $record['title'],'/')
                    
                    ->addData('record',$record)
                    ->addInputField('system.settings.uploadtpls_title','title',$record['title'],['required'=>TRUE,'readonly'=>!$isnew])
                    ->addDropDownField('system.settings.uploadtpls_model','model',$this->model_Settings->getUploadDriversSources(TRUE),$record['model'],['required'=>TRUE,'readonly'=>$tpls])
                    ->addInputField('system.settings.uploadtpls_key','lookupKey',$record['lookupKey'],['required'=>TRUE])
                    ->addDropDownField('system.settings.uploadtpls_key','lookupKey',$record['columns'],$record['lookupKey'],['required'=>TRUE,'readonly'=>!$tpls])
                    ->addDataGrid('system.settings.uploadtpls_cols','filemap',$record['grid_columns'],$record['filemap'],TRUE,[])
                    
                    
                    ->addSimpleValidation()
                    ->addSelect2();
            
            return $this->view->render();
        }
        $this->setTableView()
                    ->setData($this->model_Settings->getUploadDrivers())
                    ->setPageTitle('system.settings.uploadtpls_index')
                    //Fiilters settings
                    ->addFilters('uploadtpls')
                    ->addFilterField('name %')
                    ->addFilterField('model %')
                    //Table Columns settings
                    ->addColumn('system.settings.uploadtpls_title','title',TRUE)
                    ->addColumn('system.settings.uploadtpls_model','model',TRUE,$this->model_Settings->getUploadDriversSources(TRUE)) 
                    ->addColumn('system.settings.uploadtpls_key','lookupKey',TRUE)
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('system.settings.uploadtpls_index',url($this,'uploadtpls'))
                    //Table Riows buttons
                    ->addEditButton('products.btn_partedit','uploadtpls/-drvid-',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addNewButton($this->model_Settings->getUploadDriversSources(FALSE,'*',TRUE))
                    ->addDeleteButton(AccessLevel::edit)
                    ->setDataIDField('name','uploadtpls');
        
        return $this->view->render();
    }
    
    public function rules($record=null)
    { 
        $set=$this->model_Settings->get('system.rules_actions_notify_send_email',TRUE);
        //$set['args'][1]= $set['args'][0];
        //$set['args'][0]= Pages\HtmlItems\EmailField::create()->setArgs(['name'=>'action_args[email]','id'=>'id_action_arg_email','label'=>'system.tasks.actions_notify_label_email'])->serialize();
        //$set['action']['args']['cust']='email';
        //dump($set);exit;
        if ($record!=null)
        {
            if ($record=='getfield')
            {
                $get=$this->request->getGet();
                if (array_key_exists('field', $get))
                {
                    $id=$this->model_Rules->find(array_key_exists('id', $get) ? $get['id'] : 0);
                    
                    if (is_array($id) && array_key_exists('rulaction_args', $id))
                    {
                        $get['field']=$id['rulaction'];
                        $id= json_decode($id['rulaction_args'],TRUE);
                        if (!is_array($id))
                        {
                            goto noid_error;
                        }
                        if (!array_key_exists('args', $id))
                        {
                            goto noid_error;
                        }
                        $id=$id['args'];
                    } else 
                    {
                        if (strlen($get['field']) > 0)
                        {
                            $get['field']='rules_actions_notify_'.$get['field'];
                            $id=[];
                            goto parse_fields;
                        }
                        
                        noid_error:
                        $get=['error'=>lang('system.errors.invalid_rule_actionfields')];
                        goto end_function;
                    }
                    parse_fields:
                    $get=$this->model_Settings->get('system.'.$get['field'],TRUE);
                    
                    if (is_array($get) && Arr::KeysExists(['args','action'], $get) && is_array($get['args']))
                    {
                        foreach ($get['args'] as $key=>$value)
                        {
                            $value= Pages\HtmlItems\HtmlItem::createField($value);
                            $name=str_replace(['action_args[',']'],'',$value->getArgs('name'));
                            if (array_key_exists($name, $id))
                            {
                                $value->setValue($id[$name]);
                            }
                            $get['args'][$key]='<div class="form-class"><label>'.lang($value->getArgs('label')).'</label>'.$value->render().'</div>';
                        }
                        $get['args']=base64_encode(implode('',$get['args']));
                        $get['action']=base64_encode(json_encode($get['action']));
                    } else 
                    {
                       goto noid_error;
                    }
                    
                }
                end_function:
                return json_encode($get);
            }
            return $this->rule($record);
        }
        $this->setTableView()
                    ->setData('rules',null,TRUE,null,[])
                    ->setPageTitle('system.tasks.rules_page')
                    //Fiilters settings
                    ->addFilters('rules')
                    ->addFilterField('rulname %')
                    //Table Columns settings
                    ->addColumn('system.tasks.rulname','rulname',TRUE)
                    ->addColumn('system.tasks.ruldesc','ruldesc',TRUE,[])
                    ->addColumn('system.tasks.enabled','enabled',FALSE,'yesno')
                    ->addColumn('system.tasks.access','access',FALSE,'access')
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('system.tasks.mainmenu_rules',url($this))
                    //Table Riows buttons
                    ->addEditButton('system.tasks.rules_editbtn','rules',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addNewButton('rules/new')
                    ->addDeleteButton(AccessLevel::edit)
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit);
        
        return $this->view->render();
    }
    
    private function rule($record)
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_Rules->find($record);              
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
            $record=$this->model_Rules->getNewRecordData(TRUE);
            $record['enabled']=1;
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView('Tasks/rule_edit')
                ->setFormTitle('system.tasks.rule_page')
		->setPageTitle('system.tasks.rule_page')
		->setFormAction($this,'save',['rules'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Rules->primaryKey=>$record[$this->model_Rules->primaryKey],
                            'rule_action'=>'',
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumbSubSettings()
                ->addBreadcrumb('system.tasks.mainmenu_rules',url($this,'rules'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['rulname'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->setTab('cfgtab','system.tasks.rule_cfgtab')
                ->addFieldsFromModel('rules',$record,'system.tasks.-key-')
                ->addSelect2('.select2')
                ->addData('getfieldurl',url($this,'rules',['getfield']));
        
            return $this->view->render();
    }
    
    function uploaddata($driver)
    {
        $driver_name='template_'.$driver.'.csv';
        $driver=$this->model_Settings->getUploadDriverData($driver);
        foreach($driver['columns'] as $ckey=>$column)
        {
            $driver['columns'][$ckey]=lang($column);
        }
        if (is_array($driver) && Arr::KeysExists(['model','lookupKey','columns'], $driver) && is_array($driver['columns']))
        {
            $post=$this->request->getPost();
            if (is_array($post) && array_key_exists('upload_key', $post))
            {
                $post[$post['upload_key']]='';
                $post['_export_justname']=TRUE;
                $post['_uploads_dir']='@temp';
                $this->uploadFiles($post);
                $post=parsePath($post[$post['upload_key']],TRUE);
                
                if (file_exists($post))
                {
                    $columns=array_keys($driver['columns']);
                    if (is_numeric($columns[0]))
                    {
                        $columns= array_values($driver['columns']);
                    }
                    $columns_file=array_map('strtolower', array_values($driver['columns']));
                    
                    $lineNumber = 1;
                    $arr=[];
                    $file=fopen($post,'r');
                    while (($raw_string = fgets($file)) !== false) 
                    {
                        $row = str_getcsv($raw_string);
                        if ($lineNumber==1)
                        {
                            foreach($row as $col)
                            {
                                if (!in_array(strtolower($col), $columns_file))
                                {
                                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.settings.upload_error_columns','danger'));
                                }
                            }
                        } else 
                        {
                            if (count($columns)!=count($row))
                            {
                                $row=$row+array_fill(count($row), count($columns)-count($row), '');
                            }
                            $arr[]= array_combine($columns, $row);
                        }
                        $lineNumber++;
                    }
                    fclose($file);
                    unlink($post);
                    
                    if (count($arr) > 0)
                    {
                        model($driver['model'])->updateMany($arr,FALSE,$driver['lookupKey']);
                        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.settings.upload_ok','success'));
                    } 
                }
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.settings.upload_error','danger'));
                
            } else 
            {
                $file= parsePath('@storage/temp/'.$driver_name,TRUE);
                $file=Arr::toCSVFile([array_values($driver['columns'])],$file,FALSE);
                header('Content-Disposition: attachment; filename="'.$driver_name.'"');
                $this->response->setHeader('Content-Type','application/octet-stream');
                ob_clean();
                flush();
                readfile($file);
                unlink($file);exit;
            }
             
            
        }
        //dump($driver);exit;
    }
    
    function notification($record)
    {
        return parent::enablesingle('notify', $record, 0);
    }
    
    function query()
    {
        $refurl=$this->getRefUrl();
        $post['query']='';
        if ($this->request->getMethod()=='post')
        {
            $post=$this->request->getPost();
            if (!array_key_exists('query', $post))
            {
                $this->session->setFlashdata('error',$this->createMessage('system.settings.query_errorpost','danger'));
            }
            if (strlen($post['query']) < 2)
            {
               $this->session->setFlashdata('error',$this->createMessage('system.settings.query_errorpost','danger')); 
            }
            if (Str::startsWith($post['query'], '@'))
            {
                $post['query']= parsePath($post['query'],TRUE);
                if (!file_exists($post['query']))
                {
                    $this->session->setFlashdata('error',$this->createMessage('system.settings.query_errorpost','danger')); 
                    goto start_form;
                }
                $post['query']= file_get_contents($post['query']);
            }
            $post['query']=explode(';',$post['query']);
            $max=count($post['query']);
            $qty=round($max/5000,0);
            $start=0;
            $arr=[];
            for($i=0;$i < $qty+1; $i++)
            {
                $start=$i*5000;
                $sql=implode(';',array_slice($post['query'], $start,5000));
                if (strlen($sql) > 2)
                {
                    //$sql= str_replace([PHP_EOL,'\n\r','\n','\r'], '',$sql);
                    if ($this->model_Settings->db()->query($sql))
                    {
                        $this->session->setFlashdata('error',$this->createMessage($this->model_Settings->errors,'danger')); 
                    }
                }
            }
            $this->session->setFlashdata('error',$this->createMessage('system.settings.query_run_ok','success')); 
           /*
            if (!Str::startsWith($post['query'], 'START TRANSACTION;'))
            {
                $post['query']='START TRANSACTION;'.$post['query'];
            }
            
            if (!Str::endsWith($post['query'], 'COMMIT;'))
            {
                $post['query']=$post['query'].'COMMIT;';
            }
            $post['query']= str_replace(PHP_EOL, '', $post['query']);
            
            if ($this->model_Settings->db()->query($post['query']))
            {
                $this->session->setFlashdata('error',$this->createMessage('system.settings.query_run_ok','success')); 
                $post['query']='';
            } else 
            {
                $this->session->setFlashdata('error',$this->createMessage($this->model_Settings->errors,'danger')); 
            }
            */
        }
        start_form:
        return $this->setFormView('Settings/query')
                ->setFormTitle('system.settings.mainmenu_query')
		->setPageTitle('system.settings.mainmenu_query')
		->setFormAction($this,'query',[],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs([],[],[])
		->setCustomViewEnable(FALSE)
                ->addBreadcrumbSubSettings()
		->addBreadcrumb('system.settings.mainmenu_query',url($this))
                ->addTextAreaField('system.settings.query_body','query',$post['query'],[])
                ->render();
    }
    function modules($record = null) {
        if ($record != null) {
            return $this->module($record);
        }
        return $this->setTableView()
                        ->setData('modules', 'mname', TRUE, null, [])
                        ->setPageTitle('system.settings.mainmenu_modules')
                        ->addFilters('modules')
                        ->addFilterField('mname %')
                        ->addColumn('system.settings.modules_mname', 'mname', TRUE)
                        ->addColumn('system.settings.modules_mdesc', 'mdesc', FALSE, 'len:80')
                        ->addColumn('system.settings.modules_route', 'route', FALSE)
                        ->addBreadcrumbSubSettings()
                        ->addBreadcrumb('system.settings.mainmenu_modules', '/')
                        ->addEditButton('system.settings.modules_editbtn', 'modules', null, 'btn-primary edtBtn', 'fa fa-edit', [])
                        //->addEnableButton()
                        //->addDisableButton()
                        //->addDeleteButton()
                        ->addNewButton('modules/new')
                        ->render();
    }

    function module($record) {
        $refurl = $this->getRefUrl(null);
        $isnew = FALSE;

        if (is_numeric($record)) 
        {
            $record = $this->model_Modules->find($record);
        } else
        if ($record!='new')
        {
           $record = $this->model_Modules->where('mname',$record)->first(); 
        }else
        {
            $record = null;
        }
        $record = $this->getFlashData('_postdata', $record);

        if ($record == null || $record == 'new') {
            $isnew = TRUE;
            $record = $this->model_Modules->getNewRecordData(TRUE);
        }
        
        if (Str::isJson($record['cfgmth']))
        {
            $record['cfgmth']= json_decode($record['cfgmth'],TRUE);
            
            if (array_key_exists('params', $record['cfgmth']) && $record['cfgmth']['params']=='params')
            {
                $record['params']=$this->model_Settings->get($record['mname'].'.*',false,null,FALSE);
            }
        }
        
        $this->setFormView('Settings/edit_module')
                ->setFormTitle('system.settings.modules_edit')
                ->setPageTitle('system.settings.modules_edit')
                ->setFormAction($this, 'save', ['modules'], ['refurl' => base64url_encode($refurl)])
                ->setFormArgs(['autocomplete' => 'off'], ['modules['.$this->model_Modules->primaryKey.']' => $record[$this->model_Modules->primaryKey]])
                ->setCustomViewEnable(FALSE)
                ->setFormCancelUrl($refurl)
                ->addEditorScript()
                ->addBreadcrumbSubSettings()
                ->addBreadcrumb('system.settings.mainmenu_modules', url($this,'modules'))
                ->addBreadcrumb((!$isnew ? $record['mname'] : ''), '/')
                ->setTab('general', 'system.general.tab_info')
                ->addFieldsFromModel($this->model_Modules->getFieldsForForm($record), $record, 'system.settings.modules_-key-', FALSE)
                ->setTab('access', 'system.general.tab_access')
                ->addDataUrlScript()
                ->addData('access_groups',$this->model_Modules->getGroupAccess($record['mname']))
                ->addData('access_levels', AccessLevel::Levels)
                ->addSelect2();
        if (array_key_exists('params',$record) && is_array($record['params']))
        {
            $this->view->setTab('params', 'system.general.tab_params');
        }
        if (is_array($record['cfgmth']) && count($record['cfgmth']) > 0) 
        {
            $this->view->setTabs($record['cfgmth'], $record);
        }
        return $this->view->render();
    }

    function logs() {

        return $this->setTableView('Pallets/index')
                        ->setData($this->model_Settings->getLogsList(), 'modified DESC', 10, null, [])
                        ->setPageTitle('system.settings.mainmenu_logs')
                        //->addFilters('logs')
                        //->addFilterField('name %')
                        ->setAsDataTable(['"pageLength"' => $this->model_Settings->get('system.tables_rows_per_page')])
                        ->addColumn('system.settings.logs_name', 'name', TRUE)
                        ->addEditButton('system.buttons.download', 'showlog', null, 'btn-primary actBtn', 'fas fa-file-download', ['data-noloader'=>true], AccessLevel::view)
                        ->addEditButton('system.buttons.remove', 'deletelog', null, 'btn-danger', 'fa fa-trash', [], AccessLevel::view)
                        ->addBreadcrumbSubSettings()
                        ->addBreadcrumb('system.settings.mainmenu_logs', url($this, 'logs'))
                        ->addDataUrlScript('#form_container')
                        ->render();
    }

    function showlog($record) {
        $record = base64url_decode($record);      
        $record = parsePath('@writable/logs/' . $record, TRUE);
        if (file_exists($record))
        {
            header('Content-Disposition: attachment; filename="' .get_file_info($record)['name']. '"');
            $this->response->setHeader('Content-Type','application/octet-stream');
            ob_clean();
            flush();
            readfile($record);
        }
        exit;
    }
    
    
    function systemstat()
    {
        $refurl=$this->getRefUrl(null);

        $this->setFormView()
                ->setFormTitle('system.settings.index_systemstat')
		->setPageTitle('system.settings.index_systemstat')
		->setFormArgs(['id'=>'systemstat_form'],[],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
                ->addBreadcrumbSubSettings()
                ->addBreadcrumb('system.settings.index_systemstat',current_url())
                ->addFieldsFromModel($this->model_Settings->getFieldsForInfoForm(),[],'system.settings.-key-')
                ->addDataUrlScript('#form_container');
        
            return $this->view->render();
    }
    
    function index($mode='view')
    {
        $refurl=$this->getRefUrl(null);
        if ($mode=='clearcache')
        {
            $this->model_Settings->clear_all_cache();
            return redirect()->to(url($this,'content',[],['tab'=>'theme']))->with('error',$this->createMessage('system.settings.index_cache_clear_msg_ok','success'));
        }
        $this->setFormView()
                ->setFormTitle('system.settings.index_title')
		->setPageTitle('system.settings.index_title')
		->setFormAction($this,'savesettings',[],['refurl'=>current_url(FALSE, TRUE)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],[],['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($this)
					
		->addData('customtabs', $this->model_Settings->getCustomSettingsTab())
                ->addEditorScript(TRUE)
                ->addCodeEditorScript()
                ->addColorPickerScript()
                ->addBreadcrumbSubSettings()
                ->addBreadcrumb('system.settings.mainmenu_subindex',current_url())       
                ->setTab('home', 'system.settings.index_tab_home')
                ->setTab('storage', 'system.settings.index_tab_storage')
                ->setTab('theme', 'system.settings.index_tab_theme')
                ->setTab('emails', 'system.settings.index_tab_emails')
                ->setTab('backup', 'system.settings.index_tab_backup')
                ->addFieldsFromModel('settings',[],'products.-key-')
                ->addTimePickerScript()
                ->addSelect2('.select2')
                ->addDataUrlScript('#form_container');
        
            return $this->view->render();
    }
    
    function editparamslist($paramsgroups, $param) {
        $filters = $this->model_Settings->filtered(['paramsgroups' => $paramsgroups, 'param %' => $param])->find();
        $refurl = $this->getRefUrl();
        $this->setFormView('Settings/editparamslist')
                ->setFormTitle('system.settings.data_list_' . $param)
                ->setPageTitle('system.settings.bread_paramslist')
                ->setFormAction($this, 'savesettings', [], ['refurl' => base64url_encode($refurl)])
                ->setFormArgs(['autocomplete' => 'off'], [], ['class' => 'col-12'])
                ->setCustomViewEnable(FALSE)
                ->setFormCancelUrl($refurl)
                ->addData('paramsgroups', $paramsgroups)
                ->addData('param', $param)
                ->addBreadcrumb('system.settings.bread_Settings', url($this))
                ->addBreadcrumb('system.settings.bread_paramslist', url($this));
        foreach ($filters as $value) {
            $this->view->addInputField(str_replace([$param, '_'], '', $value['param']), 'settings[' . $value['param'] . ']', $value['value'], ['class' => 'w-25', 'data-del' => $value['id']]);
        }
        return $this->view->render();
    }

    function params($editExtra = FALSE) {

        $this->setFormView('Settings/params')
                ->setFormTitle('')
                ->setPageTitle('system.settings.mainmenu_params')
                ->setFormAction($this, 'savesettings', [], ['refurl' => current_url(FALSE, TRUE)])
                ->setFormArgs(['class' => 'w-75'], ['refurl_ok' => url($this, 'params')])
                ->addData('_formview_custom', FALSE)
                ->addBreadcrumbSubSettings()
                ->addBreadcrumb('system.settings.mainmenu_params',current_url());
        $params = $this->model_Settings->orderby('paramsgroups,param')->findAll();
        $params[] = [
                    'paramsgroups' => 'config.app',
                    'param' => 'parseLngVars',
                    'name' => 'cfg[app][parseLngVars]',
                    'value' => strval(config('APP')->parseLngVars),
                    'fieldtype' => 'yesno',
                    'tooltip' => 'Determine if labels are parsed by current language'
        ];
        foreach ($params as $value) {
            $name = array_key_exists('name', $value) ? $value['name'] : 'settings[' . $value['param'] . '][value]';
            if ($value['fieldtype'] == 'access') {
                $this->view->addAcccessField($value['paramsgroups'] . '.' . $value['param'], $value['value'], $name, [], ['tooltip' => $value['tooltip']]);
            } else
            if ($value['fieldtype'] == 'numeric') {
                $this->view->addNumberField($value['paramsgroups'] . '.' . $value['param'], $value['value'], $name, $max = 1000, $min = -1000, ['tooltip' => $value['tooltip']]);
            } else
            if ($value['fieldtype'] == 'text') {
                $this->view->addInputField($value['paramsgroups'] . '.' . $value['param'], $name, $value['value'], ['tooltip' => $value['tooltip']]);
            } else
            if ($value['fieldtype'] == 'yesno') {
                $this->view->addYesNoField($value['paramsgroups'] . '.' . $value['param'], $value['value'], $name, ['tooltip' => $value['tooltip']]);
            } else {
                $this->view->addTextAreaField($value['paramsgroups'] . '.' . $value['param'], $name, $value['value'], ['rows' => '3', 'tooltip' => $value['tooltip']]);
            }

            if ($editExtra) {
                $this->view->addDropDownField($value['param'] . '_TYPE', 'settings[' . $value['param'] . '][fieldtype]',
                        [
                            'access' => 'access',
                            'numeric' => 'numeric',
                            'textlong' => 'textlong',
                            'text' => 'text',
                            'yesno' => 'yesno',
                        ]
                        , $value['fieldtype'] == '' ? 'textlong' : $value['fieldtype']);
                $this->view->addTextAreaField($value['param'] . '_TOOLTIP', 'settings[' . $value['param'] . '][tooltip]', $value['tooltip'], ['rows' => '3']);
            }
        }

        return $this->view->render();
    }

    function customfields($record = null) 
    {
        if ($record != null) 
        {
            return $this->customfield($record);
        }
        $this->setTableView()
                ->setData('customfield', null, TRUE)
                ->setPageTitle('system.settings.customfields_page')
                ->addFilters('customfields')
                ->addFilterField('name %')
                ->addFilterField('|| enabled')
                ->addColumn('system.settings.customfield_name', 'name', TRUE)
                ->addColumn('system.settings.customfield_type', 'type', FALSE, $this->model_CustomField->getFieldTypes())
                ->addColumn('system.settings.customfield_target', 'target', FALSE)
                ->addColumn('system.general.enabled', 'enabled', FALSE, [lang('system.general.no'), lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
                ->addEditButton('system.pallets.stack_editbtn', 'customfield', null, 'btn-primary', 'fa fa-edit')
                ->addEnableButton()
                ->addDisableButton()
                ->addDeleteButton()
                ->addNewButton('customfields/new')
                ->addBreadcrumb('system.settings.mainmenu_custom',url($this,'customfields'));
        return $this->view->render();
    }

    function customfield($record) 
    {
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if (is_numeric($record))
        {
            $record=$this->model_CustomField->find($record);              
        }else
        {
            $record=null;
        }
        
        if ($record==null || $record=='new')
        {
            if (!$this->hasAccess(AccessLevel::create))
            {
                return $this->getAccessError(true);
            }
           $isnew=TRUE;
           $record=$this->model_CustomField->getNewRecordData(TRUE);
        }

        $record = $this->getFlashData('_postdata', $record);

        return $this->setFormView('Settings/customfieldedit')
                ->setFormTitle('system.settings.customfield_edit')
		->setPageTitle('system.settings.customfield_edit')
		->setFormAction($this,'save',['customfield'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_CustomField->primaryKey=>$record[$this->model_CustomField->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
		->addBreadcrumb('system.settings.mainmenu_custom',url($this,'customfields'))
                ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],'/')
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                ->addFieldsFromModel('CustomField',$record,'system.settings.customfield_-key-')
                ->render();
    }

    public function getMovementsTable($ref, array $columns = [], $refField = 'mhref') {
        if (!is_array($ref)) {
            $ref = [$refField => $ref];
        }
        $data = $this->model_System_Movements->filtered($ref)->orderby('mhdate DESC');
        if (count($columns) > 0) {
            $data = $data->select(implode(',', $columns))->find();
        } else {
            $data = $data->find();
        }
        $columns_keys = array_keys($columns);
        if ((count($columns_keys) > 0 && is_numeric($columns_keys[0])) || count($columns_keys) < 1) {
            if (count($data) > 0) {
                $columns_keys = array_keys($data[0]);
            } else {
                $columns_keys = $this->model_System_Movements->allowedFields;
            }
        }
        $table = new \CodeIgniter\View\Table(['table_open' => '<table class="table table-grid">']);
        $types = $this->model_Settings->get('movement_types.*');
        $table->setHeading($columns_keys);

        foreach ($data as $key => $row) {
            if (array_key_exists('mhdate', $row)) {
                $row['mhdate'] = convertDate($row['mhdate'], 'DB', 'd M Y H:i');
            }

            if (array_key_exists('mhtype', $row) && array_key_exists('movement_type_' . $row['mhtype'], $types)) {
                $row['mhtype'] = $types['movement_type_' . $row['mhtype']];
                $row['mhtype'] = strlen($row['mhtype']) > 0 ? lang($row['mhtype']) : $row['mhtype'];
            }
            $table->addRow($row);
        }
        return $table->generate();
        return dump($data, FALSE);
    }

    public function backup($mode='cron') 
    {
        if ($mode=='task')
        {
            $this->model_Tasks_Task->addNew('ADHOC_DB_BACKUP',['controller'=>'Settings','action'=>'backup'],'DB_BACKUP_'. formatDate());
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('system.settings.index_backup_now_msg','success'));
        }
        $settings=$this->model_Settings->get('system.backup_*');
        
        $file=parsePath('@temp/'.(config('APP')->APPName).'_Database_Dump_'.formatDate().'.sql',TRUE);
        $file=service('BackupManager')->init()
                ->getTablesStructure()
                ->getViewsStructure()
                ->getFunctionsStructure()
                ->getProceduresStructure()
                ->enableForeignKeysCheck()
                ->enableTransaction()
                ->getData()
                ->dumpToFile($file,TRUE);
        if ($settings['backup_command']=='email')
        {
            $this->sendEmail(null, $settings['backup_email'], 'Backup', '', [], [], []);
            $this->sendEmail(null, $settings['backup_email'], $file, '', [], [],[$file]) ;
        }
    }

    public function delete(array $post = []) 
    {
        $refurl = $this->getRefUrl();
        $post = count($post) > 0 ? $post : $this->request->getPost();
        if (Arr::KeysExists(['model','name'], $post))
        {
            if ($this->model_Settings->where('paramsgroups','system')->whereIn('param',$post['name'])->delete())
            {
                return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_ok', 'success'));
            }
        }else
        if (array_key_exists('id', $post) && is_array($post['id']) && count($post['id']) > 0) 
        {
            if ($this->model_Settings->removeLogs($post['id'])) 
            {
                return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_ok', 'success'));
            }
        } else 
        {
            return parent::delete($post);
        }
        return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
    }
    
    function deletesingle($model, $value, $field = null) 
    {
        $refurl = $this->getRefUrl();
        if ($model=='log')
        {
            $value = base64url_decode($value);      
            $value = parsePath('@writable/logs/' . $value, TRUE);
            if (file_exists($value))
            {
                if (unlink($value))
                {
                    return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_ok', 'success'));
                }
            }
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
        }
        parent::deletesingle($model, $value, $field);
    }
    
    public function save($type, $post = null) 
    {
        $post = $post == null ? $this->request->getPost() : $post;
        if ($type=='uploadtpls')
        {
            if (Arr::KeysExists(['filemap','model','title','lookupKey'], $post) && is_array($post['filemap']))
            {
                
                $type=$this->model_Settings->addNewUploadDriver($post['model'],$post['title'],$post['lookupKey'],$post['filemap']);
                if ($type)
                {
                    return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('system.settings.uploadtpls_cols_addnewsaveok', 'success'));
                }
            }
            return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('system.settings.uploadtpls_cols_addnewsaveerror', 'danger'));
        }else
        if ($type=='modules')
        {
            if (array_key_exists('perms', $post) && is_array($post['perms']) && count($post['perms']) > 0)
            {
                $this->model_Auth_AuthAccess->setCustomAccess('',$post['perms']);
            }
            if (array_key_exists('settings', $post) && is_array($post['settings']) && count($post['settings']) > 0)
            {
                $this->savesettings($post);
            }
            if (array_key_exists('modules', $post) && is_array($post['modules']) && count($post['modules']) > 0)
            {
                $post=$post['modules'];
            } else 
            {
                $post=[];
            }
        }else
        if ($type=='rules')
        {
            if (Arr::KeysExists(['rulaction','action_args'], $post) && is_array($post['action_args']))
            {
                $post['rulaction_args']= json_encode($post['action_args']);
            }else
            {
                $post['enabled']=0;
            }
        }
        
        $refurl = $this->getRefUrl();
        return parent::save($type, $post);//order_placed Send email when order placed successfully
    }
    
    public function savesettings($post = null) {
        $post = $post == null ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl(null, TRUE, TRUE);
        
        if (array_key_exists('cfg', $post)) 
        {
            foreach ($post['cfg'] as $key => $value) 
            {
                if (!$this->saveconfig($key, $value, $key == 'database')) {
                    return redirect()->to($refurl)->with('error', $this->createMessage('system.settings.error_config_save', 'danger'));
                }
            }
        }
        $this->uploadFiles($post,'settings');
        if (array_key_exists('theme_logo', $post))
        {
            $post['settings']['theme_logo']=$post['theme_logo'];
        }
        $model = $this->model_Settings;
        if (array_key_exists('settings', $post) && array_key_exists('delete', $post['settings'])) 
        {
            $model->filtered(['id In' => $post['settings']['delete']])->delete();
            unset($post['settings']['delete']);
        }
        
        if (array_key_exists('settings', $post) && array_key_exists('cache', $post['settings']) && !$post['settings']['cache']) 
        {
            $model->clear_all_cache();
        }
        
        if (array_key_exists('settings', $post) && Arr::KeysExists(['backup_enabled','backup_time','backup_command'], $post['settings'])) 
        {
            $model->addOrRemoveBackupJob(intval($post['settings']['backup_enabled'])==1,$post['settings']['backup_time'],$post['settings']['backup_command']);
        }
        
        if (array_key_exists('modules', $post)) {
            $this->model_Modules->save($post['modules']);
        }
        if (array_key_exists('settings', $post) && count($post['settings']) > 0 && !$model->writeMany($post['settings'])) 
        {
            return redirect()->to($refurl)->with('error', $this->createMessage($model->errors(), 'danger'));
        }
        return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_save_ok', 'success'));
    }

    private function saveconfig($group, array $values, $isdb = FALSE) {
        $group = APPPATH . 'Config/' . ucfirst($group) . '.php';

        if (!file_exists($group)) {
            return FALSE;
        }
        $content = file_get_contents($group);
        foreach ($values as $key => $value) {
            $pattern = $isdb ? "/'" . $key . "'(.*?),/" : '/public \$' . $key . '(.*?);/';
            $result = preg_match($pattern, $content, $matches);

            if (!is_numeric($value) && !is_array($value)) {
                $value = "'" . $value . "'";
            }
            if (count($matches) > 1) {
                $result = str_replace($matches[1], ($isdb ? ' => ' : ' = ') . $value, $matches[0]);
            }
            $content = str_replace($matches[0], $result, $content);
        }
        return file_put_contents($group, $content) > 0;
    }

}
