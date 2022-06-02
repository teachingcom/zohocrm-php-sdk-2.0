<?php

namespace com\zoho\api\authenticator\store;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\OAuthToken;
use DateTimeImmutable;
use Exception;

/**
 * This class stores the user token details to the file.
 */
class FileStore implements TokenStore
{
    private $filePath;
    private $columnMap = [
        Constants::ID => 0,
        Constants::USER_MAIL => 1,
        Constants::CLIENT_ID => 2,
        Constants::CLIENT_SECRET => 3,
        Constants::REFRESH_TOKEN => 4,
        Constants::ACCESS_TOKEN => 5,
        Constants::GRANT_TOKEN => 6,
        Constants::EXPIRY_TIME => 7,
        Constants::REDIRECT_URL => 8,
    ];

    /**
     * Creates an FileStore class instance with the specified parameters.
     * @param string $filePath A string containing the absolute file path to store tokens.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = trim($filePath);

        $csvWriter = fopen($this->filePath, 'a'); //opens file in append mode

        if (!trim(file_get_contents($this->filePath))) {
            fwrite($csvWriter, implode(",", $this->columnsToHeaders()));
        }

        fclose($csvWriter);
    }

    private function columnsToHeaders(): array
    {
        $headers = array_flip($this->columnMap);
        ksort($headers);

        return $headers;
    }

    private function getColumnData(array $row, string $columnName)
    {
        $columnIndex = $this->columnMap[$columnName];

        return $row[$columnIndex] ?? null;
    }

    public function getToken(UserSignature $user, OAuthToken $token): ?OAuthToken
    {
        try {
            $csvReader = file($this->filePath, FILE_IGNORE_NEW_LINES);

            for ($index = 1; $index < sizeof($csvReader); $index++) {
                $allContents = $csvReader[$index];
                $nextRecord = str_getcsv($allContents);
                if ($this->checkTokenExists($user->getEmail(), $token, $nextRecord)) {
                    $token->setAccessToken($this->getColumnData($nextRecord, Constants::ACCESS_TOKEN));
                    $token->setExpiryTime(new DateTimeImmutable($this->getColumnData($nextRecord, Constants::EXPIRY_TIME)));
                    $token->setRefreshToken($this->getColumnData($nextRecord, Constants::REFRESH_TOKEN));
                    $token->setId($this->getColumnData($nextRecord, Constants::ID));
                    $token->setUserMail($this->getColumnData($nextRecord, Constants::USER_MAIL));

                    return $token;
                }
            }

            return null;
        } catch (Exception $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_FILE_ERROR, null, $ex);
        }
    }

    public function saveToken(UserSignature $user, OAuthToken $token): void
    {
        try {
            $token->setUserMail($user->getEmail());
            $this->deleteToken($token);

            $data = [];
            $this->setColumnData($data, Constants::ID, $token->getId());
            $this->setColumnData($data, Constants::USER_MAIL, $user->getEmail());
            $this->setColumnData($data, Constants::CLIENT_ID, $token->getClientId());
            $this->setColumnData($data, Constants::CLIENT_SECRET, $token->getClientSecret());
            $this->setColumnData($data, Constants::REFRESH_TOKEN, $token->getRefreshToken());
            $this->setColumnData($data, Constants::ACCESS_TOKEN, $token->getAccessToken());
            $this->setColumnData($data, Constants::GRANT_TOKEN, $token->getGrantToken());
            $this->setColumnData($data, Constants::EXPIRY_TIME, $token->getExpiryTime());
            $this->setColumnData($data, Constants::REDIRECT_URL, $token->getRedirectURL());

            $csvWriter = file($this->filePath);
            $csvWriter[] = "\n";
            $csvWriter[] = implode(",", $data);
            file_put_contents($this->filePath, $csvWriter);
        } catch (Exception $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::SAVE_TOKEN_FILE_ERROR, null, $ex);
        }
    }

    private function setColumnData(array &$data, string $columnName, $value): void
    {
        $columnIndex = $this->columnMap[$columnName];

        $data[$columnIndex] = $value;
    }

    public function deleteToken(OAuthToken $token): void
    {
        try {
            $csvReader = file($this->filePath, FILE_IGNORE_NEW_LINES);

            for ($index = 1; $index < sizeof($csvReader); $index++) {
                $allContents = $csvReader[$index];
                $nextRecord = str_getcsv($allContents);
                if ($this->checkTokenExists($token->getUserMail(), $token, $nextRecord)) {
                    unset($csvReader[$index]);
                    // Rewrite the file after we deleted the user account details.
                    file_put_contents($this->filePath, implode("\n", $csvReader));

                    return; // Stop searching after we found the email
                }
            }
        } catch (SDKException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::DELETE_TOKEN_FILE_ERROR, null, $ex);
        }
    }

    public function getTokens(): array
    {
        try {
            $csvReader = file($this->filePath, FILE_IGNORE_NEW_LINES);
            $tokens = [];
            for ($index = 1; $index < sizeof($csvReader); $index++) {
                $allContents = $csvReader[$index];
                $nextRecord = str_getcsv($allContents);
                $grantToken = $this->getColumnData($nextRecord, Constants::GRANT_TOKEN) ?: null;

                $token = (new OAuthBuilder)
                    ->clientId($this->getColumnData($nextRecord, Constants::CLIENT_ID))
                    ->clientSecret($this->getColumnData($nextRecord, Constants::CLIENT_SECRET))
                    ->refreshToken($this->getColumnData($nextRecord, Constants::REFRESH_TOKEN))
                    ->build();
                $token->setId($this->getColumnData($nextRecord, Constants::ID));

                if ($grantToken != null) {
                    $token->setGrantToken($grantToken);
                }

                $token->setUserMail(strval($this->getColumnData($nextRecord, Constants::USER_MAIL)));
                $token->setAccessToken($this->getColumnData($nextRecord, Constants::ACCESS_TOKEN));
                $token->setExpiryTime(new DateTimeImmutable($this->getColumnData($nextRecord, Constants::EXPIRY_TIME)));
                $token->setRedirectURL($this->getColumnData($nextRecord, Constants::REDIRECT_URL));

                $tokens[] = $token;
            }

            return $tokens;
        } catch (Exception $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKENS_FILE_ERROR, null, $ex);
        }
    }

    public function deleteTokens()
    {
        try {
            file_put_contents($this->filePath, implode(",", $this->columnsToHeaders()));
        } catch (Exception $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::DELETE_TOKENS_FILE_ERROR, null, $ex);
        }
    }

    /**
     * Provides whether token exists.
     * @throws SDKException
     */
    private function checkTokenExists($email, OAuthToken $oauthToken, array $row): bool
    {
        if ($email === null) {
            throw new SDKException(Constants::USER_MAIL_NULL_ERROR, Constants::USER_MAIL_NULL_ERROR_MESSAGE);
        }

        $clientId = $oauthToken->getClientId();
        $grantToken = (string)$oauthToken->getGrantToken();
        $refreshToken = (string)$oauthToken->getRefreshToken();
        $tokenCheck = $grantToken != null
            ? $grantToken === (string)$this->getColumnData($row, Constants::GRANT_TOKEN)
            : $refreshToken === (string)$this->getColumnData($row, Constants::REFRESH_TOKEN);

        $isMatch = $email === $this->getColumnData($row, Constants::USER_MAIL);
        $isMatch &= $clientId === $this->getColumnData($row, Constants::CLIENT_ID);
        $isMatch &= $tokenCheck;

        return $isMatch;
    }

