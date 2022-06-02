<?php

namespace com\zoho\crm\api;

/**
 * The class to configure the SDK.
 */
class SDKConfig
{
    private $autoRefreshFields;
    private $pickListValidation;

    /**
     * Creates an instance of SDKConfig with the given parameters
     * @param bool $autoRefreshFields - A boolean representing autoRefreshFields
     * @param bool $pickListValidation - A boolean representing pickListValidation
     */
    public function __construct(bool $autoRefreshFields, bool $pickListValidation)
    {
        $this->autoRefreshFields = $autoRefreshFields;
        $this->pickListValidation = $pickListValidation;
    }

    /**
     * This is a getter method to get autoRefreshFields.
     */
    public function getAutoRefreshFields(): bool
    {
        return $this->autoRefreshFields;
    }

    /**
     * This is a getter method to get pickListValidation.
     */
    public function getPickListValidation(): bool
    {
        return $this->pickListValidation;
    }
}
