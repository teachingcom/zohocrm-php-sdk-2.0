<?php

namespace test\com\zoho;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\UserSignature;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

trait RequiresInitializing
{
    /** @var MockHandler|null */
    protected $clientMockHandler;

    protected function initialize(
        UserSignature $user = null,
        Environment $env = null,
        OAuthToken $token = null,
        TokenStore $store = null,
        SDKConfig $config = null,
        LoggerInterface $logger = null,
        ClientInterface $client = null
    ) {
        $user = $user ?? new UserSignature('abc@zoho.com');
        $env = $env ?? USDataCenter::DEVELOPER();
        $token = $token ?? new OAuthToken('ClientId', 'ClientSecret', null, 'GrantToken', 'RefreshToken', 'RedirectURL', 'a-cool-token');
        $store = $store ?? (new DBStore(DriverManager::getConnection(['url' => 'sqlite:///:memory:']), 'zoho'))->createSchema();
        $config = $config ?? new SDKConfig(false, true);
        $resourcePath = __DIR__ . '/../../../src';
        $logger = $logger ?? new Logger('null', [new NullHandler]);
        $this->clientMockHandler = new MockHandler;
        $client = $client ?? new Client(['handler' => HandlerStack::create($this->clientMockHandler)]);

        Initializer::initialize($user, $env, $token, $store, $config, $resourcePath, $logger, $client);
    }
}
