<?php

namespace com\zoho\crm\api\sharerecords;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse, ActionHandler, DeleteActionResponse, DeleteActionHandler
{
}
