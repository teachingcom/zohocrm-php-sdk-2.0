<?php

namespace test\com\zoho;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfig;
use com\zoho\crm\api\UserSignature;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class InitializedTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->initialize();
    }
}
