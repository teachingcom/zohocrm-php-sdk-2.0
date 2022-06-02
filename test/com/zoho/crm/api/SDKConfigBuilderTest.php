<?php

namespace test\com\zoho\crm\api;

use com\zoho\crm\api\SDKConfigBuilder;
use PHPUnit\Framework\TestCase;

class SDKConfigBuilderTest extends TestCase
{
    public function testBuildWithDefaults()
    {
        $actual = (new SDKConfigBuilder())->build();

        $this->assertFalse($actual->getAutoRefreshFields());
        $this->assertTrue($actual->getPickListValidation());
    }

    public function testBuildWithValues()
    {
        $actual = (new SDKConfigBuilder())
            ->autoRefreshFields(true)
            ->pickListValidation(false)
            ->build();

        $this->assertTrue($actual->getAutoRefreshFields());
        $this->assertFalse($actual->getPickListValidation());
    }
}
