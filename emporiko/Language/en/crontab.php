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

return [
    'mainmenu'=>'Scheduled Jobs',
    'jobedit'=>'Edit Job Details',
    'name'=>'Name',
    'desc'=>'Description',
    'enabled'=>'Is Active',
    
    'tab_time'=>'Time',
    'picktime_spec'=>'Specific time in the day',
    'picktime_hours'=>'Every hours at minute',
    'picktime_minutes'=>'Every minute',
    'patern_list'=>['Daily','Weekly','Monthly','Yearly'],
    'repeat'=>'Repeat',
    'repeat_tooltip'=>'Determines how job will be repeated',
    'repeat_monthly'=>'Pick days',
    'repeat_weekly'=>'Pick day of week',
    'repeat_weekly_list'=>['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
    'repeat_yearly'=>'Pick date',
    
    'picktime'=>'Scheduling',
    'picktime_tooltip'=>'Crontab notation. Defines frequency of job runs.<br>https://en.wikipedia.org/wiki/Cron',
    
    'hours'=>'Hour',
    'picktime_day_min'=>'Minute(s)',
    'picktime_hour_hour'=>'Every Hours',
    'picktime_hour_min'=>'Minute',
    'command'=>'Command To Run',
    'command_tooltip'=>'Determines what command will be run',
    'raw_btn_tooltip'=>'Show Raw Cron Jobs',
    'msg_delete_ok'=>'Job declaration deleted and Crontab job disbled',
    'rawjobsmodal'=>'Cron Jobs',
    'msg_invalidjobid'=>'Invalid job id',
    'editbtn'=>'Edit Job Details',
    'runbtn'=>'Run Job Now',
    'msg_jobrunfailed'=>'Cron Job execution failed',
    'msg_jobrunok'=>'Cron Job execution successful',
    'msg_jobrunfailed_2'=>'Cannot execute selected job manually',
    'msg_notloging_all'=>'System not loging all task activity, only below actions are logged:',
    
    'index_note'=>'Note: Add below line to the crontab file to run Scheduled Jobs:<br>{0}',
    
    'mov_job_call_failed'=>'Execution Failed',
    'mov_job_call_failed_view'=>'Execution Failed',
    'mov_job_call'=>'Execute',
    'mov_job_call_view'=>'Execute',
    'mov_job_call_ok'=>'Execution Success',
    'mov_job_call_ok_view'=>'Execution Success',
    'mov_job_create'=>'Job Created',
    'mov_job_edit'=>'Job Details Edited',
    'mov_job_edit_view'=>'Job Details Edited',
    
    'tab_movements'=>'Logs',
    
    'settings_cfgtab'=>'Settings',
    'settings_logstart'=>'Log Task Start',
    'settings_logstart_tooltip'=>'Determines if start of task is loged',
    'settings_logstart_info'=>'Log When Task Start',
    'settings_logsuccess'=>'Log If Task Successful',
    'settings_logsuccess_tooltip'=>'Determines if successful execution of task is logged',
    'settings_logsuccess_info'=>'Log When Task Successful',
    'settings_logfailed'=>'Log If Task Failed',
    'settings_logfailed_tooltip'=>'Determines if failure to execution of task is logged',
    'settings_logfailed_info'=>'Log When Task Failed',
    'error_msg_delete'=>'Removing job failed. Please contact support',
];