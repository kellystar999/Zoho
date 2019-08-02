<?php

namespace Zoho\Crm\Api\Modules;

/**
 * Potentials module handler.
 */
class Potentials extends AbstractRecordsModule
{
    /** @inheritdoc */
    protected static $primaryKey = 'POTENTIALID';

    /** @inheritdoc */
    protected static $associatedEntity = \Zoho\Crm\Entities\Potential::class;

    /** @inheritdoc */
    protected static $supportedMethods = [
        'getFields',
        'getRecordById',
        'getRecords',
        'getMyRecords',
        'searchRecords',
        'insertRecords',
        'updateRecords',
        'deleteRecords',
        'getDeletedRecordIds',
        'getRelatedRecords',
        'getSearchRecordsByPDC',
        'deleteFile',
    ];
}
