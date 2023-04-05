<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTO-LOADER
 * -------------------------------------------------------------------
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 * the values in this file will overwrite the framework's values.
 */
class Autoload extends AutoloadConfig
{
	/**
	 * -------------------------------------------------------------------
	 * Namespaces
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 *
	 * The '/app' and '/system' directories are already mapped for you.
	 * you may change the name of the 'App' namespace if you wish,
	 * but this should be done prior to creating any namespaced classes,
	 * else you will need to modify all of those classes for this to work.
	 *
	 * Prototype:
	 *
	 *   $psr4 = [
	 *       'CodeIgniter' => SYSTEMPATH,
	 *       'App'	       => APPPATH
	 *   ];
	 *
	 * @var array<string, string>
	 */
	public $psr4 = [
		APP_NAMESPACE => APPPATH, // For custom app namespace
		'Config'      => APPPATH.'Config',
		'Dompdf'      => APPPATH . 'ThirdParty/dompdf/src',
                'sergey144010\ImapClient'=> APPPATH . 'ThirdParty/ImapClient',
                'PhpImap'=>APPPATH . 'ThirdParty/PhpImap',
                'PhpOffice\PhpSpreadsheet'=>APPPATH . 'ThirdParty/PhpSpreadsheet',
                'Psr'=>APPPATH . 'ThirdParty/PhpSpreadsheet/Psr',
                'ZipStream'=>APPPATH . 'ThirdParty/PhpSpreadsheet/ZipStream',
                'MyCLabs'=>APPPATH . 'ThirdParty/PhpSpreadsheet/MyCLabs',
                'avadim\FastExcelWriter'=>APPPATH . 'ThirdParty/FastExcelWriter',
                'OneDrive'=>APPPATH .'ThirdParty/OneDrive',
                'RestClient'=>APPPATH .'ThirdParty/RestClient',
                'Shuchkin'=>APPPATH .'ThirdParty/SimpleXLSX',
                'CodeIgniter\Shield'=>APPPATH .'Libraries/Shield',
                
	];

	/**
	 * -------------------------------------------------------------------
	 * Class Map
	 * -------------------------------------------------------------------
	 * The class map provides a map of class names and their exact
	 * location on the drive. Classes loaded in this manner will have
	 * slightly faster performance because they will not have to be
	 * searched for within one or more directories as they would if they
	 * were being autoloaded through a namespace.
	 *
	 * Prototype:
	 *
	 *   $classmap = [
	 *       'MyClass'   => '/path/to/class/file.php'
	 *   ];
	 *
	 * @var array<string, string>
	 */
	public $classmap = [
	'Dompdf\Cpdf'=>APPPATH . 'ThirdParty/dompdf/lib/Cpdf.php',
        'HtmlParser\ParserDom'=>APPPATH .'ThirdParty/HtmlParser/ParserDom.php',
        'elFinder' => APPPATH . 'ThirdParty/elFinder/elFinder.class.php',
        'elFinderConnector' => APPPATH . 'ThirdParty/elFinder/elFinderConnector.class.php',
        'elFinderEditor' => APPPATH . 'ThirdParty/elFinder/editors/editor.php',
        'elFinderLibGdBmp' => APPPATH . 'ThirdParty/elFinder/libs/GdBmp.php',
        'elFinderPlugin' => APPPATH . 'ThirdParty/elFinder/elFinderPlugin.php',
        'elFinderPluginAutoResize' => APPPATH . 'ThirdParty/elFinder/plugins/AutoResize/plugin.php',
        'elFinderPluginAutoRotate' => APPPATH . 'ThirdParty/elFinder/plugins/AutoRotate/plugin.php',
        'elFinderPluginNormalizer' => APPPATH . 'ThirdParty/elFinder/plugins/Normalizer/plugin.php',
        'elFinderPluginSanitizer' => APPPATH . 'ThirdParty/elFinder/plugins/Sanitizer/plugin.php',
        'elFinderPluginWatermark' => APPPATH . 'ThirdParty/elFinder/plugins/Watermark/plugin.php',
        'elFinderSession' => APPPATH . 'ThirdParty/elFinder/elFinderSession.php',
        'elFinderSessionInterface' => APPPATH . 'ThirdParty/elFinder/elFinderSessionInterface.php',
        'elFinderVolumeDriver' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeDriver.class.php',
        'elFinderVolumeDropbox2' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeDropbox2.class.php',
        'elFinderVolumeFTP' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeFTP.class.php',
        'elFinderVolumeFlysystemGoogleDriveCache' => APPPATH . 'ThirdParty/elFinder/elFinderFlysystemGoogleDriveNetmount.php',
        'elFinderVolumeFlysystemGoogleDriveNetmount' => APPPATH . 'ThirdParty/elFinder/elFinderFlysystemGoogleDriveNetmount.php',
        'elFinderVolumeGoogleDrive' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeGoogleDrive.class.php',
        'elFinderVolumeGroup' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeGroup.class.php',
        'elFinderVolumeLocalFileSystem' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeLocalFileSystem.class.php',
        'elFinderVolumeMySQL' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeMySQL.class.php',
        'elFinderVolumeTrash' => APPPATH . 'ThirdParty/elFinder/elFinderVolumeTrash.class.php',    
        //'OneDrive\Business'=>APPPATH . 'ThirdParty/OneDrive/Business.php',
        //'OneDrive\Connector'=>APPPATH . 'ThirdParty/OneDrive/Connector.php',
        //'OneDrive\RestClient'=>APPPATH . 'ThirdParty/OneDrive/RestClient.php',
        //'OneDrive\StorageEngine'=>APPPATH . 'ThirdParty/OneDrive/StorageEngine.php',
	];
}
