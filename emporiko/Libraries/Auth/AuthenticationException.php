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

class AuthenticationException extends RuntimeException
{
    protected $code = 403;

    /**
     * @param string $alias Authenticator alias
     */
    public static function forUnknownAuthenticator(string $alias): self
    {
        return new self(lang('Auth.unknownAuthenticator', [$alias]));
    }

    public static function forUnknownUserProvider(): self
    {
        return new self(lang('Auth.unknownUserProvider'));
    }

    public static function forInvalidUser(): self
    {
        return new self(lang('Auth.invalidUser'));
    }

    public static function forNoEntityProvided(): self
    {
        return new self(lang('Auth.noUserEntity'), 500);
    }

    /**
     * Fires when no minimumPasswordLength has been set
     * in the Auth config file.
     */
    public static function forUnsetPasswordLength(): self
    {
        return new self(lang('Auth.unsetPasswordLength'), 500);
    }

    /**
     * When the cURL request (to Have I Been Pwned) in PwnedValidator
     * throws a HTTPException it is re-thrown as this one
     */
    public static function forHIBPCurlFail(HTTPException $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
