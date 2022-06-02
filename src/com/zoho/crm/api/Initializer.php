<?php
/*
Copyright (c) 2021, ZOHO CORPORATION PRIVATE LIMITED
All rights reserved.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

namespace com\zoho\crm\api;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\api\logger\SDKLogger;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\util\Constants;
use com\zoho\crm\api\dc\Environment;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * This class to initialize Zoho CRM SDK.
 */
class Initializer
{
    public static $LOCAL = [];

    /** @var self|null */
    private static $initializer;

    /** @var Environment|null */
    private $environment = null;

    /** @var TokenStore|null */
    private $store = null;

    /** @var UserSignature|null */
    private $user = null;

    /** @var OAuthToken|null */
    private $token = null;

    /** @var array|null */
    public static $jsonDetails = null;

    /** @var string|null */
    private $resourcePath = null;

    /** @var SDKConfig|null */
    private $sdkConfig = null;

    /** @var ClientInterface|null */
    private $client = null;

    /**
     * This to initialize the SDK.
     *
     * @param UserSignature $user A UserSignature class instance represents the CRM user.
     * @param Environment $environment A Environment class instance containing the CRM API base URL and Accounts URL.
     * @param OAuthToken $token An OAuthToken class instance containing the OAuth client application information.
     * @param TokenStore $store A TokenStore class instance containing the token store information.
     * @param SDKConfig $sdkConfig A SDKConfig class instance containing the SDK configuration.
     * @param string $resourcePath A String containing the absolute directory path to store user specific JSON files
     *     containing module fields information.
     * @param LoggerInterface $logger A PSR-compatible Logger instance.
     * @param ClientInterface $client A Guzzle REST Client instance.
     * @throws SDKException
     */
    public static function initialize(
        UserSignature $user,
        Environment $environment,
        OAuthToken $token,
        TokenStore $store,
        SDKConfig $sdkConfig,
        string $resourcePath,
        LoggerInterface $logger,
        ClientInterface $client
    ) {
        try
        {
            SDKLogger::initialize($logger);

            try
            {
                if(is_null(self::$jsonDetails))
                {
                    self::$jsonDetails = json_decode(file_get_contents(explode("src", realpath(__DIR__))[0] . Constants::JSON_DETAILS_FILE_PATH), true);
                }
            }
            catch (Throwable $ex)
            {
                throw new SDKException(Constants::JSON_DETAILS_ERROR, null, null, $ex);
            }

            self::$initializer = new Initializer();
            $initializer = new Initializer();
            $initializer->user = $user;
            $initializer->environment = $environment;
            $initializer->token = $token;
            $initializer->store = $store;
            $initializer->sdkConfig = $sdkConfig;
            $initializer->resourcePath = $resourcePath;
            $initializer->client = $client;
            self::$LOCAL[$initializer->getEncodedKey($user, $environment)] = $initializer;
            self::$initializer = $initializer;

            SDKLogger::info(Constants::INITIALIZATION_SUCCESSFUL . $initializer->toString());
        }
        catch(SDKException $e)
        {
            throw $e;
        }
        catch (Throwable $e)
        {
            throw new SDKException(Constants::INITIALIZATION_EXCEPTION, null, null, $e);
        }
    }

    public static function getJSON(string $filePath): ?array
    {
        return json_decode(file_get_contents($filePath), true);
    }

    /**
     * This method to get Initializer class instance.
     *
     * @return Initializer|null A Initializer class instance representing the SDK configuration details.
     */
    public static function getInitializer(): ?Initializer
    {
        if (!empty(self::$LOCAL) && count(self::$LOCAL) != 0)
        {
            $initializer = new Initializer();

            $key = $initializer->getEncodedKey(self::$initializer->user, self::$initializer->environment);

            if(array_key_exists($key, self::$LOCAL))
            {
                return self::$LOCAL[$key];
            }
        }

        return self::$initializer;
    }

    /**
     * This method to switch the different user in SDK environment.
     * @param UserSignature $user A UserSignature class instance represents the CRM user.
     * @param Environment $environment A Environment class instance containing the CRM API base URL and Accounts URL.
     * @param OAuthToken $token An OAuthToken class instance containing the OAuth client application information.
     * @param SDKConfig $sdkConfig A SDKConfig class instance containing the SDK configuration.
     */
    public static function switchUser(UserSignature $user, Environment $environment, OAuthToken $token, SDKConfig $sdkConfig, ClientInterface $client): void
    {
        $initializer = new Initializer();
        $initializer->user = $user;
        $initializer->environment = $environment;
        $initializer->token = $token;
        $initializer->store = self::$initializer->store;
        $initializer->sdkConfig = $sdkConfig;
        $initializer->client = $client;
        $initializer->resourcePath = self::$initializer->resourcePath;
        self::$LOCAL[$initializer->getEncodedKey($user, $environment)] = $initializer;
        self::$initializer = $initializer;

        SDKLogger::info(Constants::INITIALIZATION_SWITCHED . $initializer->toString());
    }

    public static function reset()
    {
        self::$LOCAL = [];
        self::$initializer = null;
    }

    /**
     * This is a getter method to get API environment.
     *
     * @return Environment A Environment representing the API environment.
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * This is a getter method to get API environment.
     *
     * @return TokenStore A TokenStore class instance containing the token store information.
     */
    public function getStore(): TokenStore
    {
        return $this->store;
    }

    /**
     * This is a getter method to get CRM User.
     *
     * @return UserSignature A User class instance representing the CRM user.
     */
    public function getUser(): UserSignature
    {
        return $this->user;
    }

    /**
     * This is a getter method to get the PSR-compatible REST client.

     * @param ClientInterface $client
     * @return Initializer
     */
    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * This is a getter method to get the PSR-compatible REST client.
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * This is a getter method to get OAuth client application information.
     *
     * @return OAuthToken A Token class instance representing the OAuth client application information.
     */
    public function getToken(): OAuthToken
    {
        return $this->token;
    }

    public function getResourcePath(): string
    {
        return $this->resourcePath;
    }

    /**
     * This is a getter method to get SDK configuration.
     */
    public function getSDKConfig(): SDKConfig
    {
        return $this->sdkConfig;
    }

    private function getEncodedKey(UserSignature $user, Environment $environment): string
    {
        $userMail = $user->getEmail();

        $key = explode("@", $userMail)[0] . $environment->getUrl();

        $input = unpack('C*', utf8_encode($key));

        return base64_encode(implode(array_map("chr", $input)));
    }

    public function toString(): string
    {
		return Constants::FOR_EMAIL_ID . self::$initializer->getUser()->getEmail() . Constants::IN_ENVIRONMENT . self::$initializer->getEnvironment()->getUrl() . ".";
	}
}
