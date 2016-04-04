<?php

namespace Zoho\CRM\Exception\Api;

class InvalidTicketIdException extends AbstractException
{
    protected $description = 'Invalid ticket. Also check if ticket has expired.';

    public function __construct($message)
    {
        parent::__construct($message, '4834');
    }
}
