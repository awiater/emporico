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

namespace EMPORIKO\Libraries\Auth;

use EMPORIKO\Libraries\Auth\Entities\UserEntity as User;
use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\Strings as Str;

class Auth
{
    /**
     * Available providers
     * @var array
     */
    private $_providers=[];
    
    /**
     * Current provider
     * @var AuthenticatorInterface
     */
    private $_provider=null;
    
    /**
     * Auth Access Model
     * @var Model
     */
    private $_perms=null;
    
    /**
     * Auth User Model
     * @var Model
     */
    private $_users=null;
    
    public function __construct()
    {
        $config=config('App');
        $this->_providers=$config->authProviders;
        $this->setAuthenticator($config->authProvider);
        $this->_perms=model('Auth/AuthAccessModel');
        $this->_users=model('Auth/UserModel');
    }
    
    /**
     * Authenticate user using given credentials
     * 
     * @param array $credentials
     * 
     * @return AuthResult
     */
    function authenticate(array $credentials)
    {
        return $this->_provider->trylogin($credentials);
    }
    
    /**
     * Sets the Authenticator alias that should be used for this request.
     *
     * @return $this
     */
    public function setAuthenticator(string $alias): self
    {
        if (array_key_exists(strtolower($alias), $this->_providers))
        {
            $alias=$this->_providers[$alias];
            $this->_provider= new $alias();
        }else
        {
            $this->_provider=new \CodeIgniter\Shield\Authentication\Authenticators\Session();
        }
        return $this;
    }

    /**
     * Returns the current authentication class.
     */
    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->_provider;
    }

    /**
     * Returns the current user, if logged in.
     */
    public function user()
    {
        return $this->getAuthenticator()->isLoged()
            ? $this->getAuthenticator()->getUser()
            : null;
    }
    
    /**
     * Determine if given entity have given access
     * 
     * @param mixed $ref
     * 
     * @return boolean
     */
    function hasAccess($ref)
    {
        if (!is_string($ref))
        {
            return FALSE;
        }
        
        if ($this->user()==null)
        {
            return FALSE;
        }
        $user_group=$this->user()->accessgroups;
        if (is_array($user_group))
        {
            return FALSE;
        }
        
        if (Str::startsWith($ref, '#'))
        {
            return strcmp($this->user()->accessgroups,$ref)==0;
        }
        
        if (Str::contains($ref, '.'))
        {
            $ref=explode('.',$ref);
            $access=$ref[1];
            $ref=$ref[0];
        } else 
        {
            $access='view';
        }
        $access='acc_'.$access;
        
        $access_cfg=$this->_perms->OrWhere('acc_ref',$ref.'.'.$user_group)
                                 ->orWhere('acc_ref',$user_group)
                                 ->orWhere('acc_ref',$ref.'.'.loged_user('userid'))
                                 ->orWhere('acc_ref',$user_group)
                                 ->first();
        
        if (!is_array($access_cfg))
        {
            $access_cfg=[];
        }
        
        if (array_key_exists($access, $access_cfg))
        {
            $access=$access_cfg[$access];
        }else
        {
            $access=0;
        }
        return $access || $access==1 || $access=='1';
    }
    
    /**
     * Generate and return forget key for given user (email)
     *  
     * @param string $email
     * 
     * @return boolean|array
     */
    public function generateForgetKey($email)
    {
	$email=$this->_users->where('email',$email)->first();
        
	if (is_array($email))
	{
            $email['resetkey']=Str::base64url_encode(formatDate('now','+30 minutes'));
            $this->_users->save(['userid'=>$email['userid'],'resetkey'=>$email['resetkey']]);
            return $email;
        }
	return FALSE;
    }
    
    /**
     * Change password for given user (as userID you can use forget key, username, user id) and return TRUE or error code
     * 
     * @param string $userID
     * @param string $password
     * @param bool   $fromKey
     * 
     * @return boolean|string
     */
    public function changePassword($userID,$password,bool $fromKey=FALSE)
    {
        if (!is_string($password))
        {
            return 'auth.errorPasswordEmpty';
        }
        
        if (strlen($password) < 8)
        {
           return lang('auth.errorPasswordLength',[8]);
        }
        
        if (!Str::isValidPassword($password))
        {
            return 'auth.errorPasswordCommon';
        }
        if ($fromKey)
        {
            if (!$this->validateForgetKey($userID))
            {
                return 'auth.resetTokenExpired';
            }
        }
        
        $userID=$this->_users->filtered(['resetkey'=>$userID,'|| userid'=>$userID,'|| username'=>$userID])->first();
        
        if (!is_array($userID) || (is_array($userID) && !Arr::KeysExists(['password','username'], $userID)))
        {
            return 'auth.userDoesNotExist';
        }
        $isExists=$this->_provider->check(['username'=>$userID,'password'=>$password]);
        if ($isExists->isOK())
        {
            return 'auth.passwordExists';
        }
        $model=$this->_users;
        if (!$model->save(['userid'=>$userID['userid'],'password'=>$password,'resetkey'=>'']))
        {
            return $model->errors();
        }
        return TRUE;
    }
    
    /**
     * Check if given forget key is still valid
     * 
     * @param string $key
     * 
     * @return boolean
     */
    function validateForgetKey(string $key)
    {
       $key=$this->_users->filtered(['resetkey'=>$key])->first();
       if (!is_array($key) || (is_array($key) && !array_key_exists('resetkey', $key)))
       {
           retn_false:
           return FALSE;
       }
       $key= base64_decode($key['resetkey']);
       if (!is_string($key))
       {
           return FALSE;
       }
       
       if (formatDate() > $key)
       {
           return FALSE;
       }
       return TRUE;
    }
    
    public function __call(string $method, array $args)
    {
        if (method_exists($this->_provider, $method)) 
        {
            return $this->_provider->{$method}(...$args);
        }
    }
}
