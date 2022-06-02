<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\CNDataCenter;
use PHPUnit\Framework\TestCase;

class CNDataCenterTest extends TestCase
{
    public function test_development()
    {
        $actual = CNDataCenter::DEVELOPER();
        $this->assertSame('https://developer.zohoapis.com.cn', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.cn/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.cn/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.cn', $actual->getFileUploadURL());
        $this->assertSame('cn_dev', $actual->getName());
    }

    public function test_production()
    {
        $actual = CNDataCenter::PRODUCTION();
        $this->assertSame('https://www.zohoapis.com.cn', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.cn/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.cn/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.cn', $actual->getFileUploadURL());
        $this->assertSame('cn_prd', $actual->getName());
    }

    public function test_sandbox()
    {
        $actual = CNDataCenter::SANDBOX();
        $this->assertSame('https://sandbox.zohoapis.com.cn', $actual->getUrl());
        $this->assertSame('https://accounts.zoho.com.cn/oauth/v2/auth', $actual->getAccountsAuthUrl());
        $this->assertSame('https://accounts.zoho.com.cn/oauth/v2/token', $actual->getAccountsTokenUrl());
        $this->assertSame('https://content.zohoapis.com.cn', $actual->getFileUploadURL());
        $this->assertSame('cn_sdb', $actual->getName());
    }
}
