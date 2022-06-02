<?php

namespace test\com\zoho\crm\api;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use PHPUnit\Framework\TestCase;

class UserSignatureTest extends TestCase
{
    public function getEmailInvalidData(): array
    {
        return [
            ['nope'],
            ['not@quite'],
            ['al.most'],
        ];
    }

    /** @dataProvider getEmailInvalidData */
    public function testEmailInvalid(string $email)
    {
        $this->expectExceptionObject(
            new SDKException(
                Constants::USER_SIGNATURE_ERROR,
                null,
                [
                    Constants::FIELD => Constants::EMAIL,
                    Constants::EXPECTED_TYPE => Constants::EMAIL,
                ]
            )
        );
        new UserSignature($email);
    }

    public function getEmailValidData(): array
    {
        return [
            ['yes@a.b'],
            ['sure@this.works.too'],
            ['you@can.even.try.a.really.long.one'],
        ];
    }

    /** @dataProvider getEmailValidData */
    public function testEmailValid(string $email)
    {
        $actual = new UserSignature($email);

        $this->assertSame($email, $actual->getEmail());
    }
}
