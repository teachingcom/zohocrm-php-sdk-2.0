<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\USDataCenter;
use PHPUnit\Framework\TestCase;

class USDataCenterTest extends TestCase
{
    public function test_development()
    {
        $actual = USDataCenter::DEVELOPER();
        $this->assertSame('https://developer.zohoapis.com', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com', $actual->getFileUploadURL());
        $this->assertSame('us_dev', $actual->getName());
    }

    public function test_production()
    {
        $actual = USDataCenter::PRODUCTION();
        $this->assertSame('https://www.zohoapis.com', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com', $actual->getFileUploadURL());
        $this->assertSame('us_prd', $actual->getName());
    }

    public function test_sandbox()
    {
        $actual = USDataCenter::SANDBOX();
        $this->assertSame('https://sandbox.zohoapis.com', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com', $actual->getFileUploadURL());
        $this->assertSame('us_sdb', $actual->getName());
    }
}
