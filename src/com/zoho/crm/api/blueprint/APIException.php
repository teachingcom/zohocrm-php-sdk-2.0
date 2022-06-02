<?php

namespace com\zoho\crm\api\blueprint;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse
{
}
