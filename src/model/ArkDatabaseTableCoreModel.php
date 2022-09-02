<?php


namespace sinri\ark\database\model;


use PDOStatement;
use sinri\ark\database\exception\ArkPDODatabaseQueryError;
use sinri\ark\database\exception\ArkPDOMatrixRowsLengthDifferError;
use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\model\query\ArkDatabaseQueryResult;
use sinri\ark\database\model\query\ArkDatabaseSelectTableQuery;

/**
 * Class ArkDatabaseTableCoreModel
 * @package sinri\ark\database\model
 * @since 1.7.0
 */
abstract class ArkDatabaseTableCoreModel extends ArkDatabaseTableReaderModel
{
    /**
     * @param array $data
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     * @since 2.0
     */
    public function insertOneRow(array $data, $pk = null): ArkDatabaseQueryResult
    {
        return $this->writeInto("INSERT", $data, $pk);
    }

    /**
     * @param array $data
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     * @since 2.1.2
     */
    public function insertIgnoreOneRow(array $data, $pk = null): ArkDatabaseQueryResult
    {
        return $this->writeInto("INSERT IGNORE", $data, $pk);
    }

    /**
     * @param array $data
     * @return ArkDatabaseQueryResult
     * @since 2.0
     */
    public function replaceOneRow(array $data): ArkDatabaseQueryResult
    {
        return $this->writeInto("REPLACE", $data);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @param array $modification
     * @return ArkDatabaseQueryResult
     * @since 2.0
     */
    public function updateRows(array $conditions, array $modification): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $condition_sql = ArkSQLCondition::generateConditionSQLComponent($conditions);
            $data_sql = $this->buildRowValuesForUpdate($modification);
            $table = $this->getTableExpression();
            $sql = "UPDATE {$table} SET {$data_sql} WHERE {$condition_sql}";
            $result->setSql($sql);
            $afx = $this->db()->exec($sql);
            if ($afx === false) {
                throw new ArkPDODatabaseQueryError($sql, $this->db()->getPDOErrorDescription());
            }
            $result->setAffectedRowsCount($afx);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
        } catch (ArkPDODatabaseQueryError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "DatabaseOperationError: " . $e->getMessage()
                . " Code: " . $e->getCode()
            );
        } catch (ArkPDOSQLBuilderError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError("ArkPDOConditionError: " . $e->getMessage() . ' SQL: ' . $e->getWrongSQLPiece());
        }
        return $result;
    }

    /**
     * @param array $simpleConditions ['field_1'=>3,'field_2'=>[3,4]]
     * @param array $modification
     * @return ArkDatabaseQueryResult
     * @since 2.0.6
     */
    public function quickUpdateRowsWithSimpleConditions(array $simpleConditions, array $modification): ArkDatabaseQueryResult
    {
        $conditions = [];
        foreach ($simpleConditions as $fieldName => $value) {
            if (is_array($value)) {
                $conditions[] = ArkSQLCondition::for($fieldName)->in($value);
            } elseif ($value === null) {
                $conditions[] = ArkSQLCondition::for($fieldName)->isNull();
            } else {
                $conditions[] = ArkSQLCondition::for($fieldName)->equal($value);
            }
        }
        return $this->updateRows($conditions, $modification);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkDatabaseQueryResult
     * @since 2.0
     */
    public function deleteRows(array $conditions): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $condition_sql = ArkSQLCondition::generateConditionSQLComponent($conditions);
            $table = $this->getTableExpression();
            $sql = "DELETE FROM {$table} WHERE {$condition_sql}";
            $afx = $this->db()->exec($sql);
            if ($afx === false) {
                throw new ArkPDODatabaseQueryError($sql, $this->db()->getPDOErrorDescription());
            }
            $result->setAffectedRowsCount($afx);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
        } catch (ArkPDODatabaseQueryError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "DatabaseOperationError: " . $e->getMessage()
                . " Code: " . $e->getCode()
            );
        } catch (ArkPDOSQLBuilderError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError("ArkPDOSQLBuilderError: " . $e->getMessage() . ' SQL: ' . $e->getWrongSQLPiece());

        }
        return $result;
    }

    /**
     * @param array $simpleConditions ['field_1'=>3,'field_2'=>[3,4]]
     * @return ArkDatabaseQueryResult
     * @since 2.0.6
     */
    public function quickDeleteRowsWithSimpleConditions(array $simpleConditions): ArkDatabaseQueryResult
    {
        $conditions = [];
        foreach ($simpleConditions as $fieldName => $value) {
            if (is_array($value)) {
                $conditions[] = ArkSQLCondition::for($fieldName)->in($value);
            } elseif ($value === null) {
                $conditions[] = ArkSQLCondition::for($fieldName)->isNull();
            } else {
                $conditions[] = ArkSQLCondition::for($fieldName)->equal($value);
            }
        }
        return $this->deleteRows($conditions);
    }

    /**
     * @param array[] $dataList
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     * @since 2.0
     */
    public function batchInsertRows(array $dataList, $pk = null): ArkDatabaseQueryResult
    {
        return $this->batchWriteInto("INSERT", $dataList, $pk);
    }

    /**
     * @param array $dataList
     * @param $pk
     * @return ArkDatabaseQueryResult
     * @since 2.1.1
     */
    public function batchInsertIgnoreRows(array $dataList, $pk = null): ArkDatabaseQueryResult
    {
        return $this->batchWriteInto("INSERT IGNORE", $dataList, $pk);
    }

    /**
     * @param array[] $dataList
     * @return ArkDatabaseQueryResult
     * @since 2.0
     */
    public function batchReplaceRows(array $dataList): ArkDatabaseQueryResult
    {
        return $this->batchWriteInto("REPLACE", $dataList);
    }

    /**
     * @param string $method INSERT, INSERT IGNORE, REPLACE
     * @param array $data
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     * @since 2.1.1 changed parameters to use `method`
     */
    protected function writeInto(string $method, array $data, $pk = null): ArkDatabaseQueryResult
    {
        $table = $this->getTableExpression();
        $values = $this->buildRowValuesForWrite($data, $fields);
        $result = new ArkDatabaseQueryResult();

        try {
            $sql = $method . " INTO {$table} ({$fields}) VALUES ({$values})";
            $result->setSql($sql);
            $afx = $this->db()->insert($sql, $pk);
            if ($afx === false) {
                throw new ArkPDODatabaseQueryError($sql, $this->db()->getPDOErrorDescription());
            }
            $result->setLastInsertedID($afx);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
        } catch (ArkPDODatabaseQueryError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "DatabaseOperationError: " . $e->getMessage()
                . " Code: " . $e->getCode()
            );
        }
        return $result;
    }

    /**
     * @param array $valuesForOneRow [field_name=>value], NULL for NULL.
     * @param string $fields as output
     * @return string
     * @since 1.7.4
     */
    protected function buildRowValuesForWrite(array $valuesForOneRow, &$fields = null): string
    {
        $sql = [];
        $fields = [];
        foreach ($valuesForOneRow as $key => $value) {
            $fields[] = "`{$key}`";
            if ($value === null) {
                $part = "NULL";
            } else {
                $part = $this->db()->quote($value);
            }
            $sql[] = $part;
        }
        $fields = implode(",", $fields);
        return implode(",", $sql);
    }

    /**
     * @param array $valuesForOneRow [field_name=>value], NULL for NULL.
     * @return string
     * @since 1.7.4
     */
    protected function buildRowValuesForUpdate(array $valuesForOneRow): string
    {
        $sql = [];
        foreach ($valuesForOneRow as $key => $value) {
            if ($value === null) {
                $part = "`{$key}`=NULL";
            } else {
                $part = "`{$key}`=" . $this->db()->quote($value);
            }
            $sql[] = $part;
        }
        return implode(",", $sql);
    }

    /**
     * @param string $method INSERT, INSERT IGNORE, REPLACE
     * @param array[] $dataList
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     * @since 2.1.1 change parameters to use `method`
     */
    protected function batchWriteInto(string $method, array $dataList, $pk = null): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $fields = [];
            $values = [];

            foreach ($dataList[0] as $key => $value) {
                $fields[] = "`{$key}`";
            }
            $expectedFieldsCount = count($fields);
            foreach ($dataList as $data) {
                $x = "(" . $this->buildRowValuesForWrite($data) . ")";
                if (count($data) !== $expectedFieldsCount) {
                    throw new ArkPDOMatrixRowsLengthDifferError($expectedFieldsCount, $x);
                }
                $values[] = $x;
            }
            $fields = implode(",", $fields);
            $values = implode(",", $values);
            $table = $this->getTableExpression();
            $sql = $method . " INTO {$table} ({$fields}) VALUES {$values}";
            $result->setSql($sql);

            $afx = $this->db()->insert($sql, $pk);
            if ($afx === false) {
                throw new ArkPDODatabaseQueryError($sql, $this->db()->getPDOErrorDescription());
            }

            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
            $result->setLastInsertedID($afx);
        } catch (ArkPDODatabaseQueryError $exception) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "DatabaseOperationError: " . $exception->getMessage()
                . " Code: " . $exception->getCode()
            );
        } catch (ArkPDOMatrixRowsLengthDifferError $exception) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "LengthDiffersInMatrixError: " . $exception->getMessage()
                . " SQL: " . $exception->getWrongSQLPiece()
            );
        }
        return $result;
    }

    /**
     * @param ArkDatabaseSelectTableQuery $selection
     * @param string[] $fields
     * @return ArkDatabaseQueryResult
     *
     * @since 2.0.20
     */
    public function insert_into_select(ArkDatabaseSelectTableQuery $selection, array $fields = [])
    {
        return $this->write_into_select('INSERT', $selection, $fields);
    }

    /**
     * @param ArkDatabaseSelectTableQuery $selection
     * @param array $fields
     * @return ArkDatabaseQueryResult
     * @since 2.1.1
     */
    public function insert_ignore_into_select(ArkDatabaseSelectTableQuery $selection, array $fields = [])
    {
        return $this->write_into_select('INSERT IGNORE', $selection, $fields);
    }

    /**
     * @param ArkDatabaseSelectTableQuery $selection
     * @param string[] $fields
     * @return ArkDatabaseQueryResult
     *
     * @since 2.0.20
     */
    public function replace_into_select(ArkDatabaseSelectTableQuery $selection, array $fields = [])
    {
        return $this->write_into_select('REPLACE', $selection, $fields);
    }

    /**
     * @param string $method INSERT|REPLACE
     * @param ArkDatabaseSelectTableQuery $selection
     * @param string[] $fields
     * @return ArkDatabaseQueryResult
     *
     * @since 2.0.20
     */
    protected function write_into_select($method, ArkDatabaseSelectTableQuery $selection, array $fields = [])
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $sql = $method . ' INTO ' . $this->getTableExpression() . ' ';
            if (count($fields) > 0) {
                $sql .= 'VALUES (' . implode(',', $fields) . ') ';
            }
            $sql .= $selection->generateSQL();

            $result->setSql($sql);

            $afx = $this->db()->insert($sql);
            if ($afx === false) {
                throw new ArkPDODatabaseQueryError($sql, $this->db()->getPDOErrorDescription());
            }

            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
            $result->setLastInsertedID($afx);

