<?php

namespace com\zoho\crm\api\util;

class APIException implements Model
{
    protected $status;
    protected $code;
    protected $message;
    protected $details = [];
    protected $keyModified = [];

    /**
     * The method to get the status
     */
    public function getStatus(): Choice
    {
        return $this->status;
    }

    /**
     * The method to set the value to status
     */
    public function setStatus(Choice $status): void
    {
        $this->status = $status;
        $this->keyModified['status'] = 1;
    }

    /**
     * The method to get the code
     */
    public function getCode(): Choice
    {
        return $this->code;
    }

    /**
     * The method to set the value to code
     */
    public function setCode(Choice $code): void
    {
        $this->code = $code;
        $this->keyModified['code'] = 1;
    }

    /**
     * The method to get the message
     */
    public function getMessage(): Choice
    {
        return $this->message;
    }

    /**
     * The method to set the value to message
     */
    public function setMessage(Choice $message): void
    {
        $this->message = $message;
        $this->keyModified['message'] = 1;
    }

    /**
     * The method to get the details
     * @return array A array representing the details
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * The method to set the value to details
     * @param array $details A array
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
        $this->keyModified['details'] = 1;
    }

    /**
     * The method to check if the user has modified the given key
     * @return int A int representing the modification
     */
    public function isKeyModified(string $key): ?int
    {
        return $this->keyModified[$key] ?? null;
    }

    /**
     * The method to mark the given key as modified.
     */
    public function setKeyModified(string $key, int $modification)
    {
        $this->keyModified[$key] = $modification;
    }
}
