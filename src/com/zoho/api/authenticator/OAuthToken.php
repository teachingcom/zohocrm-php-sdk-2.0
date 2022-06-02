<?php

namespace com\zoho\api\authenticator;

use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\api\logger\SDKLogger;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use DateTimeImmutable;
use DateTimeInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This class gets the tokens and checks the expiry time.
 */
class OAuthToken
{
    private $clientID;
    private $clientSecret;
    private $redirectURL;
    private $grantToken;
    private $refreshToken;
    private $accessToken;
    /** @var CarbonImmutable|null */
    private $expiryTime;
    private $userMail;
    private $id;

    /**
     * Creates an OAuthToken class instance with the specified parameters.
     * @param string $clientID A string containing the OAuth client id.
     * @param string $clientSecret A string containing the OAuth client secret.
     * @param string|null $id An optional string containing the OAuth record id.
     * @param string|null $grantToken A string containing the GRANT token.
     * @param string|null $refreshToken A string containing the Refresh token.
     * @param string|null $redirectURL An optional string containing the OAuth redirect URL.
     * @param string|null $accessToken An optional string containing the OAuth access token.
     * @param string|null $userMail An optional string containing the user email.
     * @param DateTimeInterface|null $expiryTime An optional DateTimeInterface compatible instance indicating the time of expiration.
     */
    public function __construct(
        string $clientID,
        string $clientSecret,
        string $id = null,
        string $grantToken = null,
        string $refreshToken = null,
        string $redirectURL = null,
        string $accessToken = null,
        string $userMail = null,
        DateTimeInterface $expiryTime = null
    ) {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->id = $id;
        $this->grantToken = $grantToken;
        $this->refreshToken = $refreshToken;
        $this->redirectURL = $redirectURL;
        $this->accessToken = $accessToken;
        $this->userMail = $userMail;
        $this->expiryTime = new CarbonImmutable($expiryTime);
    }

