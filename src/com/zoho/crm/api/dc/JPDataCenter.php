<?php

namespace com\zoho\crm\api\dc;

/**
 * This class represents the properties of Zoho CRM in Japan Domain.
 */
class JPDataCenter extends DataCenter
{
    protected static function getAccountsUrl(): string
    {
        return 'https://accounts.zoho.com.jp';
    }

    protected static function getFileUploadURL(): string
    {
        return 'https://content.zohoapis.com.jp';
    }

    protected static function getDeveloperUrl(): string
    {
        return 'https://developer.zohoapis.com.jp';
    }

    protected static function getDeveloperName(): string
    {
        return 'jp_dev';
    }

    protected static function getSandboxUrl(): string
    {
        return 'https://sandbox.zohoapis.com.jp';
    }

    protected static function getSandboxName(): string
    {
        return 'jp_sdb';
    }

    protected static function getProductionUrl(): string
    {
        return 'https://www.zohoapis.com.jp';
    }

    protected static function getProductionName(): string
    {
        return 'jp_prd';
    }
}
