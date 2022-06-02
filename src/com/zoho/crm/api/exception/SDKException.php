<?php

namespace com\zoho\crm\api\exception;

use Exception;
use Throwable;

/**
 * This class is the common SDKException object. This stands as a POJO for the SDKException thrown.
 */
class SDKException extends Exception
{
    private $_code;
    private $_details;

    /**
     * Creates an SDKException class instance with the specified parameters.
     * @param string|null $code A string containing the Exception error code.
     * @param string|null $message A string containing the Exception error message.
     * @param array|null $details A JSON Object containing the error response.
     * @param Throwable|null $cause A Throwable class instance.
     */
    public function __construct(string $code = null, string $message = null, array $details = null, Throwable $cause = null)
    {
        $this->_code = $code;
        $this->_details = $details;

        if (!$message && $cause) {
            $message = $cause->getMessage();
        }
        parent::__construct($message, 0, $cause);
    }

    /**
     * This is a getter method to get Exception error code.
     */
    public function getErrorCode(): string
    {
        return $this->_code;
    }

    /**
     * This is a getter method to get error response JSONObject.
     * @return array|null A JSON Object representing the error response.
     */
    public function getDetails()
    {
        return $this->_details;
    }

    public function __toString()
    {
        $returnMsg = get_class($this) . " Caused by : ";

        if($this->message == null && $this->_details != null)
        {
            $this->message = json_encode($this->_details, true);
        }

        if ($this->_code != null)
        {
            return "{$returnMsg}{$this->_code} - {$this->message}";
        }

        return $returnMsg . $this->message;
    }
}
