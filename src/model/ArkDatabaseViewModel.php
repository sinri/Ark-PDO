<?php


namespace sinri\ark\database\model;


use sinri\ark\database\model\query\ArkDatabaseQueryResult;

/**
 * Class ArkDatabaseViewModel
 * @package sinri\ark\database\model
 * @since 2.0.10
 */
abstract class ArkDatabaseViewModel extends ArkDatabaseTableModel
{

    final public function insertOneRow(array $data, $pk = null)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function replaceOneRow(array $data)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function batchInsertRows(array $dataList, $pk = null)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function batchReplaceRows(array $dataList)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function updateRows(array $conditions, array $modification)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function quickDeleteRowsWithSimpleConditions(array $simpleConditions)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function quickUpdateRowsWithSimpleConditions(array $simpleConditions, array $modification)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function deleteRows(array $conditions)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final protected function writeInto(array $data, $pk = null, bool $shouldReplace = false)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final protected function batchWriteInto(array $dataList, $pk = null, bool $shouldReplace = false)
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }
}