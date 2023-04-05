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

class MailboxData
{
    public string $Name='';
    
    public string $InHost='';
    
    public string $InUser='';
    
    public string $InPass='';
    
    public Int $InPort=0;
    
    public bool $InIsSSL=FALSE;
    
    public string $OutHost='';
    
    public string $OutUser='';
    
    public string $OutPass='';
    
    public Int $OutPort=0;
    
    public bool $OutIsSSL=FALSE;
    
    public string $InboxFolder='';
    
    public string $DraftsFolder='';
    
    public string $SentFolder='';
    
    public string $SpamFolder='';
    
    public string $BinFolder='';
    
    public array $OtherFolders=[];
    
    public string $SyncedFrom='';
    
    private array $_fields=
            [
                'Name'=>'emm_name',
                'InHost'=>'emm_inhost',
                'InUser'=>'emm_inuser',
                'InPass'=>'emm_inpass',
                'InPort'=>'emm_inport',
                'InIsSSL'=>'emm_intissl',
                'OutHost'=>'emm_outhost',
                'OutUser'=>'emm_outuser',
                'OutPass'=>'emm_outpass',
                'OutPort'=>'emm_outport',
                'OutIsSSL'=>'emm_outissl', 
                'InboxFolder'=>'emm_fldinbox',
                'DraftsFolder'=>'emm_flddraft',
                'SentFolder'=>'emm_fldsent',
                'SpamFolder'=>'emm_fldspam',
                'BinFolder'=>'emm_fldbin',
                'OtherFolders'=>'emm_fldslist',
                'SyncedFrom'=>'emm_syncedfrom'
            ];
            
    private array $_encrypted=['InUser','InPass','OutUser','OutPass'];

    static function create(array $data=[])
    {
        
        $cls=new MailboxData();
        if (count($data)>0)
        {
            $cls->fromArray($data);
        }
        return $cls;
    }
    
    function fromArray(array $data)
    {
        $encrypter = \Config\Services::encrypter();
        foreach($this->_fields as $param=>$field)
        {
            if (array_key_exists($field, $data))
            {
                
                if ($field=='emm_syncedfrom')
                {
                    if (strlen($data[$field]) > 0)
                    {
                       $data[$field]= convertDate($data[$field], null, 'd-M-Y'); 
                    }else
                    {
                        $data[$field]= convertDate(formatDate(), null, 'd-M-Y');
                    }
                }
                
                if ($field=='emm_intissl' || $field=='emm_outissl')
                {
                    $this->{$param}=intval($data[$field])==1;
                }else
                {
                    if (gettype($this->{$param})=='array' && !is_array($data[$field]))
                    {
                        if (is_string($data[$field]))
                        {
                            $data[$field]= json_decode($data[$field],TRUE);
                            if (is_array( $data[$field]))
                            {
                                $this->{$param}=$data[$field];
                            }
                        } 
                    }else{
                        $this->{$param}=$data[$field];
                    }
                    
                    //}catch(\TypeError $e)
                }
                
            }
        }
    }
    
    function toArray(array $includedFields=[])
    {
        $encrypter = \Config\Services::encrypter();
        $data=[];
        foreach($this->_fields as $param=>$field)
        {
            if (count($includedFields) < 1 || (count($includedFields) > 0 && array_key_exists($field, $includedFields)))
            {
                if (in_array($param, $this->_encrypted))
                {
                    $data[$field]= base64_encode($encrypter->encrypt($this->{$param}));
                }else
                if ($param=='OtherFolders')
                {
                   $data[$field]= json_encode($this->{$param}); 
                }else
                if ($param=='SyncedFrom' && strlen($this->{$param}) > 0)
                {
                    $data[$field]= convertDate($this->{$param}, 'd-M-Y', null);
                }else
                if ($param=='InIsSSL' || $param=='OutIsSSL') 
                {
                    $data[$field]=$this->{$param} ? 1 : 0;
                }else
                {
                    $data[$field]=$this->{$param};
                }
            }
        }
        return $data;
    }
    
    function toPublicKey()
    {
        $arr=
        [
            'Name'=>$this->Name,
            'InboxFolder'=>$this->InboxFolder,
            'DraftsFolder'=>$this->DraftsFolder,
            'SentFolder'=>$this->SentFolder,
            'SpamFolder'=>$this->SpamFolder,
            'BinFolder'=>$this->BinFolder,
            'OtherFolders'=>$this->OtherFolders,
            'SyncedFrom'=>$this->SyncedFrom
        ];
        return base64_encode(json_encode($arr));
    }
    
