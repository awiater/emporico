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

class TargetModel extends \EMPORIKO\Models\BaseModel 
{

    /**
     * Table Name
     * 
     * @var string
     */
    protected $table='emails_campaignstargets';
    
    /**
     * Table primary key name
     * 
     * @var string
     */
    protected $primaryKey = 'ectrgid';
    
    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields=['ect_name','ect_code','ect_desc','ect_addedon','ect_addby'
                              ,'ect_contacts','enabled'];
        
    protected $validationRules =
    [
        'ect_name' => 'required|is_unique[emails_campaignstargets.ect_name,ectrgid,{ectrgid}]',
    ];
    
    protected $validationMessages = 
    [
        'ect_name'=>[
            'is_unique' => 'emails.error_unique_ect_name'
        ]
    ];
    
    /**
     * Fields types declarations for forge
     * 
     * @var array
     */
    protected $fieldsTypes=
    [
        'ectrgid'=>         ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
        'ect_name'=>        ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
        'ect_code'=>        ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
        'ect_desc'=>        ['type'=>'VARCHAR','constraint'=>'200','null'=>TRUE],
        'ect_addedon'=>     ['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
        'ect_addby'=>       ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
        'ect_contacts'=>    ['type'=>'MEDIUMTEXT','null'=>TRUE],
        'enabled'=>         ['type'=>'INT','constraint'=>'11','default'=>'1','null'=>FALSE],
        
    ];
    
    function getFieldsForForm(array $record) 
    {
        //'','','',','',''
        $arr=[];
        $arr['ect_name']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                ->setName('ect_name')
                ->setID('ect_name')
                ->setText('ect_name')
                ->setMaxLength(50)
                ->setTab('general');
        
        $arr['ect_desc']= \EMPORIKO\Controllers\Pages\HtmlItems\TextAreaField::create()
                ->setName('ect_desc')
                ->setID('ect_desc')
                ->setText('ect_desc')
                ->setMaxLength(200)
                ->setTab('general');
        
        $arr['enabled']= \EMPORIKO\Controllers\Pages\HtmlItems\YesNoField::create()
                ->setName('enabled')
                ->setID('enabled')
                ->setText('ect_enabled')
                ->setTab('general');
        
        $arr['ect_contacts']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                ->setName('ect_contacts')
                ->setID('ect_contacts')
                ->setText('ect_contacts')
                ->setTab('contacts')
                ->setInputField(\EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()->setArgs(['name'=>'ect_contacts_input','id'=>'id_ect_contacts_input','options'=>$this->getModel('System/Contact')->getEmailsForTargetForm(),'advanced'=>TRUE]));
        return $arr;
    }
}