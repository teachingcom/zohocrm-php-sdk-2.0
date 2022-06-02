<?php

namespace com\zoho\crm\api\contactroles;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse, ActionHandler, RecordResponseHandler, RecordActionHandler
{
}
