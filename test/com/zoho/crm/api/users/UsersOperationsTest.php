<?php

namespace test\com\zoho\crm\api\users;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\profiles\Profile;
use com\zoho\crm\api\roles\Role;
use com\zoho\crm\api\users\ActionWrapper;
use com\zoho\crm\api\users\APIException;
use com\zoho\crm\api\users\BodyWrapper;
use com\zoho\crm\api\users\CustomizeInfo;
use com\zoho\crm\api\users\GetUsersHeader;
use com\zoho\crm\api\users\GetUsersParam;
use com\zoho\crm\api\users\RequestWrapper;
use com\zoho\crm\api\users\ResponseWrapper;
use com\zoho\crm\api\users\SuccessResponse;
use com\zoho\crm\api\users\TabTheme;
use com\zoho\crm\api\users\Territory;
use com\zoho\crm\api\users\Theme;
use com\zoho\crm\api\users\User;
use com\zoho\crm\api\users\UsersOperations;
use com\zoho\crm\api\util\Choice;
use DateTime;
use DateTimeZone;
use test\com\zoho\InitializedTestCase;

class UsersOperationsTest extends InitializedTestCase
{
    public function getErrorResponseCodes(): array
    {
        return [
            'invalid url pattern' => [404],
            'oauth scope mismatch' => [401],
            'no permission' => [403],
            'internal error' => [500],
            'invalid request method' => [400],
            'authorization failed' => [400],
            'pattern not matched' => [400],
        ];
    }

    public function testGetUsersFail()
    {
        $this->clientMockHandler->append(self::makeJsonResponse(
            400,
            <<<JSON
{
  "code": "INVALID_THINGY",
  "details": {
    "api_name": "THE_API_NAME"
  },
  "message": "some message",
  "status": "error"
}
JSON
        ));

        $response = (new UsersOperations())->getUsers();

        $this->assertInstanceOf(APIException::class, $responseObj = $response->getObject());
        /** @var APIException $responseObj */
        $this->assertEquals('INVALID_THINGY', $responseObj->getCode()->getValue());
        $this->assertEquals(['api_name' => 'THE_API_NAME'], $responseObj->getDetails());
        $this->assertEquals('some message', $responseObj->getMessage()->getValue());
        $this->assertEquals('error', $responseObj->getStatus()->getValue());
    }

    public function testGetUsersSuccess()
    {
        $paramInstance = new ParameterMap();
        $paramInstance->add(GetUsersParam::type(), 'ActiveUsers');
        $paramInstance->add(GetUsersParam::page(), 1);
        $paramInstance->add(GetUsersParam::perPage(), 2);

        $headerInstance = new HeaderMap();
        $ifModifiedSince = date_create('2020-07-15T17:58:47+05:30')->setTimezone(new DateTimeZone(date_default_timezone_get()));
        $headerInstance->add(GetUsersHeader::IfModifiedSince(), $ifModifiedSince);

        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/get-users.html
            <<<JSON
{
  "users": [
    {
      "country": "US",
      "customize_info": {
        "notes_desc": null,
        "show_right_panel": null,
        "bc_view": null,
        "show_home": false,
        "show_detail_view": true,
        "unpin_recent_item": null
      },
      "role": {
        "name": "CEO",
        "id": "4150868000000026005"
      },
      "signature": "<div><a id=\"link\" href=\"https://crm.zoho.com/bookings/ProjectDemo?rid=4b1b5d511ac5628eb3045495192827cc7f2f04de31c657e50f194521b21a27f5gid25837b76288d7b127a3faccd84a936702ba8ac270b0949d1521e82e1a251c1e5\" target=\"_blank\">Patricia Boyle</a></div>",
      "city": null,
      "name_format": "Salutation,First Name,Last Name",
      "language": "en_US",
      "locale": "en_US",
      "microsoft": false,
      "personal_account": false,
      "default_tab_group": "0",
      "Isonline": true,
      "Modified_By": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "street": null,
      "Currency": "DZD",
      "alias": null,
      "theme": {
        "normal_tab": {
          "font_color": "#FFFFFF",
          "background": "#222222"
        },
        "selected_tab": {
          "font_color": "#FFFFFF",
          "background": "#222222"
        },
        "new_background": null,
        "background": "#F3F0EB",
        "screen": "fixed",
        "type": "default"
      },
      "id": "4150868000000225013",
      "state": "Tamil Nadu",
      "fax": null,
      "country_locale": "US",
      "first_name": "Patricia",
      "email": "patricia.b@zylker.com",
      "Reporting_To": null,
      "decimal_separator": "en_IN",
      "zip": null,
      "created_time": "2019-08-20T11:21:16+05:30",
      "website": "www.zylker.com",
      "Modified_Time": "2020-07-14T18:30:01+05:30",
      "time_format": "hh:mm a",
      "offset": 19800000,
      "profile": {
        "name": "Administrator",
        "id": "4150868000000026011"
      },
      "mobile": null,
      "last_name": "Boyle",
      "time_zone": "Asia/Calcutta",
      "created_by": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "zuid": "694579958",
      "confirm": true,
      "full_name": "Patricia Boyle",
      "territories": [
        {
          "manager": true,
          "name": "Zylker",
          "id": "4150868000000236307"
        }
      ],
      "phone": null,
      "dob": null,
      "date_format": "MM/dd/yyyy",
      "status": "active"
    },
    {
      "country": null,
      "role": {
        "name": "Sales department Head",
        "id": "4150868000000231921"
      },
      "city": null,
      "language": "en_US",
      "locale": "en_US",
      "microsoft": false,
      "Isonline": false,
      "Modified_By": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "street": null,
      "Currency": "DZD",
      "alias": null,
      "id": "4150868000000231929",
      "state": null,
      "fax": null,
      "country_locale": "US",
      "first_name": "Jack",
      "email": "jack.s@zylker.com",
      "Reporting_To": null,
      "zip": null,
      "created_time": "2019-08-20T12:39:23+05:30",
      "website": null,
      "Modified_Time": "2020-07-14T18:30:01+05:30",
      "time_format": "hh:mm a",
      "offset": 19800000,
      "profile": {
        "name": "Administrator",
        "id": "4150868000000026011"
      },
      "mobile": null,
      "last_name": "Smith",
      "time_zone": "Asia/Calcutta",
      "created_by": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "zuid": null,
      "confirm": false,
      "full_name": "Jack Smith",
      "territories": [],
      "phone": null,
      "dob": null,
      "date_format": "MM/dd/yyyy",
      "status": "disabled"
    },
    {
      "country": null,
      "role": {
        "name": "Sales rep",
        "id": "4150868000000231917"
      },
      "city": null,
      "language": "en_US",
      "locale": "en_US",
      "microsoft": false,
      "Isonline": false,
      "Modified_By": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "street": null,
      "Currency": "DZD",
      "alias": null,
      "id": "4150868000000252644",
      "state": null,
      "fax": null,
      "country_locale": "US",
      "first_name": "Jane",
      "email": "Jane.J@zylker.com",
      "Reporting_To": null,
      "zip": null,
      "created_time": "2019-08-22T15:02:16+05:30",
      "website": null,
      "Modified_Time": "2020-07-14T18:30:01+05:30",
      "time_format": "hh:mm a",
      "offset": 19800000,
      "profile": {
        "name": "Administrator",
        "id": "4150868000000026011"
      },
      "mobile": null,
      "last_name": "J",
      "time_zone": "Asia/Kolkata",
      "created_by": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "zuid": null,
      "confirm": false,
      "full_name": "Jane J",
      "territories": [
        {
          "manager": false,
          "name": "Sample Territory",
          "id": "4150868000000264087"
        }
      ],
      "phone": null,
      "dob": null,
      "date_format": "MM/dd/yyyy",
      "status": "disabled"
    }
  ],
  "info": {
    "per_page": 200,
    "count": 3,
    "page": 1,
    "more_records": false
  }
}
JSON
        ));

        $response = (new UsersOperations)->getUsers($paramInstance, $headerInstance);

        $this->assertInstanceOf(ResponseWrapper::class, $responseObj = $response->getObject());
        /** @var RequestWrapper $responseObj */
        $users = $responseObj->getUsers();

        $this->assertInstanceOf(User::class, $user0 = $users[0] ?? null);
        $this->assertEquals('US', $user0->getCountry());
        $this->assertInstanceOf(CustomizeInfo::class, $user0Customize = $user0->getCustomizeInfo());
        $this->assertNull($user0Customize->getNotesDesc());
        $this->assertNull($user0Customize->getShowRightPanel());
        $this->assertNull($user0Customize->getBcView());
        $this->assertFalse($user0Customize->getShowHome());
        $this->assertTrue($user0Customize->getShowDetailView());
        $this->assertNull($user0Customize->getUnpinRecentItem());
        $this->assertInstanceOf(Role::class, $user0Role = $user0->getRole());
        $this->assertEquals('CEO', $user0Role->getName());
        $this->assertEquals('4150868000000026005', $user0Role->getId());
        $this->assertEquals('<div><a id="link" href="https://crm.zoho.com/bookings/ProjectDemo?rid=4b1b5d511ac5628eb3045495192827cc7f2f04de31c657e50f194521b21a27f5gid25837b76288d7b127a3faccd84a936702ba8ac270b0949d1521e82e1a251c1e5" target="_blank">Patricia Boyle</a></div>', $user0->getSignature());
        $this->assertNull($user0->getCity());
        $this->assertEquals('Salutation,First Name,Last Name', $user0->getNameFormat());
        $this->assertEquals('en_US', $user0->getLanguage());
        $this->assertEquals('en_US', $user0->getLocale());
        $this->assertFalse($user0->getMicrosoft());
        $this->assertFalse($user0->getPersonalAccount());
        $this->assertEquals('0', $user0->getDefaultTabGroup());
        $this->assertTrue($user0->getIsonline());
        $this->assertInstanceOf(User::class, $user0ModBy = $user0->getModifiedBy());
        $this->assertEquals('Patricia Boyle', $user0ModBy->getName());
        $this->assertEquals('4150868000000225013', $user0ModBy->getId());
        $this->assertNull($user0->getStreet());
        $this->assertEquals('DZD', $user0->getCurrency());
        $this->assertNull($user0->getAlias());
        $this->assertInstanceOf(Theme::class, $user0Theme = $user0->getTheme());
        $this->assertInstanceOf(TabTheme::class, $user0NormTab = $user0Theme->getNormalTab());
        $this->assertEquals('#FFFFFF', $user0NormTab->getFontColor());
        $this->assertEquals('#222222', $user0NormTab->getBackground());
        $this->assertInstanceOf(TabTheme::class, $user0SelTab = $user0Theme->getSelectedTab());
        $this->assertEquals('#FFFFFF', $user0SelTab->getFontColor());
        $this->assertEquals('#222222', $user0SelTab->getBackground());
        $this->assertNull($user0Theme->getNewBackground());
        $this->assertEquals('#F3F0EB', $user0Theme->getBackground());
        $this->assertEquals('fixed', $user0Theme->getScreen());
        $this->assertEquals('default', $user0Theme->getType());
        $this->assertEquals('4150868000000225013', $user0->getId());
        $this->assertEquals('Tamil Nadu', $user0->getState());
        $this->assertNull($user0->getFax());
        $this->assertEquals('US', $user0->getCountryLocale());
        $this->assertEquals('Patricia', $user0->getFirstName());
        $this->assertEquals('patricia.b@zylker.com', $user0->getEmail());
        $this->assertNull($user0->getReportingTo());
        $this->assertEquals('en_IN', $user0->getDecimalSeparator());
        $this->assertNull($user0->getZip());
        $this->assertEquals(new DateTime('2019-08-20T11:21:16+05:30'), $user0->getCreatedTime());
        $this->assertEquals('www.zylker.com', $user0->getWebsite());
        $this->assertEquals(new DateTime('2020-07-14T18:30:01+05:30'), $user0->getModifiedTime());
        $this->assertEquals('hh:mm a', $user0->getTimeFormat());
        $this->assertEquals(19800000, $user0->getOffset());
        $this->assertInstanceOf(Profile::class, $user0Prof = $user0->getProfile());
        $this->assertEquals('Administrator', $user0Prof->getName());
        $this->assertEquals('4150868000000026011', $user0Prof->getId());
        $this->assertNull($user0->getMobile());
        $this->assertEquals('Boyle', $user0->getLastName());
        $this->assertEquals('Asia/Calcutta', $user0->getTimeZone());
        $this->assertInstanceOf(User::class, $user0CreatedBy = $user0->getCreatedBy());
        $this->assertEquals('Patricia Boyle', $user0CreatedBy->getName());
        $this->assertEquals('4150868000000225013', $user0CreatedBy->getId());
        $this->assertEquals('694579958', $user0->getZuid());
        $this->assertTrue($user0->getConfirm());
        $this->assertEquals('Patricia Boyle', $user0->getFullName());
        $this->assertCount(1, $user0->getTerritories());
        $this->assertInstanceOf(Territory::class, $user0Terr = $user0->getTerritories()[0] ?? null);
        $this->assertTrue($user0Terr->getManager());
        $this->assertEquals('Zylker', $user0Terr->getName());
        $this->assertEquals('4150868000000236307', $user0Terr->getId());
        $this->assertNull($user0->getPhone());
        $this->assertNull($user0->getDob());
        $this->assertEquals('MM/dd/yyyy', $user0->getDateFormat());
        $this->assertEquals('active', $user0->getStatus());

        $this->assertInstanceOf(User::class, $user = $users[1] ?? null);
        $this->assertNull($user->getCountry());
        $this->assertInstanceOf(Role::class, $userRole = $user->getRole());
        $this->assertEquals('Sales department Head', $userRole->getName());
        $this->assertEquals('4150868000000231921', $userRole->getId());
        $this->assertNull($user->getCity());
        $this->assertEquals('en_US', $user->getLanguage());
        $this->assertEquals('en_US', $user->getLocale());
        $this->assertFalse($user->getMicrosoft());
        $this->assertFalse($user->getIsonline());
        $this->assertInstanceOf(User::class, $userModBy = $user->getModifiedBy());
        $this->assertEquals('Patricia Boyle', $userModBy->getName());
        $this->assertEquals('4150868000000225013', $userModBy->getId());
        $this->assertNull($user->getStreet());
        $this->assertEquals('DZD', $user->getCurrency());
        $this->assertNull($user->getAlias());
        $this->assertEquals('4150868000000231929', $user->getId());
        $this->assertNull($user->getState());
        $this->assertNull($user->getFax());
        $this->assertEquals('US', $user->getCountryLocale());
        $this->assertEquals('Jack', $user->getFirstName());
        $this->assertEquals('jack.s@zylker.com', $user->getEmail());
        $this->assertNull($user->getReportingTo());
        $this->assertNull($user->getZip());
        $this->assertEquals(new DateTime('2019-08-20T12:39:23+05:30'), $user->getCreatedTime());
        $this->assertNull($user->getWebsite());
        $this->assertEquals(new DateTime('2020-07-14T18:30:01+05:30'), $user->getModifiedTime());
        $this->assertEquals('hh:mm a', $user->getTimeFormat());
        $this->assertEquals(19800000, $user->getOffset());
        $this->assertInstanceOf(Profile::class, $userProf = $user->getProfile());
        $this->assertEquals('Administrator', $userProf->getName());
        $this->assertEquals('4150868000000026011', $userProf->getId());
        $this->assertNull($user->getMobile());
        $this->assertEquals('Smith', $user->getLastName());
        $this->assertEquals('Asia/Calcutta', $user->getTimeZone());
        $this->assertInstanceOf(User::class, $userCreatedBy = $user->getCreatedBy());
        $this->assertEquals('Patricia Boyle', $userCreatedBy->getName());
        $this->assertEquals('4150868000000225013', $userCreatedBy->getId());
        $this->assertNull($user->getZuid());
        $this->assertFalse($user->getConfirm());
        $this->assertEquals('Jack Smith', $user->getFullName());
        $this->assertCount(0, $user->getTerritories());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getDob());
        $this->assertEquals('MM/dd/yyyy', $user->getDateFormat());
        $this->assertEquals('disabled', $user->getStatus());
        $this->assertNull($user->getCustomizeInfo());
        $this->assertNull($user->getSignature());
        $this->assertNull($user->getNameFormat());
        $this->assertNull($user->getPersonalAccount());
        $this->assertNull($user->getDefaultTabGroup());
        $this->assertNull($user->getTheme());
        $this->assertNull($user->getDecimalSeparator());

        $this->assertInstanceOf(User::class, $user2 = $users[2] ?? null);
        $this->assertNull($user2->getCountry());
        $this->assertInstanceOf(Role::class, $user2Role = $user2->getRole());
        $this->assertEquals('Sales rep', $user2Role->getName());
        $this->assertEquals('4150868000000231917', $user2Role->getId());
        $this->assertNull($user2->getCity());
        $this->assertEquals('en_US', $user2->getLanguage());
        $this->assertEquals('en_US', $user2->getLocale());
        $this->assertFalse($user2->getMicrosoft());
        $this->assertFalse($user2->getIsonline());
        $this->assertInstanceOf(User::class, $user2ModBy = $user2->getModifiedBy());
        $this->assertEquals('Patricia Boyle', $user2ModBy->getName());
        $this->assertEquals('4150868000000225013', $user2ModBy->getId());
        $this->assertNull($user2->getStreet());
        $this->assertEquals('DZD', $user2->getCurrency());
        $this->assertNull($user2->getAlias());
        $this->assertEquals('4150868000000252644', $user2->getId());
        $this->assertNull($user2->getState());
        $this->assertNull($user2->getFax());
        $this->assertEquals('US', $user2->getCountryLocale());
        $this->assertEquals('Jane', $user2->getFirstName());
        $this->assertEquals('Jane.J@zylker.com', $user2->getEmail());
        $this->assertNull($user2->getReportingTo());
        $this->assertNull($user2->getZip());
        $this->assertEquals(new DateTime('2019-08-22T15:02:16+05:30'), $user2->getCreatedTime());
        $this->assertNull($user2->getWebsite());
        $this->assertEquals(new DateTime('2020-07-14T18:30:01+05:30'), $user2->getModifiedTime());
        $this->assertEquals('hh:mm a', $user2->getTimeFormat());
        $this->assertEquals(19800000, $user2->getOffset());
        $this->assertInstanceOf(Profile::class, $user2Prof = $user2->getProfile());
        $this->assertEquals('Administrator', $user2Prof->getName());
        $this->assertEquals('4150868000000026011', $user2Prof->getId());
        $this->assertNull($user2->getMobile());
        $this->assertEquals('J', $user2->getLastName());
        $this->assertEquals('Asia/Kolkata', $user2->getTimeZone());
        $this->assertInstanceOf(User::class, $user2CreatedBy = $user2->getCreatedBy());
        $this->assertEquals('Patricia Boyle', $user2CreatedBy->getName());
        $this->assertEquals('4150868000000225013', $user2CreatedBy->getId());
        $this->assertNull($user2->getZuid());
        $this->assertFalse($user2->getConfirm());
        $this->assertEquals('Jane J', $user2->getFullName());
        $this->assertCount(1, $user2->getTerritories());
        $this->assertInstanceOf(Territory::class, $user2Terr = $user2->getTerritories()[0] ?? null);
        $this->assertFalse($user2Terr->getManager());
        $this->assertEquals('Sample Territory', $user2Terr->getName());
        $this->assertEquals('4150868000000264087', $user2Terr->getId());
        $this->assertNull($user2->getPhone());
        $this->assertNull($user2->getDob());
        $this->assertEquals('MM/dd/yyyy', $user2->getDateFormat());
        $this->assertEquals('disabled', $user2->getStatus());
        $this->assertNull($user2->getCustomizeInfo());
        $this->assertNull($user2->getSignature());
        $this->assertNull($user2->getNameFormat());
        $this->assertNull($user2->getPersonalAccount());
        $this->assertNull($user2->getDefaultTabGroup());
        $this->assertNull($user2->getTheme());
        $this->assertNull($user2->getDecimalSeparator());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertEquals(['2020-07-15T12:28:47+00:00'], $request->getHeader('If-Modified-Since'));
        $requestUri = $request->getUri();
        $this->assertSame('/crm/v2/users', $requestUri->getPath());
        parse_str($requestUri->getQuery(), $requestParams);
        $this->assertEquals([
            'type' => 'ActiveUsers',
            'page' => 1,
            'per_page' => 2,
        ], $requestParams);
    }

    public function testDeleteUserSuccess()
    {
        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/delete-user.html
            <<<JSON
{
  "users": [
    {
      "code": "SUCCESS",
      "details": {},
      "message": "User deleted",
      "status": "success"
    }
  ]
}
JSON
        ));

        $response = (new UsersOperations)->deleteUser('12345');

        $this->assertInstanceOf(ActionWrapper::class, $responseObj = $response->getObject());
        /** @var ActionWrapper $responseObj */
        $responseUsers = $responseObj->getUsers();
        $this->assertCount(1, $responseUsers);
        $this->assertInstanceOf(SuccessResponse::class, $responseUser = $responseUsers[0] ?? null);
        /** @var SuccessResponse $responseUser */
        $this->assertSame([], $responseUser->getDetails());
        $this->assertInstanceOf(Choice::class, $responseMsg = $responseUser->getMessage());
        $this->assertSame('User deleted', $responseMsg->getValue());
        $this->assertInstanceOf(Choice::class, $responseStatus = $responseUser->getStatus());
        $this->assertSame('success', $responseStatus->getValue());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/crm/v2/users/12345', $request->getUri()->getPath());
    }

    public function testUpdateUsersSuccess()
    {
        $request = new BodyWrapper;
        $request->setUsers([
            $this->makeUser([
                'id' => '554023000000691003',
                'first' => 'TestUser',
                'last' => '12',
                'email' => 'testuser@zoho.com',
                'profile_id' => '34770610026008',
                'role_id' => '34770610026014',
            ]),
            $this->makeUser([
                'id' => '111111111111111111',
                'first' => 'Another',
                'last' => 'User',
                'email' => 'anotheruser@zoho.com',
                'profile_id' => '22222222222222',
                'role_id' => '33333333333333',
            ]),
        ]);

        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/update-user.html
            <<<JSON
{
  "users": [
    {
      "code": "SUCCESS",
      "details": {
        "id": "554023000000691003"
      },
      "message": "User updated",
      "status": "success"
    },
    {
      "code": "SUCCESS",
      "details": {
        "id": "111111111111111111"
      },
      "message": "User updated",
      "status": "success"
    }
  ]
}
JSON
        ));

        $response = (new UsersOperations)->updateUsers($request);

        $this->assertInstanceOf(ActionWrapper::class, $responseObj = $response->getObject());
        /** @var ActionWrapper $responseObj */
        $responseUsers = $responseObj->getUsers();
        $this->assertCount(2, $responseUsers);
        $this->assertInstanceOf(SuccessResponse::class, $responseUser0 = $responseUsers[0] ?? null);
        /** @var SuccessResponse $responseUser0 */
        $this->assertSame(['id' => '554023000000691003'], $responseUser0->getDetails());
        $this->assertInstanceOf(Choice::class, $responseMsg = $responseUser0->getMessage());
        $this->assertSame('User updated', $responseMsg->getValue());
        $this->assertInstanceOf(Choice::class, $responseStatus = $responseUser0->getStatus());
        $this->assertSame('success', $responseStatus->getValue());

        $apiRequest = $this->clientMockHandler->getLastRequest();
        $this->assertSame('PUT', $apiRequest->getMethod());
        $this->assertSame('/crm/v2/users', $apiRequest->getUri()->getPath());
        $this->assertEquals([
            'users' => [
                [
                    'id' => '554023000000691003',
                    'role' => ['id' => '34770610026014'],
                    'first_name' => 'TestUser',
                    'email' => 'testuser@zoho.com',
                    'profile' => ['id' => '34770610026008'],
                    'last_name' => '12',
                ],
                [
                    'id' => '111111111111111111',
                    'role' => ['id' => '33333333333333'],
                    'first_name' => 'Another',
                    'email' => 'anotheruser@zoho.com',
                    'profile' => ['id' => '22222222222222'],
                    'last_name' => 'User',
                ],
            ],
        ], json_decode($apiRequest->getBody(), true));
    }

    public function getCreateUserFailValidationData(): array
    {
        $userEmpty = $this->makeUser([]);
        $userMissingLast = $this->makeUser(['email' => 'testuser@zoho.com', 'profile_id' => '34770610026008', 'role_id' => '34770610026014']);
        $userMissingEmail = $this->makeUser(['last' => '12', 'profile_id' => '34770610026008', 'role_id' => '34770610026014']);
        $userMissingProfile = $this->makeUser(['last' => '12', 'email' => 'testuser@zoho.com', 'role_id' => '34770610026014']);
        $userMissingRole = $this->makeUser(['last' => '12', 'email' => 'testuser@zoho.com', 'profile_id' => '34770610026008']);

        return [
            'empty user' => [$userEmpty, 'MANDATORY VALUE ERROR', ['field' => 'users', 'type' => User::class, 'keys' => ['last_name', 'role', 'email', 'profile'], 'instance-number' => 0]],
            'user missing last name' => [$userMissingLast, 'MANDATORY VALUE ERROR', ['field' => 'users', 'type' => User::class, 'keys' => ['last_name'], 'instance-number' => 0]],
            'user missing email' => [$userMissingEmail, 'MANDATORY VALUE ERROR', ['field' => 'users', 'type' => User::class, 'keys' => ['email'], 'instance-number' => 0]],
            'user missing profile' => [$userMissingProfile, 'MANDATORY VALUE ERROR', ['field' => 'users', 'type' => User::class, 'keys' => ['profile'], 'instance-number' => 0]],
            'user missing role' => [$userMissingRole, 'MANDATORY VALUE ERROR', ['field' => 'users', 'type' => User::class, 'keys' => ['role'], 'instance-number' => 0]],
        ];
    }

    /** @dataProvider getCreateUserFailValidationData */
    public function testCreateUserFailValidation(User $user, string $expectedErrorCode, $expectedDetails)
    {
        $usersOperations = new UsersOperations;
        $request = new RequestWrapper;
        $request->setUsers([$user]);

        try {
            $usersOperations->createUser($request);

            $this->fail('Exception not thrown'); // shouldn't make it here
        } catch (SDKException $e) {
            $this->assertSame($expectedErrorCode, $e->getErrorCode());
            $this->assertEquals($expectedDetails, $e->getDetails());
        }
    }

    /** @dataProvider getErrorResponseCodes */
    public function testCreateUserFailResponse(int $responseCode)
    {
        $request = new RequestWrapper;
        $request->setUsers([$this->makeUser(['last' => '12', 'email' => 'testuser@zoho.com', 'profile_id' => '34770610026008', 'role_id' => '34770610026014'])]);
        $this->clientMockHandler->append(self::makeJsonResponse(
            $responseCode,
            <<<JSON
{
  "code": "INVALID_THINGY",
  "details": {
    "api_name": "THE_API_NAME"
  },
  "message": "some message",
  "status": "error"
}
JSON
        ));

        $response = (new UsersOperations)->createUser($request);

        $this->assertInstanceOf(APIException::class, $responseObj = $response->getObject());
        /** @var APIException $responseObj */
        $this->assertEquals('INVALID_THINGY', $responseObj->getCode()->getValue());
        $this->assertEquals(['api_name' => 'THE_API_NAME'], $responseObj->getDetails());
        $this->assertEquals('some message', $responseObj->getMessage()->getValue());
        $this->assertEquals('error', $responseObj->getStatus()->getValue());
    }

    public function testCreateUserSuccess()
    {
        $request = new RequestWrapper;
        $request->setUsers([$this->makeUser([
            'first' => 'TestUser',
            'last' => '12',
            'email' => 'testuser@zoho.com',
            'profile_id' => '34770610026008',
            'role_id' => '34770610026014',
        ])]);

        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/add-user.html
            <<<JSON
{
  "users": [
    {
      "code": "SUCCESS",
      "details": {
        "id": "554023000000691003"
      },
      "message": "User added",
      "status": "success"
    }
  ]
}
JSON
        ));

        $response = (new UsersOperations)->createUser($request);

        $this->assertInstanceOf(ActionWrapper::class, $responseObj = $response->getObject());
        /** @var ActionWrapper $responseObj */
        $responseUsers = $responseObj->getUsers();
        $this->assertCount(1, $responseUsers);
        $this->assertInstanceOf(SuccessResponse::class, $responseUser = $responseUsers[0] ?? null);
        /** @var SuccessResponse $responseUser */
        $this->assertSame(['id' => '554023000000691003'], $responseUser->getDetails());
        $this->assertInstanceOf(Choice::class, $responseMsg = $responseUser->getMessage());
        $this->assertSame('User added', $responseMsg->getValue());
        $this->assertInstanceOf(Choice::class, $responseStatus = $responseUser->getStatus());
        $this->assertSame('success', $responseStatus->getValue());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/crm/v2/users', $request->getUri()->getPath());
        $this->assertEquals([
            'users' => [
                [
                    'role' => ['id' => '34770610026014'],
                    'first_name' => 'TestUser',
                    'email' => 'testuser@zoho.com',
                    'profile' => ['id' => '34770610026008'],
                    'last_name' => '12',
                ]
            ],
        ], json_decode($request->getBody(), true));
    }

    /** @dataProvider getErrorResponseCodes */
    public function testUpdateUserFailResponse(int $responseCode)
    {
        $request = new BodyWrapper;
        $request->setUsers([new User]);

        $this->clientMockHandler->append(self::makeJsonResponse(
            $responseCode,
            <<<JSON
{
  "code": "INVALID_THINGY",
  "details": {
    "api_name": "THE_API_NAME"
  },
  "message": "some message",
  "status": "error"
}
JSON
        ));

        $response = (new UsersOperations)->updateUser('12345', $request);

        $this->assertInstanceOf(APIException::class, $responseObj = $response->getObject());
        /** @var APIException $responseObj */
        $this->assertEquals('INVALID_THINGY', $responseObj->getCode()->getValue());
        $this->assertEquals(['api_name' => 'THE_API_NAME'], $responseObj->getDetails());
        $this->assertEquals('some message', $responseObj->getMessage()->getValue());
        $this->assertEquals('error', $responseObj->getStatus()->getValue());
    }

    public function testUpdateUserSuccess()
    {
        $request = new BodyWrapper;
        $request->setUsers([$this->makeUser([
            'first' => 'TestUser',
            'last' => '12',
            'email' => 'testuser@zoho.com',
            'profile_id' => '34770610026008',
            'role_id' => '34770610026014',
        ])]);

        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/update-user.html
            <<<JSON
{
  "users": [
    {
      "code": "SUCCESS",
      "details": {
        "id": "554023000000691003"
      },
      "message": "User updated",
      "status": "success"
    }
  ]
}
JSON
        ));

        $response = (new UsersOperations)->updateUser('12345', $request);

        $this->assertInstanceOf(ActionWrapper::class, $responseObj = $response->getObject());
        /** @var ActionWrapper $responseObj */
        $responseUsers = $responseObj->getUsers();
        $this->assertCount(1, $responseUsers);
        $this->assertInstanceOf(SuccessResponse::class, $responseUser = $responseUsers[0] ?? null);
        /** @var SuccessResponse $responseUser */
        $this->assertSame(['id' => '554023000000691003'], $responseUser->getDetails());
        $this->assertInstanceOf(Choice::class, $responseMsg = $responseUser->getMessage());
        $this->assertSame('User updated', $responseMsg->getValue());
        $this->assertInstanceOf(Choice::class, $responseStatus = $responseUser->getStatus());
        $this->assertSame('success', $responseStatus->getValue());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/crm/v2/users/12345', $request->getUri()->getPath());
        $this->assertEquals([
            'users' => [
                [
                    'role' => ['id' => '34770610026014'],
                    'first_name' => 'TestUser',
                    'email' => 'testuser@zoho.com',
                    'profile' => ['id' => '34770610026008'],
                    'last_name' => '12',
                ]
            ],
        ], json_decode($request->getBody(), true));
    }

    public function testGetUserSuccess()
    {
        $headerInstance = new HeaderMap();
        $ifModifiedSince = date_create('2020-07-15T17:58:47+05:30')->setTimezone(new DateTimeZone(date_default_timezone_get()));
        $headerInstance->add(GetUsersHeader::IfModifiedSince(), $ifModifiedSince);

        $this->clientMockHandler->append(self::makeJsonResponse(
            200,
            // sample response from https://www.zoho.com/crm/developer/docs/api/v2/get-users.html
            <<<JSON
{
  "users": [
    {
      "country": null,
      "role": {
        "name": "Sales department Head",
        "id": "4150868000000231921"
      },
      "city": null,
      "language": "en_US",
      "locale": "en_US",
      "microsoft": false,
      "Isonline": false,
      "Modified_By": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "street": null,
      "Currency": "DZD",
      "alias": null,
      "id": "4150868000000231929",
      "state": null,
      "fax": null,
      "country_locale": "US",
      "first_name": "Jack",
      "email": "jack.s@zylker.com",
      "Reporting_To": null,
      "zip": null,
      "created_time": "2019-08-20T12:39:23+05:30",
      "website": null,
      "Modified_Time": "2020-07-14T18:30:01+05:30",
      "time_format": "hh:mm a",
      "offset": 19800000,
      "profile": {
        "name": "Administrator",
        "id": "4150868000000026011"
      },
      "mobile": null,
      "last_name": "Smith",
      "time_zone": "Asia/Calcutta",
      "created_by": {
        "name": "Patricia Boyle",
        "id": "4150868000000225013"
      },
      "zuid": null,
      "confirm": false,
      "full_name": "Jack Smith",
      "territories": [],
      "phone": null,
      "dob": null,
      "date_format": "MM/dd/yyyy",
      "status": "disabled"
    }
  ],
  "info": {
    "per_page": 1,
    "count": 1,
    "page": 1,
    "more_records": false
  }
}
JSON
        ));

        $response = (new UsersOperations)->getUser('4150868000000026011', $headerInstance);

        $this->assertInstanceOf(ResponseWrapper::class, $responseObj = $response->getObject());
        /** @var RequestWrapper $responseObj */
        $users = $responseObj->getUsers();

        $this->assertInstanceOf(User::class, $user = $users[0] ?? null);
        $this->assertNull($user->getCountry());
        $this->assertInstanceOf(Role::class, $userRole = $user->getRole());
        $this->assertEquals('Sales department Head', $userRole->getName());
        $this->assertEquals('4150868000000231921', $userRole->getId());
        $this->assertNull($user->getCity());
        $this->assertEquals('en_US', $user->getLanguage());
        $this->assertEquals('en_US', $user->getLocale());
        $this->assertFalse($user->getMicrosoft());
        $this->assertFalse($user->getIsonline());
        $this->assertInstanceOf(User::class, $userModBy = $user->getModifiedBy());
        $this->assertEquals('Patricia Boyle', $userModBy->getName());
        $this->assertEquals('4150868000000225013', $userModBy->getId());
        $this->assertNull($user->getStreet());
        $this->assertEquals('DZD', $user->getCurrency());
        $this->assertNull($user->getAlias());
        $this->assertEquals('4150868000000231929', $user->getId());
        $this->assertNull($user->getState());
        $this->assertNull($user->getFax());
        $this->assertEquals('US', $user->getCountryLocale());
        $this->assertEquals('Jack', $user->getFirstName());
        $this->assertEquals('jack.s@zylker.com', $user->getEmail());
        $this->assertNull($user->getReportingTo());
        $this->assertNull($user->getZip());
        $this->assertEquals(new DateTime('2019-08-20T12:39:23+05:30'), $user->getCreatedTime());
        $this->assertNull($user->getWebsite());
        $this->assertEquals(new DateTime('2020-07-14T18:30:01+05:30'), $user->getModifiedTime());
        $this->assertEquals('hh:mm a', $user->getTimeFormat());
        $this->assertEquals(19800000, $user->getOffset());
        $this->assertInstanceOf(Profile::class, $userProf = $user->getProfile());
        $this->assertEquals('Administrator', $userProf->getName());
        $this->assertEquals('4150868000000026011', $userProf->getId());
        $this->assertNull($user->getMobile());
        $this->assertEquals('Smith', $user->getLastName());
        $this->assertEquals('Asia/Calcutta', $user->getTimeZone());
        $this->assertInstanceOf(User::class, $userCreatedBy = $user->getCreatedBy());
        $this->assertEquals('Patricia Boyle', $userCreatedBy->getName());
        $this->assertEquals('4150868000000225013', $userCreatedBy->getId());
        $this->assertNull($user->getZuid());
        $this->assertFalse($user->getConfirm());
        $this->assertEquals('Jack Smith', $user->getFullName());
        $this->assertCount(0, $user->getTerritories());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getDob());
        $this->assertEquals('MM/dd/yyyy', $user->getDateFormat());
        $this->assertEquals('disabled', $user->getStatus());
        $this->assertNull($user->getCustomizeInfo());
        $this->assertNull($user->getSignature());
        $this->assertNull($user->getNameFormat());
        $this->assertNull($user->getPersonalAccount());
        $this->assertNull($user->getDefaultTabGroup());
        $this->assertNull($user->getTheme());
        $this->assertNull($user->getDecimalSeparator());

        $request = $this->clientMockHandler->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertEquals(['2020-07-15T12:28:47+00:00'], $request->getHeader('If-Modified-Since'));
        $requestUri = $request->getUri();
        $this->assertSame('/crm/v2/users/4150868000000026011', $requestUri->getPath());
    }

    private function makeUser(array $params): User
    {
        $user = new User;
        if ($id = $params['id'] ?? null) {
            $user->setId($id);
        }
        if ($first = $params['first'] ?? null) {
            $user->setFirstName($first);
        }
        if ($last = $params['last'] ?? null) {
            $user->setLastName($last);
        }
        if ($email = $params['email'] ?? null) {
            $user->setEmail($email);
        }
        if ($profile_id = $params['profile_id'] ?? null) {
            $profile = new Profile;
            $profile->setId($profile_id);
            $user->setProfile($profile);
        }
        if ($role_id = $params['role_id'] ?? null) {
            $role = new Role;
            $role->setId($role_id);
            $user->setRole($role);
        }

        return $user;
    }
}
