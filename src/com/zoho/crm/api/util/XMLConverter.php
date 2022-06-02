<?php
namespace com\zoho\crm\api\util;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * This class processes the API response object to the POJO object and POJO object to an XML object.
 */
class XMLConverter extends Converter
{
    public function __construct($commonAPIHandler)
    {
        parent::__construct($commonAPIHandler);
    }

    public function formRequest(array $requestOptions, $responseObject, string $pack, ?int $instanceNumber, array $memberDetail = null): array
    {
        // nothing
    }

    public function getWrappedResponse(Response $response, string $pack)
    {
        return $this->getResponse($response, $pack);
    }

    public function getResponse($response, $pack)
    {
        return null;
    }
}
