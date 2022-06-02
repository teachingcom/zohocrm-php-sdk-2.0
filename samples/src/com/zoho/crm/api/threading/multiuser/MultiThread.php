<?php

namespace samples\src\com\zoho\crm\api\threading\multiuser;

use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\crm\api\ClientBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\GetRecordsHeader;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\UserSignature;
use DateTimeZone;
use Doctrine\DBAL\DriverManager;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MultiThread
{
	public function main()
	{
		$logger = new Logger('zoho', [new StreamHandler("/Users/user_name/Documents/php_sdk_log.log", Logger::INFO)]);
		$environment1 = USDataCenter::PRODUCTION();
		$user1 = new UserSignature("abc@zoho.com");
        $resourcePath ="/Users/user_name/Documents/php-sdk-application/";

		// $tokenStore = new DBStore(DriverManager::getConnection(['url' => 'mysql://user:pass@host/mydb']), 'table');

		//Create a Token instance
		$token1 = (new OAuthBuilder)
            ->clientId("ClientId1")
            // ->id("php_abc_us_prd_")
            ->clientSecret("ClientSecret1")
            // ->grantToken("GrantToken")
            ->refreshToken("RefreshToken")
            // ->redirectURL("RedirectURL")
            ->build();

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
            ->build();

        $initializeBuilder = new InitializeBuilder();
        $initializeBuilder
            ->user($user1)
            ->environment($environment1)
            ->token($token1)
            ->store($tokenStore)
            ->SDKConfig($configInstance)
            ->resourcePath($resourcePath)
            ->logger($logger)
            ->client($client)
            ->initialize();

        $this->getRecords("Leads");

		$environment2 = USDataCenter::PRODUCTION();

		$user2 = new UserSignature("xyz@zoho.com");

        //Create a Token instance
		$token2 = (new OAuthBuilder)
            ->clientId("ClientId2")
            // ->id("php_abc_us_prd_")
            ->clientSecret("ClientSecret2")
            // ->grantToken("GrantToken")
            ->refreshToken("RefreshToken")
            // ->redirectURL("RedirectURL")
            ->build();

        $initializeBuilder
            ->user($user2)
            ->environment($environment2)
            ->token($token2)
            ->SDKConfig($configInstance)
            ->switchUser();

        $this->getRecords("Leads");

        $initializeBuilder
            ->user($user1)
            ->environment($environment1)
            ->token($token1)
            ->SDKConfig($configInstance)
            ->switchUser();

        $this->getRecords("apiName2");
    }

    public function getRecords(string $moduleAPIName)
    {
        try
        {
            $recordOperations = new RecordOperations();
            $paramInstance = new ParameterMap();
            $headerInstance = new HeaderMap();
            $ifmodifiedsince = date_create("2020-06-02T11:03:06+05:30")->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $headerInstance->add(GetRecordsHeader::IfModifiedSince(), $ifmodifiedsince);

            //Call getRecord method that takes paramInstance, moduleAPIName as parameter
            $response = $recordOperations->getRecords($moduleAPIName,$paramInstance, $headerInstance);

            echo($response->getStatusCode() . "\n");
            print_r($response);
            echo("\n");
        }
        catch (Exception $e)
        {
            print_r($e);
        }
    }
}

$obj = new MultiThread();
$obj->main();
