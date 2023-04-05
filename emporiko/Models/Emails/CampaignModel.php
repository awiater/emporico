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
namespace EMPORIKO\Models\Emails;

use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Arrays as Arr;

class CampaignModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='emails_campaigns';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'ecid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['ec_name','ec_status','ec_budget','ec_starton','ec_endon'
                              ,'ec_addedon','ec_addedby','ec_list','ec_desc','ec_type'
                              ,'ec_tpl','ec_notify','ec_links','enabled'];
        
    protected $validationRules =
    [
        'ec_name' => 'required|is_unique[emails_campaigns.ec_name,ecid,{ecid}]',
    ];
    
    protected $validationMessages = [
        'ec_name'=>[
            'is_unique' => 'connections.error_unique_ec_name'
        ]
    ];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'ecid'=>            ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
        'ec_name'=>         ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
        'ec_status'=>       ['type'=>'VARCHAR','constraint'=>'15','null'=>FALSE],
        'ec_budget'=>       ['type'=>'DOUBLE','null'=>FALSE],
        'ec_starton'=>      ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
        'ec_endon'=>        ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
        'ec_addedon'=>      ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
        'ec_addedby'=>      ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
        'ec_list'=>         ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE,'foreignkey'=>['emails_campaignstargets','ect_code','CASCADE','RESTRICT']],
        'ec_desc'=>         ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'ec_type'=>         ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
        'ec_tpl'=>          ['type'=>'LONGTEXT','null'=>TRUE],
        'ec_notify'=>       ['type'=>'LONGTEXT','null'=>TRUE],
        'ec_links'=>        ['type'=>'LONGTEXT','null'=>TRUE],
        'enabled'=>         ['type'=>'INT','constraint'=>'11','default'=>'1','null'=>FALSE],
        
    ];
    
    /**
     * Returns array with campaign statuses which could be used by drop down field
     * 
     * @return array
     */
    function getStatusListForDropDown()
    {
        return array_combine(['plan','live','complete'],lang('connections.ec_status_list'));
    }
    
    /**
     * Returns array with campaign types which could be used by drop down field
     * 
     * @param bool $withUrl
     * 
     * @return array
     */
    function getTypeListForDropDown(bool $withUrl=FALSE)
    {
        $arr=array_combine(['email','paper'],lang('connections.ec_type_list'));
        
        if ($withUrl)
        {
            foreach ($arr as $key=>$value)
            {
                $arr[$value]=url('Connections','campaigns',['new'],['type'=>$key,'refurl'=> current_url(FALSE,TRUE)]);
                unset($arr[$key]);
            }
        }
        return $arr;
    }
    
    /**
     * Returns array with editor placeholders
     * 
     * @return array
     */
    function getPlaceholdersForEditor()
    {
        return 
        [
            'ct_name'=>lang('connections.ec_tpltagname'),
            'ct_email'=>lang('connections.ec_tpltagemail'),
            '@site_url'=>lang('connections.ec_tpltagsiteurl'),
            'ec_name'=>lang('connections.ec_name'),
            'ec_starton'=>lang('connections.ec_starton'),
            'ec_endon'=>lang('connections.ec_endon'),
            'ec_desc'=>lang('connections.ec_desc'),
        ];
    }
    
    /**
     * Returns array with campaigns settings
     * 
     * @return array
     */
    function getSettings()
    {
        $arr=[];
        foreach($this->getModel('Settings')->filtered(['param %'=>'campaigns'])->find() as $value)
        {
            if (Str::isJson($value['value']))
            {
                $value['value']= json_decode($value['value'],TRUE);
            }
            $arr[$value['param']]=$value['value'];
        }
        return $arr;
    }
    
    /**
     * Change add/delete/disable campaign task to job list
     * 
     * @param type $id
     * @param bool $enable
     * 
     * @return boolean
     */
    function changeStatus($id,bool $enable=TRUE)
    {
        if (is_string($id))
        {
            $id=$this->where('ec_name',$id)->first();          
        }
        if (is_numeric($id))
        {
            $id=$this->where('ecid',$id)->first();
        }
        if (!is_array($id))
        {
            return FALSE;
        }
        
        if (!Arr::KeysExists(['ec_starton','ecid'], $id))
        {
            return FALSE;
        }
        if ($enable)
        {
            if ($this->getModel('Tasks/Task')->count(['tsk_ref'=>'campaign_'.$id['ecid'].'#']) > 0)
            {
                return TRUE;
            }
            if ($this->getModel('Tasks/Task')->addNew('Run Campaign',['controller'=>'Emails/CampaignModel','action'=>'start','args'=>[$id['ecid']]],'campaign_'.$id['ecid'].'#',$id['ec_starton']))
            {
                return $this->getModel('Tasks/Task')->addNew('End Campaign',['controller'=>'Emails/CampaignModel','action'=>'stop','args'=>[$id['ecid']]],'campaign_'.$id['ecid'].'#_end',$id['ec_endon']);
            }    
            return FALSE;
        }else
        {
           $this->getModel('Tasks/Task')->like('tsk_ref','campaign_'.$id['ecid'].'#')->where('enabled',1)->delete();
        }
        
    }
    
    function getTrackedLinkClicks(Int $campaignID)
    {
        return $this->getView('vw_movements')->filtered(['type'=>'tracked_link','mhref'=>'campaigns_'.$campaignID])->find();
    }
    
    /**
     * Start campaign
     * 
     * @param type $id
     * @param bool $returnDetails
     * 
     * @return boolean
     */
    function start($id,bool $returnDetails=FALSE)
    {
        $id=$this->find($id);
        if (!is_array($id))
        {
            return FALSE;
        }
        
        $targets=$this->getModel('Target')->where('ect_code',$id['ec_list'])->first();
        if (!is_array($targets) || (is_array($targets) && !array_key_exists('ect_contacts', $targets)))
        {
            return FALSE;
        }
        $id['ect_contacts']= json_decode($targets['ect_contacts'],TRUE);
        if (!is_array($id['ect_contacts']))
        {
            return FALSE;
        }
        if ($id['ec_type']!='email')
        {
            return $returnDetails ? $id : TRUE;
        }
        $id['ec_tpl']= json_decode($id['ec_tpl'],TRUE);
        if (!is_array($id['ec_tpl']) || (is_array($id['ec_tpl']) && !Arr::KeysExists(['subject','body'], $id['ec_tpl'])))
        {
            return FALSE;
        }
        $settings=$this->getSettings();
        
        $id['@site_url']= site_url();
        $tags= Arr::parsePaternOnKeys($id,'{key}',TRUE);
        
        $patern='#<a href="{@url:(.*?)}">(.*?)</a>#s';
        preg_match_all($patern, $id['ec_tpl']['body'], $matches);
        foreach($matches[0] as $key=>$value)
        {
            $url=url('Connections','link',[],['i'=>base64url_encode('campaigns_'.$id['ecid']),'r'=>'-email-','u'=>base64url_encode(base64_decode($matches[1][$key]))]);
            $id['ec_tpl']['body']= str_replace($value, url_tag($url, $matches[2][$key]), $id['ec_tpl']['body']);
        }
        
        $email=
        [
            0=>'@'.$id['ec_tpl']['mailbox'],
            1=>'',
            2=>str_replace(array_keys($tags), $tags, $id['ec_tpl']['subject']),
            3=>str_replace(array_keys($tags), $tags, $id['ec_tpl']['body'])
            
        ];
        $tasks=[];
        $date=$id['ec_starton'];
        $hourcount=0;
        $totalcount=0;
        foreach($id['ect_contacts'] as $key=>$value)
        {
            $email[1]=$value;
            $email[2]= str_replace(['{ct_name}','{ct_email}'],[$key,$value], $email[2]);
            $email[3]= base64_encode(str_replace(['{ct_name}','{ct_email}','-email-'],[$key,$value, base64url_encode($value)], $email[3]));
            $tasks[]=
            [
                'tsk_isauto'=>1,
                'tsk_descs'=>'Send Campaign Email',
                'tsk_addon'=> formatDate(),
                'tsk_actionon'=>$date,
                'tsk_ref'=>'campaign_'.$id['ecid'].'#_'.$value,
                'tsk_addby'=>'auto',
                'tsk_action'=> json_encode(['controller'=>'Emails','action'=>'sendFromMailbox','args'=>$email]),
                'enabled'=>1
            ];
            if ($totalcount>=intval($settings['campaigns_dayemailslimit']))
            {
                $date=formatDate($date,'+1 Day');
                $hourcount=0;
                $totalcount=0;
            }else
            if ($hourcount>=intval($settings['campaigns_houremailslimit']))
            {
                $date=formatDate($date,'+16 Minutes');
                $hourcount=0;
            }
            $totalcount++;
            $hourcount++;
        }
        $return=$this->getModel('Tasks/Task')->insertBatch($tasks);
        $this->save(['ec_status'=>'live','ecid'=>$id['ecid']]);
        return $returnDetails && $return ? $id : $return;
    }
    
    function getFieldsForForm(array $record) 
    {
        $arr=[];
        $arr['ec_name']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('ec_name')
                ->setID('ec_name')
                ->setText('ec_name')
                ->setTab('general')
                ->setMaxLength('80');
        
        $arr['ec_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                ->setName('ec_desc')
                ->setID('ec_desc')
                ->setText('ec_desc')
                ->setTab('general')
                ->setMaxLength('150');
        
        $arr['ec_type']= \EMPORIKO\Controllers\Pages\HtmlItems\HiddenField::create()
                ->setName('ec_type')
                ->setID('ec_type')
                ->setText('ec_type')
                ->setTab('general');
        
        $arr['ec_status']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('ec_status')
                ->setID('ec_status')
                ->setText('ec_status')
                ->setTab('general')
                ->setOptions($this->getStatusListForDropDown())
                ->setAsAdvanced();
        
        $arr['ec_starton']= \EMPORIKO\Controllers\Pages\HtmlItems\DatePicker::create()
                ->setName('ec_starton')
                ->setID('ec_starton')
                ->setText('ec_starton')
                ->setTimePicker()
                ->setTab('general')
                ->setMinDate(formatDate());
        
        $arr['ec_endon']= \EMPORIKO\Controllers\Pages\HtmlItems\DatePicker::create()
                ->setName('ec_endon')
                ->setID('ec_endon')
                ->setText('ec_endon')
                ->setTab('general')
                ->setMinDate(formatDate());
        
        $arr['ec_list']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField::create()
                ->setName('ec_list')
                ->setID('ec_list')
                ->setText('ec_list')
                ->setTab('general')
                ->setOptions($this->getModel('Target')->getForForm('ect_code','ect_name'))
                ->setAsAdvanced()
                ->setEditButton(url('Emails','campaigntargets',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                ->setNewButton(url('Emails','campaigntargets',['new'],['refurl'=>current_url(FALSE,TRUE)]));
        
        $arr['ec_notify']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                ->setName('ec_notify')
                ->setID('ec_notify')
                ->setText('ec_notify')
                ->setTab('notify')
                ->setInputField(\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()->setArgs(
                        [
                            'id'=>'id_ec_notify_input',
                            'name'=>'ec_notify_input',
                            'options'=>$this->getModel('Auth/User')->getForForm('email','name',FALSE,null,FALSE,['iscustomer'=>0])
                        ]));
        $arr['ec_links']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                ->setName('ec_links')
                ->setID('ec_links')
                ->setText('ec_links')
                ->setTab('movements')
                ->addArg('add_function','campaignAddTrackingLink()');
        
        if (array_key_exists('ec_type', $record) && $record['ec_type']=='email')
        {
            
        }
        
        if (array_key_exists('ec_type', $record) && $record['ec_type']=='email')
        {
            $arr['ec_tpl_mailbox']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('ec_tpl[mailbox]')
                ->setID('id_ec_tpl_mailbox')
                ->setText('ec_tpl_mailbox')
                ->setTab('editor')
                ->setOptions($this->getModel('Mailbox')->getDropdDownField())
                ->setAsAdvanced()
                ->setValue('');
            if (array_key_exists('settings_acc', $record) && $record['settings_acc'])
            {
               $arr['ec_tpl_mailbox']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownEditableField::createField($arr['ec_tpl_mailbox'])
                       ->setEditButton(url('Emails','mailboxes',['-id-'],['refurl'=> current_url(FALSE,TRUE)]))
                       ->setNewButton(url('Emails','mailboxes',['-id-'],['refurl'=> current_url(FALSE,TRUE)]));
            }
            
            $arr['ec_tpl_subj']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('ec_tpl[subject]')
                ->setID('id_ec_tpl_subj')
                ->setText('ec_tpl_subj')
                ->setTab('editor');
            
             $arr['ec_tpl_body']= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()
                ->setName('ec_tpl[body]')
                ->setID('id_ec_tpl_body')
                ->setText('ec_tpl_body')
                ->setTab('editor')
                ->setEmailToolbar()
                ->setEditPlaceHolders($this->getPlaceholdersForEditor());
             
             if (array_key_exists('ec_tpl',$record) && is_array($record['ec_tpl']) && Arr::KeysExists(['body','subject','mailbox'], $record['ec_tpl']))
             {
                 $arr['ec_tpl_body']->setValue($record['ec_tpl']['body']);
                 $arr['ec_tpl_subj']->setValue($record['ec_tpl']['subject']);
                 $arr['ec_tpl_mailbox']->setValue($record['ec_tpl']['mailbox']);
             }
        }
        
        return $arr;
    }
}