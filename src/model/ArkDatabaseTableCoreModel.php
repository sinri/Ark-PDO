<?php


namespace sinri\ark\database\model;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\exception\EnsureItemException;
use sinri\ark\database\exception\ArkPDOExecutedWithEmptyResultSituation;
use sinri\ark\database\exception\ArkPDOExecuteFailedError;
use sinri\ark\database\exception\ArkPDOExecuteNotAffectedError;
use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\exception\ArkPDOStatementException;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkDatabaseTableCoreModel
 * @package sinri\ark\database\model
 * @since 1.7.0
 */
abstract class ArkDatabaseTableCoreModel
{

    protected $fields;

    /**
     * @return false|string
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @param array|string $conditions
     * @param string $field
     * @param null|string $orderBy
     * @param null|string[] $groupBy
     * @param int $limit
     * @param int $offset
     * @param string|null $having since 1.8.4
     * @return numeric|string|null
     * @throws ArkPDOExecutedWithEmptyResultSituation
     * @since 1.7.6
     * @since 1.8.0 would throw exceptions on failure
     */
    public function selectOne($conditions, string $field, $orderBy = null, $groupBy = null, $limit = 0, $offset = 0, $having = null)
    {
        $sql = $this->makeSelectSQL($field, $conditions, $orderBy, $limit, $offset, $groupBy, $having);
        return $this->db()->getOne($sql);
    }

    /**
     * @param string|string[] $fields
     * @param array|string $conditions
     * @param null|string $sort
     * @param int $limit
     * @param int $offset
     * @param null|string[] $groupBy
     * @param string|null $having @since 1.8.4
     * @return string
     * @throws ArkPDOSQLBuilderError
     */
    protected function makeSelectSQL($fields, $conditions, $sort = null, $limit = 0, $offset = 0, $groupBy = null, $having = null)
    {
        if (is_array($fields)) {
            // $fields = '`' . implode("`,`", $fields) . '`'; // before 1.7.3
            // @since 1.7.3 the field protection sign "``" is removed
            // Of course it might open the door to the hackers to inject something evil,
            // But it is obvious that the parameters of the method, especially `fields` should not decided by user input.
            $fields = implode(",", $fields);
        }

        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();

        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition_sql} ";

        if ($groupBy !== null) {
            $sql .= "group by " . implode(",", $groupBy) . " ";
        }

        // since 1.8.4
        if ($having !== null) {
            if (is_string($having)) {
                $sql .= 'having ' . $having;
            } else {
                throw new ArkPDOSQLBuilderError('Having Component should be a string or null.', json_encode($having));
            }
        }

        if ($sort !== null) {
            $sql .= "order by " . $sort;
        }

