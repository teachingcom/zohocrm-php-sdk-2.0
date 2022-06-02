<?php

namespace com\zoho\crm\api\bulkwrite;

use com\zoho\crm\api\util\APIException as UtilAPIException;
use com\zoho\crm\api\util\Choice;

class APIException extends UtilAPIException implements ActionResponse, ResponseWrapper, ResponseHandler
{
    private $errorMessage;
    private $errorCode;
    private $xError;
    private $info;
    private $xInfo;
    private $httpStatus;

    /**
     * The method to get the errorMessage
     */
    public function getErrorMessage(): Choice
    {
        return $this->errorMessage;
    }

    /**
     * The method to set the value to errorMessage
     */
    public function setErrorMessage(Choice $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
        $this->keyModified['ERROR_MESSAGE'] = 1;
    }

    /**
     * The method to get the errorCode
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * The method to set the value to errorCode
     */
    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
        $this->keyModified['ERROR_CODE'] = 1;
    }

    /**
     * The method to get the xError
     */
    public function getXError(): Choice
    {
        return $this->xError;
    }

    /**
     * The method to set the value to xError
     */
    public function setXError(Choice $xError): void
    {
        $this->xError = $xError;
        $this->keyModified['x-error'] = 1;
    }

    /**
     * The method to get the info
     */
    public function getInfo(): Choice
    {
        return $this->info;
    }

    /**
     * The method to set the value to info
     */
    public function setInfo(Choice $info): void
    {
        $this->info = $info;
        $this->keyModified['info'] = 1;
    }

    /**
     * The method to get the xInfo
     */
    public function getXInfo(): Choice
    {
        return $this->xInfo;
    }

    /**
     * The method to set the value to xInfo
     */
    public function setXInfo(Choice $xInfo): void
    {
        $this->xInfo = $xInfo;
        $this->keyModified['x-info'] = 1;
    }

    /**
     * The method to get the httpStatus
     */
    public function getHttpStatus(): string
    {
        return $this->httpStatus;
    }

    /**
     * The method to set the value to httpStatus
     */
    public function setHttpStatus(string $httpStatus): void
    {
        $this->httpStatus = $httpStatus;
        $this->keyModified['http_status'] = 1;
    }
}
