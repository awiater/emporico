<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Models\Documents;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class DocumentModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='documents';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'did';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['doc_name','doc_desc','doc_path','doc_open','doc_folder','doc_tags'
                                  ,'doc_createdby','doc_createdon','doc_type','enabled','access'];
	
        
        protected $validationRules =
	[
		'doc_name'=>'required|is_unique[documents.doc_name,did,{did}]',
                'doc_folder'=>'required'
	];
	
	protected $validationMessages = 
        [
            'doc_name'=>
            [
                'required'=>'documents.error_required_doc_name',
                'is_unique'=>'documents.error_unique_doc_name'
            ],
            'doc_folder'=>['required'=>'documents.error_required_doc_folder']
        ];
        
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'did'=>			['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
                'doc_name'=>		['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'doc_desc'=>		['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'doc_path'=>		['type'=>'LONGTEXT','null'=>FALSE],
                'doc_open'=>		['type'=>'LONGTEXT','null'=>FALSE],
		'doc_folder'=>		['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'doc_tags'=>		['type'=>'LONGTEXT','null'=>TRUE],
                'doc_createdby'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'doc_createdon'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'doc_type'=>            ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
                'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
        
        /**
         * Get all available folder names
         * 
         * @param bool   $onlyEnabled
         * @param string $access
         * 
         * @return array
         */
        function getFolders(bool $onlyEnabled=TRUE,$access=null)
        {
            $arr=[];
            $filters=['access'=>$access==null ? '@loged_user' : $access];
            if ($onlyEnabled)
            {
                $filters['enabled']=1;
            }
            
            foreach($this->filtered($filters)->groupBy('doc_folder')->find() as $value)
            {
                $arr[]=$value['doc_folder'];
            }
            return $arr;
        }
        
        /**
         * Delete file from database and storage
         * 
         * @param type $record
         * 
         * @return boolean
         */
        function deleteFile($record)
        {
            $record=$this->filtered(['did'=>$record,'|| doc_name'=>$record])->first();
            if (!is_array($record))
            {
                return FALSE;
            }
            if ($this->where('did',$record['did'])->delete())
            {
                $record['doc_path']= json_decode($record['doc_path'],TRUE);
                if (is_array($record['doc_path']) && count($record['doc_path']) > 0)
                {
                    $record['doc_path']=$record['doc_path'][$record['doc_path']['name']];
                    if (storage($this->getModel('Settings')->get('documents.documents_storagetype'))->delete($record['doc_path']))
                    {
                        
                    }
                    $record['user']=loged_user('name');
                    $this->getModel('System/Movements')->addItem(2,$record['user'],null,null,$record['doc_folder'],lang('documents.mov_delete_view',$record),null,'documents');
                    return TRUE;
                }
            }
            return FALSE;
        }
        
        /**
         * Returns icon class (font awesome) for given extension
         * 
         * @param string $type
         * @param bool   $justIcon
         * 
         * @return string
         */
        function getIconForFileType(string $type,bool $justIcon=TRUE)
        {
            if ($type=='wav')
            {
                $type='fas fa-file-audio';
            } else
            if ($type=='doc' || $type=='docx')
            {
                $type='fas fa-file-word';
            } else 
             if ($type=='xls' || $type=='xlsx' || $type=='xlsm')
            {
                $type='fas fa-file-excel';
            } else
            if ($type=='pdf')
            {
                $type='fas fa-file-pdf';
            } else
             if ($type=='zip')
            {
                $type='fas fa-file-archive';
            } else
            {
                $type='fas fa-file';
            }
            return $justIcon ? $type : html_fontawesome($type);
        }
        
        /**
         * Returns array with files data for form
         * 
         * @param string|null $tag
         * @param string|null $folder
         * 
         * @return array
         */
        function getFilesForForm($tag,$folder=null)
        {
            $arr=[];
            $filters=['access'=>'@loged_user','enabled'=>1];
            if ($tag!=null && is_string($tag))
            {
                $filters['doc_tags %']=$tag;
            }
            
            if ($folder!=null && is_string($folder))
            {
                $filters['doc_folder']=$folder;
            }
            
            foreach($this->filtered($filters)->find() as $value)
            {
                $value['download_link']= str_replace(['-id-','-refurl-'],[$value['did'],current_url(FALSE,TRUE)], parsePath($value['doc_open']));
                $arr[]=$value;
            }
            return $arr;
        }
        
        function getDocsForForm(array $filters=[])
        {
            $arr=[];
            $filters=['access'=>'@loged_user','enabled'=>1];
            foreach($this->filtered($filters)->find() as $value)
            {
                $arr[$value['doc_folder']][$value['did']]=$value['doc_name'].'=>'.$value['doc_desc'];
            }
            return $arr;
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            $isnew=!is_numeric($record['did']);
            $arr['doc_name']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('doc_name')
                    ->setID('doc_name')
                    ->setText('doc_name')
                    ->setMaxLength(80)
                    ->setAsRequired()
                    ->setTab('general');
            
            $arr['doc_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                    ->setName('doc_desc')
                    ->setID('doc_desc')
                    ->setText('doc_desc')
                    ->setMaxLength(250)
                    ->setTab('general');
           
            if ($record['cfg_acc'] || strlen($record['doc_folder']) < 1)//
            {
                $arr['doc_folder']= \EMPORIKO\Controllers\Pages\HtmlItems\InputListField::create()
                        ->setName('doc_folder')
                        ->setID('doc_folder')
                        ->setText('doc_folder')
                        ->setMaxLength(80)
                        ->setValidation(FALSE)
                        ->setOptions($this->getFolders())
                        ->setTab($isnew ? 'general' : 'tab_file');
            }else
            {
                $arr['doc_folder']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                        ->setName('doc_folder')
                        ->setID('doc_folder')
                        ->setTab('general');
            }
            
            $arr['doc_path']= \EMPORIKO\Controllers\Pages\HtmlItems\UploadField::create()
                    ->setName('doc_path')
                    ->setID('doc_path')
                    ->setText('doc_path')
                    ->setTab($isnew ? 'general' : 'tab_file');
            
             $arr['access']= \EMPORIKO\Controllers\Pages\HtmlItems\AcccessField::create()
                    ->setName('access')
                    ->setID('access')
                    ->setText('access')
                    ->setTab('general');
             
             $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                    ->setName('enabled')
                    ->setID('enabled')
                    ->setText('enabled')
                    ->setTab('general');
            
            
            
            if ($isnew && strlen($record['doc_folder']) > 0)
            {
                $arr['doc_folder']->setReadOnly();
            }
            
            
            
            $arr['doc_tags']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomElementsListField::create()
                    ->setName('doc_tags')
                    ->setID('doc_tags')
                    ->setText('doc_tags')
                    ->setTab('tab_file');
            
            if (!$isnew)
            {
                $arr['doc_name']->setReadOnly();
                if (array_key_exists('doc_path', $record) && Str::isJson($record['doc_path']))
                {
                    $record['doc_path']= json_decode($record['doc_path'],TRUE);
                    if (is_array($record['doc_path']) && Arr::KeysExists(['ext','name'], $record['doc_path']))
                    {
                        $arr['doc_path']= \EMPORIKO\Controllers\Pages\HtmlItems\InputButtonField::createField($arr['doc_path'])
                                ->setText('doc_path_read')
                                ->setButtonIcon('fas fa-cloud-download-alt')
                                ->setInputField('<div class="form-control"><i class="'.$this->getIconForFileType($record['doc_path']['ext']).' mr-2"></i>'.$record['doc_path']['name'].'</div>')
                                ->setButtonClass('btn btn-info')
                                ->setButtonArgs(['data-noloader'=>TRUE,'data-url'=>url('Documents','download',[$record['did']],['refurl'=>current_url(FALSE,TRUE)])]);
                    }else
                    {
                        unset($arr['doc_path']);   
                    }
                }else
                {
                    unset($arr['doc_path']);
                }
            }
            
            return $arr;
        }
        
        function changeFolder(string $name,string $folder)
        {
            return $this->filtered(['did'=>$name,'|| doc_name'=>$name])->builder()->set('',$folder);
        }
        
        function storeDocument(string $file,string $folder,$name=null,$creator=null,array $storageCfg=[])
        {
            $file=parsePath($file,TRUE);
            if (!file_exists($file))
            {
                return FALSE;
            }
            $settings=$this->getModel('Settings')->get('documents.*');
            if (!Arr::KeysExists(['documents_storagetype','documents_storagefolder'], $settings))
            {
                return FALSE;
            }
            if (!array_key_exists('type', $storageCfg))
            {
                $storageCfg['type']=$settings['documents_storagetype'];
            }
            
            if (!array_key_exists('folder', $storageCfg))
            {
                $storageCfg['folder']=$settings['documents_storagefolder'];
            }
            
            $storage=storage($storageCfg['type'])->saveFileToStorage($file,$storageCfg['folder'],$name);
            $loged_user= loged_user();
            if (is_array($storage) && Arr::KeysExists(['id','name'], $storage))
            {
                return $this->save(
                        [
                            'doc_name'=>Str::before($storage['name'], '.'),
                            //'doc_desc',
                            'doc_path'=>json_encode([$storage['name']=>$storage['id'],'ext'=>Str::afterLast($file, '.'),'name'=>$storage['name']]),
                            'doc_open'=>'@/documents/download/-id-?refurl=-refurl-',
                            'doc_folder'=>$folder,
                            //'doc_tags',
                            'doc_createdby'=>$creator==null ? $loged_user['username'] : $creator,
                            'doc_createdon'=> formatDate(),
                            'doc_type'=>$this->getIconForFileType(strtolower(Str::afterLast($file, '.'))),
                            'enabled'=>1,
                            'access'=>'view',
                        ]);
            }
            return FALSE;
        }
        
}
?>