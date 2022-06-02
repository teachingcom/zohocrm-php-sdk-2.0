<?php

namespace com\zoho\crm\api\dc;

/**
 * The interface defines the methods of Zoho CRM DataCenter instances.
 */
abstract class DataCenter
{
    private static $envs = [];

    /**
     * This Environment class instance represents the Zoho CRM Developer Environment.
     */
    public static function DEVELOPER(): Environment
    {
        return self::getEnvironment(static::getDeveloperName(), static::getDeveloperUrl());
    }

    /**
     * This Environment class instance represents the Zoho CRM Production Environment.
     */
    public static function PRODUCTION(): Environment
    {
        return self::getEnvironment(static::getProductionName(), static::getProductionUrl());
    }

    /**
     * This Environment class instance represents the Zoho CRM Sandbox Environment.
     */
    public static function SANDBOX(): Environment
    {
        return self::getEnvironment(static::getSandboxName(), static::getSandboxUrl());
    }

    /** Provides the Datacenter's Zoho CRM Accounts URL. */
    abstract protected static function getAccountsUrl(): string;

    /** Provides the Datacenter's Zoho CRM File Upload URL. */
    abstract protected static function getFileUploadUrl(): string;

    /** Provides the Developer Datacenter's Zoho CRM API URL. */
    abstract protected static function getDeveloperUrl(): string;

    /** Provides the Developer Datacenter's name. */
    abstract protected static function getDeveloperName(): string;

    /** Provides the Production Datacenter's Zoho CRM API URL. */
    abstract protected static function getProductionUrl(): string;

    /** Provides the Production Datacenter's name. */
    abstract protected static function getProductionName(): string;

    /** Provides the Sandbox Datacenter's Zoho CRM API URL. */
    abstract protected static function getSandboxUrl(): string;

    /** Provides the Sandbox Datacenter's name. */
    abstract protected static function getSandboxName(): string;

    /** Provides an Environment instance matching the given name if available. */
    public static function getByName(string $name): ?Environment
    {
        $map = [
            static::getDeveloperName() => static::DEVELOPER(),
            static::getSandboxName() => static::SANDBOX(),
            static::getProductionName() => static::PRODUCTION(),
        ];

        return $map[$name] ?? null;
    }

    /**
     * Provides an `Environment` instance corresponding to the given environment key.
     */
    protected static function getEnvironment(string $name, string $url): Environment
    {
        if (!($env = self::$envs[$name] ?? null)) {
            self::$envs[$name] = $env =
                new Environment($url, static::getAccountsUrl(), static::getFileUploadUrl(), $name);
        }

        return $env;
    }
}
