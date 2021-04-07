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

    final public function insertOneRow(array $data, $pk = null): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function replaceOneRow(array $data): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function batchInsertRows(array $dataList, $pk = null): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function batchReplaceRows(array $dataList): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function updateRows(array $conditions, array $modification): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function quickDeleteRowsWithSimpleConditions(array $simpleConditions): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function quickUpdateRowsWithSimpleConditions(array $simpleConditions, array $modification): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final public function deleteRows(array $conditions): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final protected function writeInto(array $data, $pk = null, bool $shouldReplace = false): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }

    final protected function batchWriteInto(array $dataList, $pk = null, bool $shouldReplace = false): ArkDatabaseQueryResult
    {
        return ArkDatabaseQueryResult::makeErrorResult("The VIEW is not modifiable.");
    }
}