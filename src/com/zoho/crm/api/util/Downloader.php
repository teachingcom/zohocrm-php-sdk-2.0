<?php

namespace com\zoho\crm\api\util;

use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\util\Constants;
use com\zoho\crm\api\util\JSONConverter;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * This class is to process the download file and stream response.
 */
class Downloader extends Converter
{
    public function formRequest(array $requestOptions, $responseObject, string $pack, ?int $instanceNumber, array $memberDetail = null): array
    {
        // nothing
    }

    public function getWrappedResponse(Response $response, string $pack)
    {
        [$headers, $content] = explode("\r\n\r\n", strval($response), 2);

        $headerArray = (explode("\r\n", $headers, 50));

        $headerMap = array();

        $responseBody = array();

        foreach ($headerArray as $key)
        {
            if (strpos($key, ":") != false)
            {
                $splitArray = explode(":", $key);

                $headerMap[$splitArray[0]] = $splitArray[1];
            }
        }

        $responseBody[Constants::HEADERS] = $headerMap;

        $responseBody[Constants::CONTENT] = $content;

        return $this->getResponse($responseBody, $pack);
    }

    public function getResponse($response, $pack)
    {
        $recordJsonDetails = Initializer::$jsonDetails[$pack];

        $instance = null;

        if (array_key_exists(Constants::INTERFACE_KEY, $recordJsonDetails) && $recordJsonDetails[Constants::INTERFACE_KEY] == true) // if interface
        {
            $classes = $recordJsonDetails[Constants::CLASSES];

            foreach($classes as $className)
			{
				if(strpos($className, Constants::FILEBODYWRAPPER))
				{
					return $this->getResponse($response, $className);
				}
			}

			return $instance;
        }
        else
        {
            $instance = new $pack();

            foreach ($recordJsonDetails as $memberName => $memberJsonDetails)
            {
                $reflector = new \ReflectionClass($instance);

                $field = $reflector->getProperty($memberName);

                $field->setAccessible(true);

                $type = $memberJsonDetails[Constants::TYPE];

                $instanceValue = null;

                if (strtolower($type) == strtolower(Constants::STREAM_WRAPPER_CLASS_PATH))
                {
                    $responseHeaders = $response[Constants::HEADERS];

                    $responseContent = $response[Constants::CONTENT];

                    $contentDisposition = "";

                    if(array_key_exists(Constants::CONTENT_DISPOSITION, $responseHeaders))
                    {
                        $contentDisposition = $responseHeaders[Constants::CONTENT_DISPOSITION];

                        if ($contentDisposition == null)
                        {
                            $contentDisposition = $responseHeaders[Constants::CONTENT_DISPOSITION1];
                        }
                    }

                    $fileName = substr($contentDisposition, strrpos($contentDisposition, "'") + 1, strlen($contentDisposition));

                    if (strpos($fileName, "=") !== false)
                    {
                        $fileName = substr($fileName, strrpos($fileName, "=") + 1, strlen($fileName));

                        $fileName = str_replace(array(
                            '\'',
                            '"'
                        ), '', $fileName);
                    }

                    $fileContent = $responseContent;

                    $fileInstance = new $type($fileName, $fileContent, null);

                    $instanceValue = $fileInstance;

                    $field->setValue($instance, $instanceValue);
                }
            }
        }

        return $instance;
    }
}
