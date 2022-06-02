<?php

namespace test\com\zoho\crm\api;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use GuzzleHttp\ClientInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;

class InitializerTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        Initializer::reset();
        parent::tearDown();
    }

    public function testInitializeAndSwitchUser()
    {
        $user = Mockery::mock(UserSignature::class);
        $user->shouldReceive(['getEmail' => 'bill@microsoft.com']);
        $env = Mockery::mock(Environment::class);
        $env->shouldReceive(['getUrl' => 'https://env']);
        $expectToString = 'for Email Id : bill@microsoft.com in Environment : https://env.';
        $token = Mockery::mock(OAuthToken::class);
        $store = Mockery::mock(TokenStore::class);
        $config = Mockery::mock(SDKConfig::class);
        $path = __DIR__;
        $log = Mockery::mock(LoggerInterface::class);
        $log->shouldReceive('info')->with(Constants::INITIALIZATION_SUCCESSFUL . $expectToString)->atLeast()->once();
        $client = Mockery::mock(ClientInterface::class);

        Initializer::initialize($user, $env, $token, $store, $config, $path, $log, $client);
        $actual = Initializer::getInitializer();

        $this->assertInstanceOf(Initializer::class, $actual);
        $this->assertContains($actual, Initializer::$LOCAL);
        $this->assertSame($expectToString, $actual->toString());
        $this->assertSame($user, $actual->getUser());
        $this->assertSame($env, $actual->getEnvironment());
        $this->assertSame($token, $actual->getToken());
        $this->assertSame($store, $actual->getStore());
        $this->assertSame($config, $actual->getSDKConfig());
        $this->assertSame($path, $actual->getResourcePath());
        $this->assertSame($client, $actual->getClient());

        $newUser = Mockery::mock(UserSignature::class);
        $newUser->shouldReceive(['getEmail' => 'melinda@microsoft.com']);
        $newEnv = Mockery::mock(Environment::class);
        $newEnv->shouldReceive(['getUrl' => 'https://new.env']);
        $newExpectToString = 'for Email Id : melinda@microsoft.com in Environment : https://new.env.';
        $log->shouldReceive('info')->with(Constants::INITIALIZATION_SWITCHED . $newExpectToString)->atLeast()->once();
        $newToken = Mockery::mock(OAuthToken::class);
        $newConfig = Mockery::mock(SDKConfig::class);
        $newClient = Mockery::mock(ClientInterface::class);

        Initializer::switchUser($newUser, $newEnv, $newToken, $newConfig, $newClient);
        $actual = Initializer::getInitializer();

        $this->assertInstanceOf(Initializer::class, $actual);
        $this->assertContains($actual, Initializer::$LOCAL);
        $this->assertCount(2, Initializer::$LOCAL);
        $this->assertSame($newExpectToString, $actual->toString());
        $this->assertSame($newUser, $actual->getUser());
        $this->assertSame($newEnv, $actual->getEnvironment());
        $this->assertSame($newToken, $actual->getToken());
        $this->assertSame($store, $actual->getStore());
        $this->assertSame($newConfig, $actual->getSDKConfig());
        $this->assertSame($path, $actual->getResourcePath());
        $this->assertSame($newClient, $actual->getClient());
    }

    public function testGetInitializerBeforeInitialize()
    {
        $this->assertNull(Initializer::getInitializer());
    }
}
