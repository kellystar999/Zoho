<?php

declare(strict_types=1);

namespace Zoho\Crm\Exceptions;

class UnavailableHttpAsyncClientException extends Exception
{
    /** @var string The exception message */
    protected string $message = 'Concurrent requests cannot be used because no HTTP asynchronous client has been provided and none could be automatically discovered either.';
}
