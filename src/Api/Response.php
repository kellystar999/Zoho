<?php

namespace Zoho\Crm\Api;

class Response
{
    private $type;

    private $query;

    private $raw_content;

    private $content;

    private $has_multiple_records;

    public function __construct(Query $query, $content, $raw_content)
    {
        $this->query = $query;
        $this->raw_content = $raw_content;
        $this->content = $content;
        $method_class = \Zoho\Crm\getMethodClassName($this->query->getMethod());
        $this->type = $method_class::getResponseDataType();
        $this->has_multiple_records = $method_class::expectsMultipleRecords($this->query);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getRawContent()
    {
        return $this->raw_content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function isEmpty()
    {
        return $this->content === null || empty($this->content);
    }

    public function hasContent()
    {
        return ! $this->isEmpty();
    }

    public function containsRecords()
    {
        return $this->type === ResponseDataType::RECORDS;
    }

    public function hasSingleRecord()
    {
        return !$this->has_multiple_records;
    }

    public function hasMultipleRecords()
    {
        return $this->has_multiple_records;
    }

    public function isConvertibleToEntity()
    {
        return $this->containsRecords()
            || $this->query->getMethod() === 'getUsers';
    }
}
