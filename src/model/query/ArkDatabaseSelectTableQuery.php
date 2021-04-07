<?php


namespace sinri\ark\database\model\query;


use sinri\ark\database\Exception\ArkPDOSQLBuilderError;
use sinri\ark\database\Exception\ArkPDOStatementException;
use sinri\ark\database\model\ArkDatabaseTableCoreModel;
use sinri\ark\database\model\ArkSQLCondition;

/**
 * Class ArkDatabaseSelectTableQuery
 * @package sinri\ark\database\model\query
 * @since 2.0
 */
class ArkDatabaseSelectTableQuery
{
    /**
     * @var ArkDatabaseTableCoreModel
     */
    protected $model;
    /**
     * @var ArkDatabaseSelectFieldMeta[]
     */
    protected $selectFields;

    /**
     * @var ArkSQLCondition[]
     */
    protected $conditions;
    /**
     * @var string[]
     */
    protected $groupByFields;
    /**
     * @var string
     */
    protected $sortExpression;
    /**
     * @var int
     */
    protected $limit;
    /**
     * @var int
     */
    protected $offset;
    /**
     * @var string[]
     * @since 2.0.7
     */
    protected $listOfUseIndexItems;
    /**
     * @var string[]
     * @since 2.0.7
     */
    protected $listOfForceIndexItems;
    /**
     * @var string[]
     * @since 2.0.7
     */
    protected $listOfIgnoreIndexItems;

