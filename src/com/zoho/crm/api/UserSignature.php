<?php

namespace com\zoho\crm\api;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\util\Constants;

/**
 * This class represents the CRM user email.
 */
class UserSignature
{
    private $email;

    /**
     * Creates an UserSignature class instance with the specified user email.
     * @param string $email A string containing the CRM user email.
     * @throws SDKException
     */
    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $error = [
                Constants::FIELD => Constants::EMAIL,
                Constants::EXPECTED_TYPE => Constants::EMAIL,
            ];
            throw new SDKException(Constants::USER_SIGNATURE_ERROR, null, $error);
        }

        $this->email = $email;
    }

    /**
     * This is a getter method to get user email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
