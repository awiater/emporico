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

return 
[
    'contacts_mainmenu'=>'Contacts Book',
    'accounts_contactsbtn'=>'Contacts List',
    
    'contacts_viewbtn'=>'Edit Details',
    'ct_name'=>'Full Name',
    'ct_email'=>'Email Address',
    'ct_desc'=>'Description',
    'ct_account'=>'Linked Account',
    'ct_account_name'=>'Customer',
    'ct_account_sname'=>'Supplier',
    'ct_phone'=>'Main Phone Nr',
    'ct_phone2'=>'Additional Phone Nr',
    'ct_phone3'=>'Additional Phone Nr',
    'ct_linkid'=>'LinkedID Profile',
    'ct_faceb'=>'Facebook Profile',
    'ct_other'=>'Other',
    'ct_notes'=>'Notes',
    'ct_group'=>'Group',
    'ct_group_tooltip'=>'Contact group name used for campaigns etc',
    
    'contacts_cttab'=>'Contact Details',
    'contacts_soctab'=>'Social Media',
    'contacts_custab'=>'Customer Accounts',
    'contacts_othtab'=>'Other Details',
    'contacts_btn_upload'=>'Upload Contacts',
    'contacts_btn_edit'=>'Edit Contact Details',
    'contacts_btn_del'=>'Remove Contact',
    
    'caller_record_modal_title'=>'Record Call',
    'call_number'=>'Number to Call',
    'call_info'=>'Info About Call',
    'call_makebtn'=>'Make Call',
    'call_recordbtn'=>'Record Info',
    
    'pages_redirect'=>'Default - Redirect',
    'pages_link'=>'Redirect url',
    'pages_link_tooltip'=>'Url to which page will be redirected',
    'pages_inframe'=>'Open In Frame',
    'pages_inframe_tooltip'=>'Determines if link will be open in embedded frame',
    
    'ect_mainmenu'=>'Campaign Target Lists',
    'ect_editheader'=>'Edit Target List',
    'ect_name'=>'Name',
    'ect_code'=>'Target Code',
    'ect_desc'=>'Description',
    'ect_addedon'=>'Added On',
    'ect_addby'=>'Added By',
    'ect_contacts'=>'Contacts List',
    'ect_enabled'=>'Is Live',
    'ect_contacts_tab'=>'Contacts',
        
    'ec_mainmenu'=>'Campaigns',
    'ec_editheader'=>'Edit Campaign Details',
    'ec_name'=>'Campaign Name',
    'ec_status'=>'Status',
    'ec_status_list'=>['Planning','Started','Completed'],
    'ec_budget'=>'Budget',
    'ec_budget_tooltip'=>'Specify budget value for this campaign',
    'ec_starton'=>'Start On',
    'ec_starton_tooltip'=>'Determines when campaign will start',
    'ec_endon'=>'Ends on',
    'ec_endon_tooltip'=>'Determines when campaign will ends',
    'ec_addedon'=>'Added On',
    'ec_addedby'=>'Added By',
    'ec_list'=>'Target Contacts',
    'ec_list_tooltip'=>'Determines targets that should receive messages.',
    'ec_desc'=>'Description',
    'ec_type'=>'Type',
    'ec_type_tooltip'=>'Determines campaign type<br>Avaliable types:<br>Emails - Mass mail campaign<br>Web - Online flyers, promotions<br>Flyer - Flyers, paper based promotions',
    'ec_type_list'=>['Emails','Flyer'],
    'ec_tpl'=>'Campaign Template',
    'ec_tpl_subj'=>'Email Subject',
    'ec_tpl_body'=>'Email Body',
    'enabled'=>'Is Live',
    'ec_tabeditor'=>'Email Design',
    'ec_tpltagname'=>'Receipent Name',
    'ec_tpltagemail'=>'Receipent Email',
    'ec_tpltagsiteurl'=>'Website base url',
    'ec_tpl_mailbox'=>'Mailbox',
    'ec_tpl_mailbox_tooltip'=>'Mailbox which will be used to send emails from',
    'ec_tabnotify'=>'Notification',
    'ec_notify'=>'Notification Users',
    'ec_notify_tooltip'=>'Determines which users will be notify about campaign start/end',
    'ec_tabmov'=>'Activity',
    'ec_links'=>'Tracking Links',
    'ec_links_tooltip'=>'Tracking links list (links which will be tracked)<br>For internal use @ before link segments',
    'ec_tabpaper'=>'Flyer Editor',
    
    
    'msg_call_recorded'=>'Call info recorder sucessfully',
    'msg_campaign_start'=>'Campaign Start successfully',
    'msg_campaign_status_start'=>'started',
    'msg_campaign_status_end'=>'Ends',
    'msg_campaign_stop'=>'Campaign Ends successfully',
    
    'mov_record_call'=>'Make a Call',
    'mov_record_call_view'=>'{mhinfo}',
    'mov_record_call_info'=>'{user} Record Call to {caller_number}<br>{call_info}',
    
    'error_no_caller_number'=>'Invalid calling number',
    'error_no_call_action'=>'Invalid record call action. Please contact support',
    'error_unique_ct_name'=>'The Name field must contain a unique value. Given name already in use',
    'error_unique_ct_email'=>'Given email address already in use',
    'error_required_ct_name'=>'Name Field is required',
    'error_invalid_ectid'=>'Invalid Target List ID. Please contact system support',
    'error_unique_ec_name'=>'Campaign Name must be have unique name, one you provided is already in use please choose diffrenet one.',
    'error_unique_ect_name'=>'Campaign Target List Name must be have unique name, one you provided is already in use please choose diffrenet one.',
    'error_invalid_ecid'=>'Campaign ID. Please contact system support',
    'error_campaign_start'=>'Cannot start campaign. Please contact system support',
    'error_campaign_stop'=>'Cannot end/stop campaign. Please contact system support',
];