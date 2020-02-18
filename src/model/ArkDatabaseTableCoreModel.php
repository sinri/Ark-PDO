<?php


namespace sinri\ark\database\model;


use Exception;
use sinri\ark\database\model\query\ArkDatabaseQueryResult;
use sinri\ark\database\model\query\ArkDatabaseSelectTableQuery;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkDatabaseTableCoreModel
 * @package sinri\ark\database\model
 * @since 1.7.0
 */
abstract class ArkDatabaseTableCoreModel
{

    /**
     * @return false|string
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @return ArkDatabaseSelectTableQuery
     * @since 2.0
     */
    public function selectInTable()
    {
        return new ArkDatabaseSelectTableQuery($this);
    }

    /**
     * @param array $data
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     */
    public function insertOneRow(array $data, $pk = null)
    {
        return $this->writeInto($data, $pk, false);
    }

    /**
     * @param array $data
     * @param null|string $pk
     * @param bool $shouldReplace
     * @return ArkDatabaseQueryResult
     */
    protected function writeInto(array $data, $pk = null, bool $shouldReplace = false)
    {
        $table = $this->getTableExpressForSQL();
        $values = $this->buildRowValuesForWrite($data, $fields);
        $result = new ArkDatabaseQueryResult();

        try {
            $sql = ($shouldReplace ? 'REPLACE' : 'INSERT') . " INTO {$table} ({$fields}) VALUES ({$values})";
            $result->setSql($sql);
            $afx = $this->db()->insert($sql, $pk);
            if ($afx === false) {
                throw new Exception("Cannot write into table");
            }
            $result->setLastInsertedID($afx);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
        } catch (Exception $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError("Exception: " . $e->getMessage() . "; PDO Last Error: " . $this->db()->getPDOErrorDescription());
        }
        return $result;
    }

    /**
     * @return string
     * @since 1.6.2
     */
    public function getTableExpressForSQL()
    {
        $e = ($this->mappingSchemeName() === null ? "" : '`' . $this->mappingSchemeName() . "`.");
        $e .= "`" . $this->mappingTableName() . "`";
        return $e;
    }

//    /**
//     * @param string|array|ArkSQLCondition[] $conditions It might be the condition sql (the string after WHERE), or the key-value array, with FIELD-VALUE or KEY-ArkSQLCondition format.
//     * @param string $glue
//     * @return string
//     */
//    protected final function buildCondition($conditions, $glue = ' AND ')
//    {
//        $condition_sql = "";
//        if (is_string($conditions)) {
//            $condition_sql = $conditions;
//        } elseif (is_array($conditions)) {
//            $c = [];
//            foreach ($conditions as $key => $value) {
//                if (is_a($value, ArkSQLCondition::class)) {
//                    try {
//                        $c[] = $value->makeConditionSQL();
//                    } catch (Exception $e) {
//                        // ignore the error
//                    }
//                } else {
//                    if (is_array($value)) {
//                        $x = [];
//                        foreach ($value as $value_piece) {
//                            $x[] = $this->db()->quote($value_piece);
//                        }
//                        $x = implode(",", $x);
//                        $c[] = " `{$key}` in (" . $x . ") ";
//                    } else {
//                        $c[] = " `{$key}`=" . $this->db()->quote($value) . " ";
//                    }
//                }
//            }
//            $condition_sql = implode($glue, $c);
//        }
//        return trim($condition_sql);
//    }

    /**
     * @return null|string
     * @since 1.6.2
     */
    abstract public function mappingSchemeName();

    /**
     * @return string
     * @since 1.6.2
     */
    abstract public function mappingTableName();

    /**
     * @param array $valuesForOneRow [field_name=>value], NULL for NULL.
     * @param string $fields as output
     * @return string
     * @since 1.7.4
     */
    protected function buildRowValuesForWrite(array $valuesForOneRow, &$fields = null)
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
     * @return ArkPDO
     */
    abstract public function db();

    /**
     * @param array $data
     * @return ArkDatabaseQueryResult
     */
    public function replaceOneRow(array $data)
    {
        return $this->writeInto($data, null, true);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @param array $modification
     * @return ArkDatabaseQueryResult
     */
    public function updateRows(array $conditions, array $modification)
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $condition_sql = ArkSQLCondition::generateConditionSQLComponent($conditions);
            $data_sql = $this->buildRowValuesForUpdate($modification);
            $table = $this->getTableExpressForSQL();
            $sql = "UPDATE {$table} SET {$data_sql} WHERE {$condition_sql}";
            $result->setSql($sql);
            $afx = $this->db()->exec($sql);
            if ($afx === false) {
                throw new Exception("Error in updating");
            }
            $result->setAffectedRowsCount($afx);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
        } catch (Exception $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError("Exception: " . $e->getMessage() . "; PDO Last Error: " . $this->db()->getPDOErrorDescription());
        }
        return $result;
    }

    /**
     * @param array $valuesForOneRow [field_name=>value], NULL for NULL.
     * @return string
     * @since 1.7.4
     */
    protected function buildRowValuesForUpdate(array $valuesForOneRow)
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
     * @param ArkSQLCondition[] $conditions
     * @return ArkDatabaseQueryResult
     */
    public function deleteRows(array $conditions)
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $condition_sql = ArkSQLCondition::generateConditionSQLComponent($conditions);
            $table = $this->getTableExpressForSQL();
            $sql = "DELETE FROM {$table} WHERE {$condition_sql}";
            $afx = $this->db()->exec($sql);
            if ($afx === false) {
                throw new Exception("Error in updating");
            }
            $result->setAffectedRowsCount($afx);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
        } catch (Exception $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError("Exception: " . $e->getMessage() . "; PDO Last Error: " . $this->db()->getPDOErrorDescription());
        }
        return $result;
    }

    /**
     * @param array[] $dataList
     * @param null|string $pk
     * @return ArkDatabaseQueryResult
     */
    public function batchInsertRows(array $dataList, $pk = null)
    {
        return $this->batchWriteInto($dataList, $pk, false);
    }

    /**
     * @param array[] $dataList
     * @param null|string $pk
     * @param bool $shouldReplace
     * @return ArkDatabaseQueryResult
     */
    protected function batchWriteInto(array $dataList, $pk = null, bool $shouldReplace = false)
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $fields = [];
            $values = [];

            foreach ($dataList[0] as $key => $value) {
                $fields[] = "`{$key}`";
            }
            foreach ($dataList as $data) {
                if (count($data) != count($fields)) {
                    throw new Exception("Data List Format Error");
                }
                $values[] = "(" . $this->buildRowValuesForWrite($data) . ")";
            }
            $fields = implode(",", $fields);
            $values = implode(",", $values);
            $table = $this->getTableExpressForSQL();
            $sql = ($shouldReplace ? 'REPLACE' : 'INSERT') . " INTO {$table} ({$fields}) VALUES {$values}";
            $result->setSql($sql);

            $afx = $this->db()->insert($sql, $pk);
            if ($afx == false) {
                throw new Exception("Error in batch writing");
            }

            $result->setStatus(ArkDatabaseQueryResult::STATUS_EXECUTED);
            $result->setLastInsertedID($afx);
        } catch (Exception $exception) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError("Exception: " . $exception->getMessage() . "; PDO Last Error: " . $this->db()->getPDOErrorDescription());
        }
        return $result;
    }

    /**
     * @param array[] $dataList
     * @return ArkDatabaseQueryResult
     */
    public function batchReplaceRows(array $dataList)
    {
        return $this->batchWriteInto($dataList, null, true);
    }

//    protected $fields;
//
//    /**
//     * 如果model类里定义了字段名作为property，此方法可以加载一行的关联数组的数据以复写
//     * @param array $row result of `selectRow`
//     */
//    public function loadFieldsFromRowArray($row)
//    {
//        $this->fields = [];
//        foreach ($row as $key => $value) {
//            $this->fields[$key] = $value;
//        }
//    }
//
//    public function __get($name)
//    {
//        return ArkHelper::readTarget($this->fields, $name, '');
//    }
//
//    public function __set($name, $value)
//    {
//        ArkHelper::writeIntoArray($this->fields, $name, $value);
//    }
//
//    public function __isset($name)
//    {
//        return (isset($this->fields[$name]));
//    }
}