<?php

namespace Zoho\CRM\Modules;

use Zoho\CRM\Client as ZohoClient;
use Zoho\CRM\Core\BaseClassStaticHelper;
use Zoho\CRM\Core\UrlParameters;
use Doctrine\Common\Inflector\Inflector;

abstract class AbstractModule extends BaseClassStaticHelper
{
    protected static $name;

    protected static $associated_entity;

    protected static $supported_methods = [];

    private $owner;

    protected $parameters_accumulator;

    public function __construct(ZohoClient $owner)
    {
        $this->owner = $owner;
        $this->parameters_accumulator = new UrlParameters();
    }

    public static function moduleName()
    {
        return self::getChildStaticProperty('name', function() {
            return (new \ReflectionClass(static::class))->getShortName();
        });
    }

    public static function associatedEntity()
    {
        return self::getChildStaticProperty('associated_entity', function() {
            return Inflector::singularize(self::moduleName());
        });
    }

    public static function supportedMethods()
    {
        return static::$supported_methods;
    }

    public static function supports($method)
    {
        return in_array($method, static::$supported_methods);
    }

    public function getModuleOwner()
    {
        return $this->owner;
    }

    protected function request($method, array $params = [], $pagination = false)
    {
        $params = $this->parameters_accumulator->extend($params)->toArray();
        $this->parameters_accumulator->reset();
        return $this->owner->request(self::moduleName(), $method, $params, $pagination);
    }

    public function getFields(array $params = [], callable $filter = null)
    {
        $sections = $this->request('getFields', $params);

        if (isset($filter)) {
            foreach($sections as &$section) {
                $section['FL'] = array_filter($section['FL'], $filter);
                if (empty($section['FL']))
                    unset($section['FL']);
            }
        }

        return $sections;
    }

    public function getNativeFields()
    {
        return $this->getFields([], function($field) {
            return $field['customfield'] === 'false';
        });
    }

    public function getCustomFields()
    {
        return $this->getFields([], function($field) {
            return $field['customfield'] === 'true';
        });
    }

    public function getSummaryFields()
    {
        return $this->getFields(['type' => 1]);
    }

    public function getMandatoryFields()
    {
        return $this->getFields(['type' => 2]);
    }

    public function orderBy($column, $order = 'asc')
    {
        $this->parameters_accumulator['sortColumnString'] = $column;
        $this->parameters_accumulator['sortOrderString'] = $order;
        return $this;
    }

    public function modifiedAfter($date)
    {
        if (!($date instanceof \DateTime))
            $date = new \DateTime($date);

        $this->parameters_accumulator['lastModifiedTime'] = $date->format('Y-m-d H:i:s');
        return $this;
    }

    public function selectColumns(array $columns)
    {
        $selection_str = static::moduleName() . '(' . implode(',', $columns) . ')';
        $this->parameters_accumulator['selectColumns'] = $selection_str;
        return $this;
    }
}
