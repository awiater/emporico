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
namespace EMPORIKO\Models\System;

use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\Arrays as Arr;

class ContactModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='contacts';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'ctid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['ctid','ct_name','ct_email','ct_desc','ct_account','ct_phone','ct_phone2','ct_phone3',
                              'ct_linkid','ct_faceb','ct_other','ct_notes','ct_group'];
    
    protected $validationRules =
    [
        'ct_name'=>'required|is_unique[contacts.ct_name,ctid,{ctid}]',
        'ct_email'=>'is_unique[contacts.ct_email,ctid,{ctid}]',
    ];
    
    protected $validationMessages = 
    [
        'ct_name'=>
            [
                'is_unique'=>'connections.error_unique_ct_name',
                'required'=>'connections.error_required_ct_name'
            ],
        'ct_email'=>
            [
                'is_unique'=>'connections.error_unique_ct_email'
            ]
    ];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'ctid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
	'ct_name'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
        'ct_email'=>            ['type'=>'VARCHAR','constraint'=>'150','default'=>1,'null'=>TRUE],
        'ct_desc'=>   		['type'=>'TEXT','null'=>TRUE],
        'ct_account'=>		['type'=>'VARCHAR','constraint'=>'25','default'=>1,'null'=>TRUE],
        'ct_phone'=>   		['type'=>'VARCHAR','constraint'=>'25','default'=>1,'null'=>TRUE],
        'ct_phone2'=>           ['type'=>'VARCHAR','constraint'=>'25','default'=>1,'null'=>TRUE],
        'ct_phone3'=>           ['type'=>'VARCHAR','constraint'=>'25','default'=>1,'null'=>TRUE],
        'ct_group'=>            ['type'=>'VARCHAR','constraint'=>'150','default'=>1,'null'=>TRUE],
        'ct_linkid'=>           ['type'=>'TEXT','null'=>TRUE],
        'ct_faceb'=>            ['type'=>'TEXT','null'=>TRUE],
        'ct_other'=>            ['type'=>'TEXT','null'=>TRUE],
        'ct_notes'=>            ['type'=>'TEXT','null'=>TRUE],
    ];
    
    function getFieldsForForm(array $record) 
    {
        $arr=parent::getFieldsForForm($record);
        
        foreach(['ct_name','ct_desc'] as $key)
        {
            $arr[$key]->setTab('general');
        }
        
        foreach(['ct_desc','ct_notes'] as $key)
        {
            $arr[$key]=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('TextAreaField',$arr[$key]);
        }
        
        foreach(['ct_email','ct_phone','ct_phone2','ct_phone3','ct_other'] as $key)
        {
            $arr[$key]->setTab('cttab');
        }
        $arr['ct_name']->addArg('data-validate','cttab');
        $arr['ct_email']->addArg('data-validate','cttab')->addArg('type','email');
        foreach(['ct_linkid','ct_faceb'] as $key)
        {
            $arr[$key]->setTab('soctab');
        }
        
        unset($arr['ct_group']);
        
        $arr['ct_notes']->setTab('othtab');
        $arr['ct_account']= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('CustomElementsListField',$arr['ct_account'])
                ->setTab('custab');
        return $arr;
    }
    
    
    
    /**
     * Returns array with contacts groups
     * 
     * return array
     */
    function getContactGroups()
    {
        $arr=[];
        foreach($this->groupby('ct_group')->find() as $record)
        {
            $arr[$record['ct_group']]=$record['ct_group'];
        }
        return $arr;
    }
    
    /**
     * Returns array with accounts codes (customer codes) for given email addresses
     * 
     * @param array $emails
     * 
     * @return array
     */
    function getAccountsFromEmails(array $emails)
    {
        $data=['enabled'=>1,'ct_email In'=>$emails];
        $data=$this->filtered($data)->find();
        $arr=[];
        foreach(is_array($data) ? $data : [] as $rec)
        {
            $rec['ct_account']=Arr::fromString($rec['ct_account']);
            if (is_array($rec['ct_account']) && count($rec['ct_account']) > 0)
            {
                $arr= array_merge($arr, array_combine($rec['ct_account'], $rec['ct_account']));
            }
            
        }
        return $arr;
    }
    
    /**
     * Returns array with accounts emails
     * 
     * @param bool $flat
     * @param bool $encrypted
     * 
     * @return array
     */
    function getAccountsEmails(bool $flat=FALSE,bool $encrypted=FALSE)
    {
        $data=['enabled'=>1];
        $data=$this->filtered($data)->find();
        $arr=[];
        foreach(is_array($data) ? $data : [] as $rec)
        {
           $rec['ct_account']=Arr::fromString($rec['ct_account']);
           foreach(is_array($rec['ct_account']) ? $rec['ct_account'] : [] as $acc)
           {
               if (!array_key_exists($acc, $arr))
               {
                   $arr[$acc]=[];
               }
               $arr[$acc][]=['ct_name'=>$rec['ct_name'].' ('.$acc.')','ct_email'=>$rec['ct_email']];
           }
        }
        if ($flat)
        {
            foreach ($arr as $key=>$val)
            {
                $arr[$key]= json_encode($val);
                if ($encrypted)
                {
                    $arr[$key]= base64_encode($arr[$key]);
                }
            }
        }
        return $arr;
    }
    
    /**
     * Returns array with all contacts for given account code
     * 
     * @param string $acc
     * @param string $field
     * @param string $key
     * 
     * @return array
     */
    function getByAcc(string $acc,string $field=null,string $key=null)
    {
        
        $data=$this->filtered(strlen($acc) > 2 ? ['ct_account %'=>$acc] : [])->find();
        $arr=[];
        if (count($data) > 0 && array_key_exists($field, $data[0]))
        {
            foreach($data as $rec)
            {
                if (array_key_exists($key, $rec))
                {
                    $arr[$rec[$key]]=$rec[$field];
                }else
                {
                    $arr[]=$rec[$field];
                }
                
            }
            return $arr;
        }
        return $data;
    }
    
    /**
     * Returns array with email addresses by account
     * 
     * @param   type $acc
     * @param   type $chunksize
     * 
     * @return  array
     */
    function getEmailsByAcc($acc,$chunksize=1)
    {
        $arr=[];
        $current=1;
        $chunk=1;
        foreach($this->getByAcc($acc) as $record)
        {
            if ($chunksize>1)
            {
                if ($current > $chunksize)
                {
                    $chunk++;
                    $current=1;
                }
                $arr[$chunk][]=$record['ct_email'];
                $current++;
            } else 
            {
                $arr[]=$record['ct_email'];
            }
            
        }
        return $arr;
    }
    
    /**
     * Returns array with contacts accounts
     * 
     * @param string $type
     * 
     * @return array
     */
    function getAccountList($type=null)
    {
        $filters=[];
        if ($type!=null && in_array(strtolower($type), ['cust','sup']))
        {
            $filters['type']=strtolower($type);
        }
        $arr=[];
        foreach($this->getView('vw_contacts_accounts')->filtered($filters)->orderby('text')->find() as $record)
        {
            $arr[$record['code']]=$record['text'];
        }
        return $arr;
    }
    
    /**
     * Return array with emails and account details for campaign target edit form
     * 
     * @return array
     */
    function getEmailsForTargetForm()
    {
        $arr=[];
        foreach($this->find() as $record)
        {
            $record['ct_account']= json_decode($record['ct_account'],TRUE);
            if (is_array($record['ct_account']))
            {
                foreach($record['ct_account'] as $acc)
                {
                   $arr[$record['ct_email']]=$acc.' - '.$record['ct_name'];
                }
                
            }
            asort($arr);
        }
        return $arr;
    }
    
    /**
     * Returns array with user emails (and mailboxes) for form
     * 
     * @param bool $addMailboxes
     * 
     * @return array
     */
    function getSystemUsersEmailsForForm(bool $addMailboxes=TRUE)
    {
        $groups=
        [
            lang('emails.mailbox_aval'),
            lang('emails.mail_aval_users')
        ];
        $arr=[];
        if ($addMailboxes)
        {
            $arr[$groups[0]]=$this->getModel('Emails/Mailbox')->getDropdDownField('emm_inuser','emm_inuser');
            asort($arr[$groups[0]]);
        }
        $arr[$groups[1]]=[];
        foreach($this->getModel('Auth/User')->filtered(['iscustomer'=>0,'enabled'=>1])->find() as $record)
        {
            $arr[$groups[1]][$record['email']]=$record['name'].'=>'.$record['email'];
        }
        asort($arr[$groups[1]]);
        return $arr;
    }
}
/* Views
 * 
 * vw_customers_contacs
 * SELECT 
`ct`.`ctid`, 
`ct`.`ct_name`, 
`ct`.`ct_email`, 
`ct`.`ct_desc`, 
`cus`.code as `ct_account`,
`cus`.name as `ct_account_name`,
`ct`.`ct_group`, 
`ct`.`ct_phone`, 
`ct`.`ct_phone2`, 
`ct`.`ct_phone3`, 
`ct`.`ct_linkid`, 
`ct`.`ct_faceb`, 
`ct`.`ct_other`, 
`ct`.`ct_notes` 
FROM `contacts` as `ct`
RIGHT JOIN `customers` as `cus` ON `ct`.ct_account LIKE CONCAT('%',`cus`.code,'%')
ORDER BY `cus`.code,`ct`.`ct_name`
 * 
 * 
 * vw_suppliers_contacs
 * SELECT * FROM (SELECT 
`ct`.`ctid`, 
`ct`.`ct_name`, 
`ct`.`ct_email`, 
`ct`.`ct_desc`, 
`sup`.sup_code as `ct_account`,
`sup`.`sup_name` as `ct_account_sname`,
`ct`.`ct_group`, 
`ct`.`ct_phone`, 
`ct`.`ct_phone2`, 
`ct`.`ct_phone3`, 
`ct`.`ct_linkid`, 
`ct`.`ct_faceb`, 
`ct`.`ct_other`, 
`ct`.`ct_notes` 
FROM `contacts` as `ct`
RIGHT JOIN `products_suppliers` as `sup` ON `ct`.ct_account LIKE CONCAT('%',`sup`.`sup_code`,'%')
ORDER BY `sup`.sup_code,`ct`.`ct_name`
) as `ct` WHERE length(`ct`.`ct_name`) > 0
 * 
 */