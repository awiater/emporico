<?php

use Config\Services;


function set_cookie(
		$name,
		string $value = '',
		string $expire = '',
		string $domain = '',
		string $path = '/',
		string $prefix = '',
		bool $secure = false,
		bool $httpOnly = false,
		string $sameSite = null,
                string $scope='functional'
	)
{
    $response = Services::response();
    $cokiesManager=Services::CookiesManager();
    
    if ($cokiesManager->isAllowed($scope))
    {
        return $response->setcookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httpOnly, $sameSite);
    }
    
    if ($cokiesManager->isCookie($name))
    {
        return $response->setcookie($name, '', time() - 360, $domain, $path, $prefix, $secure, $httpOnly, $sameSite);
    }
    return FALSE;
}

if (! function_exists('set_cookie1'))
{
	/**
	 * Set cookie
	 *
	 * Accepts seven parameters, or you can submit an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param string|array $name     Cookie name or array containing binds
	 * @param string       $value    The value of the cookie
	 * @param string       $expire   The number of seconds until expiration
	 * @param string       $domain   For site-wide cookie.
	 *                                 Usually: .yourdomain.com
	 * @param string       $path     The cookie path
	 * @param string       $prefix   The cookie prefix
	 * @param boolean      $secure   True makes the cookie secure
	 * @param boolean      $httpOnly True makes the cookie accessible via
	 *                                 http(s) only (no javascript)
	 * @param string|null  $sameSite The cookie SameSite value
	 *
	 * @see (\Config\Services::response())->setCookie()
	 * @see \CodeIgniter\HTTP\Response::setCookie()
	 */
	
}


