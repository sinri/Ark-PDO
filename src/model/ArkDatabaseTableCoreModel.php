<?php


namespace sinri\ark\database\model;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkDatabaseTableCoreModel
 * @package sinri\ark\database\model
 * @since 1.7.0
 */
abstract class ArkDatabaseTableCoreModel
{

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
     * @return ArkPDO
     */
    abstract public function db();

    /**
     * @return false|string
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    protected final function buildCondition($conditions, $glue = ' AND ')
    {
        $condition_sql = "";
        if (is_string($conditions)) {
            $condition_sql = $conditions;
        } elseif (is_array($conditions)) {
            $c = [];
            foreach ($conditions as $key => $value) {
                if (is_a($value, ArkSQLCondition::class)) {
                    try {
                        $c[] = $value->makeConditionSQL();
                    } catch (Exception $e) {
                        // ignore the error
                    }
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
     * @param array|string $conditions
     * @param string|string[] $fields "*","f1,f2" or ["f1","f2"] @since 1.2
     * @param null|string[] $groupBy @since 1.5
     * @return array|bool
     */
    public function selectRow($conditions, $fields = "*", $groupBy = null)
    {
        if (is_array($fields)) {
            $fields = '`' . implode("`,`", $fields) . '`';
        }

        $condition_sql = $this->buildCondition($conditions, 'AND');
        if ($condition_sql === '') {
            $condition_sql = "1";
        }

        $table = $this->getTableExpressForSQL();
        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition_sql}";
        if ($groupBy !== null) {
//            foreach ($groupBy as $groupByKey => $groupByValue) {
//                $groupBy[$groupByKey] = ArkPDO::dryQuote($groupByValue);
//            }
            $sql .= "group by " . implode(",", $groupBy) . " ";
        }
        $sql .= " LIMIT 1";
        try {
            return $this->db()->getRow($sql);
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * @param array|string $conditions
     * @param int $limit
     * @param int $offset
     * @param string|string[] $fields "*","f1,f2" or ["f1","f2"] @since 1.2
     * @param null|string[] $groupBy @since 1.5
     * @return array|bool
     */
    public function selectRows($conditions, $limit = 0, $offset = 0, $fields = "*", $groupBy = null)
    {
        if (is_array($fields)) {
            $fields = '`' . implode("`,`", $fields) . '`';
        }

        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition_sql} ";
        if ($groupBy !== null) {
//            foreach ($groupBy as $groupByKey => $groupByValue) {
//                $groupBy[$groupByKey] = ArkPDO::dryQuote($groupByValue);
//            }
            $sql .= "group by " . implode(",", $groupBy) . " ";
        }
        $limit = intval($limit, 10);
        $offset = intval($offset, 10);
        if ($limit > 0) {
            $sql .= " limit {$limit} ";
            if ($offset > 0) {
                $sql .= " offset {$offset} ";
            }
        }
        try {
            return $this->db()->getAll($sql);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param array|string $conditions
     * @param string $countField @since 1.5.2
     * @param bool $useDistinct @since 1.5.2
     * @return int|bool
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
            return intval($count, 10);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $conditions
     * @param null|string $sort "field","field desc"," f1 asc, f2 desc"
     * @param int $limit
     * @param int $offset
     * @param null|string $refKey normally PK or UK if you want to get map rather than list
     * @param null|string[] $groupBy @since 1.5
     * @return array|bool
     */
    public function selectRowsWithSort($conditions, $sort = null, $limit = 0, $offset = 0, $refKey = null, $groupBy = null)
    {
        return $this->selectRowsForFieldsWithSort("*", $conditions, $sort, $limit, $offset, $refKey, $groupBy);
    }

    /**
     * @param string|string[] $fields such as '*', 'f1,f2' or ['f1','f2']
     * @param $conditions
     * @param null|string $sort "field","field desc"," f1 asc, f2 desc"
     * @param int $limit
     * @param int $offset
     * @param null|string $refKey normally PK or UK if you want to get map rather than list
     * @param null|string[] $groupBy @since 1.5
     * @return array|bool
     * @since 1.2
     */
    public function selectRowsForFieldsWithSort($fields, $conditions, $sort = null, $limit = 0, $offset = 0, $refKey = null, $groupBy = null)
    {
        if (is_array($fields)) {
            $fields = '`' . implode("`,`", $fields) . '`';
        }

        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();

        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition_sql} ";

        if ($groupBy !== null) {
//            foreach ($groupBy as $groupByKey => $groupByValue) {
//                $groupBy[$groupByKey] = ArkPDO::dryQuote($groupByValue);
//            }
            $sql .= "group by " . implode(",", $groupBy) . " ";
        }

        if ($sort !== null) {
            $sql .= "order by " . $sort;
        }

        $limit = intval($limit, 10);
        $offset = intval($offset, 10);
        if ($limit > 0) {
            $sql .= " limit {$limit} ";
            if ($offset > 0) {
                $sql .= " offset {$offset} ";
            }
        }
        try {
            $all = $this->db()->getAll($sql);
            if ($refKey) {
                $all = ArkHelper::turnListToMapping($all, $refKey);
            }
            return $all;
        } catch (Exception $exception) {
            return false;
        }
    }

    protected function writeInto($data, $pk = null, $shouldReplace = false)
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = $this->db()->quote($value);
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $table = $this->getTableExpressForSQL();

        try {
            if ($shouldReplace) {
                $sql = "REPLACE INTO {$table} ({$fields}) VALUES ({$values})";
                return $this->db()->insert($sql);
            } else {
                $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
                return $this->db()->insert($sql, $pk);
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $pkWithAI
     * @return int|false
     * @since 1.6.1
     */
    public function getMaxSinglePK($pkWithAI)
    {
        $rows = $this->selectRowsForFieldsWithSort([$pkWithAI], [], "`{$pkWithAI}` desc", 1);
        if ($rows === false) return false;
        $targetLastId = ArkHelper::readTarget($rows, [0, $pkWithAI], 0);
        return $targetLastId;
    }

    /**
     * @param array $data
     * @param null $pk
     * @return bool|string
     */
    public function insert($data, $pk = null)
    {
        return $this->writeInto($data, $pk, false);
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function replace($data)
    {
        return $this->writeInto($data, null, true);
    }

    /**
     * @param $conditions
     * @param $data
     * @return int
     */
    public function update($conditions, $data)
    {
        $condition_sql = $this->buildCondition($conditions, "AND");
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $data_sql = $this->buildCondition($data, ",");
        $table = $this->getTableExpressForSQL();
        $sql = "UPDATE {$table} SET {$data_sql} WHERE {$condition_sql}";
        try {
            return $this->db()->exec($sql);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $conditions
     * @return int
     */
    public function delete($conditions)
    {
        $condition_sql = $this->buildCondition($conditions, "AND");
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "DELETE FROM {$table} WHERE {$condition_sql}";
        try {
            return $this->db()->exec($sql);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function batchWriteInto($dataList, $pk = null, $shouldReplace = false)
    {
        try {
            $fields = [];
            $values = [];

            foreach ($dataList[0] as $key => $value) {
                $fields[] = "`{$key}`";
            }
            foreach ($dataList as $data) {
                $tmp = [];
                if (count($data) != count($fields)) {
                    return false;
                }
                foreach ($data as $key => $value) {
                    $tmp[] = $this->db()->quote($value);
                }
                $values[] = "(" . implode(",", $tmp) . ")";
            }
            $fields = implode(",", $fields);
            $values = implode(",", $values);
            $table = $this->getTableExpressForSQL();
            if ($shouldReplace) {
                $sql = "REPLACE INTO {$table} ({$fields}) VALUES {$values}";
                return $this->db()->exec($sql);
            } else {
                $sql = "INSERT INTO {$table} ({$fields}) VALUES {$values}";
                return $this->db()->insert($sql, $pk);
            }

        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param array $dataList
     * @param null $pk
     * @return bool|string
     */
    public function batchInsert($dataList, $pk = null)
    {
        return $this->batchWriteInto($dataList, $pk, false);
    }

    /**
     * @param array $dataList
     * @return bool|string
     */
    public function batchReplace($dataList)
    {
        return $this->batchWriteInto($dataList, null, true);
    }


    /**
     * @return ArkDatabaseTableFieldDefinition[]
     * @throws Exception
     */
    protected function loadTableDesc()
    {
        $fieldDefinition = [];
        $field_list = $this->db()->getAll("desc " . $this->getTableExpressForSQL());
        if (empty($field_list)) {
            throw new Exception("Seems no such table " . $this->getTableExpressForSQL());
        }
        foreach ($field_list as $field) {
            $fieldDefinition[$field['Field']] = ArkDatabaseTableFieldDefinition::makeInstanceWithDescResultRow($field);
        }
        return $fieldDefinition;
    }

    /**
     * When you design a model for a certain table which is eventually designed,
     * you might run this method to get `@property` lines for the model class PHPDoc.
     * @throws Exception
     */
    public function devShowFieldsForPHPDoc()
    {
        echo "THIS IS A HELPER FOR DEV." . PHP_EOL;
        $fieldDefinition = $this->loadTableDesc();
        foreach ($fieldDefinition as $definition) {
            echo " * @property " . $definition->getTypeCategory() . ' ' . $definition->getName() . PHP_EOL;
        }
    }

    protected $fields;

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