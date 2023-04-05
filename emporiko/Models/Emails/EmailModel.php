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

class EmailModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='emails';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'emid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['mail_to','mail_cc','mail_bcc','mail_subject','mail_body'
                             ,'mail_rec','mail_sent','mail_from','mail_reply','mail_msgid'
                             ,'mail_type','mail_folder','mail_size','mail_msgnote','mail_mailbox'
                             ,'mail_read','mail_msguser','mail_attachements','enabled'];
    
    protected $validationRules =
    [
        'mail_msgid' => 'required|is_unique[emails.mail_msgid,emid,{emid}]',
    ];
    
    protected $validationMessages = [];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'emid'=>            ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
        'mail_to'=>         ['type'=>'TEXT','null'=>TRUE],
        'mail_cc'=>         ['type'=>'TEXT','null'=>TRUE],
        'mail_bcc'=>        ['type'=>'TEXT','null'=>TRUE],
        'mail_subject'=>    ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'mail_body'=>       ['type'=>'TEXT','null'=>TRUE],
        'mail_rec'=>        ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
        'mail_sent'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
        'mail_from'=>       ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'mail_reply'=>      ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'mail_msgid'=>      ['type'=>'VARCHAR','constraint'=>'100','null'=>TRUE],
        'mail_type'=>       ['type'=>'VARCHAR','constraint'=>'10','null'=>FALSE],
        'mail_folder'=>     ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'mail_size'=>       ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
        'mail_msgnote'=>    ['type'=>'TEXT','null'=>TRUE],
        'mail_msguser'=>    ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
        'mail_mailbox'=>    ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
        'mail_read'=>       ['type'=>'INT','constraint'=>'11','default'=>'0','null'=>FALSE],
        'enabled'=>         ['type'=>'INT','constraint'=>'11','default'=>'1','null'=>FALSE],
    ];
    
    function filtered1(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
    {
        return $this->getView('vw_emails')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
    }
    
    
    /**
     * Returns array with emails data filtered by given customer email addresses
     * 
     * @param type $acc
     * @param type $enabled
     * @param type $limit
     * @param type $raw
     * 
     * @return array
     */
    function getEmailsForCustomer($acc,$enabled=1,$limit=5)
    {
        $field=$this->getModel('Settings')->get('customers.customers_custlinkedfield');
        $acc=$this->getModel('System/Contact')->getByAcc($acc,$field);
            
        if (is_array($acc) && count($acc) > 0)
        {
            return $this->getView('vw_emails')->filtered(['mail_from In'=>$acc],'mail_rec DESC',$limit);
        }
        return [];
    }
    
    /**
     * Set message state (unread/read)
     * 
     * @param Int   $id
     * @param bool  $read
     * @param mixed $mailbox
     * 
     * @return boolean
     */
    function setMessageState($id,$read,$mailbox=null)
    {
        $id= is_array($id) ? $id : [$id];
        if (intval($this->getModel('settings')->get('emails.emails_imapmode'))==1)
        {
            $mailbox=$this->getModel('Mailbox')->getMailbox($mailbox);
            
            foreach($this->WhereIn($this->primaryKey,$id)->find() as $row)
            {
                $mailbox=$mailbox->getClient($row['mail_folder']);
                if ($read)
                {
                    $mailbox->markMailAsRead($row['mail_msgid']);
                }else
                {
                    $mailbox->markMailAsUnread($row['mail_msgid']);
                }  
            }
        }
        return $this->builder()->set('mail_read',$read ? 1 : 0)->WhereIn($this->primaryKey,$id)->update();
    }
    
    function moveToFolder($id,$folder,$mailbox=null)
    {
        $id= is_array($id) ? $id : [$id];
        if (intval($this->getModel('settings')->get('emails.emails_imapmode'))==1)
        {
            $mailbox=$this->getModel('Mailbox')->getMailbox($mailbox);
            
            foreach($this->WhereIn($this->primaryKey,$id)->find() as $row)
            {
                $mailbox=$mailbox->getClient($row['mail_folder']);
                $mailbox->moveMail($row['mail_msgid'],$folder); 
            }
        }
        return $this->builder()->set('mail_folder',$folder)->WhereIn($this->primaryKey,$id)->update();
    }
    
    /**
     * Fetching all new emails for all mailboxes
     */
    function fetchNewEmailsForAllMailboxes()
    {
        foreach ($this->getModel('Mailbox')->where('enabled',1)->find() as $mailbox)
        {
            $mailbox= MailboxData::create($mailbox);
            $this->fetchNewEmailsFromMailbox($mailbox);
        }
    }
    
    function fetchNewEmailsForDefaultMailboxes()
    {
        //dump($this->getModel('Mailbox')->getDefaultMailbox(FALSE,'emm_name'));exit;
        $mailbox= MailboxData::create($this->getModel('Mailbox')->getDefaultMailbox(TRUE));
        $this->fetchNewEmailsFromMailbox($mailbox);
    }
    
    /**
     * Fetching all new emails for given mailbox
     * 
     * @param MailboxData $mailbox
     * @param string $folder
     */
    function fetchNewEmailsFromMailbox(MailboxData $mailbox,$folder=null)
    {
        if ($folder=='all')
        {
            $folder=$mailbox->getAllFolders();
        }else
        if (is_string($folder))
        {
            $folder=[$folder];
        }else
        {
            $folder=$mailbox->OtherFolders;
        }
        $criteria=$this->getModel('Settings')->get('emails.emails_searchcriteria');
        $criteria= str_replace(['@today'], [formatDate('now',FALSE,'Y-M-d')], $criteria);
        
        foreach($folder as $dir)
        {
            $this->fetchEmailsForMailbox($mailbox,'UNSEEN',$dir,null,TRUE);//
        }
    }
    
    function syncMailbox($mailbox,bool $truncate=FALSE)
    {
        if (intval($this->getModel('settings')->get('emails.emails_imapmode'))==0)
        {
             //return FALSE;
        }
        
        if ($mailbox==null)
        {
            $mailbox=$this->getModel('Mailbox')->getDefaultMailbox();
            if (is_array($mailbox))
            {
                $mailbox=$mailbox['emm_name'];
            }else
            {
                $mailbox=null;
            }
        }
        
        if (is_string($mailbox))
        {
            $mailbox=$this->getModel('Mailbox')->getMailbox($mailbox);
        }
        if ($truncate)
        {
            $this->where('mail_mailbox',$mailbox->Name)->Delete();
        }
        
        foreach($mailbox->OtherFolders as $dir)
        {
            try 
            {
               $this->fetchEmailsForMailbox($mailbox,'SINCE '.$mailbox->SyncedFrom,$dir,null,TRUE);
            } catch (\Exception $ex) 
            {
                log_message('error', $ex->getMessage());                      
            }
            
        }
    }
    
    /**
     * Returns email data
     * 
     * @param type $id
     * @param bool $raw
     * 
     * @return boolean|array
     */
    function getEmailDataByID($id,bool $getRaw=FALSE)
    {
        $id_data=$this->find($id);
        if (!is_array($id_data))
        {
           $id_data=$this->where('mail_msgid',$id)->first(); 
        }
        
        if (!$getRaw)
        {
            return $id_data;
        }
        
        if (!is_array($id_data))
        {
           return FALSE;
        }
        $mailbox=$this->getModel('Mailbox')->getMailbox($id_data['mail_mailbox']);
        if ($mailbox==null){return FALSE;}
        $client=$mailbox->getClient($id_data['mail_folder'],FALSE);
        $email=$client->getMail($id_data['mail_msgid'],FALSE);
        return $email;
    }
    
    function fetchEmailsForMailbox(MailboxData $mailbox,string $criteria,$folder='Inbox',$targetFolder=null,$markAsUnread=FALSE)
    {
        $targetFolder=$targetFolder==null ? $folder : $targetFolder;
        $client=$mailbox->getClient($folder,FALSE);
        $emails=[];
        try
        {
            $emails=$client->searchMailbox($criteria);
        }catch(\PhpImap\Exceptions\ConnectionException $ex){}
        catch (\Exception $ex){}
        $arr=[];
        //$keys=['mail_rec','mail_subject','mail_from','mail_reply','mail_cc','mail_msgid','mail_body','mail_attachements','mail_read','mail_type','mail_folder','mail_mailbox','mail_to'];
        //$values= Arr::ParsePatern($keys,'{value}');
        //$sqlIns=$this->builder()->set(array_combine($keys,$values))->getCompiledInsert();
        //$sqlUpd=$this->builder()->set(array_combine($keys,$values))->getCompiledUpdate();
        if (count($emails) > 0)
        {
            $this->db->transStart();
        }
        foreach($emails as $emailid)
        {
            $email=$client->getMail($emailid,FALSE);
            
            $item=[];
            $item['mail_msgid']=$emailid;
            if ($this->count(['mail_msgid'=>$item['mail_msgid']]) > 0)
            {
                goto loop_end;
            }
            $item['mail_rec']= date('YmdHi', strtotime($email->date));
            $item['mail_subject']=$email->subject;
            $item['mail_from']=$email->fromAddress;
            $item['mail_reply']= $email->replyTo;
            if (is_array($item['mail_reply']) && count($item['mail_reply']) >0)
            {
                $item['mail_reply']= array_keys($item['mail_reply']);
                if (Str::isValidEmail($item['mail_reply'][0]))
                {
                    $item['mail_reply']=$item['mail_reply'][0];
                } else 
                {
                    $item['mail_reply']= $email->fromAddress;
                }
            }
            /*Delete in production
            if (defined('ENVIRONMENT') && ENVIRONMENT=='development')
            {
                $email_addr=$this->getModel('System/Contact')->find();
                if (count($email_addr) > 0)
                {
                    $email_addr=$email_addr[rand(0,count($email_addr)-1)];
                    if (is_array($email_addr) && array_key_exists('ct_email', $email_addr))
                    {
                        $item['mail_from']=$email_addr['ct_email'];
                        $item['mail_reply']=$email_addr['ct_email'];
                    }
                }
            }
            /* / Delete in production*/
            $item['mail_cc']= is_array($email->cc) ? implode(';',$email->cc) : $email->cc;
            
            //$email->messageId;
            $item['mail_body']= base64_encode(strlen($email->textHtml) >0 ? $email->textHtml : $email->textPlain);
            $item['mail_attachements']=[];
            $item['mail_read']=$email->isSeen ? 0 : 1;
            foreach($email->getAttachments() as $att)
            {
                $item['mail_attachements'][]=['name'=>$att->name,'mime'=>$att->mime,'size'=>$att->sizeInBytes,'id'=>$att->id];
            }
            $item['mail_attachements']= json_encode($item['mail_attachements']);
            $item['mail_type']='in';
            $item['mail_folder']=$targetFolder;
            $item['mail_mailbox']= is_array($mailbox) && array_key_exists('Name', $mailbox) ? $mailbox['Name'] : 'default';
            $item['mail_to']=[];
            foreach($email->to as $key=>$value)
            {
                $item['mail_to'][]=$key;
            }
            $item['mail_to']=implode(';',$item['mail_to']);
            $arr[]=$item;
           
            if ($this->count(['mail_msgid'=>$item['mail_msgid']])>0)
            {
                //$sql= str_replace($values, $item,$sqlUpd);
                $this->builder()->set($item)->where(['mail_msgid'=>$item['mail_msgid']])->update();
            } else 
            {
                //$sql= str_replace($values, $item,$sqlIns);
                $this->builder()->set($item)->insert();
            }
            //$this->db->query($sql);
            loop_end:
        }
        if (count($emails) > 0)
        {
            $this->db->transComplete();
        }
        
        return count($emails) > 0 ? $this->db->transStatus() : FALSE;
    }    
    
    /**
     * Save email details to outbox 
     * 
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $cc
     * @param string $bcc
     * @param string $mailbox
     * 
     * @return bool | Int
     */
    function storeEmailInOutbox(string $from,$to,string $subject,string $body,string $cc=null,string $bcc='',string $mailbox=null)
    {
        if ($mailbox!=null)
        {
            $mailbox=$this->getModel('Mailbox')->getMailbox($mailbox);
        }
        if ($mailbox==null)
        {
            $mailbox=$this->getModel('Mailbox')->getDefaultOutMailbox(TRUE);
        }
        $currMax=$this->count(['mail_type'=>'out','enabled'=>0,'mail_sent <'=> substr(formatDate('now','+1 Day'),0,8).'2359']);
        if (is_array($to))
        {
            $to= array_values($to);
            $date= formatDate();
            $chunk=0;
            foreach($to as $i=>$email)
            {
                if (Str::isValidEmail($email))
                {
                    if ($chunk > config('Email')->maxSMTPEmailsSend)
                    {
                        $date=formatDate($date,'+'.config('Email')->maxSMTPEmailsSendDelay.' Minutes');
                        $chunk=0;
                    }
                    if ($currMax+1 > config('Email')->maxSMTPEmailsDaily)
                    {
                        $currMax=$this->count(['mail_type'=>'out','enabled'=>0,'( mail_sent <'=>substr(formatDate($date,'+1 Day'),0,8).'2359','mail_sent > )'=>substr($date,0,8).'2359']);
                        $date=substr(formatDate($date,'+1 Day'),0,8).'0059';
                    }
                    $to[$i]=
                    [
                        'mail_to'=>$email,
                        'mail_subject'=>$subject,
                        'mail_body'=> base64_encode($body),
                        'mail_sent'=>$date,
                        'mail_type'=>'out',
                        'mail_folder'=>$mailbox['emm_fldsent'],
                        'mail_mailbox'=>$mailbox['emm_name'],
                        'mail_from'=>$from,
                        'enabled'=>0,
                        'mail_cc'=>$cc,
                        'mail_bcc'=>$bcc,
                        'mail_msgid'=>Str::createUID()
                    ];
                    $chunk++;
                    $currMax++;
                }else
                {
                    unset($to[$i]);
                }
            }
            return $this->builder()->insertBatch($to);
        }
        $mailbox=$this->save(
        [
            'mail_to'=>$to,
            'mail_subject'=>$subject,
            'mail_body'=> base64_encode($body),
            'mail_sent'=> $currMax+1 >= config('Email')->maxSMTPEmails ? formatDate('now','+1 Day') : formatDate(),
            'mail_type'=>'out',
            'mail_folder'=>$mailbox['emm_fldsent'],
            'mail_mailbox'=>$mailbox['emm_name'],
            'mail_from'=>$from,
            'enabled'=>0,
            'mail_cc'=>$cc,
            'mail_bcc'=>$bcc,
            'mail_msgid'=>Str::createUID()
        ]);
        if ($mailbox)
        {
            return $this->getLastID();
        }
        return $this->errors();
    }
    
    function saveAttachement($mailID,$attachID,$attachName,$storage=null)
    {
        $mailID=$this->find($mailID);
        if (!is_array($mailID))
        {
            return FALSE;
        }
        $storage= storage($storage);
        $mailID=$this->getModel('Mailbox')->getMailbox($mailID['mail_mailbox'])->getClient($mailID['mail_folder'])->getMail($mailID['mail_msgid'])->getAttachments();
        
        if (is_array($mailID))
        {
            $mailID= array_values($mailID);
            foreach ($mailID as $key=>$attach)
            {
                if (is_a($attach, '\PhpImap\IncomingMailAttachment') && $key==$attachID && $attach->name==$attachName)
                {
                    $attachPath=parsePath('@temp/'.$attach->name,TRUE);
                    $attach->setFilePath($attachPath);
                    $attach->saveToDisk();
                    if (file_exists($attachPath))
                    {
                        $attach=$storage->move(parsePath('@files/#.'.Str::afterLast(basename($attachPath), '.'),TRUE),$attachPath);
                        unlink($attachPath);
                        if (is_string($attach))
                        {
                            return ['name'=>basename($attachPath),'path'=>$attach];
                        } else 
                        {
                            return FALSE;
                        }
                    }
                }        
            }
        }
        return false;
        //$mailbox->getClient($email['mail_folder'])->getMail($email['mail_msgid'])->getAttachments();
    }
   
    function getQty($user=null,$unread=1,$received=TRUE,$forIcon=FALSE)
    {
        $user=Str::isValidEmail($user) ? $user : loged_user('email');
        $user=$this->count(['mail_read'=>0,'enabled'=>$unread,'mail_to'=>$user,'mail_type'=>$received ? 'in' : 'out']);
        return $forIcon ? ['qty'=>$user,'url'=>url('Emails','messages')] : $user;
    }

    private function parseFolder(&$result,array $list,$parent)
    {
        foreach($list as $key=>$item)
        {
            if (is_array($item) && count($item) > 0)
            {
                $this->parseFolder($result, $item, $key);
            }else
            {
                if ($parent!=null)
                {
                   $result[]=$parent.'/'.$key; 
                }else
                {
                   $result[]=$key;  
                }
            }
        }
    }
   
    
    /**
     * Sends emails from queue
     */
    function sendEmailsFromQueue()
    {
        $emails=$this->filtered(['mail_type'=>'out','enabled'=>0,'mail_sent <'=> formatDate()])->find();
        $emailsSent=formatDate('now',TRUE,'Ymd');
        $emailsSent=$this->count(['mail_type'=>'out','enabled'=>1,'( mail_sent >'=>$emailsSent.'0000','mail_sent < )'=>$emailsSent.'2359']);
        foreach($emails as $email)
        {
            if ($emailsSent+1 < config('Email')->maxSMTPEmailsSend)
            {
                $mailbox=$this->getModel('Mailbox')->getMailbox($email['mail_mailbox']);
                $email['mail_body']=base64_decode($email['mail_body']);
                $email['mail_body']= str_replace(['-%@emailid%-'], base64url_encode($email['emid']), $email['mail_body']);
                $email['mail_body']= str_replace(['-%@email%-'], base64url_encode($email['mail_to']), $email['mail_body']);
                $email['mail_body']= base64_encode($email['mail_body']);
                $fromName=null;
                if (array_key_exists('mail_from', $email) && strlen($email['mail_from']) > 0)
                {
                    $fromName= json_decode($email['mail_from'],TRUE);
                    if (!is_array($fromName))
                    {
                        $fromName=[$email['mail_from'],$email['mail_from']];
                    }
                }
                if($mailbox->sendEmail($email['mail_to'],$email['mail_subject'], base64url_decode($email['mail_body']),explode(';',$email['mail_cc']), explode(';',$email['mail_bcc']),$fromName, []))
                {
                    $email['mail_type']='out';
                    $email['enabled']=1;
                    $email['mail_folder']=$mailbox->SentFolder;
                    if (array_key_exists('mail_msgnote', $email) && strlen($email['mail_msgnote']) > 0)
                    {
                        $arr=
                        [
                            'mhtype'=>18,
                            'mhdate'=>formatDate(),
                            'mhuser'=>$email['mail_msguser'],
                            'mhref'=>$email['mail_msgnote'],
                            'mhinfo'=>$email['mail_subject'].'&nbsp;<a href="@/emails/message/view/'. $email['emid'].'?refurl=@curr_url_ref">...</a>',
                            'type'=>'emails',
                            'enabled'=>1
                        ];
                        $this->getModel('System/Movements')->set($arr)->insert();
                        unset($email['mail_msgnote']);
                    }
                
                    $this->save($email);
                }
                $emailsSent++;   
            }
        }
    }
    
    
     /**
     * Returns array with email data search by message id
     * 
     * @param  string $id
     * @return array
     */
    function getEmailsDataByID_sergei($id)
    {
        //$this->fetchEmailsForMailbox('default','Completed','OK','SINCE '.convertDate(formatDate(),'DB','d-M-Y'));exit;
        $arr=$this->getView('vw_emails')->filtered(['emid'=>$id])->first();
        if (is_array($arr) && Arr::KeysExists(['mail_type','mail_mailbox','mail_msgid','mail_folder'], $arr) && $arr['mail_type']=='in')
        {
            try
            {
                $client=$this->getClient($arr['mail_mailbox'],$arr['mail_folder']);
                $arr['mail_body']=$client->getMessageBody($arr['mail_msgid']);
            }catch(\Exception $e){}
            if (is_object($arr['mail_body']))
            {
                
                //$arr['mail_body']=Arr::ObjectToArray($arr['mail_body']);
                if (property_exists($arr['mail_body'],'html'))
                {
                    $arr['mail_body']=$arr['mail_body']->html;
                }else
                if (property_exists($arr['mail_body'],'plain'))
                {
                    $arr['mail_body']=$arr['mail_body']->plain;
                }else
                {
                    //$arr['mail_body']='';
                }
            }
        }
        return $arr;
    }
    
    function getClient_sergei(MailboxData $mailbox,$folder=null,$onlyHeaders=TRUE)
    {
        $client= \sergey144010\ImapClient\ImapClient::init($mailbox);
        if (is_a($client,'\sergey144010\ImapClient\ImapClientException'))
        {
            return $client;
        }
        if ($folder!=null)
        {
            $client->selectFolder($folder);
        }
        
        if ($onlyHeaders)
        {
            $client->useGetMessageHeaders();//useGetMessageWithAttachments useGetMessageHeaders
        }else
        {
            $client->useGetMessageWithAttachments();
        }
        return $client;
    }
    
     function fetchEmailsForMailbox_sergeu(MailboxData $mailbox,$criteria,$folder='Inbox',$targetFolder=null,$markAsUnread=FALSE)
    {
        $targetFolder=$targetFolder==null ? $folder : $targetFolder;
       
        $client=$mailbox->getClient($folder,FALSE);
        $emails=[];
        try 
        {
            $emails=$emails+$client->getMessagesByCriteria($criteria);
        } catch (\sergey144010\ImapClient\ImapClientException $ex){}
        $arr=[];
        if (count($emails) > 0)
        {
            $this->db->transStart();
        }
        $arr=[];
        foreach($emails as $key=>$email)
        {
            //$email=Arr::ObjectToArray($email);
            $headers=$email->getHeaders();
            $item=[];
            if (property_exists($headers,'date')||property_exists($headers,'Date'))
            {
                $item['mail_rec']=$headers->{property_exists($headers,'date')? 'date':'Date'};
                $item['mail_rec']= date('YmdHi', strtotime($item['mail_rec']));
            }
            if (property_exists($headers,'subject')||property_exists($headers,'Subject'))
            {
                $item['mail_subject']=$headers->{property_exists($headers,'subject') ? 'subject':'Subject'};
            }
            
            if (property_exists($headers,'to')&& is_array($headers->to))
            {
                $item['mail_to']=[];
                foreach($headers->to as $to)
                {
                    $to=Arr::ObjectToArray($to);
                    if (Arr::KeysExists(['mailbox','host'],$to))
                    {
                       $item['mail_to'][]=$to['mailbox'].'@'.$to['host']; 
                    }
                }
                if (count($item['mail_to']) > 0)
                {
                    $item['mail_to']= implode(';',$item['mail_to']);
                }else
                {
                    $item['mail_to']=null;
                }  
            }
            
            if (property_exists($headers,'cc')&& is_array($headers->cc))
            {
                $item['mail_cc']=[];
                foreach($headers->cc as $to)
                {
                    $to=Arr::ObjectToArray($to);
                    if (Arr::KeysExists(['mailbox','host'],$to))
                    {
                       $item['mail_cc'][]=$to['mailbox'].'@'.$to['host']; 
                    }
                }
                if (count($item['mail_cc']) > 0)
                {
                    $item['mail_cc']= implode(';',$item['mail_cc']);
                }else
                {
                    $item['mail_cc']=null;
                }  
            }
            
            if (property_exists($headers,'bcc')&& is_array($headers->bcc))
            {
                $item['mail_bcc']=[];
                foreach($headers->bcc as $to)
                {
                    $to=Arr::ObjectToArray($to);
                    if (Arr::KeysExists(['mailbox','host'],$to))
                    {
                       $item['mail_bcc'][]=$to['mailbox'].'@'.$to['host']; 
                    }
                }
                if (count($item['mail_bcc']) > 0)
                {
                    $item['mail_bcc']= implode(';',$item['mail_bcc']);
                }else
                {
                    $item['mail_bcc']=null;
                }  
            }
            
            if (property_exists($headers,'reply_to')&& is_array($headers->{'reply_to'}))
            {
                $item['mail_reply']=[];
                foreach($headers->{'reply_to'} as $to)
                {
                    $to=Arr::ObjectToArray($to);
                    if (Arr::KeysExists(['mailbox','host'],$to))
                    {
                       $item['mail_reply'][]=$to['mailbox'].'@'.$to['host']; 
                    }
                }
                if (count($item['mail_reply']) > 0)
                {
                    $item['mail_reply']= implode(';',$item['mail_reply']);
                }else
                {
                    $item['mail_reply']=null;
                }  
            }
            
            if (property_exists($headers,'from')&& is_array($headers->from))
            {
                $item['mail_from']=[];
                foreach($headers->from as $to)
                {
                    $to=Arr::ObjectToArray($to);
                    if (Arr::KeysExists(['mailbox','host'],$to))
                    {
                       $item['mail_from'][]=$to['mailbox'].'@'.$to['host']; 
                    }
                }
                if (count($item['mail_from']) > 0)
                {
                    $item['mail_from']= implode(';',$item['mail_from']);
                }else
                {
                    $item['mail_from']=null;
                }  
            }
            
            if (property_exists($headers,'Size'))
            {
                $item['mail_size']=$headers->Size;
            }
            
            if (property_exists($headers,'Unseen'))
            {
                $item['enabled']=!$markAsUnread ? ($headers->Unseen ? 1 :0): 1;
            }
            if (property_exists($headers,'Msgno'))
            {
                $item['mail_msgid']=$headers->Msgno;
                
            }
            
            if (property_exists($email,'body'))
            {
                $item['mail_body']=$email->getBody();
                if (property_exists($item['mail_body'], 'html'))
                {
                    $item['mail_body']=$item['mail_body']->html;
                }else
                if (property_exists($item['mail_body'], 'plain'))
                {
                    $item['mail_body']=$item['mail_body']->plain;
                }else
                if (property_exists($item['mail_body'], 'text'))
                {
                    $item['mail_body']=$item['mail_body']->text;
                }else
                {
                    $item['mail_body']='';
                }
                if (strlen($item['mail_body']) > 0)
                {
                    $item['mail_body']= base64_encode($item['mail_body']);
                }
            }
            if (array_key_exists('mail_msgid', $item))
            {
                $item['mail_attachements']=$email->getAttachments();
                if (!is_string($item['mail_attachements']))
                {
                    $item['mail_attachements']= json_encode($item['mail_attachements']);
                }
            }
            $item['mail_type']='in';
            $item['mail_folder']=$targetFolder;
            $item['mail_mailbox']= is_array($mailbox) && array_key_exists('Name', $mailbox) ? $mailbox['Name'] : 'default';
            $arr[]=$item;
            $insert=true;
            if (strlen($item['mail_msgid'])>0)
            {
                if($this->count(['mail_msgid'=>$item['mail_msgid']])>0)
                {
                    $insert=false;
                }
            }
            if ($insert)
            {
                $sql=$this->builder()->set($item)->getCompiledInsert();
                //$this->db->query($sql);
            }
        }
        if (count($emails) > 0)
        {
            $this->db->transComplete();
        }
        dump($arr);exit;
        return count($emails) > 0 ? $this->db->transStatus() : FALSE;
    }

    public function installstorage() 
    {
        //parent::installstorage();
        if ($this->existsInStorage())
        {
            $this->setView("select 
`ct`.`ct_name` AS `mail_from_name`,
`ct`.`ct_account` AS `mail_from_acc`,
`eml`.`emid` AS `emid`,
`eml`.`mail_to` AS `mail_to`,
`eml`.`mail_cc` AS `mail_cc`,
`eml`.`mail_bcc` AS `mail_bcc`,
`eml`.`mail_subject` AS `mail_subject`,
`eml`.`mail_body` AS `mail_body`,
`eml`.`mail_rec` AS `mail_rec`,
`eml`.`mail_sent` AS `mail_sent`,
`eml`.`mail_from` AS `mail_from`,
`eml`.`mail_reply` AS `mail_reply`,
`eml`.`mail_msgid` AS `mail_msgid`,
`eml`.`mail_type` AS `mail_type`,
`eml`.`mail_folder` AS `mail_folder`,
`eml`.`mail_size` AS `mail_size`,
`eml`.`mail_msgnote` AS `mail_msgnote`,
`eml`.`mail_mailbox` AS `mail_mailbox`,
`eml`.`enabled` AS `enabled` 
from `emails` AS `eml` 
left join `contacts` AS `ct` on `ct`.`ct_other` = `eml`.`mail_from` OR `ct`.`ct_email`= `eml`.`mail_from`
");
        }
        
    }
    
}