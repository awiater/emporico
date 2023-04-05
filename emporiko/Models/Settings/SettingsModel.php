<?php

/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 * 	@author Artur W				
 * 	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Models\Settings;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\MovementType;

class SettingsModel extends \EMPORIKO\Models\BaseModel {

    /**
     * Settings table name
     * 
     * @var string
     */
    protected $table = 'settings';

    /**
     * Table primary key
     * 
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields = ['id', 'paramsgroups', 'param', 'value', 'fieldtype', 'tooltip'];

    /**
     * Fields types declarations for forge
     * @var array
     */
    protected $fieldsTypes = [
        'id' => ['type' => 'INT', 'constraint' => '36', 'auto_increment' => TRUE],
        'paramsgroups' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => FALSE],
        'param' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => FALSE, 'unique' => TRUE],
        'value' => ['type' => 'TEXT', 'null' => FALSE],
        'fieldtype' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => TRUE],
        'tooltip' => ['type' => 'TEXT', 'null' => TRUE],
    ];
    
    /**
     * Returns array with settings fields for edit form
     * 
     * @param array $record
     * 
     * @return array
     */
    function getFieldsForForm(array $record) 
    {
        $arr=[];
        $db = $this->get('system.*');
        $app = config('APP');
        
        $arr['baseURL']= \EMPORIKO\Controllers\Pages\HtmlItems\InputLockedField::create()
                ->setName('cfg[app][baseURL]')
                ->setID('id_cfg_app_baseurl')
                ->setText('=system.settings.index_baseurl')
                ->addArg('tooltip','system.settings.index_baseurl_tooltip')
                ->setTab('home')
                ->setValue($app->baseURL)
                ->setReadOnly();
        
        $arr['defaultLocale']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('cfg[app][defaultLocale]')
                ->setID('id_cfg_app_lng')
                ->setText('=system.settings.index_lng')
                ->addArg('tooltip','system.settings.index_lng_tooltip')
                ->setTab('home')
                ->setValue($app->defaultLocale)
                ->setOptions(array_combine($app->supportedLocales, $app->supportedLocales));
        
        $arr['appTimezone']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('cfg[app][appTimezone]')
                ->setID('id_cfg_app_timezone')
                ->setText('=system.settings.index_timezone')
                ->addArg('tooltip','system.settings.index_lng_tooltip')
                ->setTab('home')
                ->setValue($app->appTimezone)
                ->setOptions($this->getTimeZonesForForm())
                ->setAsAdvanced();
        
        $arr['defMailbox']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('cfg[email][defMailbox]')
                ->setID('id_cfg_email_defMailbox')
                ->setText('=system.settings.index_defmailbox')
                ->addArg('tooltip','system.settings.index_defmailbox_tooltip')
                ->setTab('home')
                ->setValue(config('Email')->defMailbox)
                ->setOptions($this->getModel('Emails/Mailbox')->getDropdDownField())
                ->setAsAdvanced();
        
        $arr['isLive']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                ->setName('cfg[app][isLive]')
                ->setID('id_cfg_app_offline')
                ->setText('=system.settings.index_offline')
                ->addArg('tooltip','system.settings.index_offline_tooltip')
                ->setTab('home')
                ->setValue(strval($app->isLive));
        
        $arr['company']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('settings[company]')
                ->setID('id_cfg_app_company')
                ->setText('=system.settings.index_company')
                ->addArg('tooltip','system.settings.index_company_tooltip')
                ->setTab('theme')
                ->setValue($db['company']);
        
        $arr['APPName']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('cfg[app][APPName]')
                ->setID('id_cfg_app_appname')
                ->setText('=system.settings.index_appname')
                ->addArg('tooltip','system.settings.index_appname_tooltip')
                ->setTab('theme')
                ->setValue($app->APPName);
        
        $arr['theme_logo']= \EMPORIKO\Controllers\Pages\HtmlItems\ImagePicker::create()
                ->setName('settings[theme_logo]')
                ->setID('id_cfg_app_theme_logo')
                ->setText('=system.settings.index_logo')
                ->addArg('tooltip','system.settings.index_logo_tooltip')
                ->setTab('theme')
                ->setValue($db['theme_logo'])
                ->setImagePreview(TRUE)
                ->setFormat('images')
                ->setAutoSize(250)
                ->setJustFileNameOption()
                ->setAsWizard()
                ->setUploadDir('@storage/files/images/');
        
        $arr['theme']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('settings[theme]')
                ->setID('id_cfg_app_theme')
                ->setText('=system.settings.index_theme')
                ->addArg('tooltip','system.settings.index_theme_tooltip')
                ->setTab('theme')
                ->setValue($db['theme'])
                ->setOptions($this->getAvaliableThemes());
        
        $arr['tables_rows_per_page']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('settings[tables_rows_per_page]')
                ->setID('id_cfg_app_rows_per_page')
                ->setText('=system.settings.index_rows_per_page')
                ->addArg('tooltip','system.settings.index_rows_per_page_tooltip')
                ->setTab('theme')
                ->setValue($db['tables_rows_per_page'])
                ->setOptions([5=>5,10=>10,20=>20,50=>50,100=>100,500=>500]);
        
        $arr['cache']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                ->setName('settings[cache]')
                ->setID('id_cfg_cache')
                ->setText('=system.settings.index_cache')
                ->addArg('tooltip','system.settings.index_cache_tooltip')
                ->setTab('theme')
                ->setValue($db['cache'])
                ->setAsWrapped()
                ->render();
        
        $arr['cache'].=\EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton::create()
                ->setAsLink()
                ->setButtonColor('danger ml-3')
                ->setButtonIcon('fas fa-broom mr-1')
                ->setHref(url('Settings','content',['clearcache']))
                ->addArg('text','system.settings.index_cache_clear')
                ->render();
        
        $arr['cache']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                ->setName('settings_cache_field')
                ->setID('id_settings_cache_field')
                ->setText('=system.settings.index_cache')
                ->addArg('tooltip','system.settings.index_cache_tooltip')
                ->setTab('theme')
                ->setClass('pl-2')
                ->setValue($arr['cache']);
        
        $arr['storageEngine']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('cfg[app][storageEngine]')
                ->setID('id_cfg_onedrivefolderid')
                ->setText('=system.settings.index_storage_type')
                ->addArg('tooltip','system.settings.index_storage_type_tooltip')
                ->setTab('storage')
                ->setValue($app->storageEngine)
                ->setOptions($this->getStorageTypes());
        
        $arr['storage_type_onedrive_token']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                ->setName('settings[storage_type_onedrive_token]')
                ->setID('id_cfg_onedrivefolderid')
                ->setText('=system.settings.index_onedrivetoken')
                ->addArg('tooltip','system.settings.index_onedrivetoken_tooltip')
                ->setTab('storage')
                ->setValue($db['storage_type_onedrive_token']);
        
         $arr['defMailbox']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('cfg[Email][defMailbox]')
                ->setID('id_cfg_email_defmailbox')
                ->setText('=system.settings.index_emails_defmailbox')
                ->addArg('tooltip','system.settings.index_emails_defmailbox_tootlip')
                ->setTab('emails')
                ->setValue(config('Email')->defMailbox)
                ->setAsAdvanced()
                ->setOptions($this->getModel('Emails/Mailbox')->getDropdDownField('emm_name','emm_name',FALSE,FALSE));
         
         $arr['maxSMTPEmailsDaily']= \EMPORIKO\Controllers\Pages\HtmlItems\NumberField::create()
                ->setName('cfg[Email][maxSMTPEmailsDaily]')
                ->setID('id_cfg_email_maxsmptpemailsdaily')
                ->setText('=system.settings.index_emails_maxsmptpemailsdaily')
                ->addArg('tooltip','system.settings.index_emails_maxsmptpemailsdaily_tooltip')
                ->setTab('emails')
                ->setValue(config('Email')->maxSMTPEmailsDaily)
                ->setMin(1)
                ->setTypeStrict();
         
         $arr['maxSMTPEmailsSend']= \EMPORIKO\Controllers\Pages\HtmlItems\NumberField::create()
                ->setName('cfg[Email][maxSMTPEmailsSend]')
                ->setID('id_cfg_email_maxsmptpemailssend')
                ->setText('=system.settings.index_emails_maxsmptpemailssend')
                ->addArg('tooltip','system.settings.index_emails_maxsmptpemailssend_tooltip')
                ->setTab('emails')
                ->setValue(config('Email')->maxSMTPEmailsSend)
                ->setMin(1)
                ->setTypeStrict();
         
         $arr['maxSMTPEmailsSendDelay']= \EMPORIKO\Controllers\Pages\HtmlItems\NumberField::create()
                ->setName('cfg[Email][maxSMTPEmailsSendDelay]')
                ->setID('id_cfg_email_maxsmptpemailssenddelay')
                ->setText('=system.settings.index_emails_maxsmptpemailssenddelay')
                ->addArg('tooltip','system.settings.index_emails_maxsmptpemailssenddelay_tooltip')
                ->setTab('emails')
                ->setValue(config('Email')->maxSMTPEmailsSendDelay)
                ->setMin(1)
                ->setTypeStrict();
         
         $arr['supportEmail']= \EMPORIKO\Controllers\Pages\HtmlItems\EmailField::create()
                ->setName('cfg[Email][supportEmail]')
                ->setID('id_cfg_email_supportemail')
                ->setText('=system.settings.index_emails_support')
                ->addArg('tooltip','system.settings.index_emails_support_tooltip')
                ->setTab('emails')
                ->setValue(config('Email')->supportEmail);
         
        
        $arr['backup_enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                ->setName('settings[backup_enabled]')
                ->setID('id_cfg_backup_enabled')
                ->setText('=system.settings.index_backup_enabled')
                ->addArg('tooltip','system.settings.index_backup_enabled_tooltip')
                ->setTab('backup')
                ->setValue($db['backup_enabled']);
        
        /*$arr['backup_time']= \EMPORIKO\Controllers\Pages\HtmlItems\TimePicker::create()
                ->setName('settings[backup_time]')
                ->setID('id_cfg_backup_time')
                ->setText('=system.settings.index_backup_time')
                ->addArg('tooltip','system.settings.index_backup_time_tooltip')
                ->setTab('backup')
                ->setValue($db['backup_time'])
                ->addArg('style', 'max-width:200px');*/
        
        $arr['backup_command']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                ->setName('settings[backup_command]')
                ->setID('id_cfg_backup_command')
                ->setText('=system.settings.index_backup_command')
                ->addArg('tooltip','system.settings.index_backup_command_tooltip')
                ->setTab('backup')
                ->setValue($db['backup_command'])
                ->setOptions(array_combine(['email','onedrive'], lang('system.settings.index_backupcommand_list')))
                ->addArg('style', 'max-width:200px');
        
        $arr['backup_email']= \EMPORIKO\Controllers\Pages\HtmlItems\EmailField::create()
                ->setName('settings[backup_email]')
                ->setID('id_cfg_backup_email')
                ->setText('=system.settings.index_backup_email')
                ->addArg('tooltip','system.settings.index_backup_email_tooltip')
                ->setTab('backup')
                ->setValue($db['backup_email'])
                ->addArg('style', 'max-width:400px');
        
        $arr['backup_now']= \EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton::create()
                ->setButtonColor('danger')
                ->setButtonIcon('fas fa-file-archive mr-1')
                ->addArg('text', 'system.settings.index_backup_now')
                ->setHref(url('Settings','backup',['task'],['refurl'=> current_url(FALSE,TRUE)]),TRUE)
                ->setTab('backup');
        if ($this->getModel('Tasks/Task')->count(['tsk_desc'=>'ADHOC_DB_BACKUP','enabled'=>1]) > 0)
        {
            $arr['backup_now']->addArg('disabled',TRUE);
        }
       
        return $arr;
    }
    
    function getFieldsForInfoForm()
    {
        $arr=[];
        $arr['systemstat']= \EMPORIKO\Controllers\Pages\HtmlItems\CustomTextField::create()
                ->setName('systemstat')
                ->setID('systemstat')
                ->setText('')
                ->setTab('systemstat')
                ->setValue($this->getServerInfo());
        return $arr;
    }
    
    /**
     * Returns info about server
     * 
     * @return string
     */
    function getServerInfo()
    {
        ob_start ();
        ob_start ();
        phpinfo ();
        $info= trim (ob_get_clean ());
        
        preg_match("/<body.*\/body>/s", $info, $body);
        $body=is_array($body) && count($body) > 0 ? $body[0] :null;
        if ($body!=null)
        {
            $html='<style type="text/css">';
            $html.='pre {margin: 0; font-family: monospace;}';
            $html.='table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}';
            $html.='.center {text-align: center;}';
            $html.='.center table {margin: 1em auto; text-align: left;}';
            $html.='.center th {text-align: center !important;}';
            $html.='td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}';
            $html.='th {position: sticky; top: 0; background: inherit;}';
            $html.='h1 {font-size: 150%;}';
            $html.='h2 {font-size: 125%;}';
            $html.='.p {text-align: left;}';
            $html.='.e {background-color: #ccf; width: 300px; font-weight: bold;}';
            $html.='.h {background-color: #99c; font-weight: bold;}';
            $html.='.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}';
            $html.='.v i {color: #999;}';
            $html.='img {float: right; border: 0;}';
            $html.='hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}';
            $body=$html.'</style>'.$body;
        }
        return $body;
    }
    
    /**
     * Returns array with currency signs (icons)
     * 
     * @param string|null $currency
     * @param bool        $justIcon
     * @param bool        $forForm
     * 
     * @return string|array
     */
    function getCurrencyIcons($currency=null,bool $justIcon=TRUE,bool $forForm=FALSE)
    {
        if ($currency!=null)
        {
            $currency= str_replace(' ', '', $currency);
        }
        
        $arr= 
        [
            'EUR'=>($forForm ? lang('system.settings.curr_eur') : ($justIcon ? 'fas fa-euro-sign' : '<i class="fas fa-euro-sign" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_eur').'"></i>')),
            'GBP'=>($forForm ? lang('system.settings.curr_gbp') :($justIcon ? 'fas fa-pound-sign' : '<i class="fas fa-pound-sign" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_gbp').'"></i>')),
            'USD'=>($forForm ? lang('system.settings.curr_usd') : ($justIcon ? 'fas fa-dollar-sign' : '<i class="fas fa-dollar-sign" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_usd').'"></i>')),
            'RUB'=>($forForm ? lang('system.settings.curr_rub') : ($justIcon ? 'fas fa-ruble-sign' : '<i class="fas fa-ruble-sign" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_rub').'"></i>')),
            'INR'=>($forForm ? lang('system.settings.curr_inr') : ($justIcon ? 'fas fa-rupee-sign' : '<i class="fas fa-rupee-sign" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_inr').'"></i>')),
            'JPY'=>($forForm ? lang('system.settings.curr_jpy') : ($justIcon ? 'fas fa-yen-sign' : '<i class="fas fa-yen-sign" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_jpy').'"></i>')),
            'PLN'=>($forForm ? lang('system.settings.curr_pln') : ($justIcon ? 'z&lstrok;' : '<b data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_pln').'">z&lstrok;</b>')),
            'CZK'=>($forForm ? lang('system.settings.curr_czk') : ($justIcon ? 'K&ccaron;' : '<b data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_czk').'">K&ccaron;</b>')),
            'RON'=>($forForm ? lang('system.settings.curr_ron') : ($justIcon ? 'lei' : '<b data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_ron').'">lei</b>')),
            'UAH'=>($forForm ? lang('system.settings.curr_uah') : ($justIcon ? 'fas fa-hryvnia' : '<i class="fas fa-hryvnia" data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_uah').'"></i>')),
            'CNY'=>($forForm ? lang('system.settings.curr_cny') : ($justIcon ? '&yen;' : '<b data-toggle="tooltip" data-placement="top" title="'.lang('system.settings.curr_cny').'">&yen;</b>')),
        ];
        
        return array_key_exists($currency, $arr) ? $arr[$currency] : $arr;
    }
    
    /**
     * Add/Remove backup cron job
     * @param bool   $enabled
     * @param string $time
     * @param string $command
     * 
     * @return boolean
     */
    function addOrRemoveBackupJob(bool $enabled,string $time,string $command)
    {
        if (!Str::contains($time, ':'))
        {
            return FALSE;
        }
        $backup_name=$this->getModel('Settings')->get('system.backup_name');
        $task=explode(':',$time);
        $task[1]=intval($task[1]) > 0 ? intval($task[1]) : '*';
        $task[0]=intval($task[0]) > 0 ? intval($task[0]) : '*';
        $task=$task[1].' '.$task[0].' * * * ';
        $task=
        [
            'command'=>'Settings::backup',
            'picktime'=>$task,
            'name'=>$backup_name,
            'desc'=>'',
            'enabled'=>$enabled ? 1 : 0
        ];
        $taskRecord=$this->getModel('Crontab/Job')->where('name',$task['name'])->first();
         if (is_array($taskRecord))
        {
            $taskRecord['picktime']=$task['picktime'];
            $taskRecord['command']=$task['command'];
            $taskRecord['enabled']=$task['enabled'];
            $task=$taskRecord;
        }
        return $this->getModel('Crontab/Job')->save($task);
    }
    
    /**
     * Clear cache folder
     */
    public function clear_all_cache()
    {
        $cache_path = parsePath('@storage/cache',TRUE);

        $handle = opendir($cache_path);
        while (($file = readdir($handle))!== FALSE) 
        {
            if ($file != '.htaccess' && $file != 'index.html')
            {
                @unlink($cache_path.'/'.$file);
            }
        }
        closedir($handle);       
    }
    
    /**
     *  Return all records from Settings table categorized by paramsgroup
     *  
     * @param  array $filter
     * @return array
     */
    public function getCategorized(array $filters = []) {
        return $this->count(['paramsgroups' => 'modules']);
        $data = [];
        foreach ($this->arrWhere($filters)->findAll() as $item) {
            $data[$item['paramsgroups']][$item['param']] = ['value' => $item['value'], 'param' => $item['param'], 'id' => $item['id']];
        }

        return $data;
    }

    /**
     *  Return all records from Settings table categorized by paramsgroup
     *  
     * @param  int   $limit  Determine how many records will be shown
     * @param  array $filter Array with filters (key is field, value is field value)
     * @return array
     */
    public function getAll($limit = null, array $filters = []) 
    {
        return parrent::getAll($limit, $filters, 'paramsgroups');
    }
    
    /**
     * Returns array with values and tooltips
     * 
     * @param string $param
     * @param bool   $encodeValue
     * 
     * @return array
     */
    function getListSettings(string $param,bool $encodeValue=FALSE)
    {
        $arr=[];
        $param=Str::endsWith($param, '*') ? $param : $param.'*';
        foreach($this->get($param,FALSE,'*') as $record)
        {
            $record['value']=$encodeValue ? base64_encode($record['value']) : $record['value'];
            $arr[$record['value']]=lang($record['tooltip']);
        }
        return $arr;
    }
    
    /**
     * Return setting param value
     * 
     * @param String $key
     * @param bool   $parseValue
     * @param String $keyToGet
     * 
     * @return mixed
     */
    public function get($key, $parseValue = FALSE, $keyToGet = 'value', $showError = TRUE) 
    {
        
        $showError = ENVIRONMENT == 'development' ? $showError : false;
        $param=$key;
        if (Str::contains($key, '.')) {
            $key = explode('.', $key);
            
            if (count($key) != 2) {
                throw new \Exception('Invalid param name');
            }
            if (Str::contains($key[0], '*')) {
                $this->Like('paramsgroups', str_replace('*', '%', $key[0]));
            } else {
                $this->where('paramsgroups', $key[0]);
            }
            $key = $key[1];
        }

        if (Str::contains($key, '*')) {
            $this->Like('param', str_replace('*', '%', $key));
        } else {
            $this->where('param', $key);
        }
        $result = [];
        $data = [];
        $data = $this->orderby('param')->find();
        
        if (count($data) == 0) 
        {
            if ($showError) 
            {
                throw new \Exception('Invalid param name '.$param);
            }
            return null;
        }
        $multi = FALSE;
        if ($keyToGet == 'values') 
        {
            $multi = TRUE;
            $keyToGet = 'value';
        }

        foreach ($data as $value) 
        {
            if (is_array($value) && array_key_exists($keyToGet, $value)) 
            {
                $result[$value['param']] = $keyToGet=='tooltip' ? lang($value[$keyToGet]) : $value[$keyToGet];
            } else 
            {
                $result[$value['param']] = $value;
            }
        }
        if (count($result) == 1) {
            if ($keyToGet == 'value' && !$multi) {
                $result = array_values($result)[0];
            }
        }
        if ($parseValue && Str::isJson($result)) {
            $result = json_decode($result, TRUE);
        } else
        if ($parseValue && Str::contains($result, '|')) 
        {
            $arr = [];
            foreach (explode('|', $result) as $value) {
                if (Str::contains($value, '=')) {
                    $value = explode('=', $value);
                    $arr[$value[0]] = $value[1];
                } else {
                    $arr[] = $value;
                }
            }
            $result = $arr;
        }else
        if ($parseValue && is_numeric($result))
        {
            return intval($result) ==1;
        }else
        if ($parseValue && is_string($result))
        {
            if (strtolower($result)=='false')
            {
                return FALSE;
            }else
            if (strtolower($result)=='true')
            {
                return TRUE;
            }
        }

        return $result;
    }
    
    /**
     * Write settings parameter data to storage
     * 
     * @param string $key
     * @param mixed  $value
     * @param string $valueParser
     * 
     * @return bool
     */
    public function write($key, $value, $valueParser = 'JSON') 
    {
        if (is_array($value))
        {
            if ($valueParser=='FLAT')
            {
                $value = Str::Flatten($value);
            }else
            {
                $value = json_encode($value);
            }
        }
        if (Str::contains($key, '.'))
        {
            $key=Str::afterLast($key, '.');
        }
        return $this->builder()->set('value',$value)->where('param', $key)->update();
    }

    /**
     * Add new parameter to storage
     * 
     * @param string $groupname
     * @param string $param
     * @param mixed  $value
     * 
     * @return boolean
     */
    public function add(string $groupname,string $param, $value, $fieldtype = 'textlong', $tooltip = '') 
    {
        $record = ['paramsgroups' => $groupname, 'param' => $param, 'value' => $this->parseValue($value,'JSON',FALSE), 'fieldtype' => $fieldtype, 'tooltip' => $tooltip];
        $id = $this->where('paramsgroups', $groupname)->where('param', $param)->first();
        if ($id != null) 
        {
            $record[$this->primaryKey] = $id[$this->primaryKey];
        }
        return $this->save($record);
    }
    
    /**
     * Write settings parameters data to storage
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function writeMany(array $data) 
    {

        $result = FALSE;
        
        foreach ($data as $key => $value) 
        {
            if (is_array($value) && !Arr::isAssoc($value))
            {
                $result = $this->write($key, implode(',',$value));
            }else
            if (is_array($value)) 
            {
                $set=FALSE;
                $builder = $this->builder();
                if (array_key_exists('value', $value)) 
                {
                    $builder = $builder->set('value', is_array($value['value']) ? json_encode($value['value']) : $value['value']);
                    $set=TRUE;
                }
                if (array_key_exists('fieldtype', $value)) {
                    $builder = $builder->set('fieldtype', $value['fieldtype']);
                    $set=TRUE;
                }
                if (array_key_exists('tooltip', $value)) {
                    $builder = $builder->set('tooltip', $value['tooltip']);
                    $set=TRUE;
                }
                if (array_key_exists('paramsgroups', $value)) 
                {
                    $builder = $builder->set('paramsgroups', $value['paramsgroups']);
                    $set=TRUE;
                }
                if (array_key_exists('param', $value)) {
                    $builder = $builder->set('param', $value['param']);
                    $set=TRUE;
                }
                
                if (Str::startsWith($key, 'new_')) 
                {
                    $result = $builder->insert();
                } else 
                {
                    if (array_key_exists('param', $value)) 
                    {
                        $result = $builder->where('param', $value['param']);
                    } else 
                    {
                        $result = $builder->where('param', $key);
                    }
                    if (!$set)
                    {
                        $builder->set('value', json_encode($value));
                    }
                    $result = $result->update();
                }
            } else 
            {
                $result = $this->write($key, $value);
            }
        }
        return $result;
    }
    
    /**
     * Parse setting parameter value
     * 
     * @param mixed   $value
     * @param string  $valueParser
     * @param boolean $decode
     * 
     * @return mixed
     */
    private function parseValue($value,$valueParser='JSON',$decode=TRUE) 
    {
        if (is_string($value) && Str::isJson($value) && $decode)
        {
            return json_decode($value,TRUE);
        }
        
        if (is_array($value) || is_object($value)) 
        {
            if ($valueParser == 'FLAT') {
                $value = Str::Flatten($value);
            } else {
                $value = json_encode($value);
            }
        }
        return $value;
    }

    /**
     * Returns field access value
     * 
     * @param  string $field
     * 
     * @return mixed
     */
    function getFieldAccess($field) {
        return $this->get('fieldsaccess.' . $field, FALSE, 'value', FALSE);
    }

    /**
     * Return array with avaliable time zones
     * 
     * @return Array
     */
    public function getTimeZonesForForm() {
        $times = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL, null);
        return array_combine(array_values($times), array_values($times));
    }

    /**
     * Return Array with available languages in system
     * 
     * @return Array
     */
    public function getAvalLocales() {
        $arr = [];
        foreach (config('APP')->supportedLocales as $value) 
        {
            $arr[$value]=$value;
        }
        return $arr;
    }

    /**
     * Add custom route to system settings
     * 
     * @param  mixed   $controller Controller object
     * @param  string  $action     Action which will be fired if route match (controller and action ie /settings/add)
     * @param  string  $route      Controller section of route to be match (first segment after webiste url)
     */
    public function addCustomRoute($controller, string $action, string $route) {

        if (is_subclass_of($controller, '\VCMS\Controllers\Core\VCMSController')) {
            $controller = get_class($controller);
        }

        if (is_string($controller) && (Str::contains($controller, '/') || Str::contains($controller, '\\')) && Str::endsWith(strtolower($controller), 'controller')) {
            $this->add('routes', $route, json_encode(['controller' => $controller, 'action' => $action]));
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Returns array with given theme settings
     * 
     * @param  string $name
     * @return array
     */
    function getTheme($name = null) 
    {
        $name = $name == null ? $this->get('system.theme') : $name;
        $path = parsePath('@template/' . $name . '/config.php', TRUE);
        if (file_exists($path)) 
        {
            return include($path);
        }
        return null;
    }
    
    /**
     * Returns array with all available theme names
     * 
     * @return array
     */
    function getAvaliableThemes()
    {
        $arr=[];
        foreach(directory_map(parsePath('@template',TRUE),2) as $key=>$folder)
        {
            if (is_array($folder) && in_array('config.php', $folder) && in_array('index.php', $folder))
            {
                $key= substr($key, 0, strlen($key)-1);
                $arr[$key]=ucwords($key);
            }
        }
        return $arr;
    }
    
 

    /**
     * Returns array with available movement types
     * 
     * @return Array
     */
    function getMovementTypes($returnField = 'value') {
        $arr = [];
        $data = $this->get('movement_types.*', FALSE, $returnField, FALSE);
        foreach ($data as $key => $value) {
            if (Str::contains($key, '_')) {
                $arr[Str::afterLast($key, '_')] = $value;
            }
        }
        return $arr;
    }

    /**
     * Returns list with log files
     * 
     * @return Array
     */
    function getLogsList(int $perPage=0) 
    {
        $arr = [];
        foreach (get_filenames(parsePath('@writable/logs', TRUE), TRUE) as $file) 
        {
            if (Str::endsWith(strtolower($file), 'log')) {
                $file = new \CodeIgniter\Files\File($file);
                $arr[] = ['id' => base64url_encode($file->getBasename()), 'path' => $file->getRealPath(), 'name' => $file->getBasename(), 'modified' => $file->getCTime(), 'file' => $file];
            }
        }
        
        return $arr;
    }
    
    /**
     * Save configuration data to file
     * 
     * @param string $group
     * @param array  $values
     * @param bool   $isdb
     * 
     * @return boolean
     */
    function saveconfig($group, array $values, $isdb = FALSE) 
    {
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
    
    /**
     * Remove Log(s) file(s)
     * 
     * @param  Array
     * 
     * @return bool
     */
    function removeLogs($data = null) {
        if ($data = null) {
            return delete_files(parsePath('@writable/logs/', TRUE));
        }
        if (is_array($data)) {
            foreach ($data as $value) {
                $file = parsePath('@writable/logs/' . $value, TRUE);
                if (!file_exists($file)) {
                    $file = parsePath('@writable/logs/' . base64url_decode($value), TRUE);
                }
                unlink($file);
            }
            return TRUE;
        }
        $data = parsePath('@writable/logs/' . $data, TRUE);
        if (file_exists($data)) {
            unlink($data);
        }
    }
    
    /**
     * Perform table install in storage
     * 
     * @param type $install
     */
    public function installstorage($install = FALSE) 
    {
        if ($install) {
            parent::installstorage();
        }
    }

    /**
     * Returns array with details data
     * 
     * @param type $type
     * @param type $field
     * 
     * @return mixed
     */
    function getDetailsData($type, $field = null) {
        $arr = [];
        foreach (model('Settings/SettingsModel')->filtered(['param %' => $type . '_', 'paramgroup' => 'details'])->find() as $key => $value) {
            $key = str_replace($type . '_', '', $value['param']);
            $arr[$key] = json_decode($value['value'], TRUE);
            if (is_array($arr[$key]) && array_key_exists($field, $arr[$key])) {
                $arr[$key] = $arr[$key][$field];
            }
        }
        return !is_array($arr) ? [] : $arr;
    }

    /**
     * Returns custom settings tabs data
     * 
     * @return array
     */
    public function getCustomSettingsTab() {
        $set = $this->get('system.settings_moretabs', TRUE);
        $arr = [];
        if (is_array($set)) {
            foreach ($set as $key => $value) {
                if ($key != null && strlen($key) > 0 && Str::contains($value, ':')) {
                    $value = explode(':', $value);
                    $arr[lang($key)] = loadModule($value[0], $value[1]);
                }
            }
        }
        return $arr;
    }
    
    /**
     * Returns array with available files storage types (ie. OneDrive)
     * 
     * @return array
     */
    function getStorageTypes()
    {
        $arr=[];        
        foreach($this->like('param','storage_type_')->find() as $value)
        {
            $arr[$value['value']]=lang(str_replace('storage_type_', '', $value['param']));
        }
        return $arr;
    }
    
    /**
     * Set view icon fetch module data
     * 
     * @param string  $name
     * @param string  $url
     * @param string  $controller
     * @param string  $action
     * @param array   $args
     * @param string  $tooltip
     */
    function addViewIconData($name,$controller,$action,array $args=[],$tooltip='View Icon Data')
    {
        $controller=['controller'=>$controller,'action'=>$action];
        if (count($args) > 0)
        {
            $controller['args']=$args;
        }
        $this->add('system', 'viewicondata_'.$name, json_encode($controller), 'text', $tooltip);
        
    }
    
    /**
     * Get view icon fetch module data for given name
     * 
     * @param  string $name
     * 
     * @return array
     */
    function getViewIconData($name)
    {
        return $this->get('system.viewicondata_'.$name, TRUE, 'value',False);
    }
    
    /**
     * Add new upload driver (settings for upload data)
     * 
     * @param string $name
     * @param string $desc
     * @param mixed  $model
     * @param string $lookupKey
     * @param array  $columns
     * 
     * @return boolean
     */
    function addUploadDriver(string $name,string $desc,$model,string $lookupKey,array $columns=[])
    {
        $name='upload_driver_'. strtolower($name);
        
        if (is_array($model) && Arr::KeysExists(['model','patern','primary'], $model) && is_a($model['model'], '\EMPORIKO\Models\BaseModel'))
        {
             $columns=[];
             foreach($model['model']->allowedFields as $field)
             {
                 if ($model['primary'])
                 {
                     $columns[$field]=$model['patern'].'.'.$field;  
                 }else
                 {
                     if ($field!=$model['model']->primaryKey)
                    {
                        $columns[$field]=$model['patern'].'.'.$field;  
                    } 
                 }
                          
             }
             $model= get_class($model['model']);
        }
        if (!is_string($model))
        {
            return FALSE;
        }
        $columns=
        [
            'model'=>$model,
            'lookupKey'=>$lookupKey,
            'columns'=>$columns
        ];
        return $this->add('system', $name, json_encode($columns), 'textlong', $desc);
    }
    
    /**
     * Get folder listing for drop down
     *  
     * @param string $parentDir
     * @return array
     */
    function getFolderList(string $parentDir='@storage')
    {
        $parentDir=parsePath($parentDir,TRUE);
        $siteDir=parsePath('@',TRUE);
        $arr=[];
        foreach(mapDir($parentDir,['@dir']) as $dir)
        {
            $dir['realpath']= str_replace($siteDir, '@', $dir['path']);
            $arr[$dir['realpath']]=$dir['file'];
        }
        return $arr;
    }

    /**
     * Returns array with upload template sources data
     * 
     * @param bool   $forForm
     * @param string $name
     * @param bool   $newItemList
     * 
     * @return array
     */
    function getUploadDriversSources(bool $forForm=FALSE,string $name='*',bool $newItemList=FALSE)
    {
        $arr=[];
        $single=$name!='*';
        $name=Str::startsWith($name,'upload_driversource_') ? $name : 'upload_driversource_'.$name;
        $name=$this->get('system.'.$name, TRUE, '*', FALSE);
        foreach(is_array($name) ? $name : [] as $name=>$value)
        {
            $value= json_decode($value['value'],TRUE);
            if (is_array($value))
            {
                $value['name']=$name;
                if($newItemList)
                {
                    $arr[$value['title']]=url('Settings','uploadtpls',['new'],['tpl'=>str_replace('upload_driversource_', '', $value['name']),'refurl'=>current_url(FALSE,TRUE)]);
                }else
                if ($forForm)
                {
                   $arr[$value['name']]= lang($value['title']); 
                }else
                {
                    $arr[]= $value;
                }
                
            }
            
        }
        return $single && count($arr)==1 ? $arr[0] : $arr; 
    }
    
    /**
     * Add new upload template source (driver)
     * 
     * @param string $name
     * @param string $title
     * @param mixed  $model
     * @param array  $columns
     * 
     * @return boolean
     */
    function addUploadDriverSource(string $name,string $title,$model,array $columns=[])
    {
        if (is_string($model))
        {
            $model=model($model);
        }
        $name= str_replace([' ','-','__'],['_','',''], $name);
        if (!$model instanceof \EMPORIKO\Models\BaseModel)
        {
            return FALSE;
        }
        $lang=get_class($model);
        $lang= str_replace([Str::afterLast(get_class($model), '\\'),'\\','Models',APP_NAMESPACE], '', $lang);
        $model= 
        [
            'model'=> get_class($model),
            'lookupKey'=>$model->primaryKey,
            'columns'=>count($columns) < 1 ? array_combine($model->allowedFields,Arr::ParsePatern($model->allowedFields, strtolower($lang).'.value')) :$columns,
            'name'=>$name,
            'title'=>$title,
       ];
       $name='upload_driversource_'. strtolower($name);
       return $this->add('system', $name, json_encode($model));
    }
    
    /**
     * Get upload driver data for given name
     * 
     * @param string $name
     * @param bool   $asUrl
     * @param bool   $parseFileMap
     * 
     * @return null/array
     */
    function getUploadDriverData(string $name,bool $asUrl=FALSE,bool $parseFileMap=FALSE)
    {
        $nameA=$this->getUploadDrivers($name);
        if (!is_array($nameA) || (is_array($nameA) && count($nameA) < 1))
        {
            $nameA=$this->getUploadDrivers(base64url_decode($name));
        }
        
        if (!is_array($nameA) || (is_array($nameA) && count($nameA) < 1))
        {
            return FALSE;
        }
        $name=$nameA[0];        
        $name['_source']=$this->getUploadDriversSources(FALSE, $name['model']);
        if (is_array($name) && array_key_exists('filemap', $name) && $parseFileMap)
        {
            $name['filemap']= json_decode($name['filemap'],TRUE);
            if (is_array($name['filemap']))
            {
                $arr=[];
                foreach ($name['filemap'] as $val)
                {
                    $arr[$val['column']]=$val['file_column'];
                }
                $name['filemap'] =$arr;
            }
        }
        if ($asUrl)
        {
            $name=str_replace('upload_drivertpl_', '', $name['name']);
            return url('Settings','uploadtpls',['gettemplate'],['id'=>base64url_encode($name),'refurl'=> current_url(FALSE,TRUE)]);
        }
        return $name;
    }
    
    /**
     * Returns file map from given upload template
     * 
     * @param string $name
     * 
     * @return boolean|array
     */
    function getUploadDriverFileMap(string $name)
    {
        if (Str::startsWith($name, '#'))
        {
            $name=$this->get(substr($name,1));
        }
        $nameA=$this->getUploadDrivers($name);
        if (!is_array($nameA) || (is_array($nameA) && count($nameA) < 1))
        {
            $nameA=$this->getUploadDrivers(base64url_decode($name));
        }
        
        if (!is_array($nameA) || (is_array($nameA) && count($nameA) < 1))
        {
            return FALSE;
        }
        if (array_key_exists('filemap', $nameA[0]))
        {
            $name=[];
            $nameA= json_decode($nameA[0]['filemap'],TRUE);
            if (is_array($nameA))
            {
                foreach($nameA as $val)
                {
                    $name[$val['column']]=$val['file_column'];
                }
                return $name;
            }
        }
        return FALSE;
    }
    
    /**
     * Returns array with upload templates (or template if name is supplied) data
     * 
     * @param string $name
     * 
     * @return array
     */
    function getUploadDrivers(string $name='*',bool $forForm=FALSE)
    {
        if ($name=='_tpl')
        {
            return 
            [
                'model'=>'',
                'lookupKey'=>'',
                'columns'=>'',
                'name'=>'',
                'title'=>'',
                'filemap'=>[]
            ];
        }
        $arr=[];
        //$name=$this->get('system.upload_drivertpl_'.$name, TRUE, '*', FALSE);
        if ($name=='*')
        {
            $name=['param %'=>'upload_drivertpl_'];
        }else
        {
            $name=Str::startsWith($name, 'upload_drivertpl_') ? $name : 'upload_drivertpl_'.$name;
            $name=['param'=>$name];
        }
        $name=$this->filtered($name)->find();
        
        foreach(is_array($name) ? $name : [] as $value)
        {
            $value=json_decode($value['value'],TRUE);
            if (is_array($value))
            {
                $value['drvid']= str_replace('upload_drivertpl_', '', $value['name']);
                $value['drvid']= base64url_encode($value['drvid']);
                if ($forForm)
                {
                    $arr[$value['drvid']]=lang($value['title']);
                }else
                {
                    if (!array_key_exists('title', $value))
                    {
                        $value['title']= ucwords(str_replace(['upload_drivertpl_','_'],'', $name));
                    }
                    $arr[]= $value;
                }
            }
            
        }
        return $arr;
    }
    
    /**
     * Add new upload template
     * 
     * @param string $model
     * @param string $title
     * @param string $lookupKey
     * @param array  $filemap
     * 
     * @return boolean
     */
    function addNewUploadDriver(string $model,string $title,string $lookupKey,array $filemap)
    {
        $name='upload_drivertpl_'.mb_url_title($title,'_');
        $name=strtolower($name);
        $model=$this->getUploadDriversSources(FALSE,$model);
        if (!is_array($model) || (is_array($model) && !Arr::KeysExists(['columns','name'], $model)))
        {
            return FALSE;
        }
        $value=
        [
            'model'=>$model['name'],
            'lookupKey'=>$lookupKey,
            'columns'=>$model['columns'],
            'name'=>$name,
            'title'=>$title,
            'filemap'=> json_encode($filemap)
        ];
        return $this->add('system', $name, json_encode($value));
    }
    
    /**
     * Returns array with available models names
     * 
     * @param bool $forForm
     * 
     * @return array
     */
    function getModelsNames(bool $forForm=FALSE)
    {
        $arr=[];
        foreach(directory_map(parsePath('@app/Models',TRUE)) as $key=>$val)
        {
            if (is_string($key))
            {
                $key= str_replace('/', '', $key);
                if (is_string($val))
                {
                    $val=Str::before($val, '.');
                    $model=APP_NAMESPACE.'\\Models\\'.$key.'\\'.$val;
                    $val= str_replace('Model', 's', $val);
                    if ($forForm)
                    {
                        $arr[$key][$model]=$vval;
                    }else
                    {
                        $arr[$model]=$val;
                    }
                }else
                if (is_array($val))
                {
                    foreach($val as $vval)
                    {
                        $vval=Str::before($vval, '.');
                        $model=APP_NAMESPACE.'\\Models\\'.$key.'\\'.$vval;
                        $vval= str_replace('Model', 's', $vval);
                        if ($forForm)
                        {
                            $arr[$key][$model]=$vval;
                        }else
                        {
                            $arr[$model]=$vval;
                        }                        
                    }
                }
            }
        }
        return $arr;
    }
    /**
     * Returns array with available font awesome icons
     * 
     * returns array
     */
    function getAvaliableIconsForForm()
    {
        $arr=[];
        $icons= json_decode(file_get_contents(parsePath('@vendor/fontawesome/icons.json',TRUE)),TRUE);
        
        foreach($icons as $icon)
        {
            $arr[$icon]= $icon;
        }
        return $arr;
    }
    
    /**
     * Returns array with predefined toolbar buttons
     * 
     * @param string $name
     * @param array $buttons
     * 
     * @return array
     */
    function getButtonsForToolbar(string $name,array $buttons=[])
    {
        $name= strtolower($name);
        $name=$this->get('buttons.buttons_'.$name,TRUE,'value',FALSE);
        foreach(is_array($name) ? $name : [] as $key=>$button)
        {
            if (is_array($button))
            {
                $buttons[$key]= \EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton::createFromArray($button);  
            }
            
        }
        ksort($buttons);
        return $buttons;
    }
    
    /**
     * Add new predefined toolbar button
     * 
     * @param string $name
     * @param array  $args
     * @param string $tooltip
     * 
     * @return boolean
     */
    function addNewToolbarButton($name,array $args=[], string $tooltip = '')
    {
       $name= 'buttons_'.strtolower($name);
       return $this->add('buttons',$name, json_encode($args), 'textlong', $tooltip);
    }
}

?>