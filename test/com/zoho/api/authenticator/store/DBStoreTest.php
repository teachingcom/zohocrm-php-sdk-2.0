<?php

namespace test\com\zoho\api\authenticator\store;

use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\crm\api\UserSignature;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use test\com\zoho\TestCase;

class DBStoreTest extends TestCase
{
    /** @var Connection */
    private $connection;
    /** @var DBStore */
    private $store;
    private $table = 'zoho';

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->store = new DBStore($this->connection, $this->table);
        $this->initialize($this->store);
    }

    public function testCreateSchema(): void
    {
        $this->store->createSchema();

        $data = $this->insertToken();
        $this->assertDatabaseHasTokenRow($data);
    }

    /** @depends testCreateSchema */
    public function testGetTokens()
    {
        $this->store->createSchema();
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

    /** @depends testCreateSchema */
    public function testSaveTokenWhenNew()
    {
        $this->store->createSchema();
        $user = new UserSignature('user@zoho.com');
        $token = $this->makeToken(['user_mail' => 'someone.else@zoho.com']);

        $this->store->saveToken($user, $token);

        $this->assertEquals('user@zoho.com', $token->getUserMail());
        $this->assertDatabaseHasToken($token);
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
     * @depends testCreateSchema
     * @dataProvider getGrantTokenData
     */
    public function testSaveTokenWhenExisting(?string $grant_token)
    {
        $this->store->createSchema();
        $user = new UserSignature($user_mail = 'user@zoho.com');
        $refresh_token = $this->faker->md5;
        $deleteData = $this->insertToken(compact('user_mail', 'grant_token', 'refresh_token'));
        $deleteToken = $this->makeToken($deleteData);
        $updatedToken = $this->makeToken($deleteData);
        $updatedToken->setId($this->faker->md5);
        $updatedToken->setAccessToken($this->faker->md5);
        $updatedToken->setExpiryTime($this->faker->dateTime);

        $this->store->saveToken($user, $updatedToken);

        $this->assertDatabaseHasToken($updatedToken);
        $this->assertDatabaseNotHasToken($deleteToken);
    }

    /** @depends testCreateSchema */
    public function testDeleteToken()
    {
        $this->store->createSchema();
        $data = $this->insertToken();
        $token = $this->makeToken($data);

        $this->store->deleteToken($token);

        $this->assertDatabaseNotHasTokenRow($data);
    }

    /** @depends testCreateSchema */
    public function testGetTokenById()
    {
        $this->store->createSchema();
        $data = $this->insertToken(['id' => 'the-id-we-are-looking-for']);
        $token = $this->makeToken(['redirect_url' => 'https://redirect.to/me']);

        $this->store->getTokenById('the-id-we-are-looking-for', $token);

        $this->assertEquals($data['client_id'], $token->getClientId());
        $this->assertEquals($data['client_secret'], $token->getClientSecret());
        $this->assertEquals($data['id'], $token->getId());
        $this->assertEquals($data['grant_token'], $token->getGrantToken());
        $this->assertEquals($data['refresh_token'], $token->getRefreshToken());
        $this->assertEquals('https://redirect.to/me', $token->getRedirectUrl());
        $this->assertEquals($data['access_token'], $token->getAccessToken());
        $this->assertEquals($data['user_mail'], $token->getUserMail());
        $this->assertEquals(new CarbonImmutable($data['expiry_time']), $token->getExpiryTime());
    }

    /** @depends testCreateSchema */
    public function testDeleteTokens()
    {
        $this->store->createSchema();
        $this->insertToken();
        $this->insertToken();
        $this->insertToken();

        $this->store->deleteTokens();

        $this->assertCount(0, $this->connection->createQueryBuilder()->select('*')->from($this->table)->fetchAllAssociative());
    }

    /**
     * @depends testCreateSchema
     * @dataProvider getGrantTokenData
     */
    public function testGetTokenWithMatch(?string $grant_token)
    {
        $this->store->createSchema();
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
        $this->connection->insert($this->table, $data);

        return $data;
    }

    private function assertDatabaseHasToken(OAuthToken $token): void
    {
        $this->assertDatabaseHasTokenRow([
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

    private function assertDatabaseHasTokenRow(array $data): void
    {
        $rows = $this->connection->createQueryBuilder()->select('*')->from($this->table)->fetchAllAssociative();
        $this->assertContainsEquals($data, $rows, "Token rows found:\n" . print_r($rows, true));
    }

    private function assertDatabaseNotHasToken(OAuthToken $token)
    {
        $this->assertDatabaseNotHasTokenRow([
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

    private function assertDatabaseNotHasTokenRow(array $data): void
    {
        $rows = $this->connection->createQueryBuilder()->select('*')->from($this->table)->fetchAllAssociative();
        $this->assertNotContainsEquals($data, $rows);
    }
}
