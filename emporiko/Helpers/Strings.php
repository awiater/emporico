<?php
/*
 *  This file is part of Emporico CRM 
 * 
 * 
 *  Arrays manipulation helper class
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Helpers;

class Strings
{
	/**
	 * Returns class name without namespace
	 * 
	 * @param  String $class Full class name
	 * @return String        Class name without namespace
	 */
	static function classShortName($class)
	{ 
		if(is_object($class))
		{
			$class=get_class($class);
		}
		return substr(strrchr($class,'\\'),1); 
	}
	
	
	static function before($haystack, $needle) 
	{
		$length = strlen($haystack)-strlen($needle); 
		$pos = strpos($haystack, $needle);
		return substr($haystack,0,$pos); 
	}
	
	static function afterLast($haystack, $needle)
	{
		$haystack=explode($needle, $haystack);
		$needle=count($haystack)-1;
		return $haystack[$needle];
	}
	
	static function after($haystack, $needle) 
	{
		$length = strlen($haystack)-strlen($needle); 
		$pos = strpos($haystack, $needle)+strlen($needle);
		return substr($haystack,$pos); 
	}
	
        /**
         * Replace string 
         * 
         * @param  array  $replaced
         * @param  string $subject
         * @param  string $keyPatern
         * @return string
         */
        static function replaceWithArray(array $replaced,$subject,$keyPatern='value')
        {
            $aReplaced=[];
            foreach ($replaced as $key=>$value)
            {
                if (!is_array($value))
                {
                    $aReplaced[str_replace('value', $key, $keyPatern)]=$value;
                }
            }
            return str_replace(array_keys($aReplaced), $aReplaced, $subject);
        }
        
	/**
	 * Convert image/video from url to base64 URI
	 * 
	 * @param  string $image Path to file
	 * 
	 * @return string
	 */
	static function resourceToBase64($image,$defaultmime='text/plain')
	{
		$image_src=str_replace(config('App')->baseURL, FCPATH, $image);
                if ($image_src==null|| strlen($image_src) < 2)
                {
                    return null;
                }
                if (!file_exists(parsePath($image_src,TRUE)))
                {
                    return null;
                }
		$imageData = base64_encode(file_get_contents($image_src));
                try 
                {
                    $imageData='data:'.mime_content_type($image_src).';base64,'.$imageData;
                } 
                catch (\Exception $ex) 
                {
                    $imageData='data:'.$defaultmime.';base64,'.$imageData;
                }
		return $imageData;
	}
        
        /**
         * Check if given string is valid email address
         * 
         * @param  string $string
         * 
         * @return bool
         */
        static function isValidEmail($string)
        {
            return $string==null ? FALSE : filter_var($string, FILTER_VALIDATE_EMAIL)==$string;
        }
        
        /**
         * Generate a random string, using a cryptographically secure
         * pseudorandom number generator (random_int)
         *  
         * @param   int  $length
         * @param   string  $keyspace
         * 
         * @return  string 
         * 
         * @throws Exception
         */
        static function createPasswordString($length,$keyspace =null) 
        {
            $keyspace=$keyspace!=null ? $keyspace :'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str = '';
            $max = mb_strlen($keyspace, '8bit') - 1;
            if ($max < 1) 
            {
                throw new Exception('$keyspace must be at least two characters long');
            }
            for ($i = 0; $i < $length; ++$i) 
            {
                $str .= $keyspace[random_int(0, $max)];
            }
            return $str;
        }
        
        /**
         * Checks if given password is valid (1 capital letter, 1 digit and specified length)
         * 
         * @param  string $password
         * @param  Int    $length
         * 
         * @return boo
         */
        static function isValidPassword($password,$length=8)
        {
            $patern='/\A(?=[\x20-\x7E]*?[A-Z])(?=[\x20-\x7E]*?[a-z])(?=[\x20-\x7E]*?[0-9])[\x20-\x7E]{'.$length.',}\z/';
            return preg_match($patern,$password);
        }
        
        /**
         * Create hashed password
         * 
         * @param  string $string
         * @param  int $cost
         * @return type
         */
        static function hashPasswordString($string,$cost=12)
        {
            return password_hash($string,PASSWORD_DEFAULT,['cost '=>$cost]);
        }
        
	/**
	 *  Create unique id
	 *  
	 *  @return String
	 */
	static function createUID($maxlength=null)
	{
		$return=base64_encode(uniqid());
		if (is_numeric($maxlength))
		{
			$maxlength=$maxlength>strlen($return)?strlen($return):$maxlength;
		}else
		{
			$maxlength=strlen($return);
		}
		$return=str_replace('=', '', $return);
		return substr($return,0, $maxlength);
	}
	
	/**
	 * Url safe base64 string encoding
	 * 
	 * @param  String $data String to encode
	 * @return String
	 */
	static function base64url_encode($data) 
	{
  		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	
	/**
	 * Url safe base64 string decoding
	 * 
	 * @param  String $data String to decode
	 * @return String
	 */
	static function base64url_decode($data) 
	{
  		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
	
	
	/**
	 * Check if string starts with given characters
	 * 
	 * @param  String $haystack String to check
	 * @param  String $needle   Characters to use
	 * @return Boolean          TRUE if string start with given characters, FALSE otherwise
	 */
	static function startsWith($haystack, $needle) 
	{ 
            $needle= is_array($needle) ? $needle : [$needle];
            $in=0;
            foreach($needle as $item)
            {
                $length = strlen($item);
                if (substr($haystack, 0, $length) === $item)
                {
                   $in++; 
                }
            }
            return  $in > 0;
	} 
	
	/**
	 * Check if string ends with given characters
	 * 
	 * @param  String $haystack String to check
	 * @param  String $needle   Characters to use
	 * @return Boolean          TRUE if string ends with given characters, FALSE otherwise
	 */
	static function endsWith($haystack, $needle) 
	{ 
            $needle= is_array($needle) ? $needle : [$needle];
            $in=0;
            foreach($needle as $item)
            {
                $length = strlen($item);
                if (substr($haystack, -$length) === $item)
                {
                   $in++; 
                }
            }
            return $in > 0;
	}
	
	/**
	 * Check if string contains given characters
	 * 
	 * @param  String $haystack String to check
	 * @param  String $needle   Characters to use
	 * @return Boolean          TRUE if string contains given characters, FALSE otherwise
	 */
	static function contains($haystack,$needle)
	{
		$needle=is_array($needle)?$needle:[$needle];
		foreach ($needle as $value) 
		{
			if (strlen($value)<1)
			{
                            return FALSE;
			}
			if (is_string($haystack) && is_string($value))
			{
                                if(strpos($haystack, $value) === FALSE)
                                {}else
                                {
                                    return TRUE;
                                };
			}else
			{
				return FALSE;
			}	
		}
		return FALSE;
	}
	
	/**
	 *  Determine if given string is valid JSON
	 * 
	 *  @param  String  $haystack String to check
	 *  @return Boolean           TRUE if given string is valid JSON or FALSE otherwise
	 */
	static function isJson($haystack)
	{
		if (!is_string($haystack))
		{
			return false;
		}
		json_decode($haystack);
 		return (json_last_error() == JSON_ERROR_NONE);
	}
}
?>