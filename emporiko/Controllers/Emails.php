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

  
namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;
use \EMPORIKO\Helpers\MenuBarItemType;

class Emails extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
            'index'=>           AccessLevel::edit,
            'compose'=>         AccessLevel::modify,
            'mailboxes'=>       AccessLevel::settings,
            'mailbox'=>         AccessLevel::settings,
            'view'=>            AccessLevel::view,
            'save'=>            AccessLevel::view,
            'movetofolder'=>    AccessLevel::edit,

        ];
        
        
        protected $no_access=[];
        
        /**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
            'emails'=>'Emails/Email',
            'accounts'=>'Emails/Mailbox',
            'contacts'=>'System/Contact',
            'cust'=>'Customers/Customer',
	];
	
        /**
	 * Array with controller method remaps ($key is fake function name and $value is actual function name)
	 */
	public $remaps=
        [
            'inbox'=>'messages',
            'index'=>'messages',
        ];
        
        /**
         * Array with available menu items (keys as function names and values as description)
         */
        public $availablemenuitems = 
        [
            'index'=>'messages'
        ];
        
        /**
	 * Array with function names which are excluded from routes actions
	 * @var Array
	 */
	protected $routerexlude=[];
       
        
        function mailboxes($record=null)
        {
            if ($record!=null)
            {
                return $this->mailbox($record);
            }
            $this->setTableView()
                    ->setData('accounts',null,TRUE,null,[])
                    ->setPageTitle('emails.mailbox_title')
                    //Fiilters settings
                    ->addFilters('mailboxes')
                    ->addFilterField('emm_name %')
                    //Table Columns settings
                    ->addColumn('emails.emm_name','emm_name',TRUE)
                    ->addColumn('emails.emm_inhost','emm_inhost',TRUE)
                    ->addColumn('emails.emm_outhost','emm_outhost',FALSE)
                    ->addColumn('emails.emm_isdef','emm_isdef',FALSE,'yesno')
                    ->addColumn('emails.enabled','enabled',FALSE,'yesno')
                    //Breadcrumb settings
                    ->addBreadcrumb('emails.mainmenu',url($this))
                    ->addBreadcrumb('emails.mailbox_bread','/')
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details','mailboxes',null,'btn-primary edtBtn','fa fa-edit',[])
                    //Table main buttons
                    ->addEnableButton(AccessLevel::edit)
                    ->addDisableButton(AccessLevel::edit)
                    ->addDeleteButton(AccessLevel::edit)
                    ->addNewButton('mailboxes/new')
                    ->addDisabledRecords($this->model_Accounts->getDefaultMailbox(TRUE,'emid'));
            return $this->view->render();
        }
        
        private function mailbox($record)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            if ($record=='_test')
            {
                $get=$this->request->getGet();
                if (\EMPORIKO\Models\Emails\MailboxData::create($get)->isValid(FALSE))
                {
                    $get=['error'=>'0','msg'=>lang('emails.msg_valid_mailboxdata')];
                } else 
                {
                    $get=['error'=>'1','msg'=>lang('emails.error_invalid_mailboxdata')];
                }
                return json_encode($get);
            }
            if (is_numeric($record))
            {
                $record=$this->model_Accounts->find($record);              
            }else
            {
                $record=null;
            }
           
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError(true);
                }
                $isnew=TRUE;
                $record=$this->model_Accounts->getNewRecordData(TRUE);
            }
            $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
            $this->setFormView('Emails/mailbox_edit')
                    ->setFormTitle('products.brands_edit')
                    ->setPageTitle('products.brands_edit')
                    ->setFormAction($this,'save',['accounts'],['refurl'=>base64url_encode($refurl)])
                    ->parseArrayFields()
                    ->setFormArgs(['autocomplete'=>'off'],
                            [
                                $this->model_Accounts->primaryKey=>$record[$this->model_Accounts->primaryKey],
                            ]
                        ,['class'=>'col-12'])
                    ->setCustomViewEnable(FALSE)
                    ->setFormCancelUrl($refurl)
					
                    ->addBreadcrumb('emails.mainmenu',url($this))
                    ->addBreadcrumb('emails.mailbox_bread',url($this,'mailboxes'))
                    ->addBreadcrumb($isnew ? 'system.buttons.new' : $record['emm_name'],'/')
			
                    ->addData('record',$record)
                    ->addData('url_test',url($this,'mailboxes',['_test']))
                    ->setTab('general','system.general.tab_info')
                    ->setTab('tab_inbox','emails.emm_tab_inbox')
                    ->setTab('tab_out','emails.emm_tab_out')
                    ->setTab('tab_flds','emails.emm_tab_flds') 
                    ->addFieldsFromModel('accounts',$record,'emails.-key-')
                    ->addSelect2('.select2');
            if (!$isnew)
            {
                $this->view->addData('url_sync',url($this,'fetchEmails',[$record['emm_name'],'sync'],['refurl'=> current_url(FALSE,TRUE)]));
            }
            return $this->view->render();
        }
        
        function messages($record=null)
        {
            if ($record=='markasread')
            {
                return $this->markasread();
            }
            $edit_acc=$this->hasAccess(AccessLevel::edit);
            
            if (!$edit_acc)
            {
                $filters=['mail_type'=>'in','mail_to'=>'cus_'.loged_user('customer')];
            }else
            {
                $mailbox=$this->model_Accounts->getMailbox();
                if (!$mailbox->isValid())
                {
                    $this->addErrorMsgData('emails.error_connection', FALSE);
                }
                $filters=['mail_type'=>'in'];
                if ($this->request->getGet('dir')!=null)
                {
                    $filters=['mail_folder'=>$this->request->getGet('dir')];
                }else
                {
                    $filters=['mail_folder'=>$mailbox->InboxFolder];
                }
            }
            $post=$this->request->getPost();
            $post_filter='';
            if (array_key_exists('filter', $post))
            {
                $filters['( mail_from %']=$post['filter'];
                $filters['|| mail_subject % )']=$post['filter'];
                $post_filter=$post['filter'];
            }
            $get=$this->request->getGet();
            if (array_key_exists('email', $get) && strlen($get['email']) > 0)
            {
               $filters['mail_from']=$get['email'];
            }
            
            if (array_key_exists('date', $get) && strlen($get['date']) > 0)
            {
               if (Str::contains($get['date'], ' '))
               {
                   $key=Str::afterLast($get['date'], ' ');
                   $filters['mail_rec'.' '.$key]= str_replace(' '.$key, '', $get['date']);
               } else 
               {
                  $filters['mail_rec']=$get['date']; 
               }
               
            }
            
            if (array_key_exists('cust', $get) && is_numeric($get['cust']))
            {
                $get['cust']=$this->model_Customers_Customer->getContactsForCustomer($get['cust']);
                $filters['mail_from In']=$get['cust'];
            }
            
            $toolbar_buttons=[];

            if ($this->hasAccess(AccessLevel::settings))
            {
                $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-user-cog', 'warning', 'emails.btn_mailbox_cfg', 'id_emailmsg_mailbox',url($this,'mailboxes',[],['refurl'=> current_url(FALSE,TRUE)]), []);
                $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-cogs', 'secondary', 'system.buttons.module_settings', 'id_module_cfg', $this->getModuleSettingsUrl(), []);
            }
            $this->getDefData($toolbar_buttons,$mailbox);
            
            $toolbar_buttons=[];
            $toolbar_buttons[]= Pages\HtmlItems\InputButtonField::create()
                            ->setButtonAfter()
                            ->setInputField(Pages\HtmlItems\InputField::create()->setArgs(['name'=>'filter','id'=>'id_emailmsg_filter','class'=>'form-control-sm','value'=>$post_filter]))
                            ->setName('emailmsg_filtergo')
                            ->setID('id_emailmsg_filtergo')
                            ->setButtonArgs(['class'=>'btn btn-secondary btn-sm','tooltip'=>'emails.filter'])
                            ->setButtonIcon('fas fa-filter');
            //$toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fa fa-plus', 'dark', 'system.buttons.new', 'id_emailmsg_new', null, []);
            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fa fa-trash', 'danger ml-2', 'emails.delete_multi', 'id_emailmsg_del', null, ['data-delurl'=>url($this,'enable',[],['enable'=>0,'refurl'=> current_url(FALSE,TRUE)]),'form'=>'emails_msgslist_form']);
            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-clipboard-check', 'primary ml-2', 'emails.btn_markasread', 'id_emailmsg_read', null, ['data-submit'=>url($this,'enable',[],['state'=>1,'refurl'=> current_url(FALSE,TRUE)]),'form'=>'emails_msgslist_form']);
            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('far fa-times-circle', 'secondary', 'emails.btn_markasunread', 'id_emailmsg_unread', null, ['data-submit'=>url($this,'enable',[],['state'=>0,'refurl'=> current_url(FALSE,TRUE)]),'form'=>'emails_msgslist_form']);
            $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-sync', 'success ml-2', 'emails.sync', 'id_emailmsg_syns', null, ['data-url'=>url($this,'fetchEmails',[$mailbox->Name,'new'],['refurl'=> current_url(FALSE,TRUE)])]);
            
            $count=model('Emails/EmailModel')->count($filters);
            $max=10;
            $start=$this->request->getGet('page');
            $start=$start==null ? 1 : $start;
            $start=$start==1 ? 1 :($start*$max)-$max;
            $end=($start==1 ? 0 : $start)+$max;
            $end=$end > $count ? $count : $end;
            $filters['enabled']=1;
            $emails=$this->model_Emails->filtered($filters,'mail_rec DESC',$max);
            $pagination=$this->model_Emails->pager;
            $this->view->setFile('Emails/inbox')
                       ->addData('emails',$emails)
                       ->addData('contacts',$this->model_System_Contact->getForForm('ct_email','ct_name'))
                       ->addData('pagination',['start'=>$start,'end'=>$end,'links'=>$pagination!=null ? $pagination->links() : '','max'=>$count])                    
                       ->addData('url_view',url($this,'message',[$filters['mail_folder']==$mailbox->DraftsFolder ? 'new' :'view','-id-'],['refurl'=>current_url(FALSE,TRUE)]))
                       
                       ->addData('edittoolbar', Pages\HtmlItems\Toolbar::create('edittoolbar',$toolbar_buttons)->render());
                       
            return $this->view->render();
        }

        function message($mode,$id=null)
        {
            if ($mode=='view')
            {
              return $this->view($id);
            }
            $settings=$this->model_Settings->get('emails.*');
            if ($mode=='new')
            {
              $record=$id;
              if (is_numeric($record))
              {
                  $record=$this->model_Emails->find($record);
              }
              
              if (!is_array($record))
              {
                  return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('errors.emails_invalid_email','danger'));
              }else
              {
                  if (array_key_exists('mail_body', $record))
                  {
                      $record['mail_body']= strlen($record['mail_body']) > 0 ? base64_decode($record['mail_body']) : '';
                  }else
                  { 
                    $newemailtpl='';
                    if (strlen($settings['emails_newemailtpl']) > 0)
                    {
                       $newemailtpl=$this->model_Documents_Report->parseEmailTemplate($settings['emails_newemailtpl'],[]);
                       $newemailtpl= is_array($newemailtpl) && array_key_exists('body', $newemailtpl) ? $newemailtpl['body'] : '';
                    }
                    $record['mail_body']=$newemailtpl;
                  }
                  
              }
              $record['emid']='system.buttons.new';
              if (array_key_exists('mail_to', $record) && is_array($record['mail_to']))
              {
                  $record['mail_to']=implode(';',$record['mail_to']);
              }
              goto email_form;
            }
            if (!is_numeric($id))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('errors.emails_invalid_email','danger'));
            }
            
            $record=$this->model_Emails->find($id);
            
            if (strlen($record['mail_body']) > 0)
            {
               $record['mail_body']=base64_decode($record['mail_body']);
            }
            
            if ($mode=='reply' || $mode=='replyall' || $mode=='forward')
            {
                $record['mail_to_orig']=$record['mail_to'];
                $record['mail_to']=$record['mail_reply'];
                $record['mail_subject']=($mode=='forward' ? 'FWD: ' :'Re: ').$record['mail_subject'];
                $record['mail_body']=view('Emails/replyforwmsg',['msg'=>($mode=='forward' ? '' : ''),'data'=>$record]).$record['mail_body'];
            }
            $refurl=$this->getRefUrl(null,FALSE);
            email_form:
            if (array_key_exists('mail_mailbox', $record) && strlen($record['mail_mailbox']) > 0)
            {
               $mailbox=$this->model_Accounts->getMailbox($record['mail_mailbox']);
            }else
            {
                $mailbox= $this->model_Accounts->getMailbox();
            }
            $toolbar_buttons=[];
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-arrow-alt-circle-left', 'dark', 'system.buttons.back', null, null,['data-url'=>$this->getRefUrl()]);
            
            $this->getDefData($mode=='new' ? [] : $toolbar_buttons,$mailbox, is_array($id) && array_key_exists('_nofolders', $id) ? FALSE : null);
            $toolbar_buttons=[];
            
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-share-square mr-1', 'primary mr-3', 'emails.btn_send_tooltip', 'emailsEditMessageSendBtn', null, ['text'=>'emails.btn_send']);
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-times mr-1', 'outline-danger', 'emails.btn_discard_tooltip', 'emailsEditMessageCancelBtn', null, ['text'=>'emails.btn_discard']);
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('far fa-save mr-1', 'dark mr-2', 'emails.btn_draft_toolip', 'emailsEditMessageDraftBtn', null, ['text'=>'emails.btn_draft']);
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create(null, 'default', 'emails.btn_add_cc', null, null,['data-target'=>'emailsEditMessageCC','text'=>'CC']);
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create(null, 'default', 'emails.btn_add_bcc', null, null,['data-target'=>'emailsEditMessageBCC','text'=>'BCC']);
            $refurl=$this->getRefUrl();
            $this->view->setFile('Emails/compose')
                       ->addData('record',$record)
                       ->addData('edittoolbar', Pages\HtmlItems\Toolbar::create('edittoolbar',$toolbar_buttons)->render())
                       ->addData('action',url($this,'save',['emails'],['refurl'=> base64url_encode($refurl),'track'=>$this->request->getGet('track')!=null ? $this->request->getGet('track') : null]))
                       ->addData('refurl',$refurl)
                       ->addData('emid',$record['emid'])
                       ->addData('mailbox',$mailbox->toPublicKey())
                       ->addData('msgnote',array_key_exists('mail_msgnote', $record) && strlen($record['mail_msgnote']) > 0 ?  $record['mail_msgnote'] : '')
                       ->addData('maxrecpermessage',$settings['emails_maxrecpermessage'])
                       ->addEditorScript()
                       ->addBreadcrumb('emails.mainmenu',url($this))
                       ->addBreadcrumb($record['emid'], current_url());
            
            return $this->view->render();
        }
        
        private function getDefData(array $toolbar_buttons,$mailbox=null,$folder=null)
        {
            $mailbox=$mailbox==null ? $this->model_Accounts->getMailbox() : $mailbox;
            
            if ($folder==null || is_string($folder))
            {
                $folder=$folder==null ? $mailbox->InboxFolder : $folder;
                $curr_folder=$this->request->getGet('dir')==null ? $folder : $this->request->getGet('dir');
            }else
            {
                $curr_folder='';
            }
            return $this->view
                        ->addData('folders', is_bool($folder) && ! $folder ? []: $mailbox->getAllFolders())
                        ->addData('curr_folder',$curr_folder)
                        ->addData('is_sent_folder',$curr_folder==$mailbox->SentFolder)
                        ->addData('url_folder',url($this,'messages',[],['dir'=>'-dir-','refurl'=> base64url_encode($this->getRefUrl())]))
                        ->addData('move_url',url($this,'movetofolder',['-id-','-folder-'],['refurl'=> current_url(FALSE,TRUE)]))
                        ->addMenuBar($toolbar_buttons,[])
                        ->addBreadcrumb('emails.mainmenu',url($this))
                        ->addBreadcrumb($curr_folder,url($this,'messages',[],['dir'=>$curr_folder]));;
        }
        
        function compose($mode,$id=null)
        {
            $get=$this->request->getGet();
            if ($mode=='template')
            {
                $data=$this->getFlashData('_template_data');
                $data= is_array($data) ? $data : [];
                if ($id==null)
                {
                    if (!array_key_exists('_tplname', $data))
                    {
                        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_compose_template','danger'));
                    }
                    $id=$data['_tplname'];
                }
                $tpl=$this->model_Documents_Report->parseEmailTemplate($id, is_array($data) ? $data : []);
                $id=[];
                
                if (array_key_exists('customer', $get))
                {
                    $get['customer']=$this->model_System_Contact->getEmailsByAcc($get['customer']);
                    if (is_array($get['customer']) && count($get['customer']) > 0)
                    {
                        $id['mail_to']=$get['customer'];
                    }
                }
                if (is_array($tpl) && Arr::KeysExists(['subject','body'], $tpl))
                {
                    $id['mail_subject']=$tpl['subject'];
                    $id['mail_body']= base64_encode($tpl['body']);
                }
            }else
            if ($mode=='systemsupport')
            {
                $id=['mail_to'=>[config('Email')->supportEmail],'mail_body'=>'','_nofolders'=>TRUE];
                if (array_key_exists('error', $get))
                {
                    $id['mail_subject']='Error '.base64url_decode($get['error']).' from '. site_url();
                }
                
            }else
            if ($mode=='contact')
            {
                $id=$this->model_Contacts->find(is_numeric($id) ? $id : 0);
                if (Arr::KeysExists(['ct_email','ct_account'], $id))
                {
                    $id=['mail_to'=>[$id['ct_email']],'mail_msgnote'=>$id['ct_account']];
                }
            }else
            if ($mode=='customer' || $mode='geturlforcus')
            {
                if (is_numeric($id))
                {
                    $id=$this->model_Cust->find(is_numeric($id) ? $id : 0);  
                }else
                {
                   $id=$this->model_Cust->where('code',$id)->first(); 
                }
                
                if (is_array($id) && array_key_exists('code', $id))
                {
                    $id=$this->model_Contacts->getByAcc($id['code'],'ct_email');
                    if (count($id)>0 && $this->checkMultiEmails($id))
                    {
                        $id=['mail_to'=>$id];
                    } else {
                        $id=null;
                    }
                    if (is_array($id))
                    {
                        $tpl=$this->getFlashData('mail_body','');
                        if (is_array($tpl) && Arr::KeysExists(['subject','body'], $tpl))
                        {
                            $id['mail_body']=base64_encode($tpl['body']);
                            $id['mail_subject']=$tpl['subject'];
                        }
                    }
                }else
                {
                    $id=null;
                }
            }else
            if (Str::contains($mode, '@'))
            {
                $id=['mail_to'=>explode(';',$mode)];
            }
            
            if (!is_array($id))
            {
                if($mode=='geturlforcus')
                {
                  return json_encode(['error'=>lang('errors.emails_invalid_email_customer')]);  
                }else
                if ($mode=='contact')
                {
                   return redirect()->to($this->getRefUrl(null))->with('error',$this->createMessage('errors.emails_invalid_email_contact','danger')); 
                }else
                if ($mode=='customer')
                {
                   return redirect()->to($this->getRefUrl(null))->with('error',$this->createMessage('errors.emails_invalid_email_customer','danger')); 
                } else 
                {
                    error_email:
                    return redirect()->to($this->getRefUrl(null))->with('error',$this->createMessage('errors.emails_invalid_email','danger'));
                }
            }
            
            if ((array_key_exists('mail_to', $id) && !$this->checkMultiEmails($id['mail_to']))||
                (array_key_exists('cc', $id) && !$this->checkMultiEmails($id['cc']))||
                (array_key_exists('bcc', $id) && !$this->checkMultiEmails($id['bcc']))
                )
            {
                    goto error_email;
            }
                
            $mail_mode='external1';
            
            if ($mail_mode=='external')
            {
               $str='mailto:';
               if (array_key_exists('mail_to', $id) && is_array($id['mail_to']))
               {
                  $str.=implode(';',$id['mail_to']); 
               }
               
               if (array_key_exists('cc', $id) && is_array($id['cc']))
               {
                   if (!Str::contains($str, '?'))
                   {
                       $str.='?';
                   }
                  $str.='cc='.implode(';',$id['cc']); 
               }
               
               if (array_key_exists('bcc', $id) && is_array($id['bcc']))
               {
                   if (!Str::contains($str, '?'))
                   {
                       $str.='?';
                   }
                  $str.='bcc='.implode(';',$id['bcc']); 
               }
               if ($mode=='geturlforcus')
               {
                   return json_encode(['url'=>$str]);
               }
               return $this->view
                           ->setFile('Emails/redirect')
                           ->addData('url',$str)
                           ->addData('refurl',$this->getRefUrl())
                           ->render();
            }else{
                return $this->message('new',$id);
            }
            
        }
        
        private function checkMultiEmails($emails)
        {
            if (!is_array($emails))
            {
                return FALSE;
            }
            foreach($emails as $email)
            {
                if (!Str::isValidEmail($email))
                {
                    return FALSE;
                }
            }
            return TRUE;
        }
        
        function fetchEmails($mailbox,$criteria,$folder=null)
        {
            if (is_string($mailbox))
            {
                $mailbox=$this->model_Accounts->getMailbox($mailbox);
            }
            if ($criteria=='new')
            {
                $this->model_Emails->fetchNewEmailsFromMailbox($mailbox);
                return redirect()->to($this->getRefUrl());
            }else
            if (Str::startsWith ($criteria,'sync'))
            {
                $this->model_Tasks_Task->addNew('Fetch emails',['controller'=>'Emails/EmailModel','action'=>'syncMailbox','args'=>[$mailbox->Name,Str::endsWith($criteria, '_fresh')]],'fetch_emails');
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.msg_syncbulk_taks_ok','info'));
            }
            $this->model_Emails->fetchEmailsForMailbox($mailbox,$folder,$folder,$criteria);
        }
        
        private function view($record)
        {
            //dump($this->model_Emails->getEmailDataByID($record,TRUE));exit;
            if (!is_numeric($record))
            {
                error_id:
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_invalid_email','danger'));
            }
            $refurl=$this->getRefUrl(null,FALSE);
            
            $email=$this->model_Emails->find($record);
            if (!is_array($email))
            {
                goto error_id;
            }
            $email['mail_attachements']= json_decode($email['mail_attachements'],TRUE);
            $mailbox=$this->model_Accounts->getMailbox($email['mail_mailbox']);
            $folders=array_flip($mailbox->getAllFolders(FALSE,TRUE));
            if ($this->request->getGet('attachement')!=null)
            {
                if (!is_array($email['mail_attachements']) || (is_array($email['mail_attachements']) && !array_key_exists($this->request->getGet('attachement'), $email['mail_attachements'])))
                {
                    error_noattachements:
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_noattachement','danger'));
                }
                $email['mail_attachements']=$email['mail_attachements'][$this->request->getGet('attachement')];
                $email['mail_attachements']['id']=$this->request->getGet('attachement');
                $get=$this->request->getGet();
                
                $attach=$mailbox->getClient($email['mail_folder'])->getMail($email['mail_msgid'])->getAttachments();
                if (!is_array($attach))
                {
                    goto error_noattachements;
                }
                $attach= array_values($attach);
                if (!array_key_exists($email['mail_attachements']['id'], $attach))
                {
                    goto error_noattachements;
                }
                $attach=$attach[$email['mail_attachements']['id']];
                if (!is_a($attach, '\PhpImap\IncomingMailAttachment'))
                {
                    goto error_noattachements;
                }
                $attach->setFilePath(parsePath('@temp/'.$email['mail_attachements']['name'],TRUE));
                $attach->saveToDisk();
                header('Content-Disposition: attachment; filename="' .$email['mail_attachements']['name']. '"');
                $this->response->setHeader('Content-Type','application/octet-stream');
                ob_clean();
                flush();
                readfile($attach->filePath);
                unlink($attach->filePath);exit;
            }
            if (strlen($email['mail_body']) > 0)
            {
               $email['mail_body']=base64_decode($email['mail_body']);
            }
            $urls_args=['refurl'=>$refurl];
            if ($this->request->getGet('track')!=null)
            {
                $urls_args['track']=$this->request->getGet('track');
            }
            $toolbar_buttons=[];//
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-backspace', 'dark', 'emails.btn_back', 'id_msgview_btn_back', null, ['data-url'=>$this->getRefUrl()]);
            $this->getDefData($toolbar_buttons,null,$email['mail_folder']);
            $toolbar_buttons=[];
            if ($email['mail_type']!='out')
            {
                $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-reply', 'default ml-2', 'emails.btn_reply', 'id_msgview_btn_reply', null, ['data-url'=>url($this,'message',['reply',$record],$urls_args)]);
                $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-reply-all', 'default', 'emails.btn_replyall', 'id_msgview_btn_replyall', null, ['data-url'=>url($this,'message',['replyall',$record],$urls_args)]);
            }
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-share', 'default', 'emails.btn_forward', 'id_msgview_btn_forward', null, ['data-url'=>url($this,'message',['forward',$record],$urls_args)]);
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('fas fa-print', 'dark ml-2 mr-2', 'emails.btn_print', 'id_msgview_btn_print', null, []);
            if (intval($email['mail_read'])==0 && $email['mail_type']!='out')
            {
                $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('fas fa-clipboard-check', 'primary', 'emails.btn_markasread', 'id_emailmsg_read', null, ['data-url'=>url($this,'enablesingle',['emailstat',$email['emid'],1],['refurl'=> current_url(FALSE,TRUE)])]);
            }else
            if ($email['mail_type']!='out')
            {
                $toolbar_buttons[]=Pages\HtmlItems\ToolbarButton::create('far fa-times-circle', 'secondary', 'emails.btn_markasunread', 'id_emailmsg_unread', null, ['data-url'=>url($this,'enablesingle',['emailstat',$email['emid'],0],['refurl'=> current_url(FALSE,TRUE)])]);
            }
            $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::create('far fa-trash-alt', 'outline-danger mr-2', 'emails.btn_delete', 'id_msgview_btn_delete', null, ['data-delurl'=>url($this,'deletesingle',['emails',$record],['refurl'=>$refurl])]);
            
            if ($email['mail_type']!='out')
            {
                $toolbar_buttons[]= Pages\HtmlItems\InputButtonField::createDropDownFieldButton('moved_folder',$mailbox->getAllFolders(FALSE,TRUE),'fas fa-folder',['class'=>'btn btn-dark btn-sm','tooltip'=>'emails.btn_move','data-move'=>$email['emid']],['class'=>'form-control-sm','value'=>$folders[$email['mail_folder']]]);
                if (in_array($email['mail_folder'], ['Quotes']))
                {
                    $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-file-invoice-dollar', 'warning ml-3', url('Orders','quotes',['new','convert'],['email'=>$email['emid'],'refurl'=> current_url(FALSE,TRUE)]), 'orders.quotes_btn_convertemail_tooltip', null, []);
                }else
                {
                    $toolbar_buttons[]= Pages\HtmlItems\ToolbarButton::createDataUrlButton('fas fa-hand-holding-usd', 'warning ml-3', url('Orders','opportunities',['new'],['email'=>$email['emid'],'refurl'=> current_url(FALSE,TRUE)]), 'orders.opportunities_btn_convertemail_tooltip', null, []);
                }
                
            }
            $this->view->setFile('Emails/view')
                       ->addData('record',$email)
                       ->addData('edittoolbar', Pages\HtmlItems\Toolbar::create('edittoolbar',$toolbar_buttons)->render())
                       ->addData('url_attach',url($this,'message',['view',$email['emid']],['attachement'=>'-id-','refurl'=> current_url(FALSE,TRUE)]))
                       ->addBreadcrumb('emails.mainmenu',url($this))
                       ->addBreadcrumb($email['mail_folder'],url($this,'messages',[],['dir'=>$email['mail_folder']]))
                       ->addBreadcrumb($record,'/');
            if ($this->request->isAjax())
            {
                return json_encode(['data'=> base64_encode(view('Emails/view',$this->view->getViewData()))]);
            }
            return $this->view->render();
        }
        
        function settings($tab,$record)
        {
            $settings=$this->model_Settings->get('emails.*',FALSE,'*');
            
            $view=new Pages\FormView($this);
            if ($tab=='cfg')
            {
                
                $view->addYesNoField('emails.settings_imapmode', $settings['emails_imapmode']['value'], 'settings[emails_imapmode]', []);
                $view->addDropDownField('emails.settings_newemailtpl', 'settings[emails_newemailtpl]', $this->model_Documents_Report->getTemplatesForForm(), $settings['emails_newemailtpl']['value'], []);
                
                
            }else
            if ($tab=='campaigns')
            {
                $input_type=$this->model_Campaign->getStatusListForDropDown();
                $tpls=$this->model_Documents_Report->getTemplatesForForm();
                $args=['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE).'&tab=campaigns')])];
                $view->addCustomElementsListField('emails.settings_enabledstatus', 'settings[campaigns_enabledstatus]', $settings['campaigns_enabledstatus']['value'], ['input_type'=>$input_type]);
                $view->addCustomElementsListField('emails.settings_disableddstatus', 'settings[campaigns_disableddstatus]', $settings['campaigns_disableddstatus']['value'], ['input_type'=>$input_type]);
                
                $view->addDropDownEditableField('emails.settings_defnotifytpl', 'settings[campaigns_defnotifytpl]',$tpls, $settings['campaigns_defnotifytpl']['value'],$args);
                $view->addNumberField('emails.settings_houremailslimit',$settings['campaigns_houremailslimit']['value'], 'settings[campaigns_houremailslimit]',400,1);
                $view->addNumberField('emails.settings_dayemailslimit',$settings['campaigns_dayemailslimit']['value'], 'settings[campaigns_dayemailslimit]',40000,1);
                $view->addNumberField('emails.settings_massmailhourdelay',$settings['campaigns_massmailhourdelay']['value'], 'settings[campaigns_massmailhourdelay]',60,1);
            }
            return view('System/form_fields',$view->getViewData());
        }   
        
        

        function getEmailsByAccount(string $account,string $mode,int $limt=10)
        {
            $emails=$this->model_Emails->getEmailsForCustomer($account,1,$limt);
            
            if (!is_array($emails))
            {
                goto _endfunc;
            }
            $data=[];
            $data['emails']=$emails;
            $data['url_email_view']=url($this,'message',['view','-id-'],['track'=>$account,'refurl'=>current_url(FALSE,TRUE)]);
            _endfunc:
            return view('Emails/widget',$data);  
        }

        /**
         * Enable or disable items in DB
         * 
         * @param array  $post
         * @param string $msgYes
         * @param string $msgNo
         * 
         * @return bool
         */
        public function enable($post = null,$msgYes=null,$msgNo=null)
        {
            $post=$this->request->getPost();
            $get=$this->request->getGet();
            $refurl=$this->getRefUrl();
            if (array_key_exists('model', $post) && $post['model']=='emails')
            {
                if (array_key_exists('emid', $post) && is_array($post['emid']))
                {
                    if (array_key_exists('state', $get))
                    {
                         if ($this->model_Emails->setMessageState($post['emid'],$get['state']))
                         {
                             return redirect()->to($refurl)->with('error', $this->createMessage('emails.msg_emails_state_'.$get['state'], 'success'));
                         }
                         return redirect()->to($refurl)->with('error', $this->createMessage('emails.error_emails_state_'.$get['state'], 'danger'));
                    }else
                    {
                        foreach($post['emid'] as $email)
                        {
                            $this->addMovementHistory('remove_email', null, null,$email);
                        }
                        return parent::enable($post, 'emails.msg_emails_removed', 'emails.error_emails_removed'); 
                    }
                
                }else
                {
                  return redirect()->to($refurl)->with('error', $this->createMessage('emails.error_noemails_sel', 'warning'));  
                }
            }
            return parent::enable($post, $msgYes, $msgNo);
        }
        
        function enablesingle($model, $id, $value, $field = null) 
        {
            if ($model=='emailstat')
            {
                if ($this->model_Emails->setMessageState($id,$value))
                {
                    return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('emails.msg_emails_state_'.$value, 'success'));
                }
                return redirect()->to($this->getRefUrl())->with('error', $this->createMessage('emails.error_emails_state_'.$value, 'danger'));
            }
            return parent::enablesingle($model, $id, $value, $field);
        }
        
        function movetofolder($msgID,$folderID)
        {
            if (!is_numeric($msgID))
            {
                emails_invalid_email:
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_invalid_email','danger'));
            }
            $msgID=$this->model_Emails->find($msgID);
            if (!is_array($msgID))
            {
                goto emails_invalid_email;
            }
            $mailbox=$this->model_Accounts->getMailbox($msgID['mail_mailbox']);
            $folders=$mailbox->getAllFolders(FALSE,TRUE);
            if (!array_key_exists($folderID, $folders))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_invalid_folder','danger'));
            }
            if (!$this->model_Emails->moveToFolder($msgID['emid'],$folders[$folderID]))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_folder_move','danger'));
            }
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.msg_folder_move','success'));
        } 
        
        function save($type, $post = null) 
        {
            $post = $post == null ? $this->request->getPost() : $post;
            $refurl = $this->getRefUrl();
            
            if ($type=='campaign')
            {
                if (Arr::KeysExists(['ec_starton','ec_endon'], $post))
                {
                    $time=convertDate(formatDate(),null, 'Hi');
                    $post['ec_starton']=substr($post['ec_starton'],0,8).$time;
                    $post['ec_endon']=substr($post['ec_endon'],0,8).$time;
                }
                if (!is_numeric($post['ecid']))
                {
                    $post['ec_addedon']= formatDate();
                    $post['ec_addedby']= loged_user('name');
                }
                
            }else
            if ($type=='target')
            {
                if (array_key_exists('ect_contacts', $post))
                {
                    $post['ect_contacts']= json_encode($post['ect_contacts']);
                }
                if (Arr::KeysExists(['ectrgid','ect_name'], $post) && !is_numeric($post['ectrgid']))
                {
                    $post['ect_code']= strtolower(mb_url_title($post['ect_name'], '_'));
                }
            }
            if ($type=='emails')
            {
                if (array_key_exists('mail_body', $post))
                {
                    $post['mail_body']= base64_encode($post['mail_body']);
                }
                
                if (array_key_exists('id', $post))
                {
                    $post['emid']= $post['id'];
                }
                
                if (array_key_exists('mailbox', $post))
                {
                    $post['mailbox']= json_decode(base64_decode($post['mailbox']),TRUE);
                    if (!is_array($post['mailbox']))
                    {
                        error_sendmsg:
                        return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('emails.error_sendmsg','danger'));
                    }
                }else
                {
                    goto error_sendmsg;
                }
                $post['mail_type']='out';
                $post['mail_msgid']=formatDate();
                $msg='emails.msg_sendmsg';
                if (array_key_exists('folder', $post) && $post['folder']=='drafts')
                {
                    $post['enabled']=1;
                    $post['mail_type']='tmp';
                    $post['mail_folder']=$post['mailbox']['DraftsFolder'];
                    $msg='emails.msg_sendmsg_draft';
                    $post['mail_msgid'].='draft';
                }else
                {
                    $post['enabled']=0;
                    $post['mail_folder']=$post['mailbox']['SentFolder'];
                    $post['mail_msgid'].='sent';
                }
                
                $post['mail_msguser']= loged_user('username');
                $post['mail_mailbox']=$post['mailbox']['Name'];
                unset($post['mailbox']);
                unset($post['emid']);
                $post['mail_sent']= formatDate();
                $post['mail_rec']= formatDate();
                $post['mail_read']=1;
                $model=$this->model_Emails;
                if ($this->request->getGet('track')!=null)
                {
                    $post['msg_note']=$this->request->getGet('track');
                }
                
                if (!$model->save($post))
                {
                    return redirect()->to($this->getRefUrl())->with('error',$this->createMessage($model->errors(),'danger')); 
                }
                $this->_after_save($type, $post, $this->getRefUrl(), $this->getRefUrl());
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage($msg,'success'));
            }
            
            return parent::save($type, $post);
        }
        
        function _after_save($type, $post, $refurl, $refurl_ok): bool 
        {
               
            if ($type=='campaign' || $type=='model_campaign')
            {
                $settings=$this->model_Campaign->getSettings();
                if (!array_key_exists('ecid', $post))
                {
                    $post['ecid']=$this->model_Campaign->getLastID();
                }
                if (array_key_exists('ec_status',$post) && in_array($post['ec_status'], $settings['campaigns_enabledstatus']))
                {
                    $this->model_Campaign->changeStatus($post);
                }else
                if (array_key_exists('ec_status',$post) && in_array($post['ec_status'], $settings['campaigns_disableddstatus']))
                {
                    $this->model_Campaign->changeStatus($post,FALSE);
                }
            }
            return TRUE;
        }
        
        function sendFromMailbox($mailbox, $to, $subject, $msg, array $cc = [], array $bcc = [], $attachements = [], $debug = FALSE): mixed 
        {
            $mailbox=$this->model_Emails_Mailbox->getMailbox($mailbox);
            if ($mailbox==null)
            {
                $mailbox=$this->model_Emails_Mailbox->getMailbox();
            }
            $msg= base64_decode($msg);
            return $mailbox->sendEmail($to,$subject,$msg,$cc,$bcc,$mailbox->Name, $attachements);
        }
        
        /**
         * Returns menu items form/array with items names
         * 
         * @param  mixed $value
         * @param  bool  $justItems
         * @return mixed
         */
        function getMenuItemsData($value = null, $justItems = FALSE) 
        {
            $this->availablemenuitems=array_combine($this->availablemenuitems, lang('carsassets.menu_action_list'));
            return parent::getMenuItemsData($value, $justItems);
        }
        
        
}