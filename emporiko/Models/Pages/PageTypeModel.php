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
 
namespace EMPORIKO\Models\Pages;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class PageTypeModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='pages_types';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'pgtid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['pgt_name','pgt_urltooltip','pgt_cfgact','pgt_allowguest','pgt_defaccess','pgt_editable','pgt_removable','pgt_manualadd'];
	
	protected $validationRules =
	[
		'pgt_name'=>'required|is_unique[pages_types.pgt_name,pgtid,{pgtid}]',
                'pgt_cfgact'=>'required'
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'pgtid'=>           ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
                'pgt_name'=>        ['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
                'pgt_urltooltip'=>  ['type'=>'MEDIUMTEXT','null'=>FALSE],    
		'pgt_cfgact'=>      ['type'=>'MEDIUMTEXT','null'=>FALSE],
                'pgt_allowguest'=>  ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
                'pgt_defaccess'=>   ['type'=>'VARCHAR','constraint'=>'150','default'=>'customer','null'=>FALSE],
                'pgt_editable'=>    ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
		'pgt_removable'=>   ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
                'pgt_manualadd'=>   ['type'=>'INT','constraint'=>'11','default'=>1,'null'=>FALSE],
	];
        
        /**
         * Add new page type to database
         * 
         * @param string $name
         * @param string $urltooltip
         * @param type $cfgact
         * @param bool $allowguest
         * @param string $defaccess
         * @param bool $editable
         * @param bool $removable
         * 
         * @return bool
         */
        function addNew(string $name,string $urltooltip,$cfgact,bool $allowguest=TRUE,string $defaccess=\EMPORIKO\Helpers\AccessLevel::view,bool $editable=TRUE,bool $removable=TRUE)
        {
            return $this->save(
            [
                'pgt_name'=>$name,
                'pgt_urltooltip'=>$urltooltip,
                'pgt_cfgact'=>$cfgact,
                'pgt_allowguest'=>$allowguest ? 1 :0,
                'pgt_defaccess'=>$defaccess,
                'pgt_editable'=>$editable ? 1 :0,
                'pgt_removable'=>$removable ? 1 :0,
            ]);
        }
        
        /**
         * Returns page types data (only names if $justNames is TRUE)
         * 
         * @param bool $justNames
         * 
         * @return array
         */
        function getPagesTypes(bool $justNames=FALSE)
        {
            $arr=
            [
                lang('documents.pages_static')=>url('Pages','list',['new','static'],['refurl'=>current_url(FALSE,TRUE)]),
            ];
            $arr=[];
            foreach($this->where('pgt_manualadd',1)->find() as $cfg)
            {
                if ($justNames)
                {
                    $arr[lang($cfg['pgt_urltooltip'])]=url('Pages','list',['new',$cfg['pgt_name']],['refurl'=>current_url(FALSE,TRUE)]);
                }else
                {
                    $arr[$cfg['pgt_name']]=$cfg;
                }
            }
            ksort($arr);
            return $arr;
        }
}

