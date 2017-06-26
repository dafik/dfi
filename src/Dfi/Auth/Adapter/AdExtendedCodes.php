<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 22.06.17
 * Time: 11:40
 */

namespace Dfi\Auth\Adapter;


class AdExtendedCodes
{
    public static $byLdapError = [
        49 => [
            '52e' => self::AD_INVALID_CREDENTIALS,
            525 => self::USER_NOT_FOUND,
            530 => self::NOT_PERMITTED_TO_LOGON_AT_THIS_TIME,
            531 => self::RESTRICTED_TO_SPECIFIC_MACHINES,
            532 => self::PASSWORD_EXPIRED,
            533 => self::ACCOUNT_DISABLED,
            568 => self::ERROR_TOO_MANY_CONTEXT_IDS,
            701 => self::ACCOUNT_EXPIRED,
            773 => self::USER_MUST_RESET_PASSWORD,
        ]
    ];

    const AD_INVALID_CREDENTIALS = '52e';
    const USER_NOT_FOUND = 525;
    const NOT_PERMITTED_TO_LOGON_AT_THIS_TIME = 530;
    const RESTRICTED_TO_SPECIFIC_MACHINES = 531;
    const PASSWORD_EXPIRED = 532;
    const ACCOUNT_DISABLED = 533;
    const ERROR_TOO_MANY_CONTEXT_IDS = 568;
    const ACCOUNT_EXPIRED = 701;
    const USER_MUST_RESET_PASSWORD = 773;

    public static $messages = [
        self::AD_INVALID_CREDENTIALS =>
            'Indicates an Active Directory (AD) AcceptSecurityContext error, which is returned when the username is valid but the combination of password and user credential is invalid. 
            This is the AD equivalent of LDAP error code 49.',

        self::USER_NOT_FOUND =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error that is returned when the username is invalid.',

        self::NOT_PERMITTED_TO_LOGON_AT_THIS_TIME =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error that is logon failure caused because the user is not permitted to log on at this time.
             Returns only when presented with a valid username and valid password credential.',

        self::RESTRICTED_TO_SPECIFIC_MACHINES =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error that is logon failure caused because the user is not permitted to log on from this computer.
             Returns only when presented with a valid username and valid password credential.',

        self::PASSWORD_EXPIRED =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error that is a logon failure. The specified account password has expired. 
            Returns only when presented with valid username and password credential.',

        self::ACCOUNT_DISABLED =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error that is a logon failure. The account is currently disabled. 
            Returns only when presented with valid username and password credential.',

        self::ERROR_TOO_MANY_CONTEXT_IDS =>
            'Indicates that during a log-on attempt, the user\'s security context accumulated too many security IDs. 
            This is an issue with the specific LDAP user object/account which should be investigated by the LDAP administrator.',

        self::ACCOUNT_EXPIRED =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error that is a logon failure. The user\'s account has expired. 
            Returns only when presented with valid username and password credential.',

        self::USER_MUST_RESET_PASSWORD =>
            'Indicates an Active Directory (AD) AcceptSecurityContext data error. The user\'s password must be changed before logging on the first time. 
            Returns only when presented with valid user-name and password credential.',

    ];

    public static function checkExtended(\Zend_Auth_Result $result)
    {
        $extendedMessage = $result->getMessages()[1];
        $data = false;

        if (preg_match('/\((.*?)\)/', $extendedMessage, $matches)) {
            list($message, $additional) = explode(";", $matches[1]);
            $adMessages = explode(",", $additional);
            foreach ($adMessages as $message) {
                if (preg_match('/data\s(\w+)/', $message, $dataMatches)) {
                    $data = $dataMatches[1];
                    break;
                }
            }
        }
        $result->message = $message;
        $result->extendedCode = $data;
    }
}