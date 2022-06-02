<?php

namespace test\com\zoho;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\UserSignature;
use Doctrine\DBAL\DriverManager;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class TestCase extends MockeryTestCase
{
    /** @var Generator */
    protected $faker;
    /** @var MockHandler */
    protected $clientMockHandler;

    protected static function getRootPath(): string
    {
        return realpath(__DIR__ . '/../../../');
    }

    protected static function makeJsonResponse(int $status = null, string $body = null): Response
    {
        return new Response($status, ['content-type' => 'application/json'], $body);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = $this->faker();
        Carbon::setTestNow($this->now());
        CarbonImmutable::setTestNow($this->now());
    }

    protected function tearDown(): void
    {
        $resources = glob(self::getRootPath() . '/src/resources/*.json');
        foreach ($resources as $resource) {
            if ('jsondetails.json' != strtolower(substr($resource, -16))) {
                unlink($resource);
            }
        }
        parent::tearDown();
    }

    protected function faker(): Generator
    {
        return Factory::create();
    }

    protected function now(): Carbon
    {
        return new Carbon('2022-06-07 01:02:03');
    }

    protected function initialize(TokenStore $store = null): void
    {
        $user = new UserSignature('abc@zoho.com');
        $env = USDataCenter::DEVELOPER();
        $token = (new OAuthBuilder)
            ->clientId('ClientId')
            ->clientSecret('ClientSecret')
            ->grantToken('GrantToken')
            ->refreshToken('RefreshToken')
            ->redirectURL('https://redirect.to')
            ->accessToken('access-token')
            ->expiryTime($this->now()->addHour())
            ->build();
        $store = $store ?? (new DBStore(DriverManager::getConnection(['url' => 'sqlite:///:memory:']), 'zoho'))->createSchema();
        $config = new SDKConfig(false, true);
        $resourcePath = __DIR__ . '/../../../src';
        $logger = new Logger('null', [new NullHandler]);
        $this->clientMockHandler = new MockHandler;
        $client = new Client(['handler' => HandlerStack::create($this->clientMockHandler)]);

        Initializer::initialize($user, $env, $token, $store, $config, $resourcePath, $logger, $client);
    }

    protected function getFakeTokenData(array $data = [])
    {
        return array_merge([
            'id' => $this->faker->md5,
            'user_mail' => $this->faker->email,
            'client_id' => $this->faker->md5,
            'client_secret' => $this->faker->md5,
            'refresh_token' => $this->faker->md5,
            'access_token' => $this->faker->md5,
            'grant_token' => $this->faker->md5,
            'expiry_time' => $this->faker->dateTime->format('Y-m-d H:i:s'),
            'redirect_url' => $this->faker->url,
        ], $data);
    }

    protected function makeToken(array $data = []): OAuthToken
    {
        $data = $this->getFakeTokenData($data);
        return new OAuthToken(
            $data['client_id'],
            $data['client_secret'],
            $data['id'],
            $data['grant_token'],
            $data['refresh_token'],
            $data['redirect_url'],
            $data['access_token'],
            $data['user_mail'],
            new CarbonImmutable($data['expiry_time']),
        );
    }
}