        $limit = intval($limit);
        $offset = intval($offset);
        if ($limit > 0) {
            $sql .= " limit {$limit} ";
            if ($offset > 0) {
                $sql .= " offset {$offset} ";
            }
        }
        return $sql;
    }

    /**
     * @param string|array|ArkSQLCondition[] $conditions It might be the condition sql (the string after WHERE), or the key-value array, with FIELD-VALUE or KEY-ArkSQLCondition format.
     * @param string $glue
     * @return string
     * @throws ArkPDOSQLBuilderError
     */
    protected final function buildCondition($conditions, $glue = ' AND ')
    {
        $condition_sql = "";
        if (is_string($conditions)) {
            $condition_sql = $conditions;
        } elseif (is_array($conditions)) {
            $c = [];
            foreach ($conditions as $key => $value) {
                if (is_a($value, ArkSQLCondition::class)) {
                    // since 1.7.9, mistakes here would be thrown
                    $c[] = $value->makeConditionSQL();
                } else {
                    if (is_array($value)) {
                        $x = [];
                        foreach ($value as $value_piece) {
                            $x[] = $this->db()->quote($value_piece);
                        }
                        $x = implode(",", $x);
                        $c[] = " `{$key}` in (" . $x . ") ";
                    } else {
                        $c[] = " `{$key}`=" . $this->db()->quote($value) . " ";
                    }
                }
            }
            $condition_sql = implode($glue, $c);
        }
        return trim($condition_sql);
    }

    /**
     * @return ArkPDO
     */
    abstract public function db();

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
     * @param array|string $conditions
     * @param string|string[] $field Remove limitation of string for group by situation
     * @param null|string $orderBy
     * @param null|string[] $groupBy
     * @param int $limit
     * @param int $offset
     * @param null|int|string $useAnotherKeyToFetch maybe you need field name, alias, index
     * @param string|null $having since 1.8.4
     * @return array
     * @throws ArkPDOSQLBuilderError
     * @throws ArkPDOStatementException
     * @since 1.7.6
     * @since 1.8.0 would throw exceptions on failure
     * @since 1.8.2 fix the ambiguous point between $useAnotherKeyToFetch and $field
     */
    public function selectColumn($conditions, $field, $orderBy = null, $groupBy = null, $limit = 0, $offset = 0, $useAnotherKeyToFetch = null, $having = null)
    {
        $sql = $this->makeSelectSQL($field, $conditions, $orderBy, $limit, $offset, $groupBy, $having);
        return $this->db()->getCol($sql, ($useAnotherKeyToFetch === null ? $field : $useAnotherKeyToFetch));
    }

    /**
     * @param array|string $conditions
     * @param string|string[] $fields "*","f1,f2" or ["f1","f2"] @since 1.2
     * @param null|string[] $groupBy @since 1.5
     * @param null|string $having @since 1.8.4
     * @return array
     * @throws ArkPDOExecutedWithEmptyResultSituation
     * @since 1.8.0 would throw exceptions on failure
     */
    public function selectRow($conditions, $fields = "*", $groupBy = null, $having = null)
    {
        $sql = $this->makeSelectSQL($fields, $conditions, null, 1, 0, $groupBy, $having);
        return $this->db()->getRow($sql);
    }


    /**
     * @param array|string $conditions
     * @param int $limit
     * @param int $offset
     * @param string|string[] $fields "*","f1,f2" or ["f1","f2"] @since 1.2
     * @param null|string[] $groupBy @since 1.5
     * @return array
     * @throws ArkPDOSQLBuilderError
     * @throws ArkPDOStatementException
     * @deprecated @since 1.8.0 use selectRowsForFieldsWithSort instead
     * @since 1.8.0 would throw exceptions on failure
     */
    public function selectRows($conditions, $limit = 0, $offset = 0, $fields = "*", $groupBy = null)
    {
        return $this->selectRowsForFieldsWithSort($fields, $conditions, null, $limit, $offset, null, $groupBy);
    }

    /**
     * @param string|string[] $fields such as '*', 'field_1,field_2 as x,sum(field_3)' or ['field_1','field_2 as x','sum(field_3)']
     * @param array|string $conditions
     * @param null|string $sort "field","field desc"," f1 asc, f2 desc"
     * @param int $limit
     * @param int $offset
     * @param null|string $refKey normally PK or UK if you want to get map rather than list
     * @param null|string[] $groupBy @since 1.5
     * @param null|string $having @since 1.8.4
     * @return array
     * @throws ArkPDOSQLBuilderError
     * @throws ArkPDOStatementException
     * @since 1.2
     * @since 1.8.0 would throw exceptions on failure
     */
    public function selectRowsForFieldsWithSort($fields, $conditions, $sort = null, $limit = 0, $offset = 0, $refKey = null, $groupBy = null, $having = null)
    {
        $sql = $this->makeSelectSQL($fields, $conditions, $sort, $limit, $offset, $groupBy, $having);
        $all = $this->db()->getAll($sql);
        if ($refKey) {
            $all = ArkHelper::turnListToMapping($all, $refKey);
        }
        return $all;
    }

    /**
     * @param array|string $conditions
     * @param string $countField @since 1.5.2
     * @param bool $useDistinct @since 1.5.2
     * @return int
     * @since 1.8.0 would throw exceptions on failure
     */
    public function selectRowsForCount($conditions, $countField = '*', $useDistinct = false)
    {
        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }

        if ($countField === '*') {
            $countTarget = "*";
        } else {
            $countTarget = ($useDistinct ? "DISTINCT " : '') . $countField;
        }

        $table = $this->getTableExpressForSQL();
        $sql = "SELECT count({$countTarget}) FROM {$table} WHERE {$condition_sql} ";

        try {
            $count = $this->db()->getOne($sql);
            return intval($count);
        } catch (ArkPDOExecutedWithEmptyResultSituation $e) {
            // Note: the count sql should not unfetchable unless SQL error.
            throw new ArkPDOStatementException($e->getRelatedSQL(), $e->getCode(), $e);
        }
    }

    /**
     * @param $conditions
     * @param null|string $sort "field","field desc"," f1 asc, f2 desc"
     * @param int $limit
     * @param int $offset
     * @param null|string $refKey normally PK or UK if you want to get map rather than list
     * @param null|string[] $groupBy @since 1.5
     * @return array
     * @throws ArkPDOSQLBuilderError
     * @throws ArkPDOStatementException
     * @deprecated @since 1.7.3 Please use `selectRowsForFieldsWithSort` instead!
     * @since 1.8.0 would throw exceptions on failure
     */
    public function selectRowsWithSort($conditions, $sort = null, $limit = 0, $offset = 0, $refKey = null, $groupBy = null)
    {
        return $this->selectRowsForFieldsWithSort("*", $conditions, $sort, $limit, $offset, $refKey, $groupBy);
    }

    /**
     * @param string $pkWithAI
     * @return float|int
     * @throws ArkPDOSQLBuilderError
     * @throws ArkPDOStatementException
     * @since 1.6.1
     * @deprecated since 1.7.1 use getMaxValueOfFiled instead
     * @since 1.8.0 would throw exceptions on failure
     */
    public function getMaxSinglePK($pkWithAI)
    {
        return $this->getMaxValueOfFiled($pkWithAI);
    }

    /**
     * @param string $field
     * @return int|float
     * @throws ArkPDOSQLBuilderError
     * @throws ArkPDOStatementException
     * @since 1.7.1
     * @since 1.8.0 would throw exceptions on failure
     */
    public function getMaxValueOfFiled($field)
    {
        $rows = $this->selectRowsForFieldsWithSort([$field], [], "`{$field}` desc", 1);
        return 1 * ArkHelper::readTarget($rows, [0, $field], 0);
    }

    /**
     * @param array $data
     * @param null|string $pk
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0 would throw exceptions on failure
     */
    public function insert($data, $pk = null)
    {
        return $this->writeInto($data, $pk);
    }

    /**
     * @param array $data
     * @param string|null $pk
     * @param bool $shouldReplace
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0 would throw exceptions on failure
     */
    protected function writeInto($data, $pk = null, $shouldReplace = false, $shouldIgnore = false)
    {
        $table = $this->getTableExpressForSQL();
        $values = $this->buildRowValuesForWrite($data, $fields);

        if ($shouldReplace) {
            $sql = "REPLACE INTO {$table} ({$fields}) VALUES ({$values})";
            return $this->db()->insert($sql);
        } else {
            $sql = "INSERT " . ($shouldIgnore ? "IGNORE" : "") . " INTO {$table} ({$fields}) VALUES ({$values})";
            return $this->db()->insert($sql, $pk);
        }
    }

    /**
     * @param array $valuesForOneRow [field_name=>value], NULL for NULL.
     * @param string $fields as output
     * @return string
     * @since 1.7.4
     */
    protected function buildRowValuesForWrite($valuesForOneRow, &$fields = null)
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
     * @param array $data
     * @param string|null $pk
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.15
     */
    public function insertIgnore($data, $pk = null)
    {
        return $this->writeInto($data, $pk, false, true);
    }

    /**
     * @param array $data
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0 would throw exceptions on failure
     */
    public function replace($data)
    {
        return $this->writeInto($data, null, true);
    }

    /**
     * @param $conditions
     * @param $data
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @throws ArkPDOSQLBuilderError
     * @since 1.8.0 would throw exceptions on failure
     */
    public function update($conditions, $data)
    {
        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $data_sql = $this->buildRowValuesForUpdate($data);
        $table = $this->getTableExpressForSQL();
        $sql = "UPDATE {$table} SET {$data_sql} WHERE {$condition_sql}";

        return $this->db()->exec($sql);
    }

    /**
     * @param array $valuesForOneRow [field_name=>value], NULL for NULL.
     * @return string
     * @since 1.7.4
     */
    protected function buildRowValuesForUpdate($valuesForOneRow)
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
     * @param $conditions
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @throws ArkPDOSQLBuilderError
     * @since 1.8.0 would throw exceptions on failure
     */
    public function delete($conditions)
    {
        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "DELETE FROM {$table} WHERE {$condition_sql}";

        return $this->db()->exec($sql);
    }

    /**
     * @param array[] $dataList
     * @param string|null $pk
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @throws ArkPDOSQLBuilderError
     * @since 1.8.0 would throw exceptions on failure
     */
    public function batchInsert($dataList, $pk = null)
    {
        return $this->batchWriteInto($dataList, $pk);
    }

    /**
     * @param array[] $dataList
     * @param string|null $pk
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.15
     */
    public function batchInsertIgnore($dataList, $pk = null)
    {
        return $this->batchWriteInto($dataList, $pk, false, true);
    }

    /**
     * @param array[] $dataList
     * @param string|null $pk
     * @param bool $shouldReplace
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @throws ArkPDOSQLBuilderError
     * @since 1.8.0 would throw exceptions on failure
     */
    protected function batchWriteInto($dataList, $pk = null, $shouldReplace = false, $shouldIgnore = false)
    {
        $fields = [];
        $values = [];

        foreach ($dataList[0] as $key => $value) {
            $fields[] = "`{$key}`";
        }
        foreach ($dataList as $data) {
            if (count($data) != count($fields)) {
                throw new ArkPDOSQLBuilderError('Fields and Data have different column number.');
            }
            $values[] = "(" . $this->buildRowValuesForWrite($data) . ")";
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $table = $this->getTableExpressForSQL();
        if ($shouldReplace) {
            $sql = "REPLACE INTO {$table} ({$fields}) VALUES {$values}";
            return $this->db()->exec($sql);
        } else {
            $sql = "INSERT " . ($shouldIgnore ? "IGNORE" : "") . " INTO {$table} ({$fields}) VALUES {$values}";
            return $this->db()->insert($sql, $pk);
        }

    }

    /**
     * @param array[] $dataList
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @throws ArkPDOSQLBuilderError
     * @since 1.8.0 would throw exceptions on failure
     */
    public function batchReplace($dataList)
    {
        return $this->batchWriteInto($dataList, null, true);
    }

    /**
     * @param array[] $dataList
     * @param array $duplicateModification
     * @throws ArkPDOExecuteFailedError
     * @since 1.8.14
     */
    public function insertOnDuplicateKeyUpdate($dataList, $duplicateModification)
    {
        $fields = [];
        $values = [];

        foreach ($dataList[0] as $key => $value) {
            $fields[] = "`{$key}`";
        }
        $expectedFieldsCount = count($fields);
        foreach ($dataList as $data) {
            $x = "(" . $this->buildRowValuesForWrite($data) . ")";
            if (count($data) !== $expectedFieldsCount) {
                throw new ArkPDOSQLBuilderError("Expected Fields Count is " . $expectedFieldsCount, $x);
            }
            $values[] = $x;
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $table = $this->getTableExpressForSQL();
        $sql = "INSERT INTO {$table} ({$fields}) VALUES {$values} ON DUPLICATE KEY UPDATE ";

        $duplicateModificationPairs = [];
        foreach ($duplicateModification as $k => $v) {
            $duplicateModificationPairs[] = ($k . ' = ' . $v);
        }
        $sql .= implode(",", $duplicateModificationPairs);

        $this->db()->safeExecute($sql, []);
    }

    /**
     * When you design a model for a certain table which is eventually designed,
     * you might run this method to get `@property` lines for the model class PHPDoc.
     * @throws ArkPDOStatementException
     * @throws EnsureItemException
     */
    public function devShowFieldsForPHPDoc()
    {
        echo "THIS IS A HELPER FOR DEV." . PHP_EOL;
        $fieldDefinition = $this->loadTableDesc();
        foreach ($fieldDefinition as $definition) {
            echo " * @property " . $definition->getTypeCategory() . ' ' . $definition->getName() . PHP_EOL;
        }
    }

    /**
     * @return ArkDatabaseTableFieldDefinition[]
     * @throws EnsureItemException
     * @throws ArkPDOStatementException
     */
    protected function loadTableDesc()
    {
        $fieldDefinition = [];
        $field_list = $this->db()->getAll("desc " . $this->getTableExpressForSQL());
        if (empty($field_list)) {
            throw new EnsureItemException("Seems no such table " . $this->getTableExpressForSQL());
        }
        foreach ($field_list as $field) {
            $fieldDefinition[$field['Field']] = ArkDatabaseTableFieldDefinition::makeInstanceWithDescResultRow($field);
        }
        return $fieldDefinition;
    }

    /**
     * 如果model类里定义了字段名作为property，此方法可以加载一行的关联数组的数据以复写
     * @param array $row result of `selectRow`
     */
    public function loadFieldsFromRowArray($row)
    {
        $this->fields = [];
        foreach ($row as $key => $value) {
            $this->fields[$key] = $value;
        }
    }

    public function __get($name)
    {
        return ArkHelper::readTarget($this->fields, $name, '');
    }

    public function __set($name, $value)
    {
        ArkHelper::writeIntoArray($this->fields, $name, $value);
    }

    public function __isset($name)
    {
        return (isset($this->fields[$name]));
    }
}