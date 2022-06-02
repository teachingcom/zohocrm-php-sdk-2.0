<?php

namespace com\zoho\crm\api\currencies;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse, ActionHandler, BaseCurrencyActionHandler
{
}
