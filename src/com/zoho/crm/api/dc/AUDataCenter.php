<?php

namespace com\zoho\crm\api\dc;

/**
 * This class represents the properties of Zoho CRM in AU Domain.
 */
class AUDataCenter extends DataCenter
{
    protected static function getAccountsUrl(): string
    {
        return 'https://accounts.zoho.com.au';
    }

    protected static function getFileUploadURL(): string
    {
        return 'https://content.zohoapis.com.au';
    }

    protected static function getDeveloperUrl(): string
    {
        return 'https://developer.zohoapis.com.au';
    }

    protected static function getDeveloperName(): string
    {
        return 'au_dev';
    }

    protected static function getSandboxUrl(): string
    {
        return 'https://sandbox.zohoapis.com.au';
    }

    protected static function getSandboxName(): string
    {
        return 'au_sdb';
    }

    protected static function getProductionUrl(): string
    {
        return 'https://www.zohoapis.com.au';
    }

    protected static function getProductionName(): string
    {
        return 'au_prd';
    }
}
