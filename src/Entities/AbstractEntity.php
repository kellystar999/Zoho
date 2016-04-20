<?php

namespace Zoho\CRM\Entities;

use Zoho\CRM\Core\BaseClassStaticHelper;
use Zoho\CRM\Exception\UnsupportedEntityPropertyException;

abstract class AbstractEntity extends BaseClassStaticHelper
{
    protected static $name;

    protected static $properties_mapping = [];

    protected $properties = [];

    public function __construct(array $data = [])
    {
        $this->properties = $data;
    }

    public static function getEntityName()
    {
        return self::getChildStaticProperty('name', self::class, function() {
            return (new \ReflectionClass(static::class))->getShortName();
        });
    }

    public function getData()
    {
        return $this->properties;
    }

    public function toArray()
    {
        $hash = [];

        // Reverse the properties keys mapping,
        // from ['clean_name' => 'ZOHO NAME'] to ['ZOHO NAME' => 'clean_name']
        $reversed_properties_mapping = array_flip(static::$properties_mapping);

        // Generate a new hashmap with the entity's properties names as keys
        foreach ($this->properties as $key => $value)
            $hash[$reversed_properties_mapping[$key]] = $value;

        return $hash;
    }

    public function __get($property)
    {
        if (array_key_exists($property, static::$properties_mapping))
            return $this->properties[static::$properties_mapping[$property]];
        else
            throw new UnsupportedEntityPropertyException($this->getEntityName(), $property);
    }

    public function __toString()
    {
        return print_r($this->toArray(), true);
    }
}
