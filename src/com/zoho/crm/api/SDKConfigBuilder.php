<?php

namespace com\zoho\crm\api;

/**
 * The Builder class to build SDKConfig
 */
class SDKConfigBuilder
{
    private $autoRefreshFields = false;
    private $pickListValidation = true;

    /**
     * This is a setter method to set autoRefreshFields.
     */
    public function autoRefreshFields(bool $autoRefreshFields): SDKConfigBuilder
    {
        $this->autoRefreshFields = $autoRefreshFields;

        return $this;
    }

    /**
     * This is a setter method to set pickListValidation.
     */
    public function pickListValidation(bool $pickListValidation): SDKConfigBuilder
    {
        $this->pickListValidation = $pickListValidation;

        return $this;
    }

    /**
     * The method to build the SDKConfig instance
     */
    public function build(): SDKConfig
    {
        return new SDKConfig($this->autoRefreshFields, $this->pickListValidation);
    }
}
