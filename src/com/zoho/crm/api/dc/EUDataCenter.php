<?php

namespace com\zoho\crm\api\dc;

/***
 * This class represents the properties of Zoho CRM in EU Domain.
 */
class EUDataCenter extends DataCenter
{
    protected static function getAccountsUrl(): string
    {
        return 'https://accounts.zoho.com.eu';
    }

    protected static function getFileUploadURL(): string
    {
        return 'https://content.zohoapis.com.eu';
    }

    protected static function getDeveloperUrl(): string
    {
        return 'https://developer.zohoapis.com.eu';
    }

    protected static function getDeveloperName(): string
    {
        return 'eu_dev';
    }

    protected static function getSandboxUrl(): string
    {
        return 'https://sandbox.zohoapis.com.eu';
    }

    protected static function getSandboxName(): string
    {
        return 'eu_sdb';
    }

    protected static function getProductionUrl(): string
    {
        return 'https://www.zohoapis.com.eu';
    }

    protected static function getProductionName(): string
    {
        return 'eu_prd';
    }
}