//            $done = $this->db()->safeExecute($sql, [], $statement);
//            if ($done) {
//                $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
//                $result->setLastInsertedID($this->db()->getLastInsertID());
//                $result->setAffectedRowsCount($this->db()->getAffectedRowCount($statement));
//            } else {
//                $result->setError($this->db()->getPDOErrorDescription());
//                $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
//            }
        } catch (ArkPDOSQLBuilderError $e) {
            $result->setError($e->getMessage());
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
        } catch (ArkPDODatabaseQueryError $e) {
            $result->setError($e->getMessage());
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
        }
        return $result;
    }

    /**
     * @param array[] $dataList
     * @param array $duplicateModification
     * @return ArkDatabaseQueryResult
     * @since 2.0.30
     * @see https://dev.mysql.com/doc/refman/8.0/en/insert-on-duplicate.html
     */
    public function insertOnDuplicateKeyUpdate($dataList, $duplicateModification)
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $fields = [];
            $values = [];

            foreach ($dataList[0] as $key => $value) {
                $fields[] = "`{$key}`";
            }
            $expectedFieldsCount = count($fields);
            foreach ($dataList as $data) {
                $x = "(" . $this->buildRowValuesForWrite($data) . ")";
                if (count($data) !== $expectedFieldsCount) {
                    throw new ArkPDOMatrixRowsLengthDifferError($expectedFieldsCount, $x);
                }
                $values[] = $x;
            }
            $fields = implode(",", $fields);
            $values = implode(",", $values);
            $table = $this->getTableExpression();
            $sql = "INSERT INTO {$table} ({$fields}) VALUES {$values} ON DUPLICATE KEY UPDATE ";

            $duplicateModificationPairs = [];
            foreach ($duplicateModification as $k => $v) {
                $duplicateModificationPairs[] = ($k . ' = ' . $v);
            }
            $sql .= implode(",", $duplicateModificationPairs);
            $result->setSql($sql);

            $statement = new PDOStatement();
            $afx = $this->db()->safeExecute($sql, [], $statement);
            if ($afx === false) {
                throw new ArkPDODatabaseQueryError($sql, $this->db()->getPDOErrorDescription());
            }

            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
            $result->setLastInsertedID($this->db()->getLastInsertID());
            $result->setAffectedRowsCount($statement->rowCount());
        } catch (ArkPDODatabaseQueryError $exception) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "DatabaseOperationError: " . $exception->getMessage()
                . " Code: " . $exception->getCode()
            );
        } catch (ArkPDOMatrixRowsLengthDifferError $exception) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                "LengthDiffersInMatrixError: " . $exception->getMessage()
                . " SQL: " . $exception->getWrongSQLPiece()
            );
        }
        return $result;
    }

}