    public function getTokenById(string $id, OAuthToken $token): OAuthToken
    {
        try {
            $csvReader = file($this->filePath, FILE_IGNORE_NEW_LINES);
            for ($index = 1; $index < sizeof($csvReader); $index++) {
                $allContents = $csvReader[$index];
                $nextRecord = str_getcsv($allContents);
                if ($this->getColumnData($nextRecord, Constants::ID) == $id) {
                    $token->setId($id);
                    $token->setUserMail($this->getColumnData($nextRecord, Constants::USER_MAIL));
                    $token->setClientId($this->getColumnData($nextRecord, Constants::CLIENT_ID));
                    $token->setClientSecret($this->getColumnData($nextRecord, Constants::CLIENT_SECRET));
                    $token->setRefreshToken($this->getColumnData($nextRecord, Constants::REFRESH_TOKEN));
                    $token->setAccessToken($this->getColumnData($nextRecord, Constants::ACCESS_TOKEN));
                    if (0 < strlen($grantToken = $this->getColumnData($nextRecord, Constants::GRANT_TOKEN))) {
                        $token->setGrantToken($grantToken);
                    }
                    $token->setExpiryTime(new DateTimeImmutable($this->getColumnData($nextRecord, Constants::EXPIRY_TIME)));
                    if (0 < strlen($redirectURL = $this->getColumnData($nextRecord, Constants::REDIRECT_URL))) {
                        $token->setRedirectURL($redirectURL);
                    }

                    return $token;
                }
            }

            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_BY_ID_FILE_ERROR);
        } catch (SDKException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_FILE_ERROR, null, $ex);
        }
    }
}
