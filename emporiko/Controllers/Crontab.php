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

  
namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Crontab as Cron;
use EMPORIKO\Libraries\Scheduler\Scheduler as Scheduler;
use EMPORIKO\Libraries\Scheduler\Humanizer;

class Crontab extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
         * 
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'=>       AccessLevel::edit,
                'job'=>         AccessLevel::edit,
                'save'=>        AccessLevel::create,
	];
        
        /**
	 * Access module class name if different that current controller
         * 
	 * @var String
	 */
	//protected $access_controller='Settings';
        
        /**
         * Array with function names on which access levels are not checked
         * 
         * @var array
         */
        protected $no_access=['run'];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
            'jobs'=>'Crontab/Job'
	];
	
        /**
	 * Array with controller method remaps ($key is fake function name and $value is actual function name)
	 */
	public $remaps=[
            'jobs'=>'index',
        ];
        
        
        function index($record=null)
        {
            if ($record!=null)
            {
                return $this->job($record);
            }
            $log=$this->model_Jobs->isLogAll();
            if (is_array($log))
            {
                $log=lang('crontab.msg_notloging_all').'<br>'.implode(', ',$log); 
            }else
            {
                $log=FALSE;
            }
            return $this->setTableView('Crontab/index')
                        ->setData('jobs',null,TRUE,null,[])
                        ->setCustomViewEnable(FALSE)
                        ->setPageTitle('crontab.mainmenu')
                        ->addFilters('jobs')
                        ->addFilterField('name %')
                        ->addColumn('crontab.name','name',TRUE,[],null,'desc')
                        ->addColumn('crontab.picktime','picktime',TRUE,[],'Crontab::parseExpr')
                        ->addColumn('crontab.enabled','enabled',FALSE,'yesno')
                    
			->addBreadcrumbSubSettings()
                        ->addBreadcrumb('crontab.mainmenu',current_url())
				   
                        ->addEditButton('crontab.editbtn','jobs',null,'btn-primary edtBtn','fa fa-edit',[])
                        ->addEditButton('system.buttons.remove',url($this,'deletesingle',['jobs','-id-'],['refurl'=> current_url(FALSE,TRUE)]),null,'btn-danger','far fa-trash-alt',['data-delete'=>'true'])
                        ->addEditButton('crontab.runbtn','runcommand',null,"btn-success edtBtn",'fas fa-bolt',[],AccessLevel::create)
			
                        ->addNewButton('jobs/new')
                        ->addModuleSettingsButton()
                        ->addData('_msg',$log)
			->render();
        }
        
        function parseExpr($expr)
        {
            try
            {
                return (Humanizer::fromCronString($expr))->asNaturalLanguage();
            } catch (\Exception $e)
            {
                return $expr;
            }
        }
        
        function job($record,$mode='edit')
        {
            //Scheduler::init()->getMasterCronJob()
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            if (is_numeric($record))
            {
                $record=$this->model_Jobs->find($record);              
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
                $record=$this->model_Jobs->getNewRecordData(TRUE);
            }
            $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
            $record['command']=base64_encode($record['command']);
            $log=$this->model_Jobs->isLogAll();
            if (is_array($log))
            {
                $record['_msg']=lang('crontab.msg_notloging_all').'<br>'.implode(', ',$log); 
            }
            $this->setFormView('Crontab/edit')
                    ->setFormTitle('crontab.jobedit')
                    ->setPageTitle('crontab.jobedit')
                    ->setFormAction($this,'save',['jobs'],['refurl'=>base64url_encode($refurl)])
                    ->parseArrayFields()
                    ->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->model_Jobs->primaryKey=>$record[$this->model_Jobs->primaryKey],
                            'hash'=>$record['hash']
                        ]
                    ,['class'=>'col-12'])
                    ->setCustomViewEnable(FALSE)
                    ->setFormCancelUrl($refurl)
                    
                    ->addBreadcrumbSubSettings()
                    ->addBreadcrumb('crontab.mainmenu',url($this))
                    ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['name'],'/')
			
                    ->addData('record',$record)         
                    ->setTab('general','system.general.tab_info')
                    //->setTab('time','crontab.tab_time')
                    ->setTab('movements','crontab.tab_movements')
                    ->addFieldsFromModel('jobs',$record,'crontab.-key-')
                    ->addData('url_delete',url($this,'deletesingle',['jobs',$record['cjid']],['refurl'=> base64url_encode($this->getRefUrl())]));
            return $this->view->render();
        }
        
        function run($id)
        {
            $id=$this->model_Jobs->find($id);
            if (!is_array($id))
            {
                return null;
            }
            if (!array_key_exists('command', $id))
            {
                return null;
            }
            $id=$id['command'];
            if (Str::startsWith($id, '/'))
            {
                $id= substr($id, 1);
            }
            $id=explode('/',$id);
            loadModule($id[0], count($id) > 1 ? $id[1] : null, count($id) > 2 ? array_slice($id, 2) : []);
        }
        
        function runcommand($id)
        {
            $refurl=$this->getRefUrl();
            if (is_numeric($id))
            {
                $id=$this->model_Jobs->find($id);
                if (!is_array($id))
                {
                    return redirect()->to($refurl)->with('error',$this->createMessage('crontab.msg_invalidjobid','danger'));
                }
                
                if (array_key_exists('command', $id) && Str::contains($id['command'],'::'))
                {
                    try
                    {
                        $job=$this->model_Jobs->createJob($id);
                        $job->execute();
                        $this->addMovementHistory(10, null, null, $job->id, '', 'cron', 'auto');
                        return redirect()->to($refurl)->with('error',$this->createMessage('crontab.msg_jobrunok','success'));
                    }catch(\Exception $e)
                    {
                        $this->addMovementHistory(9, null, null, $job->id, $e->getMessage(), 'cron', 'auto');
                        return redirect()->to($refurl)->with('error',$this->createMessage('crontab.msg_jobrunfailed','danger'));
                    }
                }
                
                return redirect()->to($refurl)->with('error',$this->createMessage('crontab.msg_jobrunfailed_2','danger'));
            }
        }
        
        function savefile()
        {
           $path= parsePath('@storage/files/test.txt',TRUE);
           file_put_contents($path, formatDate());
        }
        
        function enablesingle($model, $id, $value, $field = null) 
        {
            if ($model=='jobs')
            {
                if ($value)
                {
                    $id=$this->model_Jobs->find($id);
                    if (is_array($id))
                    {
                        $this->model_Jobs->createJob($id)->write();
                    }else
                    {
                        $this->model_Jobs->createJob($id)->delete();
                    }
                }
            }
            return parent::enablesingle($model, $id, $value, $field);
        }
        
        
        function save($type, $post = null) 
        {
            $post=$post==null ? $this->request->getPost(): $post;
            if ($type=='jobs')
            {
                if (array_key_exists('hash', $post))
                {
                    $scheduler=\EMPORIKO\Libraries\Scheduler\Scheduler::init();
                    $scheduler->deleteJob($post['hash']);
                }
                if (Arr::KeysExists(['hour','minute'], $post))
                {
                    $post['hours']=$post['hour'];
                    $post['minutes']=$post['minute'];
                }
                if (Arr::KeysExists(['step_minute','step_hour'], $post) && strlen($post['step_hour']) > 0)
                {
                    $post['hours']=intval($post['step_hour'])==1 ? '*' : '*/'.intval($post['step_hour']);
                    $post['minutes']=isset($post['step_minute']) ? intval($post['step_minute']) : 0;
                }else
                if (array_key_exists('step_minute', $post) && strlen($post['step_minute']) > 0)
                {
                    $post['minutes']='*/'.intval($post['step_minute']);
                }
                if (array_key_exists('weekdays', $post) && is_array($post['weekdays']))
                {
                    $post['weekdays']=implode(',',$post['weekdays']);
                }
                if (array_key_exists('days', $post) && is_array($post['days']))
                {
                    $post['days']=implode(',',$post['days']);
                }
                if (array_key_exists('command', $post) && strlen($post['command']) > 0)
                {
                    $post['command']= base64_decode($post['command']);
                }
                
            }
            return parent::save($type, $post);
        }
        
        function _after_save($type, $post, $refurl, $refurl_ok) 
        {
            if ($type=='model_jobs')
            {
                if(array_key_exists('cjid', $post))
                {
                    $this->addMovementHistory('job_edit', null, null, $post['cjid'], null, 'cron');
                }else
                {
                    $this->addMovementHistory('job_create', null, null, $this->model_Jobs->getLastID(), null, 'cron');
                }
            }
            return TRUE;
        }
        
        function enable($post = null, $msgYes = null, $msgNo = null) 
        {
            $post= is_array($post) ? $post : $this->request->getPost();
            $enable=$this->request->getGet('enable');
            if (array_key_exists('model', $post) && $post['model']=='jobs' && $enable!=null)
            {
                $post['fields']=$this->model_Jobs->filtered(['In'=>$post['cjid']])->find();
                foreach($post['fields'] as $value)
                {
                    if ($enable==0 || $enable=='0')
                    {
                        Scheduler::init()->deleteJob($value['hash']);
                    } else 
                    {
                        $this->model_Jobs->createJob($value)->write();
                    }
                    
                }
            }
            return parent::enable($post);
        }
        
        function delete(array $post = []) 
        {
            $post= count($post) >0 ? $post : $this->request->getPost();
            if (array_key_exists('model', $post) && $post['model']=='jobs' 
                    && array_key_exists('cjid', $post) && is_array($post['cjid']) && count($post['cjid']) > 0)
            {
               $cjid=$this->model_Jobs->filtered(['cjid In'=>$post['cjid']])->find();
               foreach(is_array($cjid) ? $cjid : []  as $job)
               {
                   Scheduler::init()->deleteJob($job['hash']);
               }
            }
            return parent::delete($post);
        }
        
        function deletesingle($model, $value, $field = null) 
        {
            $refurl=$this->getRefUrl();
            if ($model=='jobs')
            {
                $value=$this->model_Jobs->find($value);
                if (is_array($value))
                {
                    if ($this->model_Jobs->where('cjid', $value['cjid'])->delete())
                    {
                        Scheduler::init()->deleteJob($value['hash']);
                        return redirect()->to($refurl)->with('error',$this->createMessage('crontab.msg_delete_ok','success'));
                    }
                    
                }
                return redirect()->to($refurl)->with('error',$this->createMessage('crontab.error_msg_delete','danger'));
            }
            
            return parent::deletesingle($model, $value, $field);
        }
        
        function settings($tab,$record)
        {
            $settings=$this->model_Settings->get('crontab.croncfg_*',FALSE,'*');
            $view=new Pages\FormView($this);
            if ($tab=='cfg')
            {
                $view->addYesNoField('crontab.settings_logstart', $settings['croncfg_logstart']['value'],'settings[croncfg_logstart]');
                $view->addYesNoField('crontab.settings_logsuccess', $settings['croncfg_logsuccess']['value'],'settings[croncfg_logsuccess]');
                $view->addYesNoField('crontab.settings_logfailed', $settings['croncfg_logfailed']['value'],'settings[croncfg_logfailed]');
            }
            return view('System/form_fields',$view->getViewData());
        }
        
}