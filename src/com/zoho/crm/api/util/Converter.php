<?php

namespace com\zoho\crm\api\util;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * This abstract class is to construct API request and response.
 */
abstract class Converter
{
    protected $commonAPIHandler;

    /**
     * Creates a Converter class instance with the CommonAPIHandler class instance.
     */
    public function __construct(CommonAPIHandler $commonAPIHandler)
    {
       $this->commonAPIHandler = $commonAPIHandler;
    }

    /**
     * This abstract method is to process the API response.
     * @param object $response A object containing the API response contents or response.
     * @param string $pack A string containing the expected method return type.
     * @return object A object representing the POJO class instance.
     */
    public abstract function getResponse($response, $pack);

    /**
     * This abstract method is to construct the API request.
     * @param array $requestOptions Array of request options to be appended with form data.
     * @param mixed $responseObject A object containing the POJO class instance.
     * @param string $pack A string containing the expected method return type.
     * @param integer|null $instanceNumber An integer containing the POJO class instance list number.
     * @param array|null $memberDetail An array containing the member Detail
     * @return array Updated `$requestOptions`.
     */
    public abstract function formRequest(array $requestOptions, $responseObject, string $pack, ?int $instanceNumber, array $memberDetail = null): array;

    /**
     * This abstract method is to process the API response.
     * @param Response $response A object containing the HttpResponse class instance.
     * @param string $pack $pack A string containing the expected method return type.
     */
    public abstract function getWrappedResponse(Response $response, string $pack);

