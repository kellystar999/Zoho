<?php

namespace Zoho\CRM\Exception\Api;

class RateLimitExceededException extends AbstractException
{
    protected $description = 'API call cannot be completed as you have exceeded the "rate limit".';

    public function __construct($message)
    {
        parent::__construct($message, '4820');
    }
}
