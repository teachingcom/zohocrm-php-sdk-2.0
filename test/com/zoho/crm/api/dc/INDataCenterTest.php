<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\INDataCenter;
use PHPUnit\Framework\TestCase;

class INDataCenterTest extends TestCase
{
    public function test_development()
    {
        $actual = INDataCenter::DEVELOPER();
        $this->assertSame('https://developer.zohoapis.com.in', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.in/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.in/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.in', $actual->getFileUploadURL());
        $this->assertSame('in_dev', $actual->getName());
    }

    public function test_production()
    {
        $actual = INDataCenter::PRODUCTION();
        $this->assertSame('https://www.zohoapis.com.in', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.in/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.in/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.in', $actual->getFileUploadURL());
        $this->assertSame('in_prd', $actual->getName());
    }

    public function test_sandbox()
    {
        $actual = INDataCenter::SANDBOX();
        $this->assertSame('https://sandbox.zohoapis.com.in', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.in/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.in/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.in', $actual->getFileUploadURL());
        $this->assertSame('in_sdb', $actual->getName());
    }
}
