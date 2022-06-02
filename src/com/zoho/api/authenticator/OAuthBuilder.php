<?php

namespace com\zoho\api\authenticator;

use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\util\Constants;
use com\zoho\crm\api\util\Utility;
use DateTimeInterface;

class OAuthBuilder
{
    private $clientID;
    private $clientSecret;
    private $redirectURL;
    private $refreshToken;
    private $grantToken;
    private $accessToken;
    private $id;
    private $userMail;
    private $expiryTime;

    public function id(?string $id): OAuthBuilder
    {
        $this->id = $id;

        return $this;
    }

    public function clientId(string $clientID): OAuthBuilder
    {
        $this->clientID = $clientID;

        return $this;
    }

    public function clientSecret(string $clientSecret): OAuthBuilder
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function redirectURL(?string $redirectURL): OAuthBuilder
    {
        $this->redirectURL = $redirectURL;

        return $this;
    }

    public function refreshToken(?string $refreshToken): OAuthBuilder
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function grantToken(?string $grantToken): OAuthBuilder
    {
        $this->grantToken = $grantToken;

        return $this;
    }

    public function accessToken(?string $accessToken): OAuthBuilder
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function userMail(?string $userMail): OAuthBuilder
    {
        $this->userMail = $userMail;

        return $this;
    }

    public function expiryTime(?DateTimeInterface $expiryTime): OAuthBuilder
    {
        $this->expiryTime = $expiryTime;

        return $this;
    }

    /** @throws SDKException */
    public function build(): OAuthToken
    {
        Utility::assertNotNull($this->clientID, Constants::TOKEN_ERROR, Constants::CLIENT_ID_NULL_ERROR_MESSAGE);
        Utility::assertNotNull($this->clientSecret, Constants::TOKEN_ERROR, Constants::CLIENT_SECRET_NULL_ERROR_MESSAGE);

        return new OAuthToken($this->clientID, $this->clientSecret, $this->id, $this->grantToken,
            $this->refreshToken, $this->redirectURL, $this->accessToken, $this->userMail, $this->expiryTime);
    }
}
