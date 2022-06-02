<?php

namespace com\zoho\crm\api\notification;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ActionResponse, ActionHandler, ResponseHandler
{
}
