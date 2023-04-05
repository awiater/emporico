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

class Documents extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * 
     * @var array
     */
    protected $access=
    [
        'index'=>       AccessLevel::edit,
        'files'=>       AccessLevel::edit,
    ];
    
    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = ['api'];
    
    /**
     * Array with function names and linked models names
     * 
     * @var array
     */   
    public $assocModels=
    [
        'files'=>'Documents/Document',
    ];
    
    /**
     * Array with function names which are enabled when accessing from mobile device
     * @var Array
     */
    protected $mobilenebaled=['download','files'];
    
    /**
     * Array with controller method remaps ($key is fake function name and $value is actual function name)
     * 
     * @var array
     */    
    public $remaps=
    [
        'index'=>'files',
        'deletefile'=>['deletesingle',['files','$1']],
        'test'=>['filesform',[null,[],[['doc_name','doc_desc']]]],
        'show'=>['files',['$1',null,'view']]
    ];
        
    
    function files($record=null,string $folder=null,$mode='edit')
    {
        if ($record!=null || ($folder=='new' && $record!=null))
        {
            if ($folder=='new')
            {
                $folder=$record;
                $record='new';
            }
            return $this->file($record,$folder,$mode);
        }
        $filters=[];
        if ($folder!=null)
        {
            $filters['doc_folder']=$folder;
        }
        $this->setTableView()
                    ->setData('files',null,TRUE,null,$filters)
                    ->setPageTitle('documents.files_mainmenu')
                    //Fiilters settings
                    ->addFilters('files')
                    ->addFilterField('doc_name %')
                    ->addFilterField('doc_tags %')
                    //Table Columns settings
                    ->addColumn('documents.files_doc_name','doc_name',TRUE,[],null,'doc_desc')
                    ->addColumn('documents.files_doc_folder','doc_folder',TRUE)
                    ->addColumn('documents.files_doc_createdby','doc_createdby',TRUE)
                    ->addColumn('documents.files_doc_createdon','doc_createdon',TRUE,[],'d M Y H:i')
                    ->addColumn('documents.files_enabled','enabled',TRUE,'yesno')
                    ->addColumn('documents.files_access','access',TRUE,'access')
                    //Breadcrumb settings
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('documents.files_mainmenu',url($this))
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','files/-id-',null,'btn-primary edtBtn','fas fa-edit',[])
                    ->addEditButton('documents.btn_download','download/-id-',null,'btn-info','fas fa-cloud-download-alt',['data-noloader'=>TRUE])
                    ->addEditButton('documents.btn_download','deletefile/-id-',null,'btn-danger','far fa-trash-alt',[])
                    //Table main buttons
                    ->addNewButton('files/new')
                    ->addEnableButton()
                    ->addDisableButton()
                    ->addModuleSettingsButton(null,null,['margin'=>'ml-3']); 
        
        return $this->view->render();
    } 
    
    private function file($record,string $folder=null,$mode='edit')
    {  
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        if ($record==null || $record=='new')
        {
            if (!$this->hasAccess(AccessLevel::create))
            {
                return $this->getAccessError(true);
            }
            $isnew=TRUE;
            $record=$this->model_Files->getNewRecordData(TRUE);
            $record['enabled']=1;
            $record['doc_folder']=$folder;
        }else
        {
            if ($record==0 && $this->request->getGet('file')!=null)
            {
                $record= base64url_decode($this->request->getGet('file'));
            }
            $record=$this->model_Files->filtered(['did'=>$record,'|| doc_name'=>$record])->first(); 
        }
        
        $record=$this->getFlashData('_postdata',$record);
        
        if (!is_array($record))
        {
            record_id_error:
            return redirect()->to($refurl)->with('error',$this->createMessage('documents.error_record_not_exists','danger'));    
        }
        
        if ($mode=='view')
        {
            $record['doc_path']= json_decode($record['doc_path'],TRUE);
            if (!is_array($record['doc_path']))
            {
                goto record_id_error;
            }
            $ispdf= array_key_exists('ext', $record['doc_path']) && $record['doc_path']['ext']=='PDF';
            $settings=$this->model_Settings->get('documents.*');
            $record['doc_path']= array_values($record['doc_path']);
            $record['doc_path']=storage($settings['documents_storagetype'])->getFile($record['doc_path'][0]);
            
            if ($ispdf)
            {
                if (strlen($record['doc_path']) < 2)
                {
                    echo view('error/html/exception',['type'=>'danger','pernament'=>TRUE,'msg'=>'File Not Exists']);exit;
                }
                $pdf = file_get_contents($record['doc_path']);
                header('Content-Type: application/pdf');
                header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                header('Pragma: public');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                header('Content-Length: '.strlen($pdf));
                header('Content-Disposition: inline; filename="'.$record['doc_name'].'";');
                ob_clean(); 
                flush(); 
                echo $pdf;
            }else
            {
                return redirect()->to($record['doc_path']);
            }
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $record['cfg_acc']=$this->hasAccess(AccessLevel::settings);
        $this->setFormView()
                ->setFormTitle('documents.file_edit')
		->setPageTitle('documents.file_edit')
		->setFormAction($this,'save',['files'],['refurl'=>base64url_encode($refurl)])
                ->parseArrayFields()
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Files->primaryKey=>$record[$this->model_Files->primaryKey]
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
		
                ->addBreadcrumbSubSettings()
		->addBreadcrumb('documents.files_mainmenu',url($this))
                
			
		->addData('record',$record)         
                ->setTab('general','system.general.tab_info')
                
                ->addFieldsFromModel('files',$record,'documents.files_-key-')
                ->addValidation('files')
                ->addSelect2()
                ->addDataUrlScript('#form_container');
        if (!$isnew)
        {
            $this->view->setTab('tab_file','documents.files_tab_file');
        }
        if ($folder!=null)
        {
            $this->view->addBreadcrumb($folder,url($this,'files',[$folder]));
        }
        $this->view->addBreadcrumb($isnew ? 'system.buttons.new' : $record['doc_name'],'/');
        return $this->view->render();
    }
    
    function download($record=null)
    {
        if ($record==':get' || $record==null)
        {
            if ($this->request->getGet('file')!=null)
            {
                $record= base64url_decode($this->request->getGet('file'));
            } else 
            {
                goto error;
            }
        }else
        {
            $record= base64url_decode($record);
        }
        if (is_numeric($record))
        {
            $record=$this->model_Orders_Order->find($record);
            return loadModule('Sales','download',[$record,-1]);
        }else
        {
            $record=$this->model_Files->filtered(['did'=>$record,'|| doc_name'=>$record])->first();
        }
        
        if (!is_array($record))
        {
            error:
            return ['error'=>'documents.error_api_no_files'];
        }
        $record['doc_path']= json_decode($record['doc_path'],TRUE);
        if (!is_array($record['doc_path']))
        {
            goto error;
        }
        $record['doc_path']= array_values($record['doc_path']);
        
        if ($this->request->getGet('tracked')!=null)
        {
            $tracked= base64url_decode($this->request->getGet('tracked'));
            
            if (is_string($tracked) && Str::contains($tracked, ';'))
            {
                $tracked= explode(';', $tracked);
                $this->addDownloadHistory($tracked[0], count($tracked) > 1 ? $tracked[1] : null, $record['doc_name']);
            }
        }
        $settings=$this->model_Settings->get('documents.*');
        $this->storage=storage($settings['documents_storagetype']);
       
        
        $record['doc_path'][0]=$this->storage->getFile($record['doc_path'][0]);
        header('Content-Disposition: attachment; filename="' .$record['doc_path'][2]. '"');
        $this->response->setHeader('Content-Type','application/octet-stream');
        ob_clean();
        flush();
        readfile($record['doc_path'][0]);
        exit;
        
    }
    
    function filesform($folder=null,array $tags=[],array $columns=[],string $tableID=null)
    {
        $filters=['enabled'=>1,'access'=>'@loged_user'];
        if ($folder!=null)
        {
            $filters['doc_folder %']=$folder;
        }
        
        if (count($tags) > 0)
        {
            $filters['doc_tags In']=$tags;
        }
        $view=new Pages\TableView($this, TRUE);
        $view->setFile('Documents/files_table')
                    ->setData('files','doc_createdon DESC',TRUE,null,$filters)
                    ->setPageTitle('documents.files_mainmenu');
        $avcolumns=$this->model_Files->allowedFields;
        if ($tableID!=null)
        {
            $view->setTableID($tableID);
        }
        if (count($columns) < 1)
        {
            $columns=$avcolumns;
        }
        
        foreach($columns as $column)
        {
            if (is_array($column))
            {
                $view->addColumn('documents.files_'.$column[0],$column[0],TRUE,[],null,$column[1]);
            }else
            if ($column=='access')
            {
                $view->addColumn('documents.files_access','access',TRUE,'access');
            }else
            if ($column=='enabled')
            {
                $view->addColumn('documents.files_enabled','enabled',TRUE,'yesno');
            }else
            if ($column=='doc_createdon')
            {
                $view->addColumn('documents.files_doc_createdon','doc_createdon',TRUE,[],'d M Y H:i');
            }else
            if ($column=='doc_type')
            {
                $view->addColumn('documents.files_doc_type','doc_type',TRUE,[],'icon');
            }else
            if ($column!='doc_path')
            {
                $view->addColumn('documents.files_'.$column,$column,TRUE);
            }
        }
                    
        $view->addEditButton('system.buttons.edit_details','files/-id-',null,'btn-xs  btn-primary edtBtn','fas fa-edit',[], AccessLevel::settings)
                    ->addEditButton('documents.btn_download','download/-id-',null,'btn-xs btn-info','fas fa-cloud-download-alt',['data-noloader'=>TRUE])
                    ->addEditButton('documents.btn_delete','deletefile/-id-',null,'btn-xs btn-danger','far fa-trash-alt',[], AccessLevel::delete)
                    //->addHeaderButton('files/new',null,'button','btn btn-dark btn-xs','fas fa-plus','button_text',AccessLevel::edit,[])
                    ->addNewButton('files/new/'.$folder, AccessLevel::edit,['class'=>'btn btn-dark btn-xs'])
                    ->setSmallTable()
                    ->setDarkTable(TRUE)
                    ->setNoDataMessage('documents.msg_notdata'); 
        
        return $view->render('justview');
    }
    
    function api($command='list')
    {
        $post=$this->request->getPost();
        $post=$post+$this->request->getGet();
        $post['_command']=$command;
        
        if (!array_key_exists('_uploads_dir', $post))
        {
            $return=['error'=>lang('documents.error_api_no_dir')];
            goto end_of_api;
        }
        
        if ($post['_command']=='getfilelist')
        {
            if ($post['_uploads_dir']=='.' || $post['_uploads_dir']=='@storage')
            {
                $post['current_dir']='@storage';
                $post['master_folder']=TRUE;
                $post['_uploads_dir']=parsePath('@storage',TRUE);
                $post['parent_dir']='@storage';
            }else
            {
                $post['master_folder']=FALSE;
                $post['current_dir']=$post['_uploads_dir'];
                $post['parent_dir']=Str::afterLast(substr($post['_uploads_dir'],0, strlen($post['_uploads_dir'])-1), '/');
                $post['parent_dir']=Str::before($post['_uploads_dir'], $post['parent_dir']);
                $post['_uploads_dir']=parsePath($post['_uploads_dir'],TRUE);
            }
            
            return 
            [
                'parent_dir'=>$post['parent_dir'],
                'list'=> directory_map($post['_uploads_dir']),
                '_uploads_dir'=>$post['_uploads_dir'],
                'master_folder'=>$post['master_folder']
            ];
        }else
        if ($post['_command']=='new')
        {
           if (!array_key_exists('_folder', $post))
            {
                $return=['error'=>lang('documents.error_api_no_files')];
                goto end_of_api;
            }
            mkdir(parsePath($post['_uploads_dir'].$post['_folder'],TRUE));
        }else
        if ($post['_command']=='remove')
        {
            if (!array_key_exists('_files', $post) || (array_key_exists('_files', $post) && !is_array($post['_files'])))
            {
                $return=['error'=>lang('documents.error_api_no_files')];
                goto end_of_api;
            }
            foreach($post['_files'] as $file)
            {
                $file=parsePath($file,TRUE);
                if (!file_exists($file))
                {
                    $return=['error'=>lang('documents.error_api_no_files')];
                    goto end_of_api;
                }else
                {
                    if (is_dir($file))
                    {
                        rmdir($file);
                    }else
                    {
                        unlink($file);
                    }
                    goto end_of_api;
                }
            }
            
        }else
        if ($post['_command']=='upload')
        {
            parent::uploadFiles($post);
            if (!array_key_exists('form_upload_file', $post))
            {
                $return=['error'=>lang('documents.error_upload_api')];
            }else
            {
                if (!array_key_exists('_uploads_dir', $post))
                {
                    return json_encode(['error'=>lang('documents.error_api_no_dir')]);
                }
            } 
        }else
        if ($post['_command']=='list')
        {
            if (array_key_exists('_folder', $post) && strlen($post['_folder']) > 0)
            {
                $post['_uploads_dir'].=$post['_folder'];
            }
            if (array_key_exists('_filters', $post) && $post['_filters']=='images')
            {
                $post['_filters']=['.jpg','.jpeg','.bmp','.png','.ico','.gif'];
            }
        }
        
        $return=['_uploads_dir'=>$post['_uploads_dir'],'parent'=>dirname($post['_uploads_dir']),'files'=>mapDir($post['_uploads_dir'], array_key_exists('_filters', $post) ? (is_array($post['_filters']) ? $post['_filters'] : explode(',', $post['_filters'])) : [])];    
        end_of_api:
        return $return;   
    }
    
    
    function settings($tab,$record)
    {
        $settings=$this->model_Settings->get('documents.*',FALSE,'*');
        $view=new Pages\FormView($this);
        if ($tab=='cfg')
        {
            $view->addDropDownField('documents.settings_storagetype', 'settings[documents_storagetype]', $this->model_Settings->getStorageTypes(), $settings['documents_storagetype']['value'], []); 
            $view->addInputField('documents.settings_storagefolder', 'settings[documents_storagefolder]', $settings['documents_storagefolder']['value'], []);
        }
        return view('System/form_fields',$view->getViewData());
    }
    
    function save($type, $post = null) 
    {
        $post = $post == null ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
        
        if ($type=='files')
        {
            $this->uploadFiles($post);
            if (!array_key_exists('doc_open', $post))
            {
                $post['doc_open']='@/documents/download/-id-?refurl=-refurl-';
            }
            if (array_key_exists('doc_path', $post) && Str::isJson($post['doc_path']))
            {
                $doc_path= json_decode($post['doc_path'],TRUE);
                if (is_array($doc_path) && array_key_exists('ext', $doc_path))
                {
                    $post['doc_type']=$this->model_Files->getIconForFileType($doc_path['ext']);
                }
            }
            if (!is_numeric($post['did']))
            {
                $post['doc_createdby']= loged_user('name');
                $post['doc_createdon']= formatDate();
            }
        }
        
        return parent::save($type, $post);
    }
    
    function _after_save($type, $post, $refurl, $refurl_ok): bool 
    {
        if ($type=='files' || $type=='model_files')
        {
            if (Arr::KeysExists(['doc_folder','doc_createdby','doc_name'], $post))
            {
                $this->addMovementHistory('notify', null, null, $post['doc_folder'], lang('documents.mov_create_view',$post),'documents');
            }
        }
        return TRUE;
    }


    function deletesingle($model, $value, $field = null) 
    {
        $refurl = $this->getRefUrl();
        if ($model=='files')
        {
            if ($this->model_Files->deleteFile($value))
            {
                return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_ok', 'success'));
            }
            return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_no', 'danger'));
        }
        return parent::deletesingle($model, $value, $field);
    }
    
    function uploadFiles(&$post, $keyToExtract = null) 
    {
        //kerry.radcliffe@apdcw.onmicrosoft.com Rah18343
        $settings=$this->model_Settings->get('documents.*');
        $this->storage=storage($settings['documents_storagetype']);
        $post['_uploads_dir']=$settings['documents_storagefolder'];
        return parent::uploadFiles($post, $keyToExtract);
    }
    
    function pages(string $mode, Pages\FormView $view, array $data) 
    {
        if ($mode=='download')
        {
            return $view->setTab('download_tab','documents.pages_download_tab')
                        ->addDropDownField('documents.pages_download_file', 'pg_cfg[file]', $this->model_Files->getDocsForForm(), array_key_exists('file', $data) ? $data['file'] : null, ['advanced'=>TRUE])
                        ->addHiddenField('pg_cfg[url]','current_url')
                        ->addHiddenField('pg_action', 'Products::downloadfile@{pricefile},{brand},{url}')
                        ->addFileManagerScript();
        }
        return $view;
    }
    
    private function addDownloadHistory(string $mhref,string $mhfrom,string $mhinfo)
    {
        $this->addMovementHistory('docs_download', $mhfrom, null, $mhref, $mhinfo, null, 'auto');
    }
}
        
        /*
        function files($record=null)
        {
            $filters=['category'=>'/'];
            if ($record!=null)
            {
                $category=$this->model_Cats->find($record);
                if ($category==null)
                {
                   return $this->file($record); 
                } 
                $filters['category']=$category['category'].'/'.$category['name'];
                $filters['category']= str_replace('//', '/', $filters['category']);
            }else
            {
                $category=null;//$this->model_Cats->where('category', $filters['category'])->first();
            }
            $post=$this->request->getPost();
            /*if (array_key_exists('filter', $post) && strlen($post['filter']) > 0)
            {
                $filters['( name %']=$post['filter'];
                $filters['|| tags % )']=$post['filter'];
                if ($filters['category']=='/')
                {
                    unset($filters['category']);
                    
                }
            }
           
            $viewmode='list';
            $edit_access=$this->auth->hasAccess($this->getModuleAccess(AccessLevel::edit));
            if (!$edit_access)
            {
                $filters['enabled']=1;
            }
            if ($this->request->getGet('filtered')!=null && array_key_exists('category', $filters))
            {
                unset($filters['category']);
            }
            $this->setTableView('Documents/Index/index_'.$viewmode)
                    ->setData('files::getFilesAndFolders','name',TRUE,null,$filters)
                    //->setFormTitle('documents.docs_mainmenu')
                    ->setPageTitle('documents.docs_mainmenu')
                    //->setCustomViewEnable(TRUE)
                    ->setDataIDField('id')
                    ->addColumn('name','name',FALSE)
                    ->addFilters('files')
                    ->addFilterField('( name %')
                    ->addFilterField('|| tags % )')
                    //->setFormArgs([],[],['class'=>'col-12','id'=>'id_documentsfiles'])
                    //->addData('folders',$this->model_Files->getFilesAndFolders($filters))
                    ->addHeaderButton(null,'documentsNewItemBtn','button',$class='btn btn-sm btn-primary','<i class="fas fa-plus"></i>','documents.btn_new',AccessLevel::create)
                    ->addData('date_format','d M Y')
                    ->addData('url',[
                                     'del_dir'=>url($this,'deletesingle',['-model-','-id-'],['refurl'=>current_url(FALSE,TRUE)]),
                                     'open_dir'=>'/documents/cats/-id-',
                                     'edit'=>url('/-url-',null,[],['dir'=> is_array($category) && array_key_exists('dcid', $category) ? $category['dcid'] : '0','refurl'=>current_url(FALSE,TRUE)]),
                                     'open'=>url($this,null,['files','-id-'],['refurl'=>current_url(FALSE,TRUE)]),
                                     'enable'=>url($this,'enablesingle',['-model-','-id-','-value-'],['refurl'=>current_url(FALSE,TRUE)]),
                                     'download'=>url($this,'donwloaddoc',['-id-'],['refurl'=>current_url(FALSE,TRUE)]),
                                     'share'=>url($this,'share',[],['refurl'=>current_url(FALSE,TRUE)])
                                     ])
                    
                    ->addData('doc_types',$this->model_Types->getForForm('name','*'))
                    ->addData('repKeys',$this->model_Files->getFilesAndFoldersKeys(TRUE))
                    ->addData('category',$category)
                    ->addData('edit_access',$edit_access)
                    ->addData('del_access',$this->auth->hasAccess($this->getModuleAccess(AccessLevel::delete)))
                    ->addData('new_access',$this->auth->hasAccess($this->getModuleAccess(AccessLevel::create)))
                    ->addData('shareUrl',url($this,'share',[],['refurl'=>current_url(FALSE,TRUE)]))
                    ->addData('thumbnails',$this->model_Files->getThumbNails($filters));
            if (array_key_exists('category', $filters) && $filters['category']!='/' && is_array($category) && array_key_exists('text', $category))
            {
                if (Str::contains(substr($filters['category'],1),'/'))
                {
                    $filters['category']=Str::before(substr($filters['category'], 1),'/');//
                    $filters['category']=$this->model_Cats->where('name',$filters['category'])->first();
                    if (is_array($filters['category']) && Arr::KeysExists(['text','dcid'], $filters['category']))
                    {
                        $this->view->addBreadcrumb($filters['category']['text'],url($this,'files',[$filters['category']['dcid']]));
                        $this->view->addData('url_back',url($this,'files',[$filters['category']['dcid']]));
                    }
                    
                }else
                {
                    $this->view->addBreadcrumb('documents.mainmenu',url($this));
                    $this->view->addData('url_back',url($this,'files'));
                }
                $this->view->addBreadcrumb($category['text'],url($this,'files',[$record]));
                
            }else
            {
                $this->view->addBreadcrumb('documents.mainmenu',url($this));
                $this->view->addData('url_back',url($this,'files'));
            }
            if (array_key_exists('name %', $filters))
            {
                $this->view->addData('filters_name',$filters['name %']);
            }
            if($category==null)
            {
                 $this->view->addData('url_back',null);
            }
            return   $this->view->render();
        }
        
        function file_form($record,$type,array $excludeFields=[])
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            $dir=$this->request->getGet('dir');
            if (is_numeric($record))
            {
                $record=$this->model_Files->find($record);
            }else
            {
		$record=null;
            }
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError();
                }
                $isnew=TRUE;
		$record=$this->model_Files->getNewRecordData(TRUE);
                $record['createdby']= loged_user('username');
                $record['createdon']= formatDate();
                $record['modon']=$record['createdby'];
                $record['type']=$type==null ? 'file' : $type;
                if ($dir!=null)
                {
                    $dir=$this->model_Cats->find($dir);
                    if ($dir==null)
                    {
                       $dir='/';
                       $record['dir']=$dir;
                    }else
                    {
                        $record['dir']=$dir['name'];
                        $dir=$dir['category'].'/'.$dir['name'];
                    }
                    $record['category']= str_replace('//', '/', $dir);
                    
                }
                $record['enabled']=1;
                $record['expireon']= formatDate('now','+ 1 year');
                $record['access']=$this->model_Settings->get('documents.document_cfg_employeeaccess');
            } else 
            {
                $record['modby']= loged_user('username');
                $record['args']= json_decode($record['args'],TRUE);               
            }
            
            $record['modon']= formatDate();
            $linked=$this->model_Files->getDocumentsTypes($record['type'])['linked'];
            if ($linked && (!is_array($record['args']) || (is_array($record['args']) && !array_key_exists('emails', $record['args']))))
            {
                if (!is_array($record['args']))
                {
                    $record['args']=['emails'=>[]];
                }else
                {
                   $record['args']['emails']=[]; 
                }
                
            }
            $fields=$this->model_Files->getFieldsForForm($record);
            $fields= array_diff_key($fields, array_flip($excludeFields));

            $this->setFormView()
                //->setCustomViewEnable(FALSE)    
                ->setFormTitle('documents.doc_edit')
		->setPageTitle('documents.doc_edit')
		->setFormAction($this,'save',['files'],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],[
                                $this->model_Files->primaryKey=>$record[$this->model_Files->primaryKey],
                                'createdby'=>$record['createdby'],
                                'createdon'=>$record['createdon'],
                                'modby'=>$record['modby'],
                                'modon'=>$record['modon'],
                                'type'=>$record['type'],
                                '_uploads_dir'=>$this->model_Settings->get('documents.document_cfg_onedrivefolderid')
                                //'category'=>$record['category']
                              ])
		->setFormCancelUrl($refurl)
				
			
		->setTab('general','system.general.tab_info')
                ->addFieldsFromModel($fields,$record,'documents.doc_-key-',FALSE)
                ->addData('record',$record);
            if (!$isnew && $linked)
            {
              $this->view->setTab('emailstab','documents.doc_emailstab')
                ->addElementsListBoxField('documents.doc_emails_list','args',$record['args']['emails'],['input'=>'email']);
            }
            
            $this->getBreadcrumb($record,$isnew,false);
            return $this->view;
        }
        function upload($type,$field)
        {
           $post=[$field=>''];
           $this->uploadFiles($post);
           $post[$field]= json_decode($post[$field],TRUE);
           $field= array_values($post[$field]);
           if ($type=='tiny')
           {
               $field=parsePath($field[0],TRUE);
               $type=protected_link($field,TRUE);
               unlink($field);
               $field=$type;
           }
           echo json_encode(['location' =>$field]);exit;
        }
        function file($record,$type=null)
        {
            $this->file_form($record, $type,$type=='text' ? ['path'] :[]);
            $record=$this->view->getViewData('record');
            
            if ($record['type']=='text')
            {
               $this->view->setTab('edit','documents.tab_edit')
                          ->addEditor('','path',$record['path'],'full','500',null,TRUE);
            }
            return $this->view->render();
        }
        
        function show($record)
        {
            $refurl=$this->getRefUrl(null);
            
            if (is_numeric($record))
            {
                $record=$this->model_Files->find($record);
            }else
            {
		$record=null;
            }
            
            if (is_array($record['path']))
            {
                $record['path']= array_values($record['path']);
                $record['path']=$record['path'][0];
            }
            $this->addMovementHistory('docs_open', null, null, $record['path'], $record['did'], 'docs');
            if ($record['type']=='images')
            {
                return img(protected_link(parsePath($record['path']),TRUE),FALSE,['class'=>'img-fluid']);
            }

            if (Str::endsWith(strtolower($record['path']), '.pdf'))
            {
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="' . $record['path'] . '"');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');
                echo readfile($record['path']);exit; 
            }
            if ($record['type']=='redirect')
            {
                 return redirect()->to($record['path']);
            }
            if ($record['type']!='text')
            {
                return redirect()->to('https://docs.google.com/viewer?url='.$record['path'].'&embedded=true');
            }
            $breadcumbs=$this->model_Cats->getFoldersNames();
            $breadcumbs_urls=$this->model_Cats->getFoldersNames('name','dcid');
            $this->setFormView('Documents/show')//'Documents/show')
                        ->setFormTitle($record['docname'])
                        ->setPageTitle($record['docname'])
                        ->setFormArgs([],[],['class'=>'w-100'])
                        ->setFormCancelUrl($refurl)
                        ->setCustomViewEnable(FALSE)
                        ->addData('record',$record)
                        
                        ->addBreadcrumb('documents.mainmenu',$refurl)
                        ->addButtonsToolBar('navigation',
                                [
                                    ['type'=>'a','icon'=>'fas fa-arrow-alt-circle-left','class'=>'btn btn-sm btn-danger text-white mr-2','href'=>$refurl,'tooltip'=>'documents.btn_up'],
                                    ['type'=>'button','id'=>'toollbutton_print','icon'=>'fas fa-print','class'=>'btn btn-sm btn-primary text-white','tooltip'=>'documents.btn_print'],
                                    ['type'=>'a','icon'=>'fas fa-download','class'=>'btn btn-sm btn-secondary text-white','href'=>url($this,'donwloaddoc',[$record['did']]),'tooltip'=>'documents.btn_download']
                                ],['background'=>'white'])
                    ->addCustomTextField('','','<div id="file_content">'.$record['path'].'</div>',[])
                    ->addPrintLibrary();
            foreach(explode('/',$record['category']) as $item)
            {
              if (strlen($item) > 0 && array_key_exists($item, $breadcumbs))
              {
                  $this->view->addBreadcrumb($breadcumbs[$item],url($this,'files',[$breadcumbs_urls[$item]]));
              }
            }
            $this->view->addBreadcrumb($record['docname'],current_url());
            return $this->view->render();
        }
        function share($id=null)
        {
            $refurl=$this->getRefUrl();
            $post=$this->request->getPost();
            $key=$this->request->getGet('key');
            if ($this->request->getMethod()=='post' && $id==null)
            {
                if (array_key_exists('sharedEmailsID', $post) && array_key_exists('sharedEmails', $post) && is_numeric($post['sharedEmailsID']) && is_array($post['sharedEmails']))
                {
                    
                    $post['sharedEmailsID']=$this->model_Files->find($post['sharedEmailsID']);
                    if (!is_array($post['sharedEmailsID']))
                    {
                        return redirect()->to($refurl)->with('error',$this->createMessage('documents.error_invalid_id','danger'));
                    }
                    $post['pass']=Str::createPasswordString(8);
                    $post['sharedEmailsID']['args']= json_encode(['pass'=>[Str::hashPasswordString($post['pass'])],'emails'=>$post['sharedEmails']]);
                    $send=$this->shareLinkWithEmails($post['sharedEmailsID']['did'],$post['sharedEmails'],$post);
                    if (!$send)
                    {
                        return redirect()->to($refurl)->with('error',$this->createMessage('documents.error_sendemailfailed','danger'));
                    }else 
                    {
                        $this->model_Files->save($post['sharedEmailsID']);
                        return redirect()->to($refurl)->with('error',$this->createMessage('documents.error_sendemailok','success'));
                    }
                }
            }else
            if ($this->request->getMethod()=='post' && is_numeric($id))
            {
               check_login:
               $id=$this->model_Files->find($id);
               if (!is_array($id))
               {
                   id_error:
                   return redirect()->to(current_url())->with('error',$this->createMessage('documents.error_invalid_id','danger'));
               }
               if (!Arr::KeysExists(['login','password'], $post))
               {
                   login_error:
                   return redirect()->to(current_url())->with('error',$this->createMessage('system.auth.loginform_error','danger'));
               }
               $id['args']= json_decode($id['args'],TRUE);
               
               if (is_array($id['args']) && Arr::KeysExists(['pass','emails'], $id['args']) && is_array($id['args']['emails']))
               {
                   if (!in_array($post['login'], $id['args']['emails']))
                   {
                       goto login_error;
                   }
                   $key= array_key_exists($post['login'], $id['args']['pass']) ? $post['login']: 0;
                   if (!password_verify($post['password'], $id['args']['pass'][$key]))
                   {
                       goto login_error;
                   }
                   download_file:
                   $id['path']= json_decode($id['path'],TRUE); 
                   $id['path']=array_values($id['path']);
                   $id['path']=$id['path'][0];
                  
                   return $this->download($id['path'],$id['docname']);
               }
               goto id_error;
               //
                
            }else
            if (is_numeric($id))
            {
                $id=$this->model_Files->find($id);
                if (!is_array($id))
                {
                    return redirect()->to(site_url())->with('error',$this->createMessage('documents.error_invalid_id','danger'));
                }
                $error=false;
                if (!Str::isJson($id['args']))
                {
                    $error=$this->createMessage('documents.error_invalid_id','danger');
                }
                if($this->auth->isLoged())
                {
                    goto download_file;
                }
               
                return view('Documents/share_login',
                           [
                               'form_hidden'=>[],
                               'url_forget'=>null,
                               '_loginPage'=>'Auth/login_index',
                               'currentView'=>$this->view,
                               'welcome_msg'=>lang('documents.doc_share_login_welcome'),
                               'file_error'=>$error
                           ]);
            }
            //artur@apdcw.co.uk
            
        }
        
        private function shareLinkWithEmails($fileId,array $emails,$post)
        {
            $settings=$this->model_Settings->get('documents.document_cfg*');
            $post['url']=url($this,'share',[$fileId],['key'=> base64url_encode($post['pass'])]);
            $post['url']= url_tag($post['url'], $post['url']);
            $post['username']=loged_user('name');
            $settings['document_cfg_shareemail_body']= Str::replaceWithArray($post, $settings['document_cfg_shareemail_body'],'{value}');
            $settings['document_cfg_shareemail_subject']= Str::replaceWithArray($post, $settings['document_cfg_shareemail_subject'],'{value}');
            $settings['document_cfg_shareemail_body']=$this->getParsedTemplate($settings['document_cfg_shareemail_body'], $post);
            return $this->sendEmail(null, null, [], $settings['document_cfg_shareemail_subject'], $settings['document_cfg_shareemail_body'],[],$emails);                   
        }
        
	function cats($record=null)
        {
            if ($record!=null)
            {
                return $this->cat($record);
            }
            return $this->setTableView()
                        ->setData('cats',null,TRUE,null,[])
                        ->setPageTitle('documents.cats_mainmenu')
                        ->addFilters('cats')
                        ->addFilterField('name %')
                        ->addColumn('documents.cats_name','name',TRUE)
                        ->addColumn('documents.cats_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
                        ->addColumn('documents.cats_enabled','enabled',FALSE,'yesno')
				   
                        ->addBreadcrumb('documents.cats_mainmenu','/')
				   
                        ->addEditButton('documents.cats_editbtn','cats',null,'btn-primary edtBtn','fa fa-edit',[])
			 	   
                        ->addEnableButton()
                        ->addDisableButton()
                        ->addDeleteButton()
                        ->addNewButton('cats/new') 
			->render();
        }
        
        function cat($record)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            $dir=$this->request->getGet('dir');
            if (is_numeric($record))
            {
                $record=$this->model_Cats->find($record);
            }else
            {
		$record=null;
            }
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->auth->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError();
                }
                $isnew=TRUE;
		$record=$this->model_Cats->getNewRecordData(TRUE);
                $record['createdby']= loged_user('username');
                $record['createdon']= formatDate();
                $record['modon']=$record['createdby'];
                if ($dir!=null)
                {
                    $dir=$this->model_Cats->find($dir);
                    if ($dir==null)
                    {
                       $dir='/';  
                    }else
                    {
                        $dir=$dir['category'].'/'.$dir['name'];
                        $dir= str_replace('//', '/', $dir);
                    }
                    $record['category']=$dir;
                }
            } else 
            {
                $record['modby']= loged_user('username'); 
            }
            
            $record['modon']= formatDate();
           
            //dump($folders);exit;
            $this->setFormView()
                ->setFormTitle('documents.cats_edit')
		->setPageTitle('documents.cats_edit')
		->setFormAction($this,'save',['cats'],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Cats->primaryKey=>$record[$this->model_Cats->primaryKey],
                            'old_cat'=>$record['category'].'/'.$record['name']
                        ])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)
					
			
		->setTab('general','system.general.tab_info')
		->addFieldsFromModel($this->model_Cats->getFieldsForForm($record),$record,'documents.cats_-key-',FALSE)
                //->addHiddenField('_check_array_fields','true')
               ;
            $this->getBreadcrumb($record,$isnew);
            return $this->view->render();
        }
        
        private function getBreadcrumb($record,$isnew,$iscat=TRUE)
        {
            $folders=$this->model_Cats->getFoldersNames('name',null);
            if (array_key_exists('category', $record))
            {
                $dir=Str::afterLast($record['category'], '/');
            }else
            {
                $dir='/';
            }
            if (Str::startsWith($dir, '/'))
            {
                $dir= substr($dir,1);
            }
            
             $this->view->addBreadcrumb(array_key_exists($dir,$folders) ? $folders[$dir]['text'] : 'documents.mainmenu',array_key_exists($dir,$folders) ? url($this,'files',[$folders[$dir]['dcid']]): url($this));
            
            
            if ($isnew)
            {
                $this->view->addBreadcrumb(lang('documents.'.($iscat ? 'bread_newdir':'bread_newfile')),current_url());
            } else 
            {
                $this->view->addBreadcrumb(lang('documents.cats_edit_bread',[$record[$iscat ? 'text':'docname']]),current_url());
            }
        }
        
        function donwloaddoc($record)
        {
            $record=$this->model_Files->find($record);
            
            if ($record==null)
            {
                error:
                return redirect()->to($refurl)->with('error',$this->createMessage('documents.error_invalid_id','danger')); 
            }
            
            if (!array_key_exists('path', $record))
            {
                goto error;
            }
            if (array_key_exists('type', $record) && $record['type']=='external')
            {
                return redirect()->to($record['path']);
            }
            $fileName=$record['docname'];
            $this->addMovementHistory('docs_download', null, null, $record['did'], null, 'download');
            if ($record['type']=='text')
            {
                $dompdf = new \Dompdf\Dompdf();
		$dompdf->loadHtml($record['path']);//
		$dompdf->setPaper('A4', 'portrait');
        	$dompdf->render();
                $file_content=$dompdf->output();
                $file=parsePath('@storage/temp/'.$record['docname'].'.pdf',TRUE);
                file_put_contents($file, $file_content);
                $file = new \CodeIgniter\Files\File($file);
                header('Content-Disposition: attachment; filename="' .$file->getFilename(). '"');
                $this->response->setHeader('Content-Length: '. $file->getSize(),'');
                $this->response->setHeader('Content-Type','application/octet-stream');
                ob_clean();
                flush();
                readfile($file->getPathname());exit;
            }
            $record=json_decode($record['path'],TRUE);
            if (!is_array($record))
            {
                 goto error;
            }
           
            $record=array_values($record);
            $record=$record[0]; 
           
            return $this->download($record,$fileName);
        }
        
        function download($record,$fileName,$deleteAfter=false)
        {
            $refurl=$this->getRefUrl();
            $config=$this->model_Settings->get('documents.*');
            $file=\EMPORIKO\Libraries\StorageEngine\StorageEngine::init()->setFile($record);
           
            if (config('Storage')->storageEngine!='local')
            {
                
                $record=$file->getFile();
                if ($record!=FALSE)
                {
                    return redirect()->to($record); 
                }
                
                
                return redirect()->to($refurl);
            }
            if ($file==FALSE)
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('documents.error_invalid_id','error')); 
            }

            $record = $file->getFile();
            
            $fileName=$fileName.'.'.$record->getExtension();
            header('Content-Disposition: attachment; filename="' .$fileName. '"');
            $this->response->setHeader('Content-Length: '. $record->getSize(),'');
            $this->response->setHeader('Content-Type','application/octet-stream');
            ob_clean();
            flush();
            readfile($record->getPathname());
            if ($deleteAfter)
            {
                unlink($record->getPathname());
            }
        }
        
        function settings($tab,$record)
        {
            $settings=$this->model_Settings->get('documents.cfg_*');
            $view=new Pages\FormView($this);
            if ($tab=='cfg')
            {
                $view->addDropDownField('documents.settings_onedrivefolderid', 'settings[document_cfg_onedrivefolderid]]', service('StorageEngine')->mapDir(),$settings['document_cfg_onedrivefolderid']);
                $view->addAcccessField('documents.settings_employeeaccess', $settings['document_cfg_employeeaccess'], 'settings[document_cfg_employeeaccess]');
                return view('System/form_fields',$view->getViewData());        
            }else
            if ($tab=='msgs')
            {
                $view->addInputField('documents.settings_shareemail_subject', 'settings[document_cfg_shareemail_subject]',$settings['document_cfg_shareemail_subject'])
                     ->addDropDownField('documents.settings_shareemail_body', 'settings[document_cfg_shareemail_body]', $this->model_Documents_Report->getTemplatesForForm(), $settings['document_cfg_shareemail_body']);
                return view('System/form_fields',$view->getViewData());
            }
            
            return null;
        }
        public function deletesingle($model,$value,$field=null)
        {
            if ($model=='cats')
            {
                $value_r=$this->model_Cats->find($value);
                if (is_array($value_r) && array_key_exists('name', $value_r))
                {
                    $this->model_Cats->deleteFolder(base64_encode($value_r['name']));
                }
                
            } else 
            if ($model=='files')
            {
               $this->model_Files->deleteDocument($value); 
            }
            $this->addMovementHistory('docs_delete', null, null, $value, null, 'docs');
            return parent::deletesingle($model, $value, $field);
        }
        
        public  function save($type, $post = null) 
        {
            $post=$this->request->getPost();
            
            if (strtolower($type)=='cats')
            {
                if (array_key_exists('name', $post) && array_key_exists('text', $post) && strlen($post['text']) > 0 && strlen($post['name']) < 1)
                {
                    $post['name']= strtolower(mb_url_title($post['text']));
                }
                
                if (Arr::KeysExists(['category','name','old_cat'], $post) && $post['old_cat']!=null && strlen($post['old_cat']) > 0 && ($post['old_cat']!='/' && $post['old_cat']!='//'))
                {
                    $this->model_Files->setDocumentCat($post['old_cat'],$post['category'].'/'.$post['name']);
                    $this->model_Cats->setDocumentCat($post['old_cat'],$post['category'].'/'.$post['name']);
                }
            }
            if (array_key_exists('did',$post) && array_key_exists('args',$post))
            {
                if (is_array($post['args']) && array_key_exists('notifylist', $post))
                {
                    $record=$this->model_Files->find($post['did']);
                    if (is_array($record))
                    {
                        $record['args']= json_decode($record['args'],TRUE);
                    }else
                    {
                       $record['args']=['emails'=>[],'pass'=>[]]; 
                    }
                    
                    if (is_array($record['args']) && array_key_exists('emails', $record['args']))
                    {
                        $post['notifylist']=array_diff($post['args'],$record['args']['emails']);
                        if (is_array($post['notifylist']) && count($post['notifylist'])>0)
                        {
                            $post['pass']=Str::createPasswordString(8);
                            $record['args']['pass']=$record['args']['pass']+(array_fill_keys($post['notifylist'], Str::hashPasswordString($post['pass'])));
                        }else
                        {
                            unset($record['notifylist']);
                        }
                        
                        $post['record'] = $record;   
                        $record['args']['emails']=$post['args'];
                        $post['args']= json_encode($record['args']);
                        
                    } else 
                    {
                        unset($post['args']);  
                    }
                }
            }
          
            if (array_key_exists('tags',$post) && is_array($post['tags']))
            {
                $post['tags']= implode(',',$post['tags']);
            }else
            {
                $post['tags']='';
            }
            
            $this->uploadFiles($post);
            
            return parent::save($type, $post);
        }
        
        protected function _after_save($type, $post, $refurl, $refurl_ok) 
        {
            if (strtolower($type)=='model_cats')
            {
               if (array_key_exists('name', $post)  && strlen($post['name']) > 0)
               {
                   $this->model_Cats->createFolder(base64_encode($post['name']));
               }                       
            }
            if (array_key_exists('notifylist', $post) && array_key_exists('pass', $post) && is_array($post['notifylist']))
            {
                $fileId= array_key_exists('did', $post) ? $post['did'] : $this->model_Files->getLastID();
                $this->shareLinkWithEmails($fileId,$post['notifylist'],$post);
            }
           
            if (array_key_exists('type', $post) && !array_key_exists('did', $post) && $post['type']=='images')
            {
                
                if (array_key_exists('path', $post) && Str::isJson($post['path']))
                {
                    $post['path']= json_decode($post['path'],TRUE);
                    $post['path']= is_array($post['path']) ? array_values($post['path']) : [];
                    
                    if (count($post['path']) > 0)
                    {
                        $this->model_Files->createThumbNail($post['path'][0]);
                    }
                }
            }
            return TRUE;
        }
        
        function uploadFiles1(&$post, $keyToExtract = null) 
        {
            $config=$this->model_Settings->get('documents.*');
            if (is_numeric($config['document_cfg_storage_type']) && $config['document_cfg_storage_type']==1)
            {
                $uploads = $this->request->getFiles();
                $uploads= array_key_exists($keyToExtract, $uploads) ? $uploads[$keyToExtract] : $uploads;
                $fieldName= array_keys($uploads);
                $uploads= array_values($uploads);
                if (count($uploads)>0 && $uploads[0]->isValid() && !$uploads[0]->hasMoved())
                {
                    $onedrive = new \OneDrive\Business($config['document_cfg_onedrivetoken']);
                    $file_path=$uploads[0]->store('.');
                    $file_path=WRITEPATH . 'uploads/' .$file_path;
                    $ofile_path=$onedrive->create_file($file_path, $config['document_cfg_onedrivefolderid'],$post['docname']);
                    if (is_array($ofile_path) && array_key_exists('id', $ofile_path))//@microsoft.graph.downloadUrl
                    {
                      $post[$fieldName[0]]= json_encode([$fieldName[0]=>$ofile_path['id']]);
                    }
                    unlink($file_path);
                }
            }else
            {
              parent::uploadFiles($post, $keyToExtract);  
            } 
        }
        

        function getMenuItemsData($value = null, $justItems = FALSE) 
        {
            $this->availablemenuitems=array_combine($this->availablemenuitems, lang('documents.menu_action_list'));
            return parent::getMenuItemsData($value, $justItems);
        }*/