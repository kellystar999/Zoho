<?php

namespace Zoho\CRM\Core;

use Zoho\CRM\Exception\UnreadableResponseException;
use Zoho\CRM\Exception\MethodNotFoundException;

class ApiResponseParser
{
    public static function clean(Request $request, $data)
    {
        $parsed_data = self::parse($data, $request->getFormat());

        if (self::validate($parsed_data)) {
            $api_method_handler = "\\Zoho\\CRM\\Methods\\" . ucfirst($request->getMethod());
            if (class_exists($api_method_handler))
                return $api_method_handler::tidyResponse($parsed_data, $request->getModule());
            else
                throw new MethodNotFoundException("Method handler $api_method_handler not found.");
        } else {
            return null;
        }
    }

    public static function parse($data, $format)
    {
        switch ($format) {
            case ResponseFormat::XML:
                return self::parseXml($data);
                break;
            case ResponseFormat::JSON:
                return self::parseJson($data);
                break;
            default:
                break;
        }
    }

    private static function parseXml($data)
    {
        // TODO
    }

    private static function parseJson($data)
    {
        return json_decode($data, true);
    }

    private static function validate($parsed)
    {
        if ($parsed === null || !is_array($parsed)) {
            throw new UnreadableResponseException();
        }

        if (isset($parsed['response']['error'])) {
            ApiErrorHandler::handle($parsed['response']['error']);
        }

        if (isset($parsed['response']['nodata'])) {
            // It is not a fatal error, so we won't raise an exception
            return false;
        }

        return true;
    }
}
