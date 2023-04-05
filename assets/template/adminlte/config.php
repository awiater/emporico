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
 	'css'=>
 		[
 			'adminlte'=>'@template/apd/css/adminlte.min.css',
 			'google_font'=>'https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@700&display=swap',
 			'template'=>'@template/apd/css/emporico.css',
 		],
 	'scripts'=>
 		[
 			'@template/apd/js/adminlte.min.js',
 		],
     'sections'=>
		[
			'logedusermenu'=>['Menu','htmlmenu',['logedusermenu',['li'=>'dropdown-item','url'=>'d-flex']]],
			'mainmenu'=>['Menu','htmlmenu',['mainmenu',['mtext_wrapper'=>'p','ul'=>FALSE]]],
		],
	'views'=>[],
];