    /**
     * Determines if mailbox settings are correct
     * 
     * @param type $logErrors
     * 
     * @return boolean
     */
    function isValid($logErrors=TRUE)
    {
        try
        {
            $this->getClient()->searchMailbox('UNSEEN');
        }catch(\PhpImap\Exceptions\ConnectionException $ex)
        { 
            if ($logErrors)
            {
                log_message('error', $ex->getMessage());
            }
            return FALSE;
        }
        catch (\Exception $ex)
        {
            if ($logErrors)
            {
                log_message('error', $ex->getMessage());
            }
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Get array with mailbox folders
     * 
     * @param boolean $getFromServer
     * @param boolean $onlyMovable
     * 
     * @return array
     */
    function getAllFolders(bool $getFromServer=FALSE,bool $onlyMovable=FALSE)
    {
        $arr=[];
        $arr[$this->InboxFolder]=lang('emails.InboxFolder');
        if (!$onlyMovable)
        {
            $arr[$this->DraftsFolder]=lang('emails.DraftsFolder');
            $arr[$this->SentFolder]=lang('emails.SentFolder');
        }
        
        $arr[$this->SpamFolder]=lang('emails.SpamFolder');
        $arr[$this->BinFolder]=lang('emails.BinFolder');
        
        if (is_array($this->OtherFolders))
        {
            $arr=$arr+$this->OtherFolders;
        }
        return !$onlyMovable ? $arr : array_values($arr);
    }
    
    /**
     * Returns PhpImap\Mailbox object
     * 
     * @param type $folder
     * 
     * @return \PhpImap\Mailbox
     */
    function getClient($folder=null)
    {
       $folder=$folder==null ? $this->InboxFolder : $folder;
       return new \PhpImap\Mailbox('{'.$this->InHost.':'.$this->InPort.'/imap/ssl/novalidate-cert}'.$folder, $this->InUser, $this->InPass);       
    }
    
    /**
     * Send email to recipient
     * 
     * @param string|array   $to
     * @param string $subject
     * @param string $msg
     * @param array  $cc
     * @param array  $bcc
     * @param string $fromName
     * @param array  $attachements
     * 
     * @return bool
     */
    function sendEmail($to,string $subject,string $msg, array $cc = [], array $bcc = [],$fromName=null, array $attachements=[]) 
    {
        $email = \Config\Services::email();
        $email->initialize(
        [
            'protocol'=>'smtp',
            'SMTPHost'=>$this->OutHost,
            'SMTPUser'=>$this->OutUser,
            'SMTPPass'=>$this->OutPass,
            'SMTPPort'=>$this->OutPort,
            'SMTPCrypto'=>$this->OutIsSSL ? 'tls' : 'ssl',
        ]);
        $fromName=$fromName==null ? $this->Name : $fromName;
        $fromName= is_array($fromName) ? $fromName : [$fromName,$this->OutUser];
        $email->setFrom($this->OutUser, $fromName[0]);
        $email->setReplyTo($fromName[1], $fromName[0]);

        if (is_string($to) && Str::contains($to,';'))
        {
            $to= explode(';', $to);
        }
        $emailAddrA=[];
        foreach(is_array($to) ? $to : [$to] as $key=>$emailAddr)
        {
            if (Str::isValidEmail($emailAddr)) 
            {
                $emailAddrA[$key]=$emailAddr;
            }
        }
        if (count($emailAddrA) > 0)
        {
            $email->setTo($emailAddrA);
        }
        if (is_array($subject))
        {
            if (count($subject)==2)
            {
               $subject= array_values($subject);
               $subject=lang($subject[0],$subject[1]); 
            }else 
            {
                $subject=null;
            }  
        }
        $email->setSubject($subject);
        
        if (is_array($msg))
        {
            if (count($msg)==2)
            {
               $msg= array_values($msg);
               $msg=lang($msg[0],$msg[1]); 
            } else 
            {
                $msg=null;
            }  
        }
        $email->setMessage($msg);
        $email->setAltMessage(strip_tags($msg));
        
        foreach ($cc as $value) 
        {
            if (Str::isValidEmail($value)) {
                $email->setCC($value);
            }
        }

        foreach ($bcc as $value) 
        {
            if (Str::isValidEmail($value)) {
                $email->setBCC($value);
            }
        }
        foreach($attachements as $file)
        {
            $name= is_string($file) ?  Str::afterLast($file, '/') : 'file';
            if (is_array($file) && Arr::KeysExists(['name','path'], $file))
            {
                $name=$file['name'];
                $file=$file['path'];
            }
            if (is_string($file))
            {
                $file= parsePath($file,TRUE);
                if (file_exists($file) || Str::startsWith($file, 'http'))
                { 
                    $email->attach($file,'attachment',$name);  
                }
            }
        }
        if (!$email->send(FALSE))
        {
            log_message('error', $email->printDebugger(['headers']));
            return FALSE;
        }
        return TRUE;
    }
}