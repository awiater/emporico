<?php
/*
 *  This file is part of EMPORIKO CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Libraries\Auth\Authenticators;

use EMPORIKO\Libraries\Auth\AuthenticatorInterface;
use EMPORIKO\Libraries\Auth\AuthResult;
use EMPORIKO\Libraries\Auth\Entities\UserEntity as User;
use EMPORIKO\Helpers\Strings as Str;

class Session implements AuthenticatorInterface
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
    
    
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws AuthenticationException
     */
    public function trylogin(array $credentials): AuthResult
    {
        $result = $this->check($credentials);

        // Credentials mismatch.
        if (! $result->isOK()) 
        {
            $this->user = null;
            
            unset($credentials['password']);

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
    public function check(array $credentials): AuthResult
    {
        if (empty($credentials['password']) || count($credentials) < 2) 
        {
            return $this->_getAuthResult();
        }
        
        $filters=['username'=>$credentials['username']];
        
        if (Str::contains($credentials['username'],'@'))
        {
            $filters['|| email']=$credentials['username'];
        }
		
        $user=$this->_users->getUserData($filters);
        
        if ($user==null || ($user!=null && !$user->isActive()))
        {
            return $this->_getAuthResult();
	}
        
        if ((!config('App')->isLive) && !in_array($user->username, $this->modelUsers->getSuperAdminUsers('username')))
        {
            return $this->_getAuthResult(FALSE,'system.errors.offline');
        }
        
        if (!password_verify($credentials['password'], $user->password))
        {
            return $this->_getAuthResult();
        }
        
        //$this->_users->clearResetKey($user->username);
        
        $this->_login($user);
        return $this->_getAuthResult(TRUE,$user);
    }

    /**
     * Checks if the user is currently logged in.
     */
    public function isLoged(): bool
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
        $token=$this->_users->getUserData(['userid'=>$token['userid']]);
       
        if (!$token->isActive())
        {
            $this->_session->removeTempdata($this->_authTokenName);
        }
        $this->_user=$token;
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
    
    private function _getAuthResult(bool $success=FALSE,$msg='system.auth.loginform_error')
    {
        $arr=['success' => $success];
        if (!$success)
        {
            $arr['reason']=lang($msg);
        }else
        {
            $arr['extraInfo']=$msg;
        }
        return new AuthResult($arr);
    }
    
    private function _createSessionToken($user)
    {
        $user=$this->_cryptor->encrypt(json_encode(['userid'=>$user,'lastlogin'=>date($this->_timeFormat)]));
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
        $this->user=$user;
        $this->_createSessionToken($user->userid);
    }
    
}
