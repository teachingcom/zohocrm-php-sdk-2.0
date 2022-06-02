<?php

namespace com\zoho\crm\api\dc;

/**
 * This class represents the properties of Zoho CRM in IN Domain.
 */
class INDataCenter extends DataCenter
{
    protected static function getAccountsUrl(): string
    {
        return 'https://accounts.zoho.com.in';
    }

    protected static function getFileUploadURL(): string
    {
        return 'https://content.zohoapis.com.in';
    }

    protected static function getDeveloperUrl(): string
    {
        return 'https://developer.zohoapis.com.in';
    }

    protected static function getDeveloperName(): string
    {
        return 'in_dev';
    }

    protected static function getSandboxUrl(): string
    {
        return 'https://sandbox.zohoapis.com.in';
    }

    protected static function getSandboxName(): string
    {
        return 'in_sdb';
    }

    protected static function getProductionUrl(): string
    {
        return 'https://www.zohoapis.com.in';
    }

    protected static function getProductionName(): string
    {
        return 'in_prd';
    }
}
