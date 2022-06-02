<?php

namespace test\com\zoho\crm\api\dc;

use com\zoho\crm\api\dc\AUDataCenter;
use com\zoho\crm\api\dc\CNDataCenter;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\dc\EUDataCenter;
use com\zoho\crm\api\dc\INDataCenter;
use com\zoho\crm\api\dc\JPDataCenter;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\exception\SDKException;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{

    public function getNameData(): array
    {
        return [
            'australia dev' => [AUDataCenter::DEVELOPER()->getName(), AUDataCenter::DEVELOPER()],
            'australia sandbox' => [AUDataCenter::SANDBOX()->getName(), AUDataCenter::SANDBOX()],
            'australia production' => [AUDataCenter::PRODUCTION()->getName(), AUDataCenter::PRODUCTION()],
            'china dev' => [CNDataCenter::DEVELOPER()->getName(), CNDataCenter::DEVELOPER()],
            'china sandbox' => [CNDataCenter::SANDBOX()->getName(), CNDataCenter::SANDBOX()],
            'china production' => [CNDataCenter::PRODUCTION()->getName(), CNDataCenter::PRODUCTION()],
            'europe dev' => [EUDataCenter::DEVELOPER()->getName(), EUDataCenter::DEVELOPER()],
            'europe sandbox' => [EUDataCenter::SANDBOX()->getName(), EUDataCenter::SANDBOX()],
            'europe production' => [EUDataCenter::PRODUCTION()->getName(), EUDataCenter::PRODUCTION()],
            'india dev' => [INDataCenter::DEVELOPER()->getName(), INDataCenter::DEVELOPER()],
            'india sandbox' => [INDataCenter::SANDBOX()->getName(), INDataCenter::SANDBOX()],
            'india production' => [INDataCenter::PRODUCTION()->getName(), INDataCenter::PRODUCTION()],
            'japan dev' => [JPDataCenter::DEVELOPER()->getName(), JPDataCenter::DEVELOPER()],
            'japan sandbox' => [JPDataCenter::SANDBOX()->getName(), JPDataCenter::SANDBOX()],
            'japan production' => [JPDataCenter::PRODUCTION()->getName(), JPDataCenter::PRODUCTION()],
            'united states dev' => [USDataCenter::DEVELOPER()->getName(), USDataCenter::DEVELOPER()],
            'united states sandbox' => [USDataCenter::SANDBOX()->getName(), USDataCenter::SANDBOX()],
            'united states production' => [USDataCenter::PRODUCTION()->getName(), USDataCenter::PRODUCTION()],
        ];
    }

    /** @dataProvider getNameData */
    public function testGetByNameSuccess(string $name, Environment $expected)
    {
        $actual = Environment::getByName($name);

        $this->assertEquals($actual, $expected);
    }

    public function testGetByNameWhenUnrecognized()
    {
        try {
            Environment::getByName('not_real');
            $this->fail('Exception not thrown.');
        } catch (SDKException $e) {
            $this->assertEquals('UNKNOWN_ENV', $e->getErrorCode());
            $this->assertEquals('Unrecognized environment name: not_real', $e->getMessage());
            $this->assertEmpty($e->getDetails());
        }
    }
}
