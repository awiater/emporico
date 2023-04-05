<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  Arrays manipulation helper class
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Helpers;

class AccessLevel
{
	//view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	
	 const view='view';
	
	 const modify='modify';
	 
	 const edit='edit';
	 
	 const create='create';
	 
	 const delete='delete';
	 
	 const settings='settings';
         
         const state='0';
	 
	 const Levels=
	 	[
	 		'view'=>AccessLevel::view,
			'modify'=>AccessLevel::modify,
			'edit'=>AccessLevel::edit,
			'create'=>AccessLevel::create,
			'delete'=>AccessLevel::delete,
			'settings'=>AccessLevel::settings,
	 	];
	 
	 
}