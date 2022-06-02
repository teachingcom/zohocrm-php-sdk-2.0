<?php

namespace com\zoho\crm\api\dc;

/**
 * This class represents the properties of Zoho CRM in CN Domain.
 */
class CNDataCenter extends DataCenter
{
    protected static function getAccountsUrl(): string
    {
        return 'https://accounts.zoho.com.cn';
    }

    protected static function getFileUploadURL(): string
    {
        return 'https://content.zohoapis.com.cn';
    }

    protected static function getDeveloperUrl(): string
    {
        return 'https://developer.zohoapis.com.cn';
    }

    protected static function getDeveloperName(): string
    {
        return 'cn_dev';
    }

    protected static function getSandboxUrl(): string
    {
        return 'https://sandbox.zohoapis.com.cn';
    }

    protected static function getSandboxName(): string
    {
        return 'cn_sdb';
    }

    protected static function getProductionUrl(): string
    {
        return 'https://www.zohoapis.com.cn';
    }

    protected static function getProductionName(): string
    {
        return 'cn_prd';
    }
}
