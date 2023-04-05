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

class Autopart extends BaseController
{
    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = 
    [
        'sendOrder'=>           AccessLevel::view,
    ];


    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = [];

    /**
     * Determines if authentication is enabled
     * @var bool
     */
    private $_noauth = FALSE;

    /**
     * Array with function names and linked models names
     */
    public $assocModels =
    [
        'emails'=>'Emails/Email',
        'orders'=>'Orders/Order',
    ];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actual function name)
     */
    public $remaps =[
        'index'=>'settings',
    ];
    
    /**
     * Array with function names which are excluded from routes actions
     * @var Array
     */
    protected $routerexlude = [
        'sendOrder',
        //'getQuotes'
    ];

    
    public function sendOrder(string $buyer,string $buyerorder,string $supporder,array $orderlines,string $desc=null)
    {
        \EMPORIKO\Libraries\AutopartAPI\AutopartOrder::init()
                    ->setBuyer($buyer)
                    ->setBuyerOrder($buyerorder)
                    ->setSupplierOrder($supporder)
                    ->addOrderLines($orderlines)
                    ->setSpecialInstruct($desc==null ? '' : $desc)
                    ->saveAsBundle($this->getSettings('autopart_folder_orders'),FALSE);
    }
    
    function scheduler()
    {
        $jobs= json_decode($this->model_Settings->get('autopart.autopart_scheduler_items'),TRUE);
        if (is_array($jobs))
        {
            //$this->addLog(TRUE,'sched','START');
            if (in_array('getQuotesFromEmails',$jobs))
            {
                $this->getQuotesFromEmails(FALSE);
            }
            if (in_array('getQuotes',$jobs))
            {
                $this->getQuotes();
            }
            if (in_array('getOrders',$jobs))
            {
                $this->getOrders();
            }
            if (in_array('getPriceFiles',$jobs))
            {
                $this->getPriceFiles();
            }
        }else
        {
            $this->addLog(FALSE,'sched','NO JOBS ENABLED');
        }
    }
    
    function getPriceFiles()
    {
        $settings=$this->getSettings();
        foreach(directory_map(parsePath($settings['autopart_folder_pricing'],TRUE)) as $file)
        {
            $file=parsePath($settings['autopart_folder_pricing'].'/'.$file,TRUE);
            $name=pathinfo($file);
            $name['filename']=Str::contains($name['filename'], '_') ? Str::before($name['filename'], '_') : $name['filename'];
            $name['status']=$this->model_Products_PriceFilePart->updateFromFile($name['filename'],$file,$settings['autopart_pricing_iploadtplmode'],'',$settings['autopart_pricing_iploadtpl']);
            $this->addLog(TRUE,'pricefile',$name['filename']);
        }
    }
    
    function getQuotesFromEmails(bool $processQuotes=TRUE,array $filters=[])//https://portal.apdcw.co.uk/autopart/getQuotesFromEmails
    {
        if (count($filters) < 1)
        {
            $filters=
            [
                'mail_subject %'=>'Quote',
                'mail_from'=>'ledgermail@apdcw.co.uk',
                '( mail_attachements %'=>'.HTM',
                '|| mail_attachements % )'=>'.PDF',
                'mail_read'=>0
            ];
        }else 
        if (!array_key_exists('mail_attachements %', $filters))
        {
            $filters['mail_attachements %']='.HTM';
        }
        
        
        $emails=$this->model_Emails->filtered($filters)->find();
        
        if (!is_array($emails) || (is_array($emails) && count($emails) < 1))
        {
            return FALSE;
        }
        $arr=[];
        foreach($emails as $email)
        {
            if (Arr::KeysExists(['mail_attachements','mail_mailbox','mail_folder','mail_msgid'], $email))
            {
                $email['mail_attachements']= json_decode($email['mail_attachements'],TRUE);
                $r_date=$email['mail_rec'];
                if (is_array($email['mail_attachements']) && count($email['mail_attachements']) > 0&& array_key_exists('name', $email['mail_attachements'][0]))
                {
                    $id=$email['emid'];
                    $path=$this->model_Settings->get('autopart.autopart_folder_quotes');
                    $path=parsePath($path.'/'.$email['mail_attachements'][0]['name'],TRUE);
                    $email['mail_mailbox']=$this->model_Emails_Mailbox->getMailbox($email['mail_mailbox']);
                    $email=$email['mail_mailbox']->getClient($email['mail_folder'])->getMail($email['mail_msgid'])->getAttachments();
                    if (is_array($email) && count($email) > 0)
                    {
                        $email= array_values($email);
                        $email=$email[0];
                        if (is_a($email, '\PhpImap\IncomingMailAttachment'))
                        {
                            $email->setFilePath($path);
                            $email->saveToDisk();
                            $this->model_Emails->save(['emid'=>$id,'mail_read'=>1]);
                        } 
                    }
                    
                    if (Str::endsWith(strtolower($email->name), '.pdf') && file_exists($path))
                    {
                        $ref= Str::before(basename($path), '.');
                        $model_Docs=$this->model_Documents_Document;
                        $model_Docs->storeDocument($path,'quotes',basename($path),'autopart',['folder'=>'files','type'=>'onedrive']);
                        $arr[$ref]=
                        [
                            'ord_ref'=>$ref,
                            'ord_refcus'=>$ref,
                            'ord_addon'=> $r_date,
                            'ord_addby'=>'autopart',
                            'enabled'=>0,
                            'ord_type'=>1,
                            'ord_cusacc'=>'XXX999',
                            'ord_prdsource'=>Str::before(basename($path), '.')
                        ];
                        if (file_exists($path))
                        {
                            unlink($path);
                        }
                    }
                }
            }
        }
        if (is_array($arr) && count($arr) > 0)
        {
            foreach($arr as $line)
            {
                if ($this->model_Orders_Order->count(['ord_ref'=>$line['ord_ref']]) < 1)
                {
                    $this->model_Orders_Order->builder()->set($line)->insert();
                }
            }
        }
        if ($processQuotes)
        {
            $this->getQuotes();
        }
        return TRUE;
    }
    
    function getQuotes()
    {
        $path=$this->getSettings('autopart_folder_quotes');
        if (!is_string($path))
        {
            return ['error'=>'Invalid quote folder path'];
        }
        $path=parsePath($path,TRUE);
        $arr=[];
        $settings=$this->getSettings();
        foreach(directory_map($path) as $file)
        {
            $fileName= parsePath($path.'/'.$file,TRUE);
            if (Str::endsWith(strtolower($fileName), '.pdf'))
            {
                $this->model_Documents_Document->storeDocument($fileName,'quotes');
            }else
            if (Str::endsWith(strtolower($fileName), '.htm'))
            {
                $html =new \HtmlParser\ParserDom(file_get_contents($fileName));
                $ref=$html->getNodeValue('span.f24_');
                $this->model_Documents_Document->changeFolder($ref,$html->getNodeValue('span.f45_'));
                $arr[$ref]=
                [
                    'ord_ref'=>$ref,
                    'ord_refcus'=>$html->getNodeValue('span.f47_'),
                    'ord_addon'=> convertDate($html->getNodeValue('span.f40_'), 'd/m/y', 'db'),
                    'ord_addby'=>'autopart',
                    'enabled'=>1,
                    'ord_type'=>1,
                    'ord_cusacc'=>$html->getNodeValue('span.f45_'),
                    '_lines'=>[]
                ];
                $oport=$this->model_Orders->filtered(['ord_refcus'=>$arr[$ref]['ord_refcus'],'ord_type'=>0])->first();
                if (is_array($oport) && count($oport) > 0)
                {
                    $arr[$ref]['ord_source']='oport';
                    $arr[$ref]['ord_source_ref']=$oport['ord_ref'];
                }
                foreach($html->find('div.s0_') as $node)
                {
                    $line=[];
                    $arr[$ref]['_lines'][]=
                    [
                        'ol_ref'=>$ref,
                        'ol_oepart'=>$node->getNodeValue('span.f2_'),
                        'ol_qty'=>$node->getNodeValue('span.f4_'),
                        'ol_price'=>$node->getNodeValue('span.f5_'),
                        'ol_ourpart'=>$node->getNodeValue('span.f2_'),
                        'ol_partbrand'=>$node->getNodeValue('span.f1_'),
                        'enabled'=>1
                    ];
                }
            }else
            if (Str::endsWith(strtolower($fileName), '.csv'))
            {
                $file = fopen($fileName, 'r');
                while (($line = fgetcsv($file)) !== FALSE)
                {
                    if (!array_key_exists($line[2], $arr))
                    {
                        $arr[$line[2]]=
                            [
                                'ord_ref'=>$line[$settings['autopart_quotefilecol_ord_ref']],
                                'ord_refcus'=>$line[$settings['autopart_quotefilecol_ord_refcus']],//
                                'ord_addon'=>$line[$settings['autopart_quotefilecol_ord_addon']].'0000',//
                                'ord_addby'=>'autopart',
                                'ord_status'=>$settings['autopart_quotefilecol_ord_status'],
                                'ord_cusacc'=>$line[$settings['autopart_quotefilecol_ord_cusacc']],
                                'ord_isquote'=>1,
                                'ord_source'=>'autopart',
                                'enabled'=>1,
                                '_lines'=>[]
                            ];
                    }
                    $arr[$line[2]]['_lines'][]=
                    [
                        'ol_ref'=>$line[$settings['autopart_quotefilecol_ord_ref']],
                        'ol_oepart'=>$line[$settings['autopart_quotefilecol_ol_oepart']],
                        'ol_qty'=>$line[$settings['autopart_quotefilecol_ol_qty']],
                        'ol_price'=>$line[$settings['autopart_quotefilecol_ol_price']],
                        'ol_cusacc'=>$line[$settings['autopart_quotefilecol_ord_cusacc']],
                        'ol_ourpart'=>$line[$settings['autopart_quotefilecol_ol_ourpart']],
                        'ol_commodity'=>$line[$settings['autopart_quotefilecol_ol_commodity']],
                        'ol_origin'=>$line[$settings['autopart_quotefilecol_ol_origin']],
                        'enabled'=>1
                    ];
                    end_loop:
                }
                fclose($file);
                $arr= array_values($arr);
                unset($arr[0]);
            }
            if (file_exists($fileName))
            {
                unlink($fileName);
            }
        }
        
        foreach($arr as $line)
        {
            if ($this->model_Orders_Order->count(['ord_ref'=>$line['ord_ref']]) < 1)
            {
                    $lines=$line['_lines'];
                    unset($line['_lines']);
                    $this->model_Orders_Order->builder()->set($line)->insert();
                    $this->model_Orders_OrderLine->builder()->insertBatch($lines);
                    $this->addLog(TRUE,'quote',$line['ord_ref']);
            }
        }
        
    }
    
    function getOrders()
    {
        $path=$this->getSettings('autopart_folder_orders_status');
        if (!is_string($path))
        {
            return ['error'=>'Invalid order folder path'];
        }
        
        $path=parsePath($path,TRUE);
        $arr=[];
        $settings=$this->getSettings();
        //
        foreach(directory_map($path) as $file)
        {
            $fileName= parsePath($path.'/'.$file,TRUE);
            
            if (Str::endsWith(strtolower($fileName), '.csv'))
            {
                $file = fopen($fileName, 'r');
                while (($line = fgetcsv($file)) !== FALSE)
                {
                    $ref=$settings['autopart_orderfilecol_ord_reftpl'];
                    $line_status=$settings['autopart_orderfilecol_ol_statustpl'];
                    foreach($settings as $key=>$value)
                    {
                        if (array_key_exists($settings[$key], $line))
                        {
                            $ref= str_replace('{'.str_replace('autopart_orderfilecol_', '', $key).'}', $line[$settings[$key]], $ref);
                            $line_status= str_replace('{'.str_replace('autopart_orderfilecol_', '', $key).'}', $line[$settings[$key]], $line_status);
                        }
                        $settings_tpl[$key]=$value;
                    }
                    $ref=$line[$settings['autopart_orderfilecol_ord_refcus']];
                    if (!array_key_exists($ref, $arr))
                    {
                        $arr[$ref]=
                            [
                                'ord_ref'=>'CRM'.$ref,
                                'ord_refcus'=>$line[$settings['autopart_orderfilecol_ord_refcus']],//
                                'ord_addon'=>$line[$settings['autopart_orderfilecol_ord_addon']].'0000',//
                                'ord_addby'=>'autopart',
                                'ord_status'=>$settings['autopart_orderfilecol_ord_status'],
                                'ord_cusacc'=>$line[$settings['autopart_orderfilecol_ord_cusacc']],
                                'ord_isquote'=>0,
                                'ord_source'=>'autopart',
                                'enabled'=>1,
                                '_lines'=>[]
                            ];
                    }
                    $arr[$ref]['_lines'][]=
                    [
                        'ol_ref'=>'CRM'.$ref,
                        'ol_oepart'=>$line[$settings['autopart_orderfilecol_ol_oepart']],
                        'ol_qty'=>$line[$settings['autopart_orderfilecol_ol_qty']],
                        'ol_price'=>$line[$settings['autopart_orderfilecol_ol_price']],
                        'ol_cusacc'=>$line[$settings['autopart_orderfilecol_ord_cusacc']],
                        'ol_ourpart'=>$line[$settings['autopart_orderfilecol_ol_ourpart']],
                        'ol_commodity'=>$line[$settings['autopart_orderfilecol_ol_commodity']],
                        'ol_origin'=>$line[$settings['autopart_orderfilecol_ol_origin']],
                        'ol_status'=>$line_status,
                        'ol_avalqty'=>$line[$settings['autopart_orderfilecol_ol_avalqty']],
                        'enabled'=>1
                    ];
                    end_loop:
                }
                fclose($file);
                $arr= array_values($arr);
                unset($arr[0]);
            }
            
            if (file_exists($fileName))
            {
                unlink($fileName);
            }
        }
        
        foreach($arr as $line)
        {
            if ($this->model_Orders_Order->count(['ord_ref'=>$line['ord_ref']]) < 1)
            {
                    $lines=$line['_lines'];
                    unset($line['_lines']);
                    $this->model_Orders_Order->builder()->set($line)->insert();
                    $this->model_Orders_OrderLine->builder()->insertBatch($lines);
                    $this->addLog(TRUE,'order',$line['ord_ref']);
            }else
            {
                foreach($line['_lines'] as $line_record)
                {
                    $this->model_Orders_OrderLine->builder()
                            ->set(['ol_status'=>$line_record['ol_status'],'ol_avalqty'=>$line_record['ol_avalqty']])
                            ->where('ol_ref',$line_record['ol_ref'])
                            ->where('ol_ourpart',$line_record['ol_ourpart'])
                            ->update();
                    $this->addLog(TRUE,'order',$line_record['ol_ref']);
                }
            }
        }
        
    }
    
    function settings($tab,$record)
    {
        
        $settings=$this->model_Settings->get('autopart.*',FALSE,'*');
        $view=new Pages\FormView($this);
        $fields=['ord_ref','ord_refcus','ord_addon','ord_cusacc','ol_oepart','ol_qty','ol_price','ol_ourpart','ol_commodity','ol_origin','ol_avalqty','ol_status'];
        if ($tab=='log')
        {
            $view->addMovementsDataField('','movements',null,'autopart',['view'=>'table','columns'=>['mhdate'=>'system.movements.mhdate','mhfrom'=>'autopart.movementlog_colfrom','mhref'=>'autopart.movementlog_colcomm','mhinfo'=>'system.movements.mhref'],'date_format'=>'d/m/Y H:i','addlog'=>TRUE]);
        }else
        if ($tab=='fld')
        {
            $view->addElementsListBoxField('autopart.settings_scheduler_items', 'settings[autopart_scheduler_items]', $settings['autopart_scheduler_items']['value'],['input_field'=>$this->getJobNames()]);
            $view->addInputField('autopart.settings_folder_quotes', 'settings[autopart_folder_quotes]', $settings['autopart_folder_quotes']['value']);
            $view->addInputField('autopart.settings_folder_orders', 'settings[autopart_folder_orders]', $settings['autopart_folder_orders']['value']);
            $view->addInputField('autopart.settings_folder_orders_status', 'settings[autopart_folder_orders_status]', $settings['autopart_folder_orders_status']['value']);
            $view->addInputField('autopart.settings_folder_pricing', 'settings[autopart_folder_pricing]', $settings['autopart_folder_pricing']['value']);
            $view->addDropDownField('autopart.settings_pricing_iploadtpl', 'settings[autopart_pricing_iploadtpl]', $this->model_Settings->getUploadDrivers('*',TRUE), $settings['autopart_pricing_iploadtpl']['value'], ['advanced'=>TRUE]);
            $view->addDropDownField('products.import_obsolete','settings[autopart_pricing_iploadtplmode]',$this->model_Products_PriceFile->getUploadModes(),$settings['autopart_pricing_iploadtplmode']['value'],['advanced'=>TRUE]);
            $view->addEmailField('autopart.settings_emails_from', 'settings[autopart_emails_from]', $settings['autopart_emails_from']['value']);
        } else 
        if ($tab=='quotereport')
        {
            $view->addCustomTextField('', 'quotereport_label','<p>'.lang('autopart.settings_quotereport_label').'</p>');
            foreach($fields as $field)
            {
                if (array_key_exists('autopart_quotefilecol_'.$field, $settings))
                {
                    $view->addNumberField('autopart.settings_quotefilecol_'.$field, $settings['autopart_quotefilecol_'.$field]['value'], 'settings[autopart_quotefilecol_'.$field.']',50, 0, []); 
                }
            }
            $view->addDropDownField('autopart.settings_quotefilecol_ord_status', 'settings[autopart_quotefilecol_ord_status]', $this->model_Orders_Order->getAvaliableStatuses(), $settings['autopart_quotefilecol_ord_status']['value']);           
        }else 
        if ($tab=='quotehtm')
        {
            $view->addCustomTextField('', 'quotehtm_label','<p>'.lang('autopart.settings_quotehtm_label').'</p>');
            foreach($fields as $field)
            {
                if (array_key_exists('autopart_quotehtmcol_'.$field, $settings))
                {
                    $view->addInputField('autopart.settings_quotefilecol_'.$field, 'settings[autopart_quotehtmcol_'.$field.']', $settings['autopart_quotehtmcol_'.$field]['value']);
                }
            }
            $view->addDropDownField('autopart.settings_quotefilecol_ord_status', 'settings[autopart_quotehtmcol_ord_status]', $this->model_Orders_Order->getAvaliableStatuses(), $settings['autopart_quotehtmcol_ord_status']['value']);           
        }else
        if ($tab=='orderfile')
        {
            $view->addCustomTextField('', 'orderfile_label','<p>'.lang('autopart.settings_quotereport_label').'</p>');
            $view->addInputField('autopart.settings_orderfilecol_ord_reftpl', 'settings[autopart_orderfilecol_ord_reftpl]', $settings['autopart_orderfilecol_ord_reftpl']['value']);
            $view->addInputField('autopart.settings_orderfilecol_ol_statustpl', 'settings[autopart_orderfilecol_ol_statustpl]', $settings['autopart_orderfilecol_ol_statustpl']['value']);
            foreach($fields as $field)
            {
                if (array_key_exists('autopart_orderfilecol_'.$field, $settings))
                {
                    $view->addNumberField('autopart.settings_quotefilecol_'.$field, $settings['autopart_orderfilecol_'.$field]['value'], 'settings[autopart_orderfilecol_'.$field.']',50, 0, []); 
                }
            }
            $view->addDropDownField('autopart.settings_quotefilecol_ord_status', 'settings[autopart_orderfilecol_ord_status]', $this->model_Orders_Order->getAvaliableStatuses(), $settings['autopart_orderfilecol_ord_status']['value']);           
        }
        
        return view('System/form_fields',$view->getViewData());
        
        
    }
    
    private function getJobNames()
    {
        return 
        [
            'getQuotesFromEmails'=>lang('autopart.settings_job_quotesemails'),
            'getQuotes'=>lang('autopart.settings_job_quotes'),
            'getOrders'=>lang('autopart.settings_job_orders'),
            'getPriceFiles'=>lang('autopart.settings_job_pricefiles'),
        ];
    }
    
    private function getSettings(string $setting='all')
    {
        $setting= strtolower($setting)=='all' ? '*' : $setting;
        return $this->model_Settings->get('autopart.'.$setting);
    }
    
    private function addLog(bool $status,string $ref,string $info)
    {
        switch($ref)
        {
            case 'pricefile': $ref='autopart.movementlog_pricefile';break;
            case 'quote': $ref='autopart.movementlog_quotes';break;
            case 'order': $ref='autopart.movementlog_orders';break;
            case 'sched': $ref='autopart.movementlog_sched';break;
        }
        $this->addMovementHistory('autopart', lang($status?'system.general.success':'system.general.failed'), null, $ref,$info);
    }
    
}