<?php

namespace test\com\zoho\crm\api\org;

use com\zoho\crm\api\org\Org;
use com\zoho\crm\api\org\OrgOperations;
use com\zoho\crm\api\org\ResponseWrapper;
use DateTime;
use test\com\zoho\InitializedTestCase;

class OrgOperationsTest extends InitializedTestCase
{
    public function testGetOrganization()
    {
        $this->clientMockHandler->append($this->makeJsonResponse(
            200,
            <<<JSON
{
  "org": [
    {
      "country": "India",
      "photo_id": "7358375680a0dcf8c26dd5b86af1a28e6cce8d4ec7cba59bd9071012460de963ed4a34822bb8d7d2d49c63c6337195238b5730c7acd93b7eca3e4e90f1702fed",
      "city": "Chennai",
      "description": "This is a sample description.",
      "mc_status": true,
      "gapps_enabled": false,
      "domain_name": "org694902309",
      "translation_enabled": true,
      "street": "GST Road",
      "alias": "sample alias",
      "currency": "Indian Rupee",
      "id": "4150868000000225097",
      "state": "Tamil Nadu",
      "fax": "0987654321",
      "employee_count": "100",
      "zip": "603202",
      "website": "https://www.zylker.com/",
      "currency_symbol": "₹",
      "mobile": "0909090909",
      "currency_locale": "en_IN",
      "primary_zuid": "694579958",
      "zia_portal_id": "710883907",
      "time_zone": "Asia/Calcutta",
      "zgid": "694902309",
      "country_code": "IN",
      "license_details": {
        "paid_expiry": "2020-09-20T05:30:00+05:30",
        "users_license_purchased": 5,
        "trial_type": null,
        "trial_expiry": null,
        "paid": true,
        "paid_type": "enterprise"
      },
      "phone": "04467447070",
      "company_name": "Zylker",
      "privacy_settings": true,
      "primary_email": "patricia.b@zohocorp.com",
      "hipaa_compliance_enabled": true,
      "iso_code": "INR"
    }
  ]
}
JSON
        ));

        $responseObj = (new OrgOperations)->getOrganization()->getObject();

        $this->assertInstanceOf(ResponseWrapper::class, $responseObj);
        /** @var ResponseWrapper $responseObj */
        $this->assertCount(1, $responseObj->getOrg());
        $this->assertInstanceOf(Org::class, $org = $responseObj->getOrg()[0]);
        /** @var Org $org */
        $this->assertEquals('India', $org->getCountry());
        $this->assertEquals('7358375680a0dcf8c26dd5b86af1a28e6cce8d4ec7cba59bd9071012460de963ed4a34822bb8d7d2d49c63c6337195238b5730c7acd93b7eca3e4e90f1702fed', $org->getPhotoId());
        $this->assertEquals('Chennai', $org->getCity());
        $this->assertEquals('This is a sample description.', $org->getDescription());
        $this->assertTrue($org->getMcStatus());
        $this->assertFalse($org->getGappsEnabled());
        $this->assertEquals('org694902309', $org->getDomainName());
        $this->assertTrue($org->getTranslationEnabled());
        $this->assertEquals('GST Road', $org->getStreet());
        $this->assertEquals('sample alias', $org->getAlias());
        $this->assertEquals('Indian Rupee', $org->getCurrency());
        $this->assertEquals('4150868000000225097', $org->getId());
        $this->assertEquals('Tamil Nadu', $org->getState());
        $this->assertEquals('0987654321', $org->getFax());
        $this->assertEquals('100', $org->getEmployeeCount());
        $this->assertEquals('603202', $org->getZip());
        $this->assertEquals('https://www.zylker.com/', $org->getWebsite());
        $this->assertEquals('₹', $org->getCurrencySymbol());
        $this->assertEquals('0909090909', $org->getMobile());
        $this->assertEquals('en_IN', $org->getCurrencyLocale());
        $this->assertEquals('694579958', $org->getPrimaryZuid());
        $this->assertEquals('710883907', $org->getZiaPortalId());
        $this->assertEquals('Asia/Calcutta', $org->getTimeZone());
        $this->assertEquals('694902309', $org->getZgid());
        $this->assertEquals('IN', $org->getCountryCode());
        $this->assertEquals(new DateTime('2020-09-20T05:30:00+05:30'), $org->getLicenseDetails()->getPaidExpiry());
        $this->assertEquals(5, $org->getLicenseDetails()->getUsersLicensePurchased());
        $this->assertNull($org->getLicenseDetails()->getTrialType());
        $this->assertNull($org->getLicenseDetails()->getTrialExpiry());
        $this->assertTrue($org->getLicenseDetails()->getPaid());
        $this->assertEquals('enterprise', $org->getLicenseDetails()->getPaidType());
        $this->assertEquals('04467447070', $org->getPhone());
        $this->assertEquals('Zylker', $org->getCompanyName());
        $this->assertTrue(true, $org->getPrivacySettings());
        $this->assertEquals('patricia.b@zohocorp.com', $org->getPrimaryEmail());
        $this->assertEquals('INR', $org->getIsoCode());
    }
}
