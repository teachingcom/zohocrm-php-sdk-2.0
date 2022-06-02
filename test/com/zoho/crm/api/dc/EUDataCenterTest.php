<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\EUDataCenter;
use PHPUnit\Framework\TestCase;

class EUDataCenterTest extends TestCase
{
    public function test_development()
    {
        $actual = EUDataCenter::DEVELOPER();
        $this->assertSame('https://developer.zohoapis.com.eu', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.eu/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.eu/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.eu', $actual->getFileUploadURL());
        $this->assertSame('eu_dev', $actual->getName());
    }

    public function test_production()
    {
        $actual = EUDataCenter::PRODUCTION();
        $this->assertSame('https://www.zohoapis.com.eu', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.eu/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.eu/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.eu', $actual->getFileUploadURL());
        $this->assertSame('eu_prd', $actual->getName());
    }

    public function test_sandbox()
    {
        $actual = EUDataCenter::SANDBOX();
        $this->assertSame('https://sandbox.zohoapis.com.eu', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.eu/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.eu/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.eu', $actual->getFileUploadURL());
        $this->assertSame('eu_sdb', $actual->getName());
    }
}
