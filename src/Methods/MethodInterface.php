<?php

namespace Zoho\CRM\Methods;

interface MethodInterface
{
    public static function tidyResponse(array $response, $module);

    public static function expectsMultipleRecords($request);
}
