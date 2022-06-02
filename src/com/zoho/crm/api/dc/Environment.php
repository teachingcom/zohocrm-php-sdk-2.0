<?php

namespace com\zoho\crm\api\dc;

use com\zoho\crm\api\exception\SDKException;

/**
 * The class represents the properties of Zoho CRM Environment.
 */
class Environment
{
    /** @var callable[] */
    private static $datacenters = [
        [AUDataCenter::class, 'getByName'],
        [CNDataCenter::class, 'getByName'],
        [EUDataCenter::class, 'getByName'],
        [INDataCenter::class, 'getByName'],
        [JPDataCenter::class, 'getByName'],
        [USDataCenter::class, 'getByName'],
    ];

    private $url;
    private $accountsAuthUrl;
    private $accountsTokenUrl;
    private $fileUploadUrl;
    private $name;

    /**
     * Provides an environment by its name.
     * @throws SDKException
     */
    public static function getByName(string $name): self
    {
        foreach (self::$datacenters as $callable) {
            if ($env = call_user_func($callable, $name)) {
                return $env;
            }
        }

        throw new SDKException('UNKNOWN_ENV', "Unrecognized environment name: {$name}");
    }

    public function __construct(string $url, string $accountsUrl, string $fileUploadUrl, string $name)
    {
        $this->url = $url;
        $this->accountsAuthUrl = "{$accountsUrl}/oauth/v2/auth";
        $this->accountsTokenUrl = "{$accountsUrl}/oauth/v2/token";
        $this->fileUploadUrl = $fileUploadUrl;
        $this->name = $name;
    }

    /**
     * This method to get Zoho CRM API URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * This method to get Zoho CRM Accounts Tokens URL.
     */
    public function getAccountsTokenUrl(): string
    {
        return $this->accountsTokenUrl;
    }

    /**
     * This method to get Zoho CRM Accounts Auth URL.
     */
    public function getAccountsAuthUrl(): string
    {
        return $this->accountsAuthUrl;
    }

    /**
     * This method to get File Upload URL.
     */
    public function getFileUploadURL(): string
    {
        return $this->fileUploadUrl;
    }

    /**
     * This method to get name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
