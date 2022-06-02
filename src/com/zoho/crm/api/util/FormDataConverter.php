<?php

namespace com\zoho\crm\api\util;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * This class is to process the upload file and stream.
 */
class FormDataConverter extends Converter
{
    private $_uniqueValuesMap = [];

    /**
     * @throws SDKException
     * @throws ReflectionException
     */
    public function formRequest(array $requestOptions, $responseObject, string $pack, ?int $instanceNumber, array $memberDetail = null): array
    {
        $classDetail = Initializer::$jsonDetails[$pack];

        $reflector = new ReflectionClass($responseObject);

        $params = [];

        foreach ($classDetail as $memberName => $memberDetail)
        {
            $modification = null;

            if (($memberDetail[Constants::READ_ONLY] ?? false) || ! array_key_exists(Constants::NAME, $memberDetail))
            {
                continue;
            }

            try
            {
                $modification = $reflector->getMethod(Constants::IS_KEY_MODIFIED)->invoke($responseObject, $memberDetail[Constants::NAME]);
            }
            catch (Throwable $ex)
            {
                throw new SDKException(Constants::EXCEPTION_IS_KEY_MODIFIED, null, null, $ex);
            }

            // check required
            if (!$modification && $memberDetail[Constants::REQUIRED] ?? false)
            {
                throw new SDKException(Constants::MANDATORY_VALUE_ERROR, Constants::MANDATORY_KEY_ERROR . $memberName);
            }

            $field = $reflector->getProperty($memberName);

            $field->setAccessible(true);

            $fieldValue = $field->getValue($responseObject);

            if ($modification && $fieldValue != null && $this->valueChecker(get_class($responseObject), $memberName, $memberDetail, $fieldValue, $this->_uniqueValuesMap, $instanceNumber))
            {
                $name = $memberDetail[Constants::NAME];
                $type = $memberDetail[Constants::TYPE];
                $filename = null;
                if ($type == Constants::LIST_NAMESPACE)
                {
                    $contents = $this->setJSONArray($fieldValue, $memberDetail);
                }
                else if ($type == Constants::MAP_NAMESPACE)
                {
                    $contents = $this->setJSONObject($fieldValue, $memberDetail);
                }
                else if (array_key_exists(Constants::STRUCTURE_NAME, $memberDetail))
                {
                    $contents = $this->formRequest($fieldValue, $memberDetail[Constants::STRUCTURE_NAME], 1, $memberDetail);
                }
                else if ($fieldValue instanceof StreamWrapper)
                {
                    $contents = $fieldValue->getStream();
                    $filename = $fieldValue->getName();
                }
                else
                {
                    $contents = $fieldValue;
                }

                $requestOptions['multipart'][] = array_filter(compact('name', 'contents', 'filename'));
            }
        }

        return $requestOptions;
    }

    public function setJSONObject($fieldValue, $memberDetail)
    {
        $jsonObject = [];

        if ($memberDetail == null)
        {
            foreach ($fieldValue as $key => $value)
            {
                $jsonObject[$key] = $this->redirectorForObjectToJSON($value);
            }
        }
        else
        {
            $keysDetail = $memberDetail[Constants::KEYS];

            foreach ($keysDetail as $keyDetail)
            {
                $keyName = $keyDetail[Constants::NAME];

                $type = $keyDetail[Constants::TYPE];

                $keyValue = null;

                if (array_key_exists($keyName, $fieldValue) && $fieldValue[$keyName] != null)
                {
                    if (array_key_exists(Constants::STRUCTURE_NAME, $keyDetail))
                    {
                        $keyValue = $this->formRequest($request, $fieldValue[$keyName], $keyDetail[Constants::STRUCTURE_NAME], 1, $memberDetail);
                    }
                    else
                    {
                        $keyValue = $this->redirectorForObjectToJSON($fieldValue[$keyName]);
                    }

                    $varType = gettype($keyValue);

                    if (in_array($varType, Constants::PRIMITIVE_TYPES))
                    {
                        $test = strcasecmp($varType, $type);

                        if ($test)
                        {
                            throw new SDKException(Constants::DATATYPE_VALIDATE, $keyName . " Expected datatype {$type}");
                        }
                    }

                    $jsonObject[$keyName] = $keyValue;
                }
            }
        }

        return $jsonObject;
    }

    public function setJSONArray($requestObjects, $memberDetail)
    {
        $jsonArray = [];

        if ($memberDetail == null)
        {
            foreach ($requestObjects as $request)
            {
                $jsonArray[] = $this->redirectorForObjectToJSON($request);
            }
        }
        else
        {
            if (array_key_exists(Constants::STRUCTURE_NAME, $memberDetail))
            {
                $instanceCount = 0;

                $pack = $memberDetail[Constants::STRUCTURE_NAME];

                foreach ($requestObjects as $request)
                {
                    $jsonArray[] = $this->formRequest($request, $request, $pack, ++$instanceCount, $memberDetail);
                }
            }
            else
            {
                foreach ($requestObjects as $request)
                {
                    $jsonArray[] = $this->redirectorForObjectToJSON($request);
                }
            }
        }

        return $jsonArray;
    }

    public function redirectorForObjectToJSON($request)
    {
        $type = gettype($request);

        if ($type == Constants::ARRAY_KEY)
        {
            foreach (array_keys($request) as $key)
            {
                if (gettype($key) == strtolower(Constants::STRING_NAMESPACE))
                {
                    $type = strtolower(Constants::MAP_NAMESPACE);
                }

                break;
            }

            if ($type == strtolower(Constants::MAP_NAMESPACE))
            {
                return $this->setJSONObject($request, null);
            }
            else
            {
                return $this->setJSONArray($request, null);
            }
        }
        else
        {
            return $request;
        }
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
