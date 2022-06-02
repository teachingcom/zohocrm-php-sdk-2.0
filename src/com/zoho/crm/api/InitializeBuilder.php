<?php

namespace com\zoho\crm\api;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\api\logger\SDKLogger;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\util\Constants;
use com\zoho\crm\api\util\Utility;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class InitializeBuilder
{
    /** @var Environment|null */
    private $environment;
    /** @var TokenStore|null */
    private $store;
    /** @var UserSignature|null */
    private $user;
    /** @var OAuthToken|null */
    private $token;
    /** @var string|null */
    private $resourcePath;
    /** @var SDKConfig|null */
    private $sdkConfig;
    /** @var LoggerInterface|null */
    private $logger;
    /** @var string */
    private $errorMessage;
    /** @var ClientInterface|null */
    private $client;

    public function __construct()
    {
        $initializer = Initializer::getInitializer();
        $this->errorMessage = ($initializer != null) ? Constants::SWITCH_USER_ERROR : Constants::INITIALIZATION_ERROR;

        if ($initializer != null)
        {
            $this->user = $initializer->getUser();
            $this->environment = $initializer->getEnvironment();
            $this->token = $initializer->getToken();
            $this->sdkConfig = $initializer->getSDKConfig();
            $this->logger = SDKLogger::getLogger();
            $this->client = $initializer->getClient();
        }
    }

    /** @throws SDKException */
    public function initialize()
    {
        Utility::assertNotNull($this->user, $this->errorMessage, Constants::USERSIGNATURE_ERROR_MESSAGE);
        Utility::assertNotNull($this->environment, $this->errorMessage, Constants::ENVIRONMENT_ERROR_MESSAGE);
        Utility::assertNotNull($this->token, $this->errorMessage, Constants::TOKEN_ERROR_MESSAGE);
        Utility::assertNotNull($this->logger, $this->errorMessage, Constants::LOGGER_ERROR_MESSAGE);

        $root = realpath(__DIR__ . '/../../../../../');
        if (is_null($this->store))
        {
            $this->store = new FileStore($root . DIRECTORY_SEPARATOR . Constants::TOKEN_FILE);
        }

        if (is_null($this->sdkConfig))
        {
            $this->sdkConfig = (new SDKConfigBuilder())->build();
        }

        if (is_null($this->resourcePath))
        {
            $this->resourcePath = $root;
        }

        if (is_null($this->client))
        {
            $this->client = (new ClientBuilder)->build();
        }

        Initializer::initialize($this->user, $this->environment, $this->token, $this->store, $this->sdkConfig, $this->resourcePath, $this->logger, $this->client);
    }

    /** @throws SDKException */
    public function switchUser()
    {
        Utility::assertNotNull(Initializer::getInitializer(), Constants::SDK_UNINITIALIZATION_ERROR, Constants::SDK_UNINITIALIZATION_MESSAGE);

        Initializer::switchUser($this->user, $this->environment, $this->token, $this->sdkConfig, $this->client);
    }

    public function logger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function token(OAuthToken $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function SDKConfig(SDKConfig $sdkConfig): self
    {
        $this->sdkConfig = $sdkConfig;

        return $this;
    }

    /** @throws SDKException */
    public function resourcePath(string $resourcePath): self
    {
        if($resourcePath != null && !is_dir($resourcePath))
        {
            throw new SDKException($this->errorMessage, Constants::RESOURCE_PATH_INVALID_ERROR_MESSAGE);
        }

        $this->resourcePath = $resourcePath;

        return $this;
    }

    public function user(UserSignature $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function store(TokenStore $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function environment(Environment $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function client(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }
}
