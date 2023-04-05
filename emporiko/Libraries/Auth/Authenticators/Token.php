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

class Token implements AuthenticatorInterface
{
    
    private $_users;
    
    private $_cryptor;
    
    private $_authkey='x-auth-key';
    
    private $_userauthkey='apitoken';

    private ?User $_user = null;
    
    
    function __construct() 
    {
        $this->_users=model('Auth/UserModel');
        $this->_cryptor=\Config\Services::encrypter();
        $config=config('App');
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
        if (!$result->isOK()) 
        {
            $this->user = null;
            return $result;
        }
        return $result;
        $user=$result->extraInfo();
        
        $this->_login($user);
        
        return $result;
    }

    /**
     * Checks a user's $credentials to see if they match an
     * 
     * existing user.
     */
    public function check(array $credentials): AuthResult
    {
        if (empty($credentials['key'])) 
        {
            return $this->_getAuthResult();
        }
        
        if ($credentials['key']=='@')
        {
            $credentials['key']=$this->_getInputKey();
        }
        
        $user=$this->_users->getUserData([$this->_userauthkey=>$credentials['key']]);
        
        if ($user==null || ($user!=null && !$user->isActive()))
        {
            return $this->_getAuthResult();
	}
               
        return $this->_getAuthResult(TRUE,$user);
    }

    /**
     * Checks if the user is currently logged in.
     */
    public function isLoged(): bool
    {
        $key=$this->trylogin(['key'=>'@']);
        if (!$key->isOK())
        {
            return FALSE;
        }
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
    
    private function _getAuthResult(bool $success=FALSE,$msg='Invalid auth token')
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
    
    private function _getInputKey()
    {
       foreach(getallheaders() as $key=>$val)
       {
           if ($this->_authkey== strtolower($key))
           {
               return $val;
           }
       }
       foreach($_GET as $key=>$val)
       {
           if ($this->_authkey== strtolower($key))
           {
               return $val;
           }
       }
       foreach($_POST as $key=>$val)
       {
           if ($this->_authkey== strtolower($key))
           {
               return $val;
           }
       }
        return null;
    }
    
    
}
