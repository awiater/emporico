<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cookies extends BaseConfig
{
    
	/**
	 * --------------------------------------------------------------------------
	 * Determines of Cookie Manager is on
	 * --------------------------------------------------------------------------
	 *
	 * @var bool
	 */
	public $enabled=TRUE;
        
        
        /**
	 * --------------------------------------------------------------------------
	 * Name of main cookie (holds scopes and other settings as JSON
	 * --------------------------------------------------------------------------
	 *
	 * @var string
	 */
        public $cookieName='gdpr_cookie';
        
        /**
	 * --------------------------------------------------------------------------
	 * Cookie expiration time in days
	 * --------------------------------------------------------------------------
	 *
	 * @var Int
	 */
        public $cookieExpiry=365;

        /**
	 * --------------------------------------------------------------------------
	 * Determines if user can change cookies consent settings
	 * --------------------------------------------------------------------------
	 *
	 * @var bool
	 */
        public $cookieBarSettings=TRUE;
        
        /**
	 * --------------------------------------------------------------------------
	 * Cookie privacy overview shown in cookies consent settings window 
         * (could be language string)
	 * --------------------------------------------------------------------------
	 *
	 * @var bool
	 */
        public $cookiePrivacy='cookies_manager.cookieBarModCfgPrivacy';
        
        
        /**
	 * --------------------------------------------------------------------------
	 * Name of main cookie (holds scopes and other settings as JSON
	 * --------------------------------------------------------------------------
	 *
	 * @var array
	 */
        public $scopes=
        [
           'needed'=>
            [
                'name'=>'needed',
                'required'=>TRUE,
                'title'=>'cookies_manager.cookieBarModCfgPolicyNeeded',
                'tooltip'=>'cookies_manager.cookieBarModCfgPolicyNeeded_tooltip',
            ],
            'functional'=>
            [
                'name'=>'needed',
                'required'=>FALSE,
                'title'=>'cookies_manager.cookieBarModCfgPolicyFunc',
                'tooltip'=>'cookies_manager.cookieBarModCfgPolicyFunc_tooltip', 
            ],
            'performance'=>
            [
                'name'=>'needed',
                'required'=>FALSE,
                'title'=>'cookies_manager.cookieBarModCfgPolicyPerf',
                'tooltip'=>'cookies_manager.cookieBarModCfgPolicyPerf_tooltip',
            ],
            'analytical'=>
            [
                'name'=>'needed',
                'required'=>FALSE,
                'title'=>'cookies_manager.cookieBarModCfgPolicyAnalytic',
                'tooltip'=>'cookies_manager.cookieBarModCfgPolicyAnalytic_tooltip',
            ],
            'advert'=>
            [
                'name'=>'needed',
                'required'=>FALSE,
                'title'=>'cookies_manager.cookieBarModCfgPolicyAdvert', 
                'tooltip'=>'cookies_manager.cookieBarModCfgPolicyAdvert_tooltip', 
            ],
            'others'=>
            [
                'name'=>'needed',
                'required'=>FALSE,
                'title'=>'cookies_manager.cookieBarModCfgPolicyOthers',
                'tooltip'=>'cookies_manager.cookieBarModCfgPolicyOthers_tooltip',
            ],
        ];
}