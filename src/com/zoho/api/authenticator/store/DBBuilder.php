<?php

namespace com\zoho\api\authenticator\store;

use com\zoho\crm\api\util\Constants;

class DBBuilder
{
    private $userName = Constants::MYSQL_USER_NAME;
    private $portNumber = Constants::MYSQL_PORT_NUMBER;
    private $password = "";
    private $host = Constants::MYSQL_HOST;
    private $databaseName = Constants::MYSQL_DATABASE_NAME;
    private $tableName = Constants::MYSQL_TABLE_NAME;

    public function userName(string $userName): DBBuilder
    {
        $this->userName = $userName;

        return $this;
    }

    public function portNumber(int $portNumber): DBBuilder
    {
        $this->portNumber = $portNumber;

        return $this;
    }

    public function password(string $password): DBBuilder
    {
        $this->password = $password;

        return $this;
    }

    public function host(string $host): DBBuilder
    {
        $this->host = $host;

        return $this;
    }

    public function databaseName(string $databaseName): DBBuilder
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    public function tableName(string $tableName): DBBuilder
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function build(): DBStore
    {
        return new DBStore($this->host, $this->databaseName);
    }
}
