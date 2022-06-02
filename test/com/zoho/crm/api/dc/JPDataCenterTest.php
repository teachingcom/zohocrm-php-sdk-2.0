<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\JPDataCenter;
use PHPUnit\Framework\TestCase;

class JPDataCenterTest extends TestCase
{
    public function test_development()
    {
        $actual = JPDataCenter::DEVELOPER();
        $this->assertSame('https://developer.zohoapis.com.jp', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.jp/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.jp/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.jp', $actual->getFileUploadURL());
        $this->assertSame('jp_dev', $actual->getName());
    }

    public function test_production()
    {
        $actual = JPDataCenter::PRODUCTION();
        $this->assertSame('https://www.zohoapis.com.jp', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.jp/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.jp/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.jp', $actual->getFileUploadURL());
        $this->assertSame('jp_prd', $actual->getName());
    }

    public function test_sandbox()
    {
        $actual = JPDataCenter::SANDBOX();
        $this->assertSame('https://sandbox.zohoapis.com.jp', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.jp/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.jp/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.jp', $actual->getFileUploadURL());
        $this->assertSame('jp_sdb', $actual->getName());
    }
}
