<?php

namespace test\com\zoho\crm\api;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Mockery;
use Psr\Log\LoggerInterface;
use test\com\zoho\TestCase;

class InitializeBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Initializer::reset();
    }

    protected function tearDown(): void
    {
        Initializer::reset();
        parent::tearDown();
    }

    public function testInitializeBeforeInitializerInitializedWithoutUser()
    {
        $builder = new InitializeBuilder;

        $this->expectExceptionObject(new SDKException(Constants::INITIALIZATION_ERROR, Constants::USERSIGNATURE_ERROR_MESSAGE));
        $builder->initialize();
    }

    public function testInitializeBeforeInitializerInitializedWithoutEnvironment()
    {
        $builder = (new InitializeBuilder)->user(Mockery::mock(UserSignature::class));

        $this->expectExceptionObject(new SDKException(Constants::INITIALIZATION_ERROR, Constants::ENVIRONMENT_ERROR_MESSAGE));
        $builder->initialize();
    }

    public function testInitializeBeforeInitializerInitializedWithoutToken()
    {
        $builder = (new InitializeBuilder)
            ->user(Mockery::mock(UserSignature::class))
            ->environment(Mockery::mock(Environment::class));

        $this->expectExceptionObject(new SDKException(Constants::INITIALIZATION_ERROR, Constants::TOKEN_ERROR_MESSAGE));
        $builder->initialize();
    }

    public function testInitializeBeforeInitializerInitializedWithoutLogger()
    {
        $builder = (new InitializeBuilder)
            ->user(Mockery::mock(UserSignature::class))
            ->environment(Mockery::mock(Environment::class))
            ->token(Mockery::mock(OAuthToken::class));

        $this->expectExceptionObject(new SDKException(Constants::INITIALIZATION_ERROR, Constants::LOGGER_ERROR_MESSAGE));
        $builder->initialize();
    }

    public function testInitializeBeforeInitializerInitializedWithoutOptionals()
    {
        (new InitializeBuilder)
            ->user($user = Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'f@g.h'])->getMock())
            ->environment($env = Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'i.j'])->getMock())
            ->token($token = Mockery::mock(OAuthToken::class))
            ->logger(Mockery::mock(LoggerInterface::class)->shouldReceive(['info' => null])->getMock())
            ->initialize();
        $actual = Initializer::getInitializer();

        $this->assertInstanceOf(Initializer::class, $actual);
        $this->assertSame($user, $actual->getUser());
        $this->assertSame($env, $actual->getEnvironment());
        $this->assertSame($token, $actual->getToken());
        $this->assertEquals(new FileStore(self::getRootPath() . DIRECTORY_SEPARATOR . Constants::TOKEN_FILE), $actual->getStore());
        $this->assertEquals((new SDKConfigBuilder)->build(), $actual->getSDKConfig());
        $this->assertSame(self::getRootPath(), $actual->getResourcePath());
        $this->assertInstanceOf(Client::class, $actual->getClient());
    }

    public function testInitializeBeforeInitializerInitializedWithOptionals()
    {
        (new InitializeBuilder)
            ->user($user = Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'f@g.h'])->getMock())
            ->environment($env = Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'i.j'])->getMock())
            ->token($token = Mockery::mock(OAuthToken::class))
            ->logger(Mockery::mock(LoggerInterface::class)->shouldReceive(['info' => null])->getMock())
            ->SDKConfig($config = Mockery::mock(SDKConfig::class))
            ->store($store = Mockery::mock(TokenStore::class))
            ->resourcePath($path = __DIR__)
            ->client($client = Mockery::mock(ClientInterface::class))
            ->initialize();
        $actual = Initializer::getInitializer();

        $this->assertInstanceOf(Initializer::class, $actual);
        $this->assertSame($user, $actual->getUser());
        $this->assertSame($env, $actual->getEnvironment());
        $this->assertSame($token, $actual->getToken());
        $this->assertSame($store, $actual->getStore());
        $this->assertSame($config, $actual->getSDKConfig());
        $this->assertSame($path, $actual->getResourcePath());
    }

    public function testInitializeAfterInitializerInitializedWithoutOptionals()
    {
        Initializer::initialize(
            $user = Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'a@b.c'])->getMock(),
            $env = Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'd.e'])->getMock(),
            $token = Mockery::mock(OAuthToken::class),
            $store = Mockery::mock(TokenStore::class),
            $config = Mockery::mock(SDKConfig::class),
            __DIR__,
            Mockery::mock(LoggerInterface::class)->shouldReceive(['info' => null])->getMock(),
            $client = Mockery::mock(ClientInterface::class)
        );
        $initializer = Initializer::getInitializer();

        (new InitializeBuilder)->initialize();
        $newInitializer = Initializer::getInitializer();

        $this->assertNotSame($initializer, $newInitializer);
        $this->assertSame($user, $newInitializer->getUser());
        $this->assertSame($env, $newInitializer->getEnvironment());
        $this->assertSame($token, $newInitializer->getToken());
        $this->assertNotSame($store, $newInitializer->getStore());
        $root = realpath(__DIR__ . '/../../../../../');
        $this->assertEquals(new FileStore($root . DIRECTORY_SEPARATOR . Constants::TOKEN_FILE), $newInitializer->getStore());
        $this->assertEquals($config, $newInitializer->getSDKConfig());
        $this->assertSame($root, $newInitializer->getResourcePath());
        $this->assertSame($client, $newInitializer->getClient());
    }

    public function testInitializeAfterInitializerInitializedWithOptionals()
    {
        Initializer::initialize(
            Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'a@b.c'])->getMock(),
            Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'd.e'])->getMock(),
            Mockery::mock(OAuthToken::class),
            Mockery::mock(TokenStore::class),
            Mockery::mock(SDKConfig::class),
            __DIR__ . '/../',
            Mockery::mock(LoggerInterface::class)->shouldReceive(['info' => null])->getMock(),
            Mockery::mock(ClientInterface::class)
        );
        $initializer = Initializer::getInitializer();

        (new InitializeBuilder)
            ->user($user = Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'f@g.h'])->getMock())
            ->environment($env = Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'i.j'])->getMock())
            ->token($token = Mockery::mock(OAuthToken::class))
            ->logger(Mockery::mock(LoggerInterface::class)->shouldReceive(['info' => null])->getMock())
            ->SDKConfig($config = Mockery::mock(SDKConfig::class))
            ->store($store = Mockery::mock(TokenStore::class))
            ->resourcePath($path = __DIR__)
            ->client($client = Mockery::mock(ClientInterface::class))
            ->initialize();
        $newInitializer = Initializer::getInitializer();

        $this->assertNotSame($initializer, $newInitializer);
        $this->assertSame($user, $newInitializer->getUser());
        $this->assertSame($env, $newInitializer->getEnvironment());
        $this->assertSame($token, $newInitializer->getToken());
        $this->assertSame($store, $newInitializer->getStore());
        $this->assertSame($config, $newInitializer->getSDKConfig());
        $this->assertSame($path, $newInitializer->getResourcePath());
        $this->assertSame($client, $newInitializer->getClient());
    }

    public function testSwitchUserBeforeInitializerInitialized()
    {
        $builder = new InitializeBuilder;

        $this->expectExceptionObject(new SDKException(Constants::SDK_UNINITIALIZATION_ERROR, Constants::SDK_UNINITIALIZATION_MESSAGE));
        $builder->switchUser();
    }

    public function testSwitchUserAfterInitializerInitialized()
    {
        Initializer::initialize(
            Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'a@b.c'])->getMock(),
            Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'd.e'])->getMock(),
            Mockery::mock(OAuthToken::class),
            Mockery::mock(TokenStore::class),
            Mockery::mock(SDKConfig::class),
            $resourcePath = __DIR__,
            Mockery::mock(LoggerInterface::class)->shouldReceive(['info' => null])->getMock(),
            $client = Mockery::mock(ClientInterface::class)
        );
        $initializer = Initializer::getInitializer();

        (new InitializeBuilder)
            ->user($newUser = Mockery::mock(UserSignature::class)->shouldReceive(['getEmail' => 'f@g.h'])->getMock())
            ->environment($newEnv = Mockery::mock(Environment::class)->shouldReceive(['getUrl' => 'i.j'])->getMock())
            ->token($newToken = Mockery::mock(OAuthToken::class))
            ->SDKConfig($newConfig = Mockery::mock(SDKConfig::class))
            ->switchUser();
        $newInitializer = Initializer::getInitializer();

        $this->assertNotSame($initializer, $newInitializer);
        $this->assertSame($newUser, $newInitializer->getUser());
        $this->assertSame($newEnv, $newInitializer->getEnvironment());
        $this->assertSame($newToken, $newInitializer->getToken());
        $this->assertSame($newConfig, $newInitializer->getSDKConfig());
        $this->assertSame($resourcePath, $newInitializer->getResourcePath());
        $this->assertSame($client, $newInitializer->getClient());
    }
}
