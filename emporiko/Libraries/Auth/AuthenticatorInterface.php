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

interface AuthenticatorInterface
{
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws AuthenticationException
     */
    public function trylogin(array $credentials): AuthResult;

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     */
    public function check(array $credentials): AuthResult;

    /**
     * Checks if the user is currently logged in.
     */
    public function isLoged(): bool;

    /**
     * Logs the given user in.
     */
    public function login(User $user): void;

    /**
     * Logs a user in based on their ID.
     * 
     * @param int|string $userId
     */
    public function loginById($userId): void;

    /**
     * Logs the current user out.
     */
    public function logout(): void;

    /**
     * Returns the currently logged in user.
     */
    public function getUser(): ?User;

    /**
     * Updates the user's last active date.
     */
    public function recordActiveDate(): void;
}
