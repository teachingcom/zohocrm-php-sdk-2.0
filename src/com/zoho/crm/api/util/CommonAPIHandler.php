<?php

namespace com\zoho\crm\api\util;

use com\zoho\api\logger\SDKLogger;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Header;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\Param;
use com\zoho\crm\api\ParameterMap;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Throwable;

/**
 * This class is to process the API request and its response.
 * Construct the objects that are to be sent as parameters or in the request body with the API.
 * The Request parameter, header and body objects are constructed here.
 * Process the response JSON and converts it to relevant objects in the library.
 */
class CommonAPIHandler
{
    private $apiPath;
    private $param;
    private $header;
    private $request;
    private $httpMethod;
    private $moduleAPIName;
    private $contentType = 'application/json';
    private $categoryMethod;
    private $mandatoryChecker;
    /** @var Client */
    private $client;

    public function __construct()
    {
        $this->header = new HeaderMap();
        $this->param = new ParameterMap();
        $this->client = Initializer::getInitializer()->getClient();
    }

    /**
     * This is a setter method to set an API request content type.
     */
    public function setContentType(string $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * This is a setter method to set the API request URL.
     */
    public function setAPIPath(string $apiPath)
    {
        $this->apiPath = $apiPath;
    }

    /**
     * This method is to add an API request parameter.
     * @param Param $param A Param containing the API request parameter .
     * @param object $value A object containing the API request parameter value.
     * @throws SDKException
     */
    public function addParam(Param $param, $value)
    {
        if ($value === null)
        {
            return;
        }

        if ($this->param === null)
        {
            $this->param = new ParameterMap();
        }

        $this->param->add($param, $value);
    }

    /**
     * This method to add an API request header.
     * @param Header $header A Header containing the API request header .
     * @param mixed $headerValue A object containing the API request header value.
     */
    public function addHeader($header, $headerValue)
    {
        if ($headerValue === null)
        {
            return;
        }

        if ($this->header === null)
        {
            $this->header = new HeaderMap();
        }

        $this->header->add($header, $headerValue);
    }

    /**
     * This is a setter method to set the API request parameter map.
     * @param ParameterMap $param A ParameterMap class instance containing the API request parameter.
     */
    public function setParam($param)
    {
        if ($param === null)
        {
            return;
        }

        if($this->param->getParameterMap() !== null && count($this->param->getParameterMap()) > 0)
        {
            $this->param->setParameterMap(array_merge($this->param->getParameterMap(), $param->getParameterMap()));
        }
        else
        {
            $this->param = $param;
        }
    }

    /**
     * This is a getter method to get the Zoho CRM module API name.
     * @return string A String representing the Zoho CRM module API name.
     */
    public function getModuleAPIName()
    {
        return $this->moduleAPIName;
    }

    /**
     * This is a setter method to set the Zoho CRM module API name.
     * @param string $moduleAPIName A string containing the Zoho CRM module API name.
     */
    public function setModuleAPIName($moduleAPIName)
    {
        $this->moduleAPIName = $moduleAPIName;
    }

    /**
     * This is a setter method to set the API request header map.
     * @param HeaderMap $header A HeaderMap class instance containing the API request header.
     */
    public function setHeader($header)
    {
        if ($header === null)
        {
            return;
        }

        if($this->header->getHeaderMap() !== null && count($this->header->getHeaderMap()) > 0)
        {
            $this->header->setHeaderMap(array_merge($this->header->getHeaderMap(), $header->getHeaderMap()));
        }
        else
        {
            $this->header = $header;
        }
    }

    /**
     * This is a setter method to set the API request body object.
     * @param object $request A object containing the API request body object.
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * This is a setter method to set the HTTP API request method.
     * @param string $httpMethod A string containing the HTTP API request method.
     */
    public function setHttpMethod(string $httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }

    /**
     * This method is used in constructing API request and response details. To make the Zoho CRM API calls.
     * @param string $className A string containing the method return type.
     * @param string $encodeType A String containing the expected API response content type.
     * @return APIResponse|null An APIResponse representing the Zoho CRM API response instance or null.
     * @throws SDKException
     */
    public function apiCall(string $className, string $encodeType)
    {
        if(!Initializer::getInitializer())
        {
            throw new SDKException(Constants::SDK_UNINITIALIZATION_ERROR,Constants::SDK_UNINITIALIZATION_MESSAGE);
        }

        try
        {
            $request = new Request($this->httpMethod, $this->getAPIUrl(), [
                'Content-Type' => $this->contentType,
                Constants::ZOHO_SDK => sprintf('%s/%s/php-2.0/%s:%s', php_uname('s'), php_uname('r'), phpversion(), Constants::SDK_VERSION),
            ]);
        }
        catch(SDKException $e)
        {
            SDKLogger::severeError(Constants::SET_API_URL_EXCEPTION, $e);

            throw $e;
        }
        catch (Throwable $e)
        {
            $exception = new SDKException(null, null, null, $e);

            SDKLogger::severeError(Constants::SET_API_URL_EXCEPTION, $exception);

            throw $exception;
        }

        if ($this->header != null && count($this->header->getHeaderMap()) > 0)
        {
            foreach ($this->header->getHeaderMap() as $header => $value) {
                $request = $request->withHeader($header, $value);
            }
        }

        $requestOptions = [];
        if ($this->param != null && count($this->param->getParameterMap()) > 0)
        {
            $requestOptions['query'] = $this->param->getParameterMap();
        }

        try
        {
            $request = Initializer::getInitializer()->getToken()->authenticate($request);
        }
        catch (SDKException $e)
		{
		    SDKLogger::severeError(Constants::AUTHENTICATION_EXCEPTION, $e);

		    throw $e;
        }
        catch (Throwable $e)
        {
            $exception = new SDKException(null, null, null, $e);

            SDKLogger::severeError(Constants::AUTHENTICATION_EXCEPTION, $exception);

            throw $exception;
        }

        if ($this->contentType != null && in_array(strtoupper($this->httpMethod), Constants::IS_GENERATE_REQUEST_BODY))
        {
            try
            {
                $convertInstance = $this->getConverterClassInstance(strtolower($this->contentType));
                $requestOptions = $convertInstance->formRequest($requestOptions, $this->request, get_class($this->request), null, null);
            }
            catch (SDKException $e)
			{
			    SDKLogger::severeError(Constants::FORM_REQUEST_EXCEPTION, $e);

				throw $e;
            }
            catch (Throwable $e)
            {
                $exception = new SDKException(null, null, null, $e);

                SDKLogger::severeError(Constants::FORM_REQUEST_EXCEPTION, $exception);

                throw $exception;
            }
        }

        try
        {
            $this->logRequest($request);
            $response = $this->client->send($request, $requestOptions);

            return $this->processResponse($response, $className);
        }
        catch (BadResponseException $e)
		{
            // response errors (4xx and 5xx response codes) are converted to response models
            return $this->processResponse($e->getResponse(), $className);
        }
        catch (GuzzleException $e)
		{
            $exception = new SDKException(Constants::API_EXCEPTION, null, null, $e);

            SDKLogger::severeError(Constants::API_CALL_EXCEPTION , $e);

		    throw $exception;
        }
        catch (Throwable $e)
        {
            $exception = new SDKException(null, null, null, $e);

            SDKLogger::severeError(Constants::API_CALL_EXCEPTION, $exception);

            throw $exception;
        }
    }

    private function processResponse(Response $response, string $className): APIResponse
    {
        $isModel = false;
        $returnObject = null;
        if($responseContentType = ($response->getHeader(Constants::CONTENT_TYPE)[0] ?? null))
        {
            $responseContentType = preg_replace('/(;.*)/', '', $responseContentType); // trim a `;` and anything after it
            $converterInstance = $this->getConverterClassInstance(strtolower($responseContentType));
            $returnObject = $converterInstance->getWrappedResponse($response, $className);
            if ($returnObject !== null && ($className == get_class($returnObject) || $this->isExpectedType($returnObject, $className)))
            {
                $isModel = true;
            }
        }

        return new APIResponse($response->getHeaders(), $response->getStatusCode(), $returnObject, $isModel);
    }

    private function isExpectedType(Model $model, string $className): bool
    {
        $implementsArray = class_implements($model);

        foreach($implementsArray as $class)
        {
            if($class === $className)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * This method is used to get a Converter class instance.
     * @param string $encodeType A string containing the API response content type.
     * @return Converter|null A Converter class instance.
     */
    public function getConverterClassInstance(string $encodeType)
    {
        switch ($encodeType)
        {
            case "application/json":
            case "text/plain":
            case "application/ld+json":
                return new JSONConverter($this);
            case "application/xml":
            case "text/xml":
                return new XMLConverter($this);
            case "multipart/form-data":
                return new FormDataConverter($this);
            case "image/png":
            case "image/jpeg":
            case "image/gif":
            case "image/tiff":
            case "image/svg+xml":
            case "image/bmp":
            case "image/webp":
            case "text/csv":
            case "text/html":
            case "text/css":
            case "text/javascript":
            case "text/calendar":
            case "application/x-download":
            case "application/zip":
            case "application/pdf":
            case "application/java-archive":
            case "application/javascript":
            case "application/octet-stream":
            case "application/xhtml+xml":
            case "application/x-bzip":
            case "application/msword":
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            case "application/gzip":
            case "application/x-httpd-php":
            case "application/vnd.ms-powerpoint":
            case "application/vnd.rar":
            case "application/x-sh":
            case "application/x-tar":
            case "application/vnd.ms-excel":
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            case "application/x-7z-compressed":
            case "audio/mpeg":
            case "audio/x-ms-wma":
            case "audio/vnd.rn-realaudio":
            case "audio/x-wav":
            case "audio/3gpp":
            case "audio/3gpp2":
            case "video/mpeg":
            case "video/mp4":
            case "video/webm":
            case "video/3gpp":
            case "video/3gpp2":
            case "font/ttf":
                return new Downloader($this);
            default:
                return null;
        }
    }

    /** @throws SDKException */
    private function getAPIUrl(): string
    {
        if(strpos($this->apiPath, Constants::HTTP) === false)
        {
            return Initializer::getInitializer()->getEnvironment()->getUrl() . $this->apiPath;
        }
        if(strpos($this->apiPath, Constants::CONTENT_API_URL) !== false)
        {
            try
            {
                return Initializer::getInitializer()->getEnvironment()->getFileUploadUrl()
                    . parse_url($this->apiPath)['path'];
            }
            catch (Throwable $ex)
            {
                $sdkEx = new SDKException(null, null, null, $ex);

                SDKLogger::severeError(Constants::INVALID_URL_ERROR, $sdkEx);

                throw $sdkEx;
            }
        }
        if(substr($this->apiPath, 0, 1) == "/")
        {
            $this->apiPath = substr($this->apiPath, 1);
        }

        return $this->apiPath;
    }

    public function isMandatoryChecker()
	{
		return $this->mandatoryChecker;
	}

	public function setMandatoryChecker($mandatoryChecker)
	{
		$this->mandatoryChecker = $mandatoryChecker;
	}

	public function getHttpMethod()
	{
		return $this->httpMethod;
	}

	public function getCategoryMethod()
	{
		return $this->categoryMethod;
	}

	public function setCategoryMethod($category)
	{
		$this->categoryMethod = $category;
    }

    public function getAPIPath()
	{
		return $this->apiPath;
	}

    private function logRequest(Request $request)
    {
        $headers = $request->getHeaders();
        $headers[Constants::AUTHORIZATION] = [Constants::CANT_DISCLOSE];

        SDKLogger::info(sprintf(
            "%s - %s = %s , %s = %s , %s = %s.",
            $request->getMethod(),
            Constants::URL,
            $request->getUri(),
            Constants::HEADERS,
            json_encode($headers, JSON_UNESCAPED_UNICODE),
            Constants::PARAMS,
            $request->getBody()
        ));
    }
}
