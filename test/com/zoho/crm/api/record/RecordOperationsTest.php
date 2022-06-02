<?php

namespace test\com\zoho\crm\api\record;

use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\APIException;
use com\zoho\crm\api\record\FileBodyWrapper;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\ResponseWrapper;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\tags\Tag;
use com\zoho\crm\api\util\StreamWrapper;
use DateTime;
use Mockery;
use test\com\zoho\InitializedTestCase;

class RecordOperationsTest extends InitializedTestCase
{
    public function testUploadPhotoSuccess()
    {
        $streamWrapper = Mockery::mock(StreamWrapper::class);
        $streamWrapper->shouldReceive(['getStream' => 'i-am-a-stream', 'getName' => 'abc.jpg']);
        $request = new FileBodyWrapper();
        $request->setFile($streamWrapper);
        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            <<<JSON
{
    "message": "photo uploaded successfully",
    "details": {},
    "status": "success",
    "code": "SUCCESS"
}
JSON,
        ));

        $response = (new RecordOperations)->uploadPhoto('3000000038009', 'Leads', $request);

        $this->assertInstanceOf(SuccessResponse::class, $responseObj = $response->getObject());
        /** @var SuccessResponse $responseObj */
        $this->assertEquals('photo uploaded successfully', $responseObj->getMessage()->getValue());
        $this->assertEmpty($responseObj->getDetails());
        $this->assertEquals('success', $responseObj->getStatus()->getValue());
        $this->assertEquals('SUCCESS', $responseObj->getCode()->getValue());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $requestUri = $request->getUri();
        $this->assertSame('/crm/v2/Leads/3000000038009/photo', $requestUri->getPath());
        $this->assertStringContainsString('Content-Disposition: form-data; name="file"; filename="abc.jpg"', $requestBody = $request->getBody()->getContents());
        $this->assertStringContainsString('Content-Type: image/jpeg', $requestBody);
        $this->assertStringContainsString('i-am-a-stream', $requestBody);
    }

    public function testSearchRecordsFail()
    {
        $this->appendFieldMetadataResponse();
        $this->clientMockHandler->append(self::makeJsonResponse(
            400,
            <<<JSON
{
  "code": "INVALID_QUERY",
  "details": {
    "reason": "i'm sure there's a good reason",
    "api_name": "THE_API_NAME"
  },
  "message": "invalid query formed",
  "status": "error"
}
JSON,
        ));

        $response = (new RecordOperations)->searchRecords('Leads');

        $this->assertInstanceOf(APIException::class, $responseObj = $response->getObject());
        /** @var APIException $responseObj */
        $this->assertEquals('INVALID_QUERY', $responseObj->getCode()->getValue());
        $this->assertEquals(['api_name' => 'THE_API_NAME', 'reason' => "i'm sure there's a good reason"], $responseObj->getDetails());
        $this->assertEquals('invalid query formed', $responseObj->getMessage()->getValue());
        $this->assertEquals('error', $responseObj->getStatus()->getValue());
    }

    public function getNoContentStatusData(): array
    {
        return [
            '204 status' => [204],
            '304 status' => [304],
        ];
    }

    /** @dataProvider getNoContentStatusData */
    public function testSearchRecordsWhenNoResult(int $status)
    {
        $this->appendFieldMetadataResponse();
        $this->clientMockHandler->append(self::makeJsonResponse($status, '{"data":[]}'));

        $result = (new RecordOperations)->searchRecords('Leads');

        $this->assertTrue($result->isExpected());
        $this->assertInstanceOf(ResponseWrapper::class, $resultObj = $result->getObject());
        /** @var ResponseWrapper $resultObj */
        $this->assertCount(0, $resultObj->getData());
    }

    public function testSearchRecordsSuccess()
    {
        $params = new ParameterMap();
        $params->add(SearchRecordsParam::criteria(), '((Last_Name:equals:Burns%5C%2CB)and(First_Name:starts_with:M))');
        $this->appendFieldMetadataResponse();
        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/search-records.html
            <<<JSON
{
    "data": [
        {
            "Account": null,
            "Owner": {
                "name": "Patricia Boyle",
                "id": "3652397000000186017"
            },
            "Company": "Company",
            "Email": "burns.mary@xyz.com",
            "\$currency_symbol": "Rs.",
            "Visitor_Score": null,
            "Last_Activity_Time": "2019-02-19T12:05:23+05:30",
            "Industry": "ERP",
            "\$converted": false,
            "\$process_flow": false,
            "Street": "4 B Blue Ridge Blvd",
            "Zip_Code": "48116",
            "id": "3652397000000190367",
            "\$approved": true,
            "\$approval": {
                "delegate": false,
                "approve": false,
                "reject": false,
                "resubmit": false
            },
            "First_Visited_URL": null,
            "Days_Visited": null,
            "Created_Time": "2018-11-14T15:31:28+05:30",
            "\$editable": true,
            "City": "Brighton",
            "No_of_Employees": 0,
            "Campaigns_Lookup": null,
            "State": "MI",
            "Country": "Livingston",
            "Last_Visited_Time": null,
            "Created_By": {
                "name": "Patricia Boyle",
                "id": "3652397000000186017"
            },
            "Annual_Revenue": 200000,
            "Secondary_Email": null,
            "Description": null,
            "Number_Of_Chats": null,
            "Rating": null,
            "Website": null,
            "Twitter": null,
            "Average_Time_Spent_Minutes": null,
            "Associated_Contacts": null,
            "Salutation": "Ms.",
            "First_Name": "Mary",
            "Lead_Status": "Contacted",
            "Full_Name": "Ms. Mary Burns",
            "Record_Image": null,
            "Modified_By": {
                "name": "Patricia Boyle",
                "id": "3652397000000186017"
            },
            "Skype_ID": "Mary-burns",
            "Phone": "555-555-5555",
            "Email_Opt_Out": true,
            "Designation": "Team Lead",
            "Modified_Time": "2019-02-19T12:05:23+05:30",
            "\$converted_detail": {},
            "Mobile": "555-555-5555",
            "Prediction_Score": null,
            "First_Visited_Time": null,
            "Last_Name": "Burns,B",
            "Referrer": null,
            "Lead_Source": "Twitter",
            "Tag": [
                {
                    "name": "Pharma",
                    "id": "3652397000000371014"
                },
                {
                    "name": "Agricultural",
                    "id": "3652397000000371015"
                }
            ],
            "Fax": null
        }
    ],
    "info": {
        "per_page": 200,
        "count": 1,
        "page": 1,
        "more_records": false
    }
}
JSON
        ));

        $result = (new RecordOperations)->searchRecords('Leads', $params);

        $this->assertTrue($result->isExpected());
        $this->assertInstanceOf(ResponseWrapper::class, $resultObj = $result->getObject());
        /** @var ResponseWrapper $resultObj */
        $this->assertCount(1, $resultObj->getData());
        $this->assertInstanceOf(Record::class, $record = $resultObj->getData()[0] ?? null);
        /** @var Record $record */
        $this->assertEquals('3652397000000190367', $record->getId());
        $this->assertEquals(new DateTime('2018-11-14T15:31:28+05:30'), $record->getCreatedTime());
        $this->assertEquals('Patricia Boyle', $record->getCreatedBy()->getName());
        $this->assertEquals('3652397000000186017', $record->getCreatedBy()->getId());
        $this->assertEquals(new DateTime('2019-02-19T12:05:23+05:30'), $record->getModifiedTime());
        $this->assertEquals('Patricia Boyle', $record->getModifiedBy()->getName());
        $this->assertEquals('3652397000000186017', $record->getModifiedBy()->getId());
        $this->assertCount(2, $record->getTag());

        $this->assertInstanceOf(Tag::class, $tag0 = $record->getTag()[0] ?? null);
        /** @var Tag $tag0 */
        $this->assertEquals('Pharma', $tag0->getName());
        $this->assertEquals('3652397000000371014', $tag0->getId());

        $this->assertInstanceOf(Tag::class, $tag1 = $record->getTag()[1] ?? null);
        /** @var Tag $tag1 */
        $this->assertEquals('Agricultural', $tag1->getName());
        $this->assertEquals('3652397000000371015', $tag1->getId());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $requestUri = $request->getUri();
        $this->assertSame('/crm/v2/Leads/search', $requestUri->getPath());
        parse_str($requestUri->getQuery(), $requestParams);
        $this->assertEquals(['criteria' => '((Last_Name:equals:Burns%5C%2CB)and(First_Name:starts_with:M))'], $requestParams);
    }

    public function testGetRecordsSuccess()
    {
        $this->appendFieldMetadataResponse();
        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/get-records.html
            <<<JSON
{
    "data": [
        {
            "Owner": {
                "name": "Patricia Boyle",
                "id": "1306462000000374001",
                "email": "p.boyle@abc.com"
            },
            "Company": "SolutionsTech",
            "Email": null,
            "\$currency_symbol": "\$",
            "\$field_states": null,
            "Last_Activity_Time": "2022-02-03T10:55:15+05:30",
            "Industry": null,
            "\$state": "save",
            "Unsubscribed_Mode": null,
            "\$converted": false,
            "\$process_flow": false,
            "Test": null,
            "Street": null,
            "Data_Processing_Basis_Details": null,
            "Zip_Code": null,
            "id": "1306462000000888026",
            "Data_Source": "API",
            "\$approved": true,
            "\$approval": {
                "delegate": false,
                "approve": false,
                "reject": false,
                "resubmit": false
            },
            "\$data_source_details": {},
            "Created_Time": "2022-02-02T17:09:49+05:30",
            "\$editable": true,
            "City": null,
            "No_of_Employees": null,
            "Related": null,
            "State": null,
            "Country": null,
            "Created_By": {
                "name": "Patricia Boyle",
                "id": "1306462000000374001",
                "email": "p.boyle@abc.com"
            },
            "Annual_Revenue": null,
            "Secondary_Email": null,
            "Description": null,
            "Rating": null,
            "\$review_process": {
                "approve": false,
                "reject": false,
                "resubmit": false
            },
            "Website": null,
            "Twitter": null,
            "Information": null,
            "\$canvas_id": null,
            "Salutation": null,
            "Marital_Status_1": null,
            "Birthday_1": null,
            "First_Name": null,
            "Full_Name": "Sam",
            "Lead_Status": null,
            "Record_Image": null,
            "Modified_By": {
                "name": "Patricia Boyle",
                "id": "1306462000000374001",
                "email": "p.boyle@abc.com"
            },
            "\$review": null,
            "Skype_ID": null,
            "Phone": null,
            "Lead_Class": "Class S",
            "Email_Opt_Out": false,
            "Designation": null,
            "Modified_Time": "2022-02-03T10:55:15+05:30",
            "\$converted_detail": {},
            "Unsubscribed_Time": null,
            "Referred_By": null,
            "Mobile": null,
            "\$orchestration": false,
            "\$stop_processing": false,
            "Last_Name": "Sam",
            "\$in_merge": false,
            "Lead_Source": null,
            "Tag": [],
            "Fax": null,
            "\$approval_state": "approved"
        }
    ]
}
JSON
        ));

        $result = (new RecordOperations)->getRecords('Leads');

        $this->assertTrue($result->isExpected());
        $this->assertInstanceOf(ResponseWrapper::class, $resultObj = $result->getObject());
        /** @var ResponseWrapper $resultObj */
        $this->assertCount(1, $resultObj->getData());
        $this->assertInstanceOf(Record::class, $record = $resultObj->getData()[0] ?? null);
        /** @var Record $record */
        $this->assertEquals('1306462000000888026', $record->getId());
        $this->assertEquals(new DateTime('2022-02-02T17:09:49+05:30'), $record->getCreatedTime());
        $this->assertEquals('Patricia Boyle', $record->getCreatedBy()->getName());
        $this->assertEquals('1306462000000374001', $record->getCreatedBy()->getId());
        $this->assertEquals(new DateTime('2022-02-03T10:55:15+05:30'), $record->getModifiedTime());
        $this->assertEquals('Patricia Boyle', $record->getModifiedBy()->getName());
        $this->assertEquals('1306462000000374001', $record->getModifiedBy()->getId());
        $this->assertCount(0, $record->getTag());
    }

    public function testGetRecordSuccess()
    {
        $this->appendFieldMetadataResponse();
        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/get-records.html
            <<<JSON
{
    "data": [
        {
            "Owner": {
                "name": "Patricia Boyle",
                "id": "1306462000000374001",
                "email": "p.boyle@abc.com"
            },
            "Company": "SolutionsTech",
            "Email": null,
            "\$currency_symbol": "\$",
            "\$field_states": null,
            "Last_Activity_Time": "2022-02-03T10:55:15+05:30",
            "Industry": null,
            "\$state": "save",
            "Unsubscribed_Mode": null,
            "\$converted": false,
            "\$process_flow": false,
            "Test": null,
            "Street": null,
            "Data_Processing_Basis_Details": null,
            "Zip_Code": null,
            "id": "1306462000000888026",
            "Data_Source": "API",
            "\$approved": true,
            "\$approval": {
                "delegate": false,
                "approve": false,
                "reject": false,
                "resubmit": false
            },
            "\$data_source_details": {},
            "Created_Time": "2022-02-02T17:09:49+05:30",
            "\$editable": true,
            "City": null,
            "No_of_Employees": null,
            "Related": null,
            "State": null,
            "Country": null,
            "Created_By": {
                "name": "Patricia Boyle",
                "id": "1306462000000374001",
                "email": "p.boyle@abc.com"
            },
            "Annual_Revenue": null,
            "Secondary_Email": null,
            "Description": null,
            "Rating": null,
            "\$review_process": {
                "approve": false,
                "reject": false,
                "resubmit": false
            },
            "Website": null,
            "Twitter": null,
            "Information": null,
            "\$canvas_id": null,
            "Salutation": null,
            "Marital_Status_1": null,
            "Birthday_1": null,
            "First_Name": null,
            "Full_Name": "Sam",
            "Lead_Status": null,
            "Record_Image": null,
            "Modified_By": {
                "name": "Patricia Boyle",
                "id": "1306462000000374001",
                "email": "p.boyle@abc.com"
            },
            "\$review": null,
            "Skype_ID": null,
            "Phone": null,
            "Lead_Class": "Class S",
            "Email_Opt_Out": false,
            "Designation": null,
            "Modified_Time": "2022-02-03T10:55:15+05:30",
            "\$converted_detail": {},
            "Unsubscribed_Time": null,
            "Referred_By": null,
            "Mobile": null,
            "\$orchestration": false,
            "\$stop_processing": false,
            "Last_Name": "Sam",
            "\$in_merge": false,
            "Lead_Source": null,
            "Tag": [],
            "Fax": null,
            "\$approval_state": "approved"
        }
    ]
}
JSON
        ));

        $result = (new RecordOperations)->getRecord('1306462000000888026', 'Leads');

        $this->assertTrue($result->isExpected());
        $this->assertInstanceOf(ResponseWrapper::class, $resultObj = $result->getObject());
        /** @var ResponseWrapper $resultObj */
        $this->assertCount(1, $resultObj->getData());
        $this->assertInstanceOf(Record::class, $record = $resultObj->getData()[0] ?? null);
        /** @var Record $record */
        $this->assertEquals('1306462000000888026', $record->getId());
        $this->assertEquals(new DateTime('2022-02-02T17:09:49+05:30'), $record->getCreatedTime());
        $this->assertEquals('Patricia Boyle', $record->getCreatedBy()->getName());
        $this->assertEquals('1306462000000374001', $record->getCreatedBy()->getId());
        $this->assertEquals(new DateTime('2022-02-03T10:55:15+05:30'), $record->getModifiedTime());
        $this->assertEquals('Patricia Boyle', $record->getModifiedBy()->getName());
        $this->assertEquals('1306462000000374001', $record->getModifiedBy()->getId());
        $this->assertCount(0, $record->getTag());
    }

    private function appendFieldMetadataResponse(): void
    {
        // record operations fetch field metadata so stub that response
        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample from https://www.zoho.com/crm/developer/docs/api/v2/field-meta.html
            <<<JSON
{
  "fields": [
    {
      "system_mandatory": false,
      "private": null,
      "webhook": true,
      "json_type": "jsonobject",
      "crypt": null,
      "field_label": "Lead Owner",
      "tooltip": null,
      "created_source": "default",
      "field_read_only": true,
      "display_label": "Lead Owner",
      "read_only": false,
      "association_details": null,
      "quick_sequence_number": "1",
      "businesscard_supported": true,
      "multi_module_lookup": {},
      "currency": {},
      "id": "4150868000000002589",
      "custom_field": false,
      "lookup": {},
      "visible": true,
      "length": 120,
      "view_type": {
        "view": true,
        "edit": true,
        "quick_create": true,
        "create": true
      },
      "subform": null,
      "api_name": "Owner",
      "unique": {},
      "data_type": "ownerlookup",
      "formula": {},
      "decimal_place": null,
      "mass_update": false,
      "multiselectlookup": {},
      "pick_list_values": [],
      "auto_number": {}
    },
    {
      "system_mandatory": false,
      "private": null,
      "webhook": true,
      "json_type": "string",
      "crypt": null,
      "field_label": "Company",
      "tooltip": null,
      "created_source": "default",
      "field_read_only": false,
      "display_label": "Company",
      "read_only": false,
      "association_details": null,
      "quick_sequence_number": "2",
      "businesscard_supported": false,
      "multi_module_lookup": {},
      "currency": {},
      "id": "4150868000000002591",
      "custom_field": false,
      "lookup": {},
      "visible": true,
      "length": 100,
      "view_type": {
        "view": true,
        "edit": true,
        "quick_create": true,
        "create": true
      },
      "subform": null,
      "api_name": "Company",
      "unique": {},
      "data_type": "text",
      "formula": {},
      "decimal_place": null,
      "mass_update": true,
      "multiselectlookup": {},
      "pick_list_values": [],
      "auto_number": {}
    },
    {
      "system_mandatory": true,
      "private": null,
      "webhook": true,
      "json_type": "string",
      "crypt": null,
      "field_label": "Last Name",
      "tooltip": null,
      "created_source": "default",
      "field_read_only": false,
      "display_label": "Last Name",
      "read_only": false,
      "association_details": null,
      "quick_sequence_number": "4",
      "businesscard_supported": false,
      "multi_module_lookup": {},
      "currency": {},
      "id": "4150868000000002595",
      "custom_field": false,
      "lookup": {},
      "visible": true,
      "length": 80,
      "view_type": {
        "view": false,
        "edit": true,
        "quick_create": true,
        "create": true
      },
      "subform": null,
      "api_name": "Last_Name",
      "unique": {},
      "data_type": "text",
      "formula": {},
      "decimal_place": null,
      "mass_update": false,
      "multiselectlookup": {},
      "pick_list_values": [],
      "auto_number": {}
    }
  ]
}
JSON
        ));
    }
}
