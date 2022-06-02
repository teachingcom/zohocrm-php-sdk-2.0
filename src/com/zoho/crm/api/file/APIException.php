<?php

namespace com\zoho\crm\api\file;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ActionResponse, ActionHandler, ResponseHandler
{
}
