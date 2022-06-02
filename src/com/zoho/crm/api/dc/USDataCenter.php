<?php

namespace com\zoho\crm\api\dc;

/**
 * This class represents the properties of Zoho CRM in US Domain.
 */
class USDataCenter extends DataCenter
{
    protected static function getAccountsUrl(): string
    {
        return 'https://accounts.zoho.com';
    }

    protected static function getFileUploadURL(): string
    {
        return 'https://content.zohoapis.com';
    }

    protected static function getDeveloperUrl(): string
    {
        return 'https://developer.zohoapis.com';
    }

    protected static function getDeveloperName(): string
    {
        return 'us_dev';
    }

    protected static function getSandboxUrl(): string
    {
        return 'https://sandbox.zohoapis.com';
    }

    protected static function getSandboxName(): string
    {
        return 'us_sdb';
    }

    protected static function getProductionUrl(): string
    {
        return 'https://www.zohoapis.com';
    }

    protected static function getProductionName(): string
    {
        return 'us_prd';
    }
}
