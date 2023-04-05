<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use  CodeIgniter\Shield\Auth;
use  CodeIgniter\Shield\Authentication\Authentication;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
	// public static function example($getShared = true)
	// {
	//     if ($getShared)
	//     {
	//         return static::getSharedInstance('example');
	//     }
	//
	//     return new \CodeIgniter\Example();
	// }
    
	public static function auth($getShared = true)
	{
            if ($getShared) 
            {
                return self::getSharedInstance('auth');
            }

            return new \EMPORIKO\Libraries\Auth\Auth();
	}
        
	public static function viewRenderer($getShared=true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('viewRenderer');
		}
                
                return Services::renderer();
	}
	
	public static function BackupManager($getShared=true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('BackupManager');
		}
    	return new \EMPORIKO\Libraries\Backup\BackupManager();
	}
        
        public static function StorageEngine($getShared=true)
        {
            if ($getShared)
            {
                return static::getSharedInstance('StorageEngine');
            }
            return new \EMPORIKO\Libraries\StorageEngine\StorageEngine();
        }
	
        public static function EmailClient($getShared=true)
        {
            if ($getShared)
            {
                return static::getSharedInstance('EmailClient');
            }
         return \sergey144010\ImapClient\ImapClient::init();
        }
        
        public static function RestClient($getShared=true)
        {
            if ($getShared)
            {
                return static::getSharedInstance('RestClient');
            }
            return new \RestClient\RestClient();
        }
        
        public static function CookiesManager($getShared=true)
        {
            if ($getShared)
            {
                return static::getSharedInstance('CookiesManager');
            }
            return new \EMPORIKO\Libraries\CookiesManager\Manager();
        }
	
}
