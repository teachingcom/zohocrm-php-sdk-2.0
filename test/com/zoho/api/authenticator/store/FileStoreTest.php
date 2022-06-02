<?php

namespace test\com\zoho\api\authenticator\store;

use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\crm\api\UserSignature;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use test\com\zoho\TestCase;

class FileStoreTest extends TestCase
{
    /** @var FileStore */
    private $store;

    private static function getPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'tokens.csv';
    }

    protected function setUp(): void
    {
        parent::setUp();

        touch(self::getPath());
        $this->store = new FileStore(self::getPath());
        $this->initialize($this->store);
    }

    protected function tearDown(): void
    {
        unlink(self::getPath());
        parent::tearDown();
    }

    public function testGetTokens()
    {
        $data0 = $this->insertToken();
        $data1 = $this->insertToken();

        $actual = $this->store->getTokens();

        $this->assertCount(2, $actual);

        $this->assertInstanceOf(OAuthToken::class, $token0 = $actual[0] ?? null);
        $this->assertEquals($data0['id'], $token0->getId());
        $this->assertEquals($data0['user_mail'], $token0->getUserMail());
        $this->assertEquals($data0['client_id'], $token0->getClientId());
        $this->assertEquals($data0['client_secret'], $token0->getClientSecret());
        $this->assertEquals($data0['refresh_token'], $token0->getRefreshToken());
        $this->assertEquals($data0['access_token'], $token0->getAccessToken());
        $this->assertEquals($data0['grant_token'], $token0->getGrantToken());
        $this->assertEquals(new CarbonImmutable($data0['expiry_time']), $token0->getExpiryTime());
        $this->assertEquals($data0['redirect_url'], $token0->getRedirectURL());

        $this->assertInstanceOf(OAuthToken::class, $token1 = $actual[1] ?? null);
        $this->assertEquals($data1['id'], $token1->getId());
        $this->assertEquals($data1['user_mail'], $token1->getUserMail());
        $this->assertEquals($data1['client_id'], $token1->getClientId());
        $this->assertEquals($data1['client_secret'], $token1->getClientSecret());
        $this->assertEquals($data1['refresh_token'], $token1->getRefreshToken());
        $this->assertEquals($data1['access_token'], $token1->getAccessToken());
        $this->assertEquals($data1['grant_token'], $token1->getGrantToken());
        $this->assertEquals(new CarbonImmutable($data1['expiry_time']), $token1->getExpiryTime());
        $this->assertEquals($data1['redirect_url'], $token1->getRedirectURL());
    }

    public function testSaveTokenWhenNew()
    {
        $user = new UserSignature('user@zoho.com');
        $token = $this->makeToken(['user_mail' => 'someone.else@zoho.com']);

        $this->store->saveToken($user, $token);

        $this->assertEquals('user@zoho.com', $token->getUserMail());
        $this->assertFileHasToken($token);
    }

    public function getGrantTokenData(): array
    {
        $faker = $this->faker();
        return [
            'with grant token' => [$faker->md5],
            'without grant token' => [null],
        ];
    }

    /**
     * @dataProvider getGrantTokenData
     */
    public function testSaveTokenWhenExisting(?string $grant_token)
    {
        $user = new UserSignature($user_mail = 'user@zoho.com');
        $refresh_token = $this->faker->md5;
        $deleteData = $this->insertToken(compact('user_mail', 'grant_token', 'refresh_token'));
        $deleteToken = $this->makeToken($deleteData);
        $updatedToken = $this->makeToken($deleteData);
        $updatedToken->setId($this->faker->md5);
        $updatedToken->setAccessToken($this->faker->md5);
        $updatedToken->setExpiryTime($this->faker->dateTime);

        $this->store->saveToken($user, $updatedToken);

        $this->assertFileHasToken($updatedToken);
        $this->assertFileNotHasToken($deleteToken);
    }

    public function testDeleteToken()
    {
        $data = $this->insertToken();
        $token = $this->makeToken($data);

        $this->store->deleteToken($token);

        $this->assertFileNotHasTokenRow($data);
    }

    public function testGetTokenById()
    {
        $data = $this->insertToken(['id' => 'the-id-we-are-looking-for']);
        $token = $this->makeToken();

        $this->store->getTokenById('the-id-we-are-looking-for', $token);

        $this->assertEquals($data['client_id'], $token->getClientId());
        $this->assertEquals($data['client_secret'], $token->getClientSecret());
        $this->assertEquals($data['id'], $token->getId());
        $this->assertEquals($data['grant_token'], $token->getGrantToken());
        $this->assertEquals($data['refresh_token'], $token->getRefreshToken());
        $this->assertEquals($data['redirect_url'], $token->getRedirectUrl());
        $this->assertEquals($data['access_token'], $token->getAccessToken());
        $this->assertEquals($data['user_mail'], $token->getUserMail());
        $this->assertEquals(new CarbonImmutable($data['expiry_time']), $token->getExpiryTime());
    }

    public function testDeleteTokens()
    {
        $this->insertToken();
        $this->insertToken();
        $this->insertToken();

        $this->store->deleteTokens();

        $this->assertCount(0, $this->getRows());
    }

    /** @dataProvider getGrantTokenData */
    public function testGetTokenWithMatch(?string $grant_token)
    {
        $user = new UserSignature('find.me@zoho.com');
        $client_id = $this->faker->md5;
        $refresh_token = $this->faker->md5;
        $data = $this->insertToken(compact('client_id', 'grant_token', 'refresh_token') + ['user_mail' => $user->getEmail()]);
        $token = $this->makeToken(compact('client_id', 'grant_token', 'refresh_token') + ['user_mail' => 'forget.me@zoho.com', 'redirect_url' => 'https://redirect.to/me']);

        $actual = $this->store->getToken($user, $token);

        $this->assertSame($token, $actual);
        $this->assertEquals($client_id, $actual->getClientId());
        $this->assertNotEquals($data['client_secret'], $actual->getClientSecret());
        $this->assertEquals($data['id'], $actual->getId());
        $this->assertEquals($grant_token, $actual->getGrantToken());
        $this->assertEquals($refresh_token, $actual->getRefreshToken());
        $this->assertEquals('https://redirect.to/me', $actual->getRedirectUrl());
        $this->assertEquals($data['access_token'], $actual->getAccessToken());
        $this->assertEquals('find.me@zoho.com', $actual->getUserMail());
        $this->assertEquals(new CarbonImmutable($data['expiry_time']), $actual->getExpiryTime());
    }

    private function insertToken(array $data = []): array
    {
        $data = $this->getFakeTokenData($data);
        $resource = fopen(self::getPath(), 'a');
        fwrite(
            $resource,
            "\n" . $data['id']
            . ',' . $data['user_mail']
            . ',' . $data['client_id']
            . ',' . $data['client_secret']
            . ',' . $data['refresh_token']
            . ',' . $data['access_token']
            . ',' . $data['grant_token']
            . ',' . $data['expiry_time']
            . ',' . $data['redirect_url']
        );
        fclose($resource);

        return $data;
    }

    private function assertFileHasToken(OAuthToken $token): void
    {
        $this->assertFileHasTokenRow([
            'client_id' => $token->getClientId(),
            'client_secret' => $token->getClientSecret(),
            'id' => $token->getId(),
            'grant_token' => $token->getGrantToken(),
            'refresh_token' => $token->getRefreshToken(),
            'redirect_url' => $token->getRedirectUrl(),
            'access_token' => $token->getAccessToken(),
            'user_mail' => $token->getUserMail(),
            'expiry_time' => $token->getExpiryTime()->toDateTimeString(),
        ]);
    }

    private function assertFileHasTokenRow(array $data): void
    {
        $row = [
            $data['id'],
            $data['user_mail'],
            $data['client_id'],
            $data['client_secret'],
            $data['refresh_token'],
            $data['access_token'],
            $data['grant_token'],
            $data['expiry_time'],
            $data['redirect_url'],
        ];
        $rows = $this->getRows();
        $this->assertContainsEquals($row, $rows, "Token rows found:\n" . print_r($rows, true));
    }

    private function assertFileNotHasToken(OAuthToken $token)
    {
        $this->assertFileNotHasTokenRow([
            'client_id' => $token->getClientId(),
            'client_secret' => $token->getClientSecret(),
            'id' => $token->getId(),
            'grant_token' => $token->getGrantToken(),
            'refresh_token' => $token->getRefreshToken(),
            'redirect_url' => $token->getRedirectUrl(),
            'access_token' => $token->getAccessToken(),
            'user_mail' => $token->getUserMail(),
            'expiry_time' => $token->getExpiryTime()->toDateTimeString(),
        ]);
    }

    private function assertFileNotHasTokenRow(array $data): void
    {
        $this->assertNotContainsEquals($data, $this->getRows());
    }

    private function getRows(): array
    {
        $resource = fopen(self::getPath(), 'r');
        fgetcsv($resource); // move past the headers
        $rows = [];
        while (false !== $row = fgetcsv($resource)) {
            $rows[] = $row;
        }

        return $rows;
    }
}
