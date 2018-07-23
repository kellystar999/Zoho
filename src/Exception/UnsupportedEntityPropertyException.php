<?php

namespace Zoho\Crm\Exception;

class UnsupportedEntityPropertyException extends \Exception
{
    public function __construct($entity, $property)
    {
        parent::__construct("Entity $entity does not have a $property property.");
    }
}