    /**
     * This is a setter method to set OAuth client id.
     */
    public function setClientId(string $clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * This is a getter method to get OAuth client id.
     */
    public function getClientId(): string
    {
        return $this->clientID;
    }

    /**
     * This is a getter method to set OAuth client secret.
     */
    public function setClientSecret(string $clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * This is a getter method to get OAuth client secret.
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * This is a getter method to get OAuth redirect URL.
     */
    public function getRedirectURL(): ?string
    {
        return $this->redirectURL;
    }

    /**
     * This is a getter method to set OAuth redirect URL.
     */
    public function setRedirectURL(string $redirectURL)
    {
        $this->redirectURL = $redirectURL;
    }

    /**
     * This is a setter method to set grant token.
     */
    public function setGrantToken(string $grantToken)
    {
        $this->grantToken = $grantToken;
    }

    /**
     * This is a getter method to get grant token.
     */
    public function getGrantToken(): ?string
    {
        return $this->grantToken;
    }

    /**
     * This is a getter method to get refresh token.
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * This is a setter method to set refresh token.
     */
    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * This is a getter method to get access token.
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * This is a setter method to set access token.
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * This is a getter method to get token expire time.
     */
    public function getExpiryTime(): ?CarbonImmutable
    {
        return $this->expiryTime;
    }

    /**
     * This is a setter method to set token expire time.
     */
    public function setExpiryTime(DateTimeInterface $expiryTime)
    {
        $this->expiryTime = new CarbonImmutable($expiryTime);
    }

    /**
     * This is a getter method to get user Mail.
     */
    public function getUserMail(): ?string
    {
        return $this->userMail;
    }

    /**
     * This is a setter method to set user Mail.
     */
    public function setUserMail(string $userMail)
    {
        $this->userMail = $userMail;
    }

    /**
     * This is a getter method to get ID.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * This is a setter method to set ID.
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * This method to set authentication token to Request instance.
     * @throws SDKException
     */
    public function authenticate(Request $request): Request
    {
        try
        {
            $initializer = Initializer::getInitializer();
            $store = $initializer->getStore();
            $user = $initializer->getUser();

            if($this->accessToken == null)
            {
                if($this->id != null)
                {
                    $oauthToken = $store->getTokenById($this->id, $this);
                }
                else
                {
                    $oauthToken = $store->getToken($user, $this);
                }
            }
            else
            {
                $oauthToken = $this;
            }

            if ($oauthToken == null)//first time
            {
                $token = $this->refreshToken ? $this->refreshAccessToken($user, $store)->getAccessToken() : $this->generateAccessToken($user, $store)->getAccessToken();
            }
            else if ($oauthToken->hasAccessTokenExpired())
            {
                SDKLogger::info(Constants::REFRESH_TOKEN_MESSAGE);

                $token = $oauthToken->refreshAccessToken($user, $store)->getAccessToken();
            }
            else
            {
                $token = $oauthToken->getAccessToken();
            }

            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $request->withHeader(Constants::AUTHORIZATION, Constants::OAUTH_HEADER_PREFIX . $token);
        }
        catch(SDKException $ex)
        {
            throw $ex;
        }
        catch(Throwable $ex)
        {
            throw new SDKException(null, null, null, $ex);
        }
    }

    /** @throws GuzzleException */
    public function getResponseFromServer(array $form_params): ResponseInterface
    {
        $initializer = Initializer::getInitializer();
        $client = $initializer->getClient();

        return $client->post($initializer->getEnvironment()->getAccountsTokenUrl(), compact('form_params'));
    }

    /** @throws SDKException */
    private function refreshAccessToken(UserSignature $user, TokenStore $store): OAuthToken
    {
        try
        {
            $response = $this->getResponseFromServer([
                Constants::CLIENT_ID => $this->clientID,
                Constants::CLIENT_SECRET => $this->clientSecret,
                Constants::GRANT_TYPE => Constants::REFRESH_TOKEN,
                Constants::REFRESH_TOKEN => $this->refreshToken,
            ]);

            $this->processResponse($response);

            if($this->id == null)
            {
                $this->generateId($user);
            }

            $store->saveToken($user, $this);
        }
        catch(SDKException $ex)
        {
            throw $ex;
        }
        catch (Throwable $ex)
        {
            throw new SDKException(null, Constants::SAVE_TOKEN_ERROR, null, $ex);
        }

        return $this;
    }

    /** @throws SDKException */
    public function generateAccessToken(UserSignature $user, TokenStore $store): OAuthToken
    {
        $requestParams = array_filter([
            Constants::CLIENT_ID => $this->clientID,
            Constants::CLIENT_SECRET => $this->clientSecret,
            Constants::GRANT_TYPE => Constants::GRANT_TYPE_AUTH_CODE,
            Constants::CODE => $this->grantToken,
            Constants::REDIRECT_URI => $this->redirectURL,
        ]);

        try
        {
            $response = $this->getResponseFromServer($requestParams);
            $this->processResponse($response);
            $this->generateId($user);
            $store->saveToken($user, $this);
        }
        catch(SDKException $ex)
        {
            throw $ex;
        }
        catch(BadResponseException $ex)
        {
            $this->processResponse($ex->getResponse());
        }
        catch (Throwable $ex)
        {
            throw new SDKException(null, Constants::SAVE_TOKEN_ERROR, null, $ex);
        }

        return $this;
    }

    /** @throws SDKException */
    public function processResponse(ResponseInterface $response): self
    {
        $jsonResponse = json_decode($response->getBody(), true);

        if (!array_key_exists(Constants::ACCESS_TOKEN, $jsonResponse))
        {
            throw new SDKException(Constants::INVALID_TOKEN_ERROR, array_key_exists(Constants::ERROR, $jsonResponse) ? $jsonResponse[Constants::ERROR] : Constants::NO_ACCESS_TOKEN_ERROR);
        }

        $this->accessToken = $jsonResponse[Constants::ACCESS_TOKEN];

        $this->expiryTime = CarbonImmutable::now()->addSeconds($jsonResponse[Constants::EXPIRES_IN]);

        if (array_key_exists(Constants::REFRESH_TOKEN, $jsonResponse))
        {
            $this->refreshToken = $jsonResponse[Constants::REFRESH_TOKEN];
        }

        return $this;
    }

    /**
     * Determines whether access token will expire in next 5 seconds or less.
     */
    public function hasAccessTokenExpired(): bool
    {
        if (!($expiryTime = $this->getExpiryTime()))
        {
            // no expiry means it couldn't possibly have expired
            return false;
        }

        return 5 >= CarbonImmutable::now()->addSeconds(5)->diffInSeconds($expiryTime);
    }

    /**
     * The method to remove the current token from the Store.
     * @throws SDKException
     */
    public function remove(): bool
    {
        try
        {
            Initializer::getInitializer()->getStore()->deleteToken($this);

            return true;
        }
        catch(SDKException $ex)
        {
            throw $ex;
        }
        catch (Throwable $ex)
        {
            throw new SDKException(null, null, null, $ex);
        }
    }

    private function generateId(UserSignature $user)
	{
		$email = $user->getEmail();

		$builder = Constants::PHP . explode("@", $email)[0] . "_";
		$builder .= Initializer::getInitializer()->getEnvironment()->getName() . "_";
		$builder .= substr($this->accessToken, strlen($this->accessToken) - 4);

		$this->id = $builder;
	}
}