    public function __construct(ArkDatabaseTableCoreModel $model)
    {
        $this->model = $model;
        $this->selectFields = [];
        $this->conditions = [];
        $this->groupByFields = [];
        $this->sortExpression = '';
        $this->limit = 0;
        $this->offset = 0;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return ArkDatabaseSelectTableQuery
     */
    public function setLimit(int $limit): ArkDatabaseSelectTableQuery
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return ArkDatabaseSelectTableQuery
     */
    public function setOffset(int $offset): ArkDatabaseSelectTableQuery
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param string $sortExpression
     * @return ArkDatabaseSelectTableQuery
     */
    public function setSortExpression(string $sortExpression): ArkDatabaseSelectTableQuery
    {
        $this->sortExpression = $sortExpression;
        return $this;
    }

    /**
     * @param string $fieldExpression
     * @param string $alias
     * @return $this
     */
    public function addSelectFieldByDetail(string $fieldExpression, string $alias = ''): ArkDatabaseSelectTableQuery
    {
        $this->selectFields[] = new ArkDatabaseSelectFieldMeta($fieldExpression, $alias);
        return $this;
    }

    /**
     * @param ArkDatabaseSelectFieldMeta $fieldMeta
     * @return $this
     */
    public function addSelectField(ArkDatabaseSelectFieldMeta $fieldMeta): ArkDatabaseSelectTableQuery
    {
        $this->selectFields[] = $fieldMeta;
        return $this;
    }

    /**
     * @param ArkDatabaseSelectFieldMeta[] $fieldMetaArray
     * @return ArkDatabaseSelectTableQuery
     */
    public function addSelectFields(array $fieldMetaArray): ArkDatabaseSelectTableQuery
    {
        foreach ($fieldMetaArray as $item) {
            if (is_a($item, ArkDatabaseSelectFieldMeta::class)) {
                $this->selectFields[] = $item;
            }
        }
        return $this;
    }

    /**
     * @param string[] $fieldNameList such as ['field_1','field_2']
     * @return $this
     * @since 2.0.5
     */
    public function addSelectFieldsWithoutAlias(array $fieldNameList): ArkDatabaseSelectTableQuery
    {
        foreach ($fieldNameList as $fieldName) {
            $this->selectFields[] = new ArkDatabaseSelectFieldMeta($fieldName);
        }
        return $this;
    }

    /**
     * Lazier Better!
     * @param string[] $fieldNames
     * @return $this
     * @since 2.0.1
     * @deprecated use `addSelectFieldsWithoutAlias` instead, which is more simple.
     */
    public function addSelectFieldNames(array $fieldNames): ArkDatabaseSelectTableQuery
    {
        foreach ($fieldNames as $item) {
            if (is_string($item)) {
                $this->selectFields[] = new ArkDatabaseSelectFieldMeta($item);
            }
        }
        return $this;
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return $this
     */
    public function addConditions(array $conditions): ArkDatabaseSelectTableQuery
    {
        foreach ($conditions as $condition) {
            if (is_a($condition, ArkSQLCondition::class)) {
                $this->conditions[] = $condition;
            }
        }
        return $this;
    }

    /**
     * @param array $simpleConditions such as ['field_1'=>1,'field_2'=>['a','b']]
     * @return $this
     * @since 2.0.5
     */
    public function quickAddSimpleConditions(array $simpleConditions): ArkDatabaseSelectTableQuery
    {
        foreach ($simpleConditions as $fieldName => $value) {
            if (is_array($value)) {
                $this->addCondition(ArkSQLCondition::makeInArray($fieldName, $value));
            } else {
                $this->addCondition(ArkSQLCondition::makeEqual($fieldName, $value));
            }
        }
        return $this;
    }

    /**
     * @param ArkSQLCondition $condition
     * @return $this
     */
    public function addCondition(ArkSQLCondition $condition): ArkDatabaseSelectTableQuery
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * @param string[] $groupByFields
     * @return $this
     */
    public function setGroupByFields(array $groupByFields): ArkDatabaseSelectTableQuery
    {
        $this->groupByFields = $groupByFields;
        return $this;
    }

    /**
     * @param string $indexKey
     * @since 2.0.7
     */
    public function useIndex(string $indexKey)
    {
        $this->listOfUseIndexItems[] = $indexKey;
    }

    /**
     * @param string $indexKey
     * @since 2.0.7
     */
    public function forceIndex(string $indexKey)
    {
        $this->listOfForceIndexItems[] = $indexKey;
    }

    /**
     * @param string $indexKey
     * @since 2.0.7
     */
    public function ignoreIndex(string $indexKey)
    {
        $this->listOfIgnoreIndexItems[] = $indexKey;
    }

    /**
     * @param string $resultRowCustomizedClass // I wonder if it is useful.
     * @return ArkDatabaseQueryResult
     */
    public function queryForRows($resultRowCustomizedClass = ArkDatabaseQueryResultRow::class): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $sql = $this->generateSQL();
            $result->setSql($sql);

            $all = $this->model->db()->getAll($sql);
            if (is_array($all)) {
                foreach ($all as $row) {
                    $result->addResultRow(new $resultRowCustomizedClass($row));
                }
            }
            $result->setStatus(ArkDatabaseQueryResult::STATUS_QUERIED);
        } catch (ArkPDOStatementException $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                'ArkPDOStatementException: ' . $e->getMessage() . ';'
                . ' SQL: ' . $e->getSql()
                . ' PDO Last Error: ' . $this->model->db()->getPDOErrorDescription()
            );
        } catch (ArkPDOSQLBuilderError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError('ArkPDOSQLBuilderError: ' . $e->getMessage() . ' SQL: ' . $e->getWrongSQLPiece());
        }
        return $result;
    }

    /**
     * @return string
     * @throws ArkPDOSQLBuilderError
     */
    public function generateSQL(): string
    {
        $table = $this->model->getTableExpressForSQL();
        $fields = ArkDatabaseSelectFieldMeta::generateFieldSQLComponent($this->selectFields);
        $condition_sql = ArkSQLCondition::generateConditionSQLComponent($this->conditions);

        $indexSql = "";
        if (!empty($this->listOfUseIndexItems)) {
            $indexSql .= " USE INDEX (" . implode(',', $this->listOfUseIndexItems) . ") ";
        }
        if (!empty($this->listOfForceIndexItems)) {
            $indexSql .= " FORCE INDEX (" . implode(',', $this->listOfForceIndexItems) . ") ";
        }
        if (!empty($this->listOfIgnoreIndexItems)) {
            $indexSql .= " IGNORE INDEX (" . implode(',', $this->listOfIgnoreIndexItems) . ") ";
        }

        $sql = "SELECT {$fields} FROM {$table} " . $indexSql . " WHERE {$condition_sql} ";

        if (!empty($this->groupByFields)) {
            $sql .= "group by " . implode(",", $this->groupByFields) . " ";
        }

        if ($this->sortExpression !== '') {
            $sql .= "order by " . $this->sortExpression . ' ';
        }

        if ($this->limit > 0) {
            $sql .= " limit {$this->limit} ";
            if ($this->offset > 0) {
                $sql .= " offset {$this->offset} ";
            }
        }
        return $sql;
    }

    /**
     * @return ArkDatabaseQueryResult
     */
    public function queryForStream(): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $sql = $this->generateSQL();
            $result->setSql($sql);

            $statement = $this->model->db()->getPdo()->query($sql);
            if ($statement === false) {
                throw new ArkPDOStatementException('Cannot build a valid PDO Statement', $sql);
            }

            $result->setResultRowStream($statement);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_STREAMING);

        } catch (ArkPDOSQLBuilderError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError('ArkPDOSQLBuilderError: ' . $e->getMessage() . ' SQL: ' . $e->getWrongSQLPiece());
        } catch (ArkPDOStatementException $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                'ArkPDOStatementException: ' . $e->getMessage() . ';'
                . ' SQL: ' . $e->getSql()
                . ' PDO Last Error: ' . $this->model->db()->getPDOErrorDescription()
            );
        }
        return $result;
    }
}