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

class Listener
{
    public static function init()
    {
        return new Listener();
    }
    
    public function listen()
    {
        $dir=parsePath('@storage/cron/',TRUE);
        $logger=model('System/MovementsModel');
        $now=sscanf(convertDate(formatDate(),null,'i G j n w'), '%d %d %d %d %d');
        $settings=model('Settings/SettingsModel')->get('crontab.croncfg_*');
        //$logger->addItem(13,'auto',null,null,null,'Listener Start',null,'cron');
        
        foreach(directory_map($dir) as $file)
        {
            $exp= str_replace(['O','A','B'], [' ','*','/'], $file);
            $exp=new Expression($exp);
            if ($exp->match($now))
            {
                try
                {
                    $job=SchedulerJob::init(Scheduler::init())->readFromExternal($dir.$file);
                    echo formatDate().'&nbsp;Start:'.$job->id.'<br>';
                    if (intval($settings['croncfg_logstart'])==1)
                    {
                         $logger->addItem(8,'auto',null,null,$job->id,null,null,'cron');
                    }
                   
                    $job->execute();
                    if (intval($settings['croncfg_logsuccess'])==1) 
                    {
                        $logger->addItem(10,'auto',null,null,$job->id,null,null,'cron');
                    }
                    echo formatDate().'&nbsp;Success:'.$job->id.'<br>';
                }catch(\Exception $e)
                {
                    if (intval($settings['croncfg_logfailed'])==1)
                    {
                        $logger->addItem(9,'auto',null,null,$job->id,null,null,'cron');
                    }
                    echo formatDate().'&nbsp;Failed:'.$job->id.'<br>';
                }
            }
        }
    }
}