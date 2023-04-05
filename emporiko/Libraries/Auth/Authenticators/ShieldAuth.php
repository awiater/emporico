<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Libraries\Auth;

use CodeIgniter\Shield\Result;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Auth as Shield;

class ShieldAuth implements AuthenticatorInterface
{
    
    private $_users;
    
    private $_cryptor;
    
    private $_timeFormat='YmdHis';
    
    private $_authTokenName;
    
    private $_authTokenExpiry;
    
    private $_session;

    private ?User $_user = null;
    
    
    function __construct() 
    {
        $this->_users=model('Auth/UserModel');
        $this->_cryptor=\Config\Services::encrypter();
        $config=config('App');
        $this->_authTokenName=$config->authTokenName;
        $this->_authTokenExpiry=$config->authTokenExpiry;
        $this->_session = \Config\Services::session();
    }
    
    public static function init()
    {
        $config = config('CodeIgniter\Shield\Config\Auth');
        $config=new Shield(new Authentication($config));
        $config->setAuthenticator('shield_auth');
        return $config;
    }
    
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws AuthenticationException
     */
    public function attempt(array $credentials): Result
    {
        $result = $this->check($credentials);

        // Credentials mismatch.
        if (! $result->isOK()) 
        {
            // Always record a login attempt, whether success or not.
            $this->recordLoginAttempt($credentials, false, $ipAddress, $userAgent);

            $this->user = null;

            // Fire an event on failure so devs have the chance to
            // let them know someone attempted to login to their account
            unset($credentials['password']);
            Events::trigger('failedLogin', $credentials);

            return $result;
        }
        
        $user=$result->extraInfo();
        
        $this->_login($user);
        
        return $result;
    }

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     */
    public function check(array $credentials): Result
    {
        if (empty($credentials['password']) || count($credentials) < 2) 
        {
            return $this->_getResult();
        }
        
        $filters=['password'=>$credentials['password'],'login'=>$credentials['username']];
        
        if (Str::contains($credentials['username'],'@'))
        {
            $filters['email']=$credentials['username'];
            $filters['login']=null;
        }
		
        $user=$this->_users->getUserData($filters['login'],null,$filters['email']);
        if ($user==null || (is_array($user) && ((array_key_exists('enabled', $user) && $user['enabled']==0) || (array_key_exists('group_enabled', $user) && $user['group_enabled']==0))))
        {
            return $this->_getResult();
	}
        
        if ((!config('App')->isLive) && !in_array($user['username'], $this->modelUsers->getSuperAdminUsers('username')))
        {
            return $this->_getResult(FALSE,'system.errors.offline');
        }
        
        if (!password_verify($post['password'], $user['password']))
        {
            return $this->_getResult();
        }
        $this->_users->clearResetKey($user['username']);
        unset($user['password']);
        
        return $this->_getResult(TRUE,$user);
    }

    /**
     * Checks if the user is currently logged in.
     */
    public function loggedIn(): bool
    {
        $token=$this->_getSessionToken();
        if (!is_array($token))
        {
            return FALSE;
        }
        $loged=\DateTime::createFromFormat($this->_timeFormat, $token['lastlogin']);
        $loged->modify('+'.($this->_authTokenExpiry/60).' minutes');
	$loged=$loged->format($this->_timeFormat);
        if (date($this->_timeFormat)>$loged)
        {
            return FALSE;  
        }
        $token=$this->_users->find($token['userid']);
        if (!array_key_exists('enabled', $token) || (array_key_exists('enabled', $token) && $token['enabled']==0))
        {
            $this->_session->removeTempdata($this->_authTokenName);
        }
        $this->_user=new User($token);
        return TRUE;
    }

    /**
     * Logs the given user in.
     * On success this must trigger the "login" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     */
    public function login(User $user): void
    {
        $this->_login($user);
    }

    /**
     * Logs a user in based on their ID.
     * On success this must trigger the "login" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     *
     * @param int|string $userId
     */
    public function loginById($userId): void
    {
        
    }

    /**
     * Logs the current user out.
     * On success this must trigger the "logout" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     */
    public function logout(): void
    {
        $this->_session->removeTempdata($this->_authTokenName);
    }

    /**
     * Returns the currently logged in user.
     */
    public function getUser(): ?User
    {
        return  $this->_user;
    }

    /**
     * Updates the user's last active date.
     */
    public function recordActiveDate(): void{}
    
    private function _getResult(bool $success=FALSE,$msg='system.auth.loginform_error')
    {
        $arr=['success' => $success];
        if (!$success)
        {
            $arr['reason']=lang($msg);
        }else
        {
            $arr['extraInfo']=$msg;
        }
        return new Result($arr);
    }
    
    private function _createSessionToken($userID)
    {
        $user=$this->_cryptor->encrypt(json_encode(['userid'=>$user['userid'],'lastlogin'=>date($this->_timeFormat)]));
        $this->_session->setTempdata($this->_authTokenName,base64_encode($user),$this->_authTokenExpiry);           
    }
    
    private function _getSessionToken()
    {
        $token=session($this->_authTokenName);
        if ($token!=null)
        {
            return json_decode($this->_cryptor->decrypt(base64_decode($this->_session->get($this->_authTokenName))),TRUE);
        }
        return $token;
    }
    
    private function _login($user)
    {
        if (!is_array($user) || (is_array($user) && !array_key_exists('userid', $user)))
        {
            return FALSE;
        }
        $this->user=$user;
        $this->_createSessionToken($user['userid']);
    }
    
}
