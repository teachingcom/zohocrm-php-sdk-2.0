<?php

namespace com\zoho\crm\api\record;

use com\zoho\crm\api\util\APIException as UtilAPIException;

class APIException extends UtilAPIException implements ResponseHandler, ActionResponse, ActionHandler, DeletedRecordsHandler, ConvertActionResponse, ConvertActionHandler, DownloadHandler, FileHandler, MassUpdateActionResponse, MassUpdateActionHandler, MassUpdateResponse, MassUpdateResponseHandler
{
}
