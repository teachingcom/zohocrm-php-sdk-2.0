<?php

namespace com\zoho\api\authenticator\store;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\UserSignature;
use com\zoho\api\authenticator\OAuthToken;

/**
 * This interface stores the user token details.
 */
interface TokenStore
{
    /**
     * This method is used to get user token details.
     * @param UserSignature $user A UserSignature class instance.
     * @param OAuthToken $token A Token class instance.
     * @return OAuthToken|null A Token class instance representing the user token details.
     * @throws SDKException
     */
    public function getToken(UserSignature $user, OAuthToken $token): ?OAuthToken;

    /**
     * This method is used to retrieve the user token details based on unique ID
     * @param string $id A String representing the unique ID
     * @param OAuthToken $token A Token class instance.
     * @return OAuthToken A Token class instance representing the user token details.
     * @throws SDKException
     */
    public function getTokenById(string $id, OAuthToken $token): OAuthToken;

    /**
     * This method is used to store user token details.
     * @param UserSignature $user A UserSignature class instance.
     * @param OAuthToken $token A Token class instance.
     * @throws SDKException
     */
    public function saveToken(UserSignature $user, OAuthToken $token): void;

    /**
     * This method is used to delete user token details.
     * @param OAuthToken $token A Token class instance.
     * @throws SDKException
     */
    public function deleteToken(OAuthToken $token): void;

    /**
     * The method to retrieve all the stored tokens.
     * @return OAuthToken[]
     * @throws SDKException
     */
    public function getTokens(): array;

    /**
     * The method to delete all the stored tokens.
     * @throws SDKException
     */
    public function deleteTokens();
}