    /**
     * This method is to validate if the input values satisfy the constraints for the respective fields.
     * @param string $className A string containing the class name.
     * @param string $memberName A string containing the member name.
     * @param array $keyDetails A array containing the key JSON details.
     * @param mixed $value A object containing the key value.
     * @param array $uniqueValuesMap A array containing the construct objects.
     * @param int|null $instanceNumber An integer containing the POJO class instance list number.
     * @throws SDKException if a problem occurs.
     */
    public function valueChecker(string $className, string $memberName, array $keyDetails, $value, array &$uniqueValuesMap, ?int $instanceNumber): bool
    {
		$detailsJO = array();

		$name = $keyDetails[Constants::NAME];

		$type  = $keyDetails[Constants::TYPE];

		$varType = gettype($value);

		$test = function ($varType, $type) { if(strtolower($varType) == strtolower($type)){return true; } return false;};

		$check = $test($varType, $type);

		if(array_key_exists($type, Constants::DATA_TYPE))
		{
			$type = Constants::DATA_TYPE[$type];

			if(is_array($value) &&  count($value) > 0 && array_key_exists(Constants::STRUCTURE_NAME, $keyDetails))
			{
				$structureName = $keyDetails[Constants::STRUCTURE_NAME];

				$index = 0;

				foreach($value as $data)
				{
					$className = get_class($data);

					$check = $test($className, $structureName);

					if(!$check)
					{
						$result = $data instanceof $structureName;

						if ($result)
						{
							$check = true;
						}
					}

					if(!$check)
					{
						$instanceNumber = $index;

						$type = Constants::ARRAY_KEY . "(" . $structureName . ")";

						$varType = Constants::ARRAY_KEY . "(" . $className . ")";

						break;
					}

					$index ++;
				}
			}
			else
			{
				$check = $test($varType, $type);

				if(!$check)
				{
					$result = $value instanceof $type;

					if ($result)
					{
						$check = true;
					}
				}
			}
		}

		if(strtolower($varType) == strtolower(Constants::OBJECT) || strtolower($type) == strtolower(Constants::OBJECT))
		{
			if(strtolower($type) == strtolower(Constants::OBJECT))
			{
				$check = true;
			}
			else
			{
				$className1 = get_class($value);

				$check = $test($className1, $type);

				if(!$check)
				{
					$result = $value instanceof $type;

					if ($result)
					{
						$check = true;
					}
				}

				$varType = $className1;
			}
		}

		if (!$check && $value != null)
        {
            $detailsJO[Constants::FIELD] = $memberName;

            $detailsJO[Constants::CLASS_KEY] =  $className;

            $detailsJO[Constants::INDEX] = $instanceNumber;

			$detailsJO[Constants::EXPECTED_TYPE] = $type;

			$detailsJO[Constants::GIVEN_TYPE] = $varType;

			throw new SDKException(Constants::TYPE_ERROR, null, $detailsJO, null);
        }

		if(array_key_exists(Constants::VALUES, $keyDetails) && (!array_key_exists(Constants::PICKLIST, $keyDetails) || ($keyDetails[Constants::PICKLIST] && Initializer::getInitializer()->getSDKConfig()->getPickListValidation())))
		{
			$valuesJA = $keyDetails[Constants::VALUES];

			if($value instanceof Choice)
			{
				$choice = $value;

				$value = $choice->getValue();
			}

			if(!in_array($value, $valuesJA))
			{
			    $detailsJO[Constants::FIELD] =  $memberName;

			    $detailsJO[Constants::CLASS_KEY] = $className;

				$detailsJO[Constants::INDEX] = $instanceNumber;

				$detailsJO[Constants::GIVEN_VALUE] = $value;

			    $detailsJO[Constants::ACCEPTED_VALUES] =  $valuesJA;

				throw new SDKException(Constants::UNACCEPTED_VALUES_ERROR, null, $detailsJO, null);
			}
		}

		if(array_key_exists(Constants::UNIQUE, $keyDetails))
		{
			$valuesArray = null;

			if(array_key_exists($name, $uniqueValuesMap))
			{
				$valuesArray = $uniqueValuesMap[$name];

				if($valuesArray != null && in_array($value, $valuesArray))
				{
					$detailsJO[Constants::FIELD] =  $memberName;

					$detailsJO[Constants::CLASS_KEY] =  $className;

					$detailsJO[Constants::FIRST_INDEX] = array_search($value, $valuesArray);

					$detailsJO[Constants::NEXT_INDEX] =  $instanceNumber;

					throw new SDKException(Constants::UNIQUE_KEY_ERROR, null , $detailsJO, null);
				}
			}
			else
			{
				if($valuesArray == null)
				{
					$valuesArray = array();
				}

				$valuesArray[] = $value;

				$uniqueValuesMap[$name] = $valuesArray;
			}
		}

		if(array_key_exists(Constants::MIN_LENGTH, $keyDetails) || array_key_exists(Constants::MAX_LENGTH, $keyDetails))
		{
			$count = 0;

			if(is_array($value))
			{
				$count = count($value);
			}
			else
			{
				$count = strlen($value);
			}

		    if(array_key_exists(Constants::MAX_LENGTH, $keyDetails) && $count > $keyDetails[Constants::MAX_LENGTH])
			{
			    $detailsJO[Constants::FIELD] =  $memberName;

			    $detailsJO[Constants::CLASS_KEY] =  $className;

			    $detailsJO[Constants::GIVEN_LENGTH] =  $count;

			    $detailsJO[Constants::MAXIMUM_LENGTH] =  $keyDetails[Constants::MAX_LENGTH];

			    throw new SDKException(Constants::MAXIMUM_LENGTH_ERROR, null, $detailsJO, null);
			}

			if(array_key_exists(Constants::MIN_LENGTH, $keyDetails) && $count < $keyDetails[Constants::MIN_LENGTH])
			{
			    $detailsJO[Constants::FIELD] =  $memberName;

			    $detailsJO[Constants::CLASS_KEY] =  $className;

			    $detailsJO[Constants::GIVEN_LENGTH] =  $count;

			    $detailsJO[Constants::MINIMUM_LENGTH] = $keyDetails[Constants::MIN_LENGTH];

				throw new SDKException(Constants::MINIMUM_LENGTH_ERROR, null, $detailsJO, null);
			}
		}

		if(array_key_exists(Constants::REGEX, $keyDetails) && !preg_match($keyDetails[Constants::REGEX], $value))
		{
		    $detailsJO[Constants::FIELD] =  $memberName;

			$detailsJO[Constants::CLASS_KEY] =  $className;

			$detailsJO[Constants::INSTANCE_NUMBER] = $instanceNumber;

			throw new SDKException(Constants::REGEX_MISMATCH_ERROR, null, $detailsJO, null);
        }

        return true;
	}

	/**
	 * This method to get the module field JSON details file path.
	 */
	public function getEncodedFileName(): string
    {
		$fileName = Initializer::getInitializer()->getUser()->getEmail();

		$fileName = explode("@", $fileName)[0] . Initializer::getInitializer()->getEnvironment()->getUrl();

		$input = unpack('C*', utf8_encode($fileName));

		$str = base64_encode(implode(array_map("chr", $input)));

		$path = Initializer::getInitializer()->getResourcePath() . DIRECTORY_SEPARATOR . Constants::FIELD_DETAILS_DIRECTORY;

		return $path . DIRECTORY_SEPARATOR . $str . ".json";
	}
}
