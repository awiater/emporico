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

namespace EMPORIKO\Libraries\Scheduler;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class Scheduler
{
    public static function init()
    {
        return new Scheduler();
    }
    
    function isExecEnabled()
    {
        return FALSE;// function_exists('exec');
    }
    
    function newJob()
    {
        return new SchedulerJob($this);
    }
    
    function saveJob(SchedulerJob $job)
    {
        if ($this->isExecEnabled())
        {
            $cmd=$this->createCrontTabCMD($job);
        }
        $job->id=$job->id==null ? formatDate() : $job->id;
        $file=$this->getJobsDir().$this->createHash($job);
        return file_put_contents($file, $job->toJSON()) > 0;
    }
    
    function createHash($job)
    {
        $file=sprintf(
                '%sO%sO%sO%sO%s_%s',
                (isset($job->minutes) ? $job->minutes : 'A'),
                (isset($job->hours) ? $job->hours : 'A'),
                (isset($job->dayOfMonth) ? $job->dayOfMonth : 'A'),
                (isset($job->month) ? $job->month : 'A'),
                (isset($job->dayOfWeek) ? $job->dayOfWeek : 'A'),
                (isset($job->id) ? $job->id : formatDate())
            );
        return str_replace(['/','*'], ['B','A'], $file);
    }
    
    function jobExists($job)
    {
        if ($job instanceof SchedulerJob)
        {
            $job=$this->createHash($job);
        }
        if (!is_string($job))
        {
            return FALSE;
        }
        if (!$this->isExecEnabled())
        {
            return file_exists($this->getJobsDir().$job);
        }
    }
    
    function deleteJob($jobs)
    {
        if (is_string($jobs))
        {
            $jobs=[$jobs];
        }
        if (!is_array($jobs))
        {
            return FALSE;
        }
        $dir=$this->getJobsDir();
        foreach ($jobs as $job)
        {
            if (file_exists($dir.$job) && is_file($dir.$job))
            {
                unlink($dir.$job);
            }
        }
    }

    public function getMasterCronJob()
    {
       return parsePath('@cron.php',TRUE);//'* * * * * '.
    }
    
    private function getJobsDir()
    {
        $dir=parsePath('@storage/cron/',TRUE);
        if (!is_dir($dir)) 
        {
            mkdir($dir, 0777, TRUE);
        }
        return $dir;
    }
    
    private function createCrontTabCMD(SchedulerJob $job)
    {
        if (!isset($job->command) || empty($job->command)) 
        {
            error:
            throw new \InvalidArgumentException(
                'Job not contains any task'
            );
        }
        
        $command=$job->command;
        $command= is_string($command) && Str::isJson($command) ? json_decode($command,TRUE) : $command;
        if (!is_array($command))
        {
            goto error;
        }
        if (!Arr::KeysExists(['controller','action'], $command))
        {
             goto error;
        }
        if ($command['controller']=='system')
        {
            $command= parsePath($command['action']);
        } else 
        {
           $command= url_from_array($command); 
        }
        
        
        $time=sprintf(
                '%s %s %s %s %s',
                (isset($job->minutes) ? intval($job->minutes) : '*'),
                (isset($job->hours) ? intval($job->hours) : '*'),
                (isset($job->dayOfMonth) ? intval($job->dayOfMonth) : '*'),
                (isset($job->month) ? intval($job->month) : '*'),
                (isset($job->dayOfWeek) ? intval($job->dayOfWeek) : '*')
            );
        return sprintf(
            '%s%s %s%s',
            '',
            $time,
            $command,
            '');
    }
    
}