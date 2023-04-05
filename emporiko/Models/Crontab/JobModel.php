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
 
namespace EMPORIKO\Models\Crontab;

use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Crontab;

class JobModel extends \EMPORIKO\Models\BaseModel
{    
    /**
     *Users table name
     *  
     * @var string
     */
    protected $table='cronjobs';
    
    /**
     * Table primary key
     * 
     * @var string
     */
    protected $primaryKey = 'cjid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['minutes','hours','days','months','weekdays','command',
                              'hash','patern','picktime','name','desc','enabled'];
	
    /**
     * Fields(columns) validation rules
     * 
     * @var array
     */
    protected $validationRules =[];
	
    /**
     * Fields(columns) validation rules messages used when error occurred
     * 
     * @var array
     */
    protected $validationMessages = [];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'cjid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
        'name'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'unique'=>TRUE],
	'minutes'=>		['type'=>'VARCHAR','constraint'=>'5','null'=>FALSE,'default'=>'*'],
	'hours'=>               ['type'=>'VARCHAR','constraint'=>'5','null'=>FALSE,'default'=>'*'],
	'days'=>		['type'=>'VARCHAR','constraint'=>'5','null'=>FALSE,'default'=>'*'],
	'months'=>		['type'=>'VARCHAR','constraint'=>'5','null'=>FALSE,'default'=>'*'],
	'weekdays'=>		['type'=>'VARCHAR','constraint'=>'5','null'=>FALSE,'default'=>'*'],
	'command'=>		['type'=>'TEXT','null'=>FALSE],
        'patern'=>              ['type'=>'TEXT','null'=>FALSE],
        'desc'=>		['type'=>'TEXT','null'=>TRUE],
        'hash'=>		['type'=>'TEXT','null'=>FALSE],
        'picktime'=>            ['type'=>'VARCHAR','constraint'=>'15','null'=>TRUE],
        'enabled'=>		['type'=>'VARCHAR','constraint'=>'11','null'=>FALSE,'default'=>'1'],
	];	
    
    
    function getFieldsForForm(array $record) 
    {
        $arr=[];
        $comms=$this->getCommands();
       
        if (is_numeric($record['cjid']))
        {
            $arr['command_view']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                            ->setName('command_view')
                            ->setID('command_view')
                            ->setTab('general')
                            ->setText('command')
                            ->setReadOnly();
            $arr['command']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                            ->setName('command')
                            ->setTab('general')
                            ->setReadOnly();
            if (array_key_exists($record['command'], $comms))
            {
                $arr['command_view']->setValue($comms[$record['command']]);
            }
        }else
        {
            $arr['command']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('command')
                ->setID('command')
                ->setOptions($comms)
                ->setAsAdvanced()
                ->setAsRequired()
                ->setTab('general')
                ->setText('command');
        }
        
        
        $arr['name']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('name')
                ->setID('name')
                ->setMaxLength(50)
                ->setAsRequired()
                ->setTab('general')
                ->addClass('w-75')
                ->setText('name');
        
        $arr['desc']=\EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                ->setName('desc')
                ->setID('desc')
                ->setTab('general')
                ->setText('desc');
       
        
        $arr['enabled']=\EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                ->setName('enabled')
                ->setID('enabled')
                ->setTab('general')
                ->setAsRequired()
                ->setValue(1)
                ->setText('enabled');
        
        $arr['picktime']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('picktime')
                ->setID('picktime')
                ->setAsRequired()
                ->setTab('general')
                ->setText('picktime');

        $arr['movements']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                ->setName('movements')
                ->setID('movements')
                ->setAsRequired()
                ->setTab('movements')
                ->setText('')
                ->setValue(loadModule('Home','movementsTable',[$record['cjid'],['mhtype','mhdate','mhuser','mhinfo']]));
       
        return $arr;
    }
    
    /**
     * Determines tasks logging level
     * 
     * @return bool|array
     */
    function isLogAll()
    {
        $settings=$this->getModel('Settings')->get('crontab.croncfg_*');
        $enabled=[];
        if (intval($settings['croncfg_logfailed'])==1)
        {
            $enabled[]=lang('crontab.settings_logfailed_info');
        }
        
        if (intval($settings['croncfg_logsuccess'])==1)
        {
            $enabled[]=lang('crontab.settings_logsuccess_info');
        }
        
        if (intval($settings['croncfg_logstart'])==1)
        {
            $enabled[]=lang('crontab.settings_logstart_info');
        }
        return count($enabled)==3 ? TRUE : $enabled; 
    }
    
    /**
     * Returns array with available commands
     * 
     * @return array
     */
    function getCommands()
    {
        $arr=[];
        foreach($this->getModel('Settings')->get('crontab.croncmd_*',FALSE,'*',FALSE) as $cmd)
        {
            $arr[base64_encode($cmd['value'])]=lang($cmd['tooltip']);
        }
        return $arr;
    }
    
    /**
     * Returns array with current cron jobs
     * 
     * @param type $parsed
     * 
     * @return array
     */
    function getCronJobs($parsed=TRUE)
    {
        if (!$parsed)
        {
            return Crontab::getJobs();
        }
        $arr=[];
        foreach(Crontab::getJobs() as $job)
        {
            $arr[]=$this->parseCronJobCommand($job);
        }
        
        return $arr;
    }
    
    /**
     * Remove given job (id or command) from crontab
     * 
     * @param Int/String $id
     * 
     * @return bool
     */
    function removeCronJob($id)
    {
        if (is_numeric($id))
        {
            $jobs=Crontab::getJobs();
            if (array_key_exists($id, $jobs))
            {
                $id=$jobs[$id];
            }
        }
        return Crontab::removeJob($id);
    }
    
    /**
     * Clear all cron jobs
     * 
     * @return bool
     */
    function clearJobs()
    {
        return shell_exec('crontab -r');
    }
    
    /**
     * Create job object
     * 
     * @param  array $jobData
     * @return type
     */
    function createJob(array $jobData,\EMPORIKO\Libraries\Scheduler\Scheduler $scheduler=null)
    {
        if (is_array($jobData))
        {
            foreach(['minutes','hours','days','months','weekdays'] as $key)
            {
                if (!array_key_exists($key, $jobData))
                {
                    $jobData[$key]='';
                }
            }
            
            $scheduler=$scheduler==null ? \EMPORIKO\Libraries\Scheduler\Scheduler::init() : $scheduler;
            $scheduler= $scheduler->newJob()
                    ->setBasicCommand($jobData['command'])
                    ->setJobID(!array_key_exists($this->primaryKey, $jobData) ? $this->getNextID() : $jobData[$this->primaryKey]);
           if(strlen($jobData['picktime']) > 0)
           {
            $scheduler->setCustomTime($jobData['picktime']);
           }else
           {
            $scheduler->setMinutes($jobData['minutes'])
                ->setHours($jobData['hours'])
                ->setDayOfMonth($jobData['days'])
                ->setMonths($jobData['months'])
                ->setDayOfWeek($jobData['weekdays']);
           }
           return $scheduler;
        }
    }
    
    public function save($data): bool {
        $scheduler=\EMPORIKO\Libraries\Scheduler\Scheduler::init();
        $job=$this->createJob($data,$scheduler);
        $data['hash']=$job->getHash();
        if (parent::save($data))
        {
            if (array_key_exists('enabled', $data))
            {
                if ($data['enabled'])
                {
                    $job->write();
                }else
                {
                    $job->delete();
                }
            }
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Parse given job (array or job id) to crontab command
     * 
     * @param mixed $post
     * 
     * @return string
     */
    function parseDBJobCommand($post)
    {
        if (is_numeric($post))
        {
            $post=$this->find($post);
            if (!is_array($post))
            {
                return null;
            }
        }
            
            $paterns=$this->getTimingPaterns();
            $command=$paterns[$post['patern']];
             if ($post['picktime']!=0 && $post['picktime']!='0')
             {
                 $command= str_replace(['i','h'],[($post['picktime']=2 && $post['picktime']='2' ? '/':'').$post['minutes'],'/'.$post['hours']], $command); 
             }else
             {
                 $command= str_replace(['i','h'],[$post['minutes'],$post['hours']], $command);
             }
             if (array_key_exists('weekdays', $post))
             {
                $command= str_replace('w', implode(',', is_array($post['weekdays']) ? $post['weekdays'] : [$post['weekdays']]), $command); 
             }
             if (array_key_exists('days', $post))
             {
                $command= str_replace('m', implode(',',is_array($post['days']) ? $post['days'] : [$post['days']]), $command); 
             }
               
             if (array_key_exists('pickdate', $post))
             {
                $post['pickdate']= substr($post['pickdate'], 4,4);
                $command= str_replace(['m','d'],[intval(substr($post['pickdate'], 2)),intval(substr($post['pickdate'],0, 2))], $command); 
             }
               
             $command.=' curl '.url('Crontab','run',[array_key_exists($this->primaryKey, $post) ? $post[$this->primaryKey] : $this->getNextID()]);
             return $command;
    }
    
    /**
     * Parse to/from cron job
     * 
     * @param mixed $comm
     * 
     * @return type
     */
    private function parseCronJobCommand($comm)
    {   
        $arr=[];
        if (is_string($comm))
        {         
            $comm=explode(' ',$comm);
            $arr['minutes']=$comm[0];
            $arr['hours']=$comm[1];
            $arr['days']=$comm[2];
            $arr['months']=$comm[3];
            $arr['weekdays']=$comm[4];
            $arr['command']= implode(' ',array_slice($comm, 6,count($comm)));
            $arr['patern']=['i','h'];
            $arr['patern'][]=is_numeric($arr['days'])?'d ':'*';
            $arr['patern'][]= is_numeric($arr['months'])?'m':'*';
            $arr['patern'][]= is_numeric($arr['weekdays'])?'w':'*';
            $arr['patern']=implode(' ',$arr['patern']);
            $arr['type']=$this->getTimingPaterns(TRUE,$arr['patern']);
            $arr['timing']= implode(' ',array_slice($comm, 0,5));
        }else
        if (is_array($comm))
        {
            $arr=implode(' ',array_slice($comm,0,6));
        }else 
        {
            $arr=null;
        }
        return $arr;
    }
   
    /**
     * Returns array with cron job timings
     * 
     * @param type $inverted
     * @param type $patern
     * 
     * @return array
     */
   function getTimingPaterns($inverted=FALSE,$patern=null)
   {
       $arr= 
       [
           'daily'=>'i h * * *',
           'weekly'=>'i h * * w',
           'monthly'=>' i h m * *',
           'yearly'=>'i h d m *',
          
       ];
       $arr=$inverted ? array_flip($arr) : $arr;
       return $patern!=null && array_key_exists($patern, $arr) ? $arr[$patern] : $arr;
   }
}