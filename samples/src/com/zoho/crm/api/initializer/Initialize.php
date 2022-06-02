<?php
namespace samples\src\com\zoho\crm\api\initializer;

use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\crm\api\ClientBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\UserSignature;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Initialize
{
    public static function initialize()
    {
        // Provide a PSR-compatible Logger instance. Monolog is a great library to use if you don't have one already.
        $logger = new Logger('zoho', [new StreamHandler("/Users/user_name/Documents/php_sdk_log.log", Logger::INFO)]);

        //Create an UserSignature instance that takes user Email as parameter
        $user = new UserSignature("abc@zoho.com");

        /**
		 * Configure the environment
		 * which is of the pattern Domain.Environment
		 * Available Domains: USDataCenter, EUDataCenter, INDataCenter, CNDataCenter, AUDataCenter
		 * Available Environments: PRODUCTION, DEVELOPER, SANDBOX
		 */
        $environment = USDataCenter::PRODUCTION();

        //Create a Token instance
		$token = (new OAuthBuilder)
            ->clientId("ClientId")
            // ->id("php_abc_us_prd_")
            ->clientSecret("ClientSecret")
            ->grantToken("GrantToken")
            ->refreshToken("RefreshToken")
            ->redirectURL("RedirectURL")
            ->build();

        // $tokenStore = new DBStore(DriverManager::getConnection(['url' => 'mysql://user:pass@host/mydb']), 'table');

        $tokenStore = new FileStore("/Users/Documents/php_sdk_token.txt");

		$resourcePath = "/Users/Documents/";

		$autoRefreshFields = false;
		$pickListValidation = false;
		$configInstance = (new SDKConfigBuilder)
            ->autoRefreshFields($autoRefreshFields)
            ->pickListValidation($pickListValidation)
            ->build();

        $enableSSLVerification = true;
        $connectionTimeout = 50; //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        $timeout = 50; //The maximum number of seconds to allow cURL functions to execute.
		$client = (new ClientBuilder)
            ->sslVerification($enableSSLVerification)
            ->connectionTimeout($connectionTimeout)
            ->timeout($timeout)
            //->proxy("https://proxyUser:password@proxyHost:3306")
            ->build();

       	/**
		 * Call static initialize method of Initializer class that takes the arguments
		 * user -> UserSignature instance
		 * environment -> Environment instance
		 * token -> Token instance
		 * store -> TokenStore instance
		 * SDKConfig -> SDKConfig instance
		 * resourcePath -> The path containing the absolute directory path to store user specific JSON files containing module fields information.
		 * logger -> Logger instance
		 * client -> REST Client instance
		 */
		(new InitializeBuilder)
            ->user($user)
            ->environment($environment)
            ->token($token)
            ->store($tokenStore)
            ->SDKConfig($configInstance)
            ->resourcePath($resourcePath)
            ->logger($logger)
            ->client($client)
            ->initialize();
    }
}
