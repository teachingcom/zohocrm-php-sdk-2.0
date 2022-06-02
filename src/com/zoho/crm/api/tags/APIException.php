<?php

namespace com\zoho\crm\api\tags;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse, ActionHandler, RecordActionResponse, RecordActionHandler, CountHandler
{
}
