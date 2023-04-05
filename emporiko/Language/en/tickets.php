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
    'notify_tile_header'=>'Recent Notifications',
    'notify_msg_noitems'=>'No Unread Notifications',
    
    'ticket_main'=>'Cases',
    'ticket_edit'=>'Add New Case',
    'tck_subject'=>'Subject',
    'tck_desc'=>'Description',
    'tck_desc_tooltip'=>'Please provide description of request/problem',
    'tck_status'=>'Status',
    'tck_priority'=>'Priority',
    'tck_priority_list'=>['Low','Normal','High','Urgent'],
    'tck_type'=>'Case Type',
    'tck_account'=>'Linked Account',
    'tck_addedon'=>'Created On',
    
    'tck_newmodal_title'=>'Plase Choose Case Type',
    'tck_newmodal_btnopen'=>'Add New Case',
    
    'mov_casefollow'=>'Case Comment',
    'casefollow_created'=>'Case Created by {contact}',
    
    'templates_main'=>'Cases Templates',
    'templates_edit'=>'Edit Case Template',
    'templates_name'=>'Name',
    'templates_title'=>'Title',
    'templates_desc'=>'Description',
    'templates_iscustomer'=>'Is Customer',
    'templates_iscustomer_tooltip'=>'Determines if this template is avaliable to customer to use',
    'templates_targetgrp'=>'Target Group',
    'templates_targetgrp_tooltip'=>'Determines which user group(s) will be notify about this case',
    'templates_new'=>'Blank Case',
    'templates_new_tooltip'=>'New blank case',
    'templates_tpltab'=>'Template',
    
    'templates_descfield'=>'Case Description',
    'templates_descfield_tooltip'=>'This field will be used as case description template',
    'templates_subjectfield'=>'Case Subject',
    'templates_subjectfield_tooltip'=>'This field will be used as case subject template',
    'templates_extrafields_tab'=>'Fields',
    'templates_extrafields'=>'Extra Fields',
    'templates_enabled'=>'Enabled',
    'templates_enabled_tooltip'=>'Determines if this template can be used as new case',
    'templates_tcktype'=>'Ticket Type',
    'templates_editabledesc'=>'Case Description Editable',
    'templates_editabledesc_tooltip'=>'Determines if when creating new case case description is editable',
    
    'types_main'=>'Ticket Types',
    'types_edit'=>'Edit Ticket Type Details',
    'tit_name'=>'Name',
    'tit_desc'=>'Description',
    'tit_editable'=>'Is Editable',
    'tit_editable_tooltip'=>'Determines if this type is editable by users below super admin access',
    'tit_type'=>'Tile Color',
    'tit_type_tooltip'=>'Determines tile color in new ticket window',
    'tit_type_list'=>['Yellow','Blue','Green','Red','Turquoise'],
    'tit_icon'=>'Tile Icon',
    'tit_icon_tooltip'=>'Determines tile background icon',
    'tit_textcolor'=>'Tile Text Color',
    'tit_textcolor_tooltip'=>'Determines tile text color in new ticket window',
    'tit_textcolor_list'=>['Yellow','Blue','Green','Red','Turquoise','Black','White'],
    'tit_canconvert'=>'Can be converted',
    'tit_canconvert_tooltip'=>'Determines if ticket can be converted to quote, oportunity (template must contains default fields of target)',
    'tit_order'=>'Show Order',
    'tit_order_tooltip'=>'Determines on what order types will be shown in new case list',
    'type_blankconv'=>'No Conversion',
    
    'btn_backtolist'=>'Back to Cases List',
    'btn_addcomm'=>'Add Follow Up Comment',
    'btn_changepriority'=>'Change Priority',
    'btn_showdetails'=>'Show Case Details',
    'btn_ticketclose'=>'Close Case',
    'btn_ticketreject'=>'Reject Case',
    'btn_ticketreopen'=>'Reopen Case',
    'btn_convert'=>'Convert Case',
    'btn_addnew'=>'Add New Case',
    
    'modal_changepriority_title'=>'Change Case Priority',
    'modal_comment'=>'Comment',
    'triger_ticket_assigned'=>'Ticket Assigned To Support',
    'msg_ticket_assigned'=>'You ticket is now assigned to our support team',
    'msg_ticket_print_title'=>'Case {0} Details',
    'msg_assigned_mov'=>'Ticket assigned to {0}',
    'msg_casestatusclosed'=>'Case closed:<br>{0}',
    'msg_casestatusrejected'=>'Request rejected:<br>{0}',
    'msg_casereopenok'=>'Case reopened successfully',
    'msg_convert_to_quote_ok'=>'Case converted successfully. {0}',
    'msg_no_data'=>'No available Cases to show',
    
    'customer_view'=>'Live Cases',
    'customer_view_notickets'=>'Customer Not have any live cases at the moment',
    'customer_view_openbtn'=>'Show all cases for customer',
    
    'settings_cfgtab'=>'Settings',
    'settings_systemsupport'=>'System Support',
    'settings_itsupportemail'=>'System IT Support Email Address',
    'settings_deftargetgroups'=>'Default Target Group(s)',
    'settings_deftargetgroups_tooltip'=>'Determines user groups which will be notify about changes on cases',
    'settings_newtickettpl'=>'New Ticket Email Template',
    'settings_newtickettpl_tooltip'=>'Template used for new ticket notification',
    'settings_ticketupdatetpl'=>'Ticket Update Email Template',
    'settings_ticketupdatetpl_tooltip'=>'Template used for ticket update notification',
    'settings_supportteamname'=>'Support Team Contact Name',
    'settings_supportteamname_tooltip'=>'Name of user/team used in email templates as {contact}<br>You can use below placeholders:<br>@username - logged username<br>@name - logged user full name',
    'settings_ticketnonpendingstatus'=>'Completed Statuses',
    'settings_ticketnonpendingstatus_tooltip'=>'List with statuses which are uses',
    'settings_ticketreopenstatus'=>'Re-opened Case Status',
    'settings_ticketreopenstatus_tooltip'=>'Ticket status which is used when case is reopen',
    'settings_tickets_status_assigned'=>'Assigned',
    'settings_tickets_status_pending'=>'Pending',
    'settings_tickets_status_closed'=>'Closed',
    'settings_tickets_status_rejected'=>'Rejected',
    'error_reopencasefailed'=>'Cannot reopen case. Please contact support',
    
    'error_case_id'=>'Invalid Case Number',
    'error_convert_to_quote_failed'=>'Cannot convert to case to quote. Please contact support',
];