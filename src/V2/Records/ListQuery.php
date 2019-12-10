<?php

namespace Zoho\Crm\V2\Records;

use Zoho\Crm\Contracts\{
    PaginatedQueryInterface,
    ResponseTransformerInterface,
    QueryPaginatorInterface,
    ResponsePageMergerInterface
};
use Zoho\Crm\Support\Helper;
use Zoho\Crm\Traits\HasPagination;
use Zoho\Crm\V2\QueryPaginator;
use Zoho\Crm\V2\CollectionPageMerger;

/**
 * A query to get a list of records.
 *
 * @see https://www.zoho.com/crm/developer/docs/api/get-records.html
 */
class ListQuery extends AbstractQuery implements PaginatedQueryInterface
{
    use HasPagination;

    /**
     * @inheritdoc
     */
    public function getUri(): string
    {
        return "$this->module?$this->urlParameters";
    }

    /**
     * @inheritdoc
     *
     * @return RecordListTransformer
     */
    public function getResponseTransformer(): ?ResponseTransformerInterface
    {
        return new RecordListTransformer();
    }

    /**
     * @inheritdoc
     *
     * @return \Zoho\Crm\V2\QueryPaginator
     */
    public function getPaginator(): QueryPaginatorInterface
    {
        return new QueryPaginator($this);
    }

    /**
     * @inheritdoc
     *
     * @return \Zoho\Crm\V2\CollectionPageMerger
     */
    public function getResponsePageMerger(): ResponsePageMergerInterface
    {
        return new CollectionPageMerger();
    }

    /**
     * Select one or more fields to retrieve.
     *
     * @param string[] $fields An array of field names
     * @return $this
     */
    public function select($fields)
    {
        $fields = is_array($fields) ? $fields : func_get_args();
        $fields = $this->normalizeSelectedFields($fields);

        $currentSelection = $this->getSelectedFields();
        $newSelection = array_unique(array_merge($currentSelection, $fields));

        return $this->param('fields', implode(',', $newSelection));
    }

    /**
     * Unselect one or more fields.
     *
     * @param string[] $fields An array of field names
     * @return $this
     */
    public function unselect($fields)
    {
        $fields = is_array($fields) ? $fields : func_get_args();
        $fields = $this->normalizeSelectedFields($fields);

        $currentSelection = $this->getSelectedFields();
        $newSelection = array_diff($currentSelection, $fields);

        return $this->param('fields', implode(',', $newSelection));
    }

    /**
     * Get the selected fields.
     *
     * @return string[]
     */
    public function getSelectedFields(): array
    {
        $selection = $this->getUrlParameter('fields');

        return $this->normalizeSelectedFields(explode(',', $selection));
    }

    /**
     * Normalize field names.
     *
     * @param string[] $fields The field names
     * @return string[]
     */
    protected function normalizeSelectedFields(array $fields): array
    {
        // Cast everything to string and trim the value
        return array_filter(array_map(function ($field) {
            return trim((string) $field);
        }, $fields));
    }

    /**
     * Check if a field is selected.
     *
     * @param string $field The field to check
     * @return bool
     */
    public function hasSelected(string $field): bool
    {
        return in_array($field, $this->getSelectedFields());
    }

    /**
     * Remove selection of fields.
     *
     * @return $this
     */
    public function unselectAll()
    {
        return $this->removeParam('fields');
    }

    /**
     * Select the creation and last modification timestamps.
     *
     * @return $this
     */
    public function selectTimestamps()
    {
        return $this->select('Created_Time', 'Modified_Time');
    }

    /**
     * Select a set of default fields which are present on all records by default.
     *
     * @return $this
     */
    public function selectDefaultFields()
    {
        return $this->selectTimestamps()->select('Created_By', 'Modified_By', 'Owner');
    }

    /**
     * Sort records by a given field, in a given direction.
     *
     * The ordering direction must be either 'asc' or 'desc'.
     *
     * @param string $field The field name
     * @param string $order (optional) The ordering direction
     * @return $this
     */
    public function sortBy(string $field, string $order = 'asc')
    {
        return $this->params([
            'sort_by' => $field,
            'sort_order' => $order
        ]);
    }

    /**
     * Sort records by a given field, in descending order.
     *
     * @param string $field The field name
     * @return $this
     */
    public function sortByDesc(string $field)
    {
        return $this->sortBy($field, 'desc');
    }

    /**
     * Sort records in ascending order.
     *
     * @return $this
     */
    public function sortAsc()
    {
        return $this->param('sort_order', 'asc');
    }

    /**
     * Sort records in descending order.
     *
     * @return $this
     */
    public function sortDesc()
    {
        return $this->param('sort_order', 'desc');
    }

    /**
     * Set the page of records to retrieve.
     *
     * @param int $page The page number
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function page(int $page)
    {
        if ($page <= 0) {
            throw new \InvalidArgumentException('Page number must be a positive non-zero integer.');
        }

        return $this->param('page', $page);
    }

    /**
     * Set the number of records to get per page.
     *
     * @param int $perPage The number of records
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function perPage(int $perPage)
    {
        $max = QueryPaginator::PAGE_MAX_SIZE;

        if ($perPage <= 0 || $perPage > $max) {
            throw new \InvalidArgumentException("\"Per page\" number must be between 1 and $max.");
        }

        return $this->param('per_page', $perPage);
    }

    /**
     * Set the minimum date for records' last modification (`Modified_Time` field).
     *
     * @param \DateTimeInterface|string|null $date A date object or a valid string
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function modifiedAfter($date)
    {
        if (is_null($date)) {
            return $this->removeHeader('If-Modified-Since');
        }

        if (! Helper::isValidDateInput($date)) {
            throw new \InvalidArgumentException('Date must implement DateTimeInterface or be a valid date string.');
        }

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $this->setHeader('If-Modified-Since', $date->format(DATE_ATOM));
    }
}
