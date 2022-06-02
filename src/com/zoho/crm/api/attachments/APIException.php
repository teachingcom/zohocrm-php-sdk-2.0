<?php

namespace com\zoho\crm\api\attachments;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse, ActionHandler
{
}
