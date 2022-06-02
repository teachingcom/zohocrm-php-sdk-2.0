<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\AUDataCenter;
use PHPUnit\Framework\TestCase;

class AUDataCenterTest extends TestCase
{
    public function test_development()
    {
        $actual = AUDataCenter::DEVELOPER();
        $this->assertSame('https://developer.zohoapis.com.au', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.au/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.au/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.au', $actual->getFileUploadURL());
        $this->assertSame('au_dev', $actual->getName());
    }

    public function test_production()
    {
        $actual = AUDataCenter::PRODUCTION();
        $this->assertSame('https://www.zohoapis.com.au', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.au/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.au/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.au', $actual->getFileUploadURL());
        $this->assertSame('au_prd', $actual->getName());
    }

    public function test_sandbox()
    {
        $actual = AUDataCenter::SANDBOX();
        $this->assertSame('https://sandbox.zohoapis.com.au', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.au/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.au/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.au', $actual->getFileUploadURL());
        $this->assertSame('au_sdb', $actual->getName());
    }
}
