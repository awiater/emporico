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
 
namespace EMPORIKO\Models\Customers;

use EMPORIKO\Helpers\Strings as Str;

class CustomerModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='customers';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'cid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['code','name','emails','phones','address_pay','address_ship','employee',
                                    'type','group','terms_pay','terms_inco','terms_credit','terms_delco',
                                    'terms_buyco','terms_area','terms_price','terms_curr','notes','enabled'];
	
	protected $validationRules =
	 [
	 	'code'=>'required|is_unique[customers.code,cid,{cid}]',
	 	'enabled'=>'required',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'cid'=>			['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'code'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'emails'=>		['type'=>'TEXT','null'=>FALSE],
                'phones'=>		['type'=>'TEXT','null'=>FALSE],
		'address_pay'=>		['type'=>'TEXT','null'=>TRUE],
                'address_ship'=>        ['type'=>'TEXT','null'=>TRUE],
		'employee'=>            ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'type'=>                ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'group'=>               ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_pay'=>           ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_inco'=>          ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_credit'=>        ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_delco'=>         ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_buyco'=>         ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_area'=>          ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'terms_price'=>         ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
                'notes'=>               ['type'=>'TEXT','null'=>TRUE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
        
        function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
        {
            $orderby=$orderby==$this->primaryKey ? 'name' : $orderby;
            return parent::filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
        }
        
        /**
         * Returns array with settings
         * 
         * @param string $setting
         * 
         * @return array
         */
        function getSettings(string $setting='*')
        {
            $arr=$this->getModel('Settings')->get('customers.'.$setting,TRUE);
            return is_array($arr) && count($arr) > 0 ? $arr : ($setting=='*' ? [] : $arr); 
        }
        
        /**
         * Returns array with available fields which could be linked with emails
         * 
         * @return type
         */
        function getFieldsForLink()
        {
            return ['ct_name'=>lang('connections.ct_name'),'ct_email'=>lang('connections.ct_email'),'ct_other'=>lang('connections.ct_other')];
        }
        
        /**
         * Get activity data for customer
         * 
         * @param string $customer
         * 
         * @return array
         */
        function getCustomerMovements($customer)
        {
            $data=$this->getView('vw_movements')
                       ->orderBy('mhdate DESC')
                       ->limit(10)
                       ->filtered(['mhref'=>$customer])->find();
            return $data;
        }
        
        /**
         * Returns array with customer data for logged user
         * 
         * @param string $field
         * 
         * @return array|string
         */
        function getCustomerForLogedUser(string $field='code')
        {
            return $this->getCustomerForUser(0,$field);
        }
        
        /**
         * Returns array with customer data for given user (logged user if null)
         * 
         * @param Int $userid
         * @param type $field
         * 
         * @return array|string
         */
        function getCustomerForUser(Int $userid=null,string $field=null)
        {
            if ($userid==null || $userid==0)
            {
                $userid= loged_user('customer');
            }else
            {
                $userid=$this->getModel('Auth/User')->find($userid);
                if (is_array($userid))
                {
                    $userid=$userid['customer'];
                }else
                {
                    return null;
                }
            }
            
            $userid=$this->getView('vw_customers_info')->filtered(['cid'=>$userid])->first();
            if (is_array($userid))
            {
                if (array_key_exists($field, $userid))
                {
                    return $userid[$field];
                }
                return $userid;
            }
            return null;
        }
        
        /**
         * Get array with customers data for drop down list
         * 
         * @param  string $field
         * @param  string $emptyValue
         * @param  bool   $basic
         * @param  array  $filters
         * @return array
         */
        function getCustomersForDropDown($field=null,$emptyValue=null,$basic=FALSE,array $filters=[])
        {
            $field=$field==null?$this->primaryKey:$field;
            $field=in_array($field, $this->allowedFields)?$field:$this->primaryKey;
            $emptyValue=$emptyValue==null ? '':$emptyValue;
            if ($emptyValue!=FALSE)
            {
                if ($basic)
                {
                    $result=['0'=>$emptyValue];
                } else 
                {
                    $emptyValue=['text'=>$emptyValue,'value'=>'0'];
                    $result=['0'=>$emptyValue];
                }
            }
            $sql=$this;
            $sql=$sql->filtered($filters,'name',FALSE);
            foreach ($sql->find() as $record) 
            {
                if ($basic)
                {
                    $result[$record[$field]]=$record['name'].' - '.$record['code'];
                } else 
                {
                   $result[$record[$field]]=['text'=>$record['name'].' - '.$record['code'],'value'=>$record[$field]]; 
                }
                
            }
            return $result;
        }
        
        function getContactsForCustomer($acc)
        {
            if (is_numeric($acc))
            {
                $acc=$this->find($acc);
                if (!is_array($acc))
                {
                    return [];
                }
                $acc=$acc['code'];
            }
            return $this->getModel('System/Contact')->getByAcc($acc,'ct_email');
            
        }
        
        function getEmailAdressesForCustomer(string $acc)
        {
            if ($acc==null || strlen($acc) < 2)
            {
                $acc=$this->getCustomerForUser(0,'code');
            }
            return $this->getModel('System/Contact')->getByAcc('','ct_email');
        }
        
        /**
         * Returns array with customers codes linked to given contact
         * 
         * @param mixed $contactID
         * 
         * @return array|null
         */
        function getCustomersLinkedToContact($contactID)
        {
            $arr=[];
            if (is_numeric($contactID))
            {
                $contactID=['ctid'=>$contactID];
            }else
            if (is_string($contactID) && Str::isValidEmail($contactID))
            {
                $contactID=['ct_email'=>$contactID];
            }else
            if (is_string($contactID))
            {
                $contactID=['ct_name'=>$contactID];
            }else
            {
                return null;
            }
            $contactID=$this->getModel('System/Contact')->filtered($contactID)->first();
            if (is_array($contactID) && array_key_exists('ct_account', $contactID))
            {
                $contactID=json_decode($contactID['ct_account'],TRUE);
                return is_array($contactID) ? $contactID : null;
            }
            return null;
        }
        
       /**
        * Returns array with emails data filtered by given customer email addresses
        * 
        * @param  string $acc
        * @param  bool   $enabled
        * @param  Int   $limit
        * @param  bool   $raw
        * @return array
        */
        function getEmailsForCustomer($acc,$enabled=1,$limit=5,$raw=FALSE)
        {
            $field=$this->getModel('Settings')->get('customers.customers_custlinkedfield');
            $acc=$this->getModel('System/Contact')->getByAcc($acc,$field);
            
            if (is_array($acc) && count($acc) > 0)
            {
                if ($raw)
                {
                    $client=$this->getModel('Emails/Email')->getClient('default');
                    $client->selectFolder('Completed');
                    $client->useGetMessageHeaders();
                    $arr=[];
                    foreach($acc as $email)
                    {
                        try 
                        {
                            $arr=$arr+$client->getMessagesByCriteria('FROM '.$email); 
                        } catch (\sergey144010\ImapClient\ImapClientException $ex){}             
                    }
                }else
                {
                    return $this->getView('vw_emails')->filtered(['mail_from In'=>$acc],'mail_rec DESC',$limit);
                }
            
        }
        return [];
    }
        
        /**
         * Returns qty of unread emails for account
         * 
         * @param  string $acc
         * @return int
         */
        function getUnseenEmailsQtyForCustomer($acc)
        {
            $field=$this->getModel('Settings')->get('customers.customers_custlinkedfield');
            $acc=$this->getModel('System/Contact')->getByAcc($acc,$field);
            if (is_array($acc) && count($acc) > 0)
            {
                return $this->getModel('Emails/Email')->count(['mail_from In'=>$acc,'enabled'=>1,'mail_read'=>0]);
            }
            return 0;
        }
        
        /**
         * Get customer account linked to given email address
         * 
         * @param string $email
         * 
         * @return null|string|array
         */
        function getCustomerByEmail(string $email,string $field='*')
        {
            $email=$this->getView('vw_customers_info')->filtered(['emails %'=>$email])->first();
            if (!is_array($email))
            {
                return null;
            }
            return is_array($email) && array_key_exists($field, $email) ? $email[$field] : $email;
        }
        
        /**
         * Get available columns settings
         * 
         * @param  array $filters
         * 
         * @return array
         */
        function getAvalColumns(array $filters=[],\EMPORIKO\Controllers\Pages\TableView $view=null,$forlist=FALSE)
        {
            $arr=[];
            $mobile_fields=['code','name','enabled'];
            foreach($this->allowedFields as $field)
            {
                $key=lang('customers.accounts_'.$field);
                if ('customers.accounts_'.$field!=$key && (count($filters) <1 || (count($filters) >0 && in_array($field, $filters))))
                {
                    $list=[];
                    if ($field=='enabled')
                    {
                        $list='yesno';
                    }
                    if ($view!=null)
                    {
                        $view=$view->addColumn('customers.accounts_'.$field,$field, in_array($field, $mobile_fields),$list,'');
                    } else 
                    {
                        if ($forlist)
                        {
                           $arr[$field]=$key;
                        } else 
                        {
                            $arr[$field]=['customers.accounts_'.$field,$field, in_array($field, $mobile_fields),$list,''];
                        }
                    } 
                }
            }
            return $view!=null ? $view : $arr;
        }
        
        /**
         * Returns array with customer data for profile (include email and username)
         * 
         * @param  array  $filters
         * @param  string $orderby
         * @param  string $paginate
         * @param  string $logeduseraccess
         * @param  boo    $Validation
         * @return array
         */
        function getProfileData(array $filters=[],$orderby=null,$paginate=null,$logeduseraccess=null,$Validation=TRUE)
        {
            return $this->getView('vw_customer_profile')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
        }
        
        /**
         * Returns array with fields for profile form
         *  
         * @param  array  $record
         * @param  string $fields
         * @return array
         */
        function getFieldsForProfile($record,$fields=null)
        {
            $arr=[];
            $fields=$fields==null ? 'name,code,terms_pay,terms_credit,address_pay,address_ship,usr_email,usr_username,password': $fields;
            foreach (explode(',',$fields=null ? ',':$fields) as $field)
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName($field)
                        ->setID($field)
                        ->setText($field)
                        ->setTab('general')
                        ->setValue('');
                if ($field=='code')
                {
                   $arr[$field]->setText('customers.profile_code')
                               ->addArg('tooltip', 'customers.profile_code_tooltip',TRUE);
                }
                
                
                if (in_array($field, ['code','terms_pay','terms_credit','usr_username','usr_email']))
                {
                    $arr[$field]->setReadOnly();
                }
                
                if (in_array($field, ['address_pay','address_ship']))
                {
                    $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('TextAreaField',$arr[$field])
                                 ->setTab('addrr');
                }
                
                if ($field=='usr_username')
                {
                   $arr[$field]->setTab('acc')
                           ->setText('customers.profile_username');
                }
                
                if ($field=='usr_email')
                {
                   $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('EmailField',$arr[$field]) 
                           ->setTab('acc')
                           ->setText('customers.profile_email');
                }
                
                if ($field=='password')
                {
                   $arr[$field]->setAsPassword()
                               ->setTab('acc')
                               ->setText('customers.profile_password')
                               ->setValue('');
                }
            }
            $arr['password_confirm']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                                        ->setName('password_confirm')
                                        ->setID('password_confirm')
                                        ->setText('customers.profile_password_confirm')
                                        ->setAsPassword()
                                        ->setTab('acc')
                                        ->setValue('');
            
            return $arr;
        }
        
        function requestDetailsChange(array $data)
        {
            $arr=[];
            foreach(['name','address_pay','address_ship','usr_email', base64_encode('usr_email_old')] as $key)
            {
                if (array_key_exists($key, $data))
                {
                    if ($key==base64_encode('usr_email_old'))
                    {
                        $arr['mailto']=$data[$key];
                    }else
                    if (strlen($data[$key]) > 0)
                    {
                        $arr[$key]=$data[$key];
                    }                   
                }
            }
            if (!array_key_exists('mailto', $arr))
            {
                return FALSE;
            }
            if (count($arr) < 2)
            {
                return FALSE;
            }
            
            $arr=$this->getModel('Documents/ReportModel')->getTemplateByName('customer_details_change_notification',$arr);
            dump($arr);exit;
        }
        
        function getFieldsForForm(array $record) 
        {
            $arr=[];
            $listdata=$this->getModel('Settings/CustomList');
            foreach($this->allowedFields as $field)
            {
                $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setName($field)
                        ->setText($field)
                        ->setID($field)
                        ->setTab('general');
                
                if (in_array($field, ['address_pay','address_ship']))
                {
                    $arr[$field]= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('TextAreaField', $arr[$field])
                            ->setTab('addrr');
                }else
                if (in_array($field, ['employee']))
                {
                    $arr[$field]->setTab('empl');
                    if ($field=='employee')
                    {
                        $arr[$field]=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('DropDownField', $arr[$field])
                                ->setOptions($this->getModel('Auth/User')->getForForm('userid','name', FALSE,null,FALSE,['iscustomer'=>0]));
                    }
                }else
                if ($field=='terms_curr')
                {
                    $arr[$field]=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('DropDownField', $arr[$field])
                            ->setTab('others')
                            ->setAsAdvanced()
                            ->setOptions($this->getModel('Settings')->getCurrencyIcons(null,FALSE,TRUE));
                }else
                if (Str::startsWith($field,'terms_'))
                {
                    $arr[$field]=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('DropDownField', $arr[$field])
                            ->setTab('others')
                            ->setAsAdvanced()
                            ->setOptions($listdata->getByGroup(in_array($field, ['terms_buyco','terms_delco']) ? 'origin_full' :$field,FALSE));
                }else
                if (in_array($field, ['type','group']))
                {
                    $arr[$field]=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('DropDownField', $arr[$field])
                            ->setAsAdvanced()
                            ->setOptions($listdata->getByGroup($field,FALSE));
                }
                if (array_key_exists('_readonly', $record) && $record['_readonly'])
                {
                    $arr[$field]->setReadOnly();
                }
            }
            $arr['notes']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('TextAreaField', $arr['notes']);
            $arr['enabled']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('YesNoField', $arr['enabled']);
           
            unset($arr['emails']);
            unset($arr['phones']);
            return $arr;
        }
        
        /**
         * Returns array with terms data for given customer
         * 
         * @param string $code
         * @param array  $columns
         * 
         * @return array
         */
        function getPricingTermsForCustomer(string $code,array $columns=[])
        {
            $code=$this->select('terms_price')->where('code',$code)->first();
            if (!is_array($code))
            {
                return [];
            }
            if (count($columns) > 0)
            {
               $code=$this->getModel('CustomerTerm')->select(implode(',',$columns))->where('name',$code)->first(); 
            }else
            {
               $code=$this->getModel('CustomerTerm')->where('name',$code)->first(); 
            }
            
            if (!is_array($code))
            {
                return [];
            }
            return $code;
        }
        
        function getterms()
        {
            //$this->getModel('Settings/CustomList')->installStorage();exit;
            $arr=[];
            foreach($this->allowedFields as $field)
            {
                if (Str::startsWith($field,'terms_') || in_array($field, ['type','group']))
                {
                  foreach($this->groupby($field)->find() as $value)
                  {
                     if ($field=='terms_delco')
                     {
                         goto endloop;
                     }
                      $value=$value[$field];
                      if ($value!=null && strlen($value) > 0)
                      {
                          
                         $this->getModel('Settings/CustomList')->add($field=='terms_buyco' ? 'origin_full' : $field,$value,$value); 
                      }
                      endloop:
                      
                  }
                }
            }
           
        }

}