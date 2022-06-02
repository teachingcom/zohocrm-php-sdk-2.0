<?php

namespace com\zoho\api\authenticator\store;

use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Throwable;

/**
 * This class stores the user token details to the MySQL DataBase.
 */
class DBStore implements TokenStore
{
    /** @var Connection */
    private $connection;
    /** @var string */
    private $tableName;

    /**
     * Create an DBStore class instance with the specified connection.
     */
    public function __construct(Connection $connection, string $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Creates the Tokens table within the database.
     * @throws SDKException
     */
    public function createSchema(): self
    {
        try {
            $schema = new Schema();

            $table = $schema->createTable($this->tableName);
            $table->addColumn('id', 'string')->setLength(255)->setNotnull(true);
            $table->setPrimaryKey(['id']);
            $table->addColumn('user_mail', 'string')->setLength(255)->setNotnull(true);
            $table->addColumn('client_id', 'string')->setLength(255)->setNotnull(false);
            $table->addColumn('client_secret', 'string')->setLength(255)->setNotnull(false);
            $table->addColumn('refresh_token', 'string')->setLength(255)->setNotnull(false);
            $table->addColumn('access_token', 'string')->setLength(255)->setNotnull(false);
            $table->addColumn('grant_token', 'string')->setLength(255)->setNotnull(false);
            $table->addColumn('expiry_time', 'string')->setLength(20)->setNotnull(false);
            $table->addColumn('redirect_url', 'string')->setLength(255)->setNotnull(false);

            $queries = $schema->toSql($this->connection->getDatabasePlatform());
            foreach ($queries as $query) {
                $this->connection->executeStatement($query);
            }

            return $this;
        } catch (Throwable $e) {
            throw new SDKException('TABLE_CREATE_ERROR', 'Unable to create database table.', [], $e);
        }
    }

    public function getToken(UserSignature $user, OAuthToken $token): ?OAuthToken
    {
        try
        {
            $qb = $this->connection->createQueryBuilder()
                ->select('*')
                ->from($this->tableName);
            $this->addWhereToken($qb, $user->getEmail(), $token);

            if (!($row = $qb->fetchAssociative())) {
                return null;
            }

            return $this->fillTokenFromRow($token, $row);
        }
        catch (Throwable $ex)
        {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function saveToken(UserSignature $user, OAuthToken $token): void
    {
        try
        {
            $token->setUserMail($user->getEmail());
            $this->deleteToken($token);

            $this->connection->insert($this->tableName, [
                'id' => $token->getId(),
                'user_mail' => $user->getEmail(),
                'client_id' => $token->getClientId(),
                'client_secret' => $token->getClientSecret(),
                'refresh_token' => $token->getRefreshToken(),
                'access_token' => $token->getAccessToken(),
                'grant_token' => $token->getGrantToken(),
                'expiry_time' => $token->getExpiryTime(),
                'redirect_url' => $token->getRedirectURL(),
            ]);
        }
        catch (Throwable $ex)
        {
            throw new SDKException(Constants::TOKEN_STORE, Constants::SAVE_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function deleteToken(OAuthToken $token): void
    {
        try
        {
            $qb = $this->connection->createQueryBuilder()
                ->delete($this->tableName);
            $this->addWhereToken($qb, $token->getUserMail(), $token)
                ->executeStatement();
        }
        catch (SDKException $ex)
        {
            throw $ex;
        }
        catch (Throwable $ex)
        {
            throw new SDKException(Constants::TOKEN_STORE, Constants::DELETE_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function getTokens(): array
    {
        try
        {
            $result = $this->connection->createQueryBuilder()
                ->select('*')
                ->from($this->tableName)
                ->executeQuery();

            $tokens = [];
            while ($row = $result->fetchAssociative())
            {
                $token = (new OAuthBuilder())
                    ->clientId($row[Constants::CLIENT_ID])
                    ->clientSecret($row[Constants::CLIENT_SECRET])
                    ->refreshToken($row[Constants::REFRESH_TOKEN])
                    ->redirectURL($row[Constants::REDIRECT_URL])
                    ->build();
                $this->fillTokenFromRow($token, $row);
                $this->fillTokenGrantFromRow($token, $row);

                $tokens[] = $token;
            }

            return $tokens;
        }
        catch (Throwable $ex)
        {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKENS_DB_ERROR, null, $ex);
        }
    }

    public function getTokenById(string $id, OAuthToken $token): OAuthToken
    {
        try
        {
            $result = $this->connection->createQueryBuilder()
                ->select('*')
                ->from($this->tableName)
                ->where('id = :id')
                ->setParameter('id', $id)
                ->executeQuery();

            if (!($row = $result->fetchAssociative()))
            {
                throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_BY_ID_DB_ERROR);
            }

            $token->setClientId($row[Constants::CLIENT_ID]);
            $token->setClientSecret($row[Constants::CLIENT_SECRET]);
            $token->setRefreshToken($row[Constants::REFRESH_TOKEN]);
            $this->fillTokenFromRow($token, $row);

            return $this->fillTokenGrantFromRow($token, $row);
        }
        catch (SDKException $ex)
        {
            throw $ex;
        }
        catch (Throwable $ex)
        {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function deleteTokens()
    {
        try
        {
            $this->connection->createQueryBuilder()
                ->delete($this->tableName)
                ->executeStatement();
        }
        catch (Throwable $ex)
        {
            throw new SDKException(Constants::TOKEN_STORE, Constants::DELETE_TOKENS_DB_ERROR, null, $ex);
        }
    }

    /**
     * @param string|null $email
     * @throws SDKException
     */
    private function addWhereToken(QueryBuilder $qb, $email, OAuthToken $token): QueryBuilder
    {
        if (!$email)
        {
            throw new SDKException(Constants::USER_MAIL_NULL_ERROR, Constants::USER_MAIL_NULL_ERROR_MESSAGE);
        }

        $qb->andWhere('user_mail = :email', 'client_id = :client_id')
            ->setParameter('email', $email)
            ->setParameter('client_id', $token->getClientId());

        if ($token->getGrantToken() != null)
        {
            return $qb->andWhere('grant_token = :grant_token')
                ->setParameter('grant_token', $token->getGrantToken());
        }

        return $qb->andWhere('refresh_token = :refresh_token')
            ->setParameter('refresh_token', $token->getRefreshToken());
    }

    private function fillTokenFromRow(OAuthToken $token, array $row): OAuthToken
    {
        $token->setId($row[Constants::ID]);
        $token->setAccessToken($row[Constants::ACCESS_TOKEN]);
        $token->setExpiryTime(new CarbonImmutable($row[Constants::EXPIRY_TIME]));
        $token->setRefreshToken($row[Constants::REFRESH_TOKEN]);
        $token->setUserMail($row[Constants::USER_MAIL]);

        return $token;
    }

    private function fillTokenGrantFromRow(OAuthToken $token, array $row): OAuthToken
    {
        if (strlen($grantToken = $row[Constants::GRANT_TOKEN] ?? '') > 0) {
            $token->setGrantToken($grantToken);
        }

        return $token;
    }
}
