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
    'settings_fldtab'=>'Other Settings',
    'settings_folder_quotes'=>'Quotes Folder',
    'settings_folder_quotes_tooltip'=>'Folder path to which quotes will be uploaded',
    'settings_folder_orders'=>'Orders Folder',
    'settings_folder_orders_tooltip'=>'Folder path to which autopart orders will be saved',
    'settings_folder_orders_status'=>'Orders Status Folder',
    'settings_folder_orders_status_tooltip'=>'Path to folder where orders status files will be uploaded',
    'settings_quotereporttab'=>'Quote CSV',
    'settings_quotehtmtab'=>'Quote HTM',
    'settings_quotefilecol_ord_ref'=>'Reference',
    'settings_quotefilecol_ord_ref_tooltip'=>'Order/Quote reference number',
    'settings_quotefilecol_ord_refcus'=>'Customer Reference',
    'settings_quotefilecol_ord_refcus_tooltip'=>'Order/Quote customer reference number',
    'settings_quotefilecol_ord_addon'=>'Added On',
    'settings_quotefilecol_ord_addon_tooltip'=>'Order/Quote added on date',
    'settings_quotefilecol_ord_status'=>'Default Status',
    'settings_quotefilecol_ord_status_tooltip'=>'Default order status set when quote are fetched from Autopart',
    'settings_quotefilecol_ord_cusacc'=>'Customer Code',
    'settings_quotefilecol_ord_cusacc_tooltip'=>'Order/Quote customer short code (ie CUS001)',
    'settings_quotefilecol_ol_oepart'=>'TecDoc Part',
    'settings_quotefilecol_ol_oepart_tooltip'=>'TecDoc (Supplier / OE) part number ',
    'settings_quotefilecol_ol_qty'=>'Qty',
    'settings_quotefilecol_ol_qty_tooltip'=>'Part quantity',
    'settings_quotefilecol_ol_price'=>'Cost',
    'settings_quotefilecol_ol_price_tooltip'=>'Part costs (system)',
    'settings_quotefilecol_ol_ourpart'=>'Our Part Number',
    'settings_quotefilecol_ol_ourpart_tooltip'=>'Internal (our) part number',
    'settings_quotefilecol_ol_commodity'=>'Commodity Code',
    'settings_quotefilecol_ol_commodity_tooltip'=>'Part commodity code number (10 digits)',
    'settings_quotefilecol_ol_origin'=>'Origin',
    'settings_quotefilecol_ol_origin_tooltip'=>'Part code of origin',
    'settings_quotefilecol_ord_addon_format'=>'Date Format',
    'settings_quotefilecol_ord_addon_format_tooltip'=>'Order/Quote date format used in file',
    'settings_quotefilecol_ol_avalqty'=>'Available Qty',
    'settings_quotefilecol_ol_avalqty_tooltip'=>'Currently in stock part quantity',
    'settings_quotefilecol_ol_status'=>'Part Status',
    'settings_quotefilecol_ol_status_tooltip'=>'Current status of part (ins stock, in transit etc)',
    'settings_emails_from'=>'Email From',
    'settings_emails_from_tooltip'=>'Email address to which quotes/orders/invoices are send',
    'settings_quotereport_label'=>'Please provide column index from file for each option.<br>Columns start from 0 (ie column A is 0, B is 1)',
    'settings_quotehtm_label'=>'Please provide column (field) class/tag reference for each option.',
    'settings_orderfiletab'=>'Order CSV',
    'settings_orderfilecol_ord_reftpl'=>'Order Reference Template',
    'settings_orderfilecol_ol_statustpl'=>'Order Line Status Template',
    'settings_folder_pricing'=>'Price Files Folder',
    'settings_folder_pricing_tooltip'=>'Path to folder where price file will be saved',
    'settings_pricing_iploadtpl'=>'Price File Upload Template',
    'settings_pricing_iploadtpl_tooltip'=>'Determines which upload template will be used when loading new price files from folder',
    'settings_logtab'=>'Logs',
    'settings_scheduler_items'=>'Enabled Jobs',
    'settings_scheduler_items_tooltip'=>'Jobs which will be run by auto tasks scheduler',
    'settings_job_quotesemails'=>'Extract Quotes From Emails',
    'settings_job_quotes'=>'Create Quotes From File',
    'settings_job_orders'=>'Create Orders From File',
    'settings_job_pricefiles'=>'Create/Update Price Files From File',
    
    'croncmd'=>'Autopart Scheduler',
    
    'movementlog'=>'Autopart Log Entry',
    'movementlog_pricefile'=>'Upload Price File',
    'movementlog_colfrom'=>'Status',
    'movementlog_colcomm'=>'Tasks',
    'movementlog_quotes'=>'Create Quote',
    'movementlog_orders'=>'Create Order',
    'movementlog_sched'=>'Run Jobs',

];