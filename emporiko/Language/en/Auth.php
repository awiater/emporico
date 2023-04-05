<?php


/*
 *  This file is part of Emporico CRM  
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} is not a valid authenticator.',
    'unknownUserProvider'   => 'Unable to determine the User Provider to use.',
    'invalidUser'           => 'Unable to locate the specified user.',
    'badAttempt'            => 'Unable to log you in. Please check your credentials.',
    'noPassword'            => 'Cannot validate a user without a password.',
    'invalidPassword'       => 'Unable to log you in. Please check your password.',
    'noToken'               => 'Every request must have a bearer token in the {0} header.',
    'badToken'              => 'The access token is invalid.',
    'oldToken'              => 'The access token has expired.',
    'noUserEntity'          => 'User Entity must be provided for password validation.',
    'invalidEmail'          => 'Unable to verify the email address matches the email on record.',
    'unableSendEmailToUser' => 'Sorry, there was a problem sending the email. We could not send an email to "{0}".',
    'throttled'             => 'Too many requests made from this IP address. You may try again in {0} seconds.',
    'forget'                =>'If email address is valid you will receive information with futher steps',

    // Buttons
    'confirm' => 'Confirm',
    'send'    => 'Send',

    // Registration
    'register'         => 'Register',
    'registerDisabled' => 'Registration is not currently allowed.',
    'registerSuccess'  => 'Welcome aboard!',

    // Login
    'login'              => 'Login',
    'needAccount'        => 'Need an account?',
    'rememberMe'         => 'Remember me?',
    'forgotPassword'     => 'Forgot your password?',
    'useMagicLink'       => 'Use a Login Link',
    'magicLinkSubject'   => 'Your Login Link',
    'magicTokenNotFound' => 'Unable to verify the link.',
    'magicLinkExpired'   => 'Sorry, link has expired.',
    'checkYourEmail'     => 'Check your email!',
    'magicLinkDetails'   => 'We just sent you an email with a Login link inside. It is only valid for {0} minutes.',
    'successLogout'      => 'You have successfully logged out.',

    // Passwords
    'errorPasswordLength'       => 'Passwords must be at least {0, number} characters long.',
    'errorPasswordCommon'       => 'Password not meet minimum criteria (at least 1 capital letter, at least 1 digit, must be minimum 8 characters long',
    'errorPasswordEmpty'        => 'A Password is required.',
    'passwordChangeSuccess'     => 'Password changed successfully',
    'passwordExists'            => 'You have used this password before. Please choose another one',
    'userDoesNotExist'          => 'Password was not changed. User does not exist',
    'resetTokenExpired'         => 'Sorry. Your reset token has expired.',

    // 2FA
    'email2FATitle'       => 'Two Factor Authentication',
    'confirmEmailAddress' => 'Confirm your email address.',
    'emailEnterCode'      => 'Confirm your Email',
    'emailConfirmCode'    => 'Enter the 6-digit code we just sent to your email address.',
    'email2FASubject'     => 'Your authentication code',
    'email2FAMailBody'    => 'Your authentication code is:',
    'invalid2FAToken'     => 'The code was incorrect.',
    'need2FA'             => 'You must complete a two-factor verification.',
    'needVerification'    => 'Check your email to complete account activation.',

    // Activate
    'emailActivateTitle'    => 'Email Activation',
    'emailActivateBody'     => 'We just sent an email to you with a code to confirm your email address. Copy that code and paste it below.',
    'emailActivateSubject'  => 'Your activation code',
    'emailActivateMailBody' => 'Please use the code below to activate your account and start using the site.',
    'invalidActivateToken'  => 'The code was incorrect.',
    'needActivate'          => 'You must complete your registration by confirming the code sent to your email address.',

    // Groups
    'unknownGroup' => '{0} is not a valid group.',
    'missingTitle' => 'Groups must have a title.',
    'access_levels'=>['View','Modify','Edit','Create','Delete','Change Settings'],

    // Permissions
    'unknownPermission' => '{0} is not a valid permission.',
    
    //Settings
    'settings_forgeturl'=>'Forget Password Link',
    'settings_forgeturl_tooltip'=>'Determines if forget password link is visible in login form',
    'settings_cfgtab'=>'Settings',
    'settings_forgetemailtpl'=>'Password Recovery Email',
    'settings_forgetemailtpl_tooltip'=>'Please choose template which will be used as email send when user request password recovery',
    
    //Forget
    'forget_reset_email_sbj' => 'Password Reset Request',
    'forget_emailmsg' => '<p>Hello %name%,</p><p>A request has been received to change the password for your account.</p><p>Please click on below link to complete request:</p><p>%key_url%</p><p><span style="color: #e03e2d;"><strong>If you did not initiate this request, please contact us immeditely </strong></span></p><p>Thank You</p>',
];
