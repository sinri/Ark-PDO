<?php


namespace sinri\ark\database\model\query;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableCoreModel;
use sinri\ark\database\model\ArkSQLCondition;

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
    public function addSelectFieldByDetail(string $fieldExpression, string $alias = '')
    {
        $this->selectFields[] = new ArkDatabaseSelectFieldMeta($fieldExpression, $alias);
        return $this;
    }

    /**
     * @param ArkDatabaseSelectFieldMeta $fieldMeta
     * @return $this
     */
    public function addSelectField(ArkDatabaseSelectFieldMeta $fieldMeta)
    {
        $this->selectFields[] = $fieldMeta;
        return $this;
    }

    /**
     * @param ArkDatabaseSelectFieldMeta[] $fieldMetaArray
     * @return ArkDatabaseSelectTableQuery
     */
    public function addSelectFields(array $fieldMetaArray)
    {
        foreach ($fieldMetaArray as $item) {
            if (is_a($item, ArkDatabaseSelectFieldMeta::class)) {
                $this->selectFields[] = $item;
            }
        }
        return $this;
    }

    /**
     * @param ArkSQLCondition $condition
     * @return $this
     */
    public function addCondition(ArkSQLCondition $condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return $this
     */
    public function addConditions(array $conditions)
    {
        foreach ($conditions as $condition) {
            if (is_a($condition, ArkSQLCondition::class)) {
                $this->conditions[] = $condition;
            }
        }
        return $this;
    }

    /**
     * @param string[] $groupByFields
     * @return $this
     */
    public function setGroupByFields(array $groupByFields)
    {
        $this->groupByFields = $groupByFields;
        return $this;
    }

    /**
     * @param string $resultRowCustomizedClass
     * @return ArkDatabaseQueryResult
     */
    public function queryForRows($resultRowCustomizedClass = ArkDatabaseQueryResultRow::class)
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $sql = $this->generateSQL();
            $result->setSql($sql);

            $all = $this->model->db()->getAll($sql);

            if (!is_array($all)) {
                throw new Exception("Non-Array Fetched From Database");
            }

            foreach ($all as $row) {
                $result->addResultRow(new $resultRowCustomizedClass($row));
            }
            $result->setStatus(ArkDatabaseQueryResult::STATUS_QUERIED);

            return $result;
        } catch (Exception $exception) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError('Exception: ' . $exception->getMessage() . '; PDO Last Error: ' . $this->model->db()->getPDOErrorDescription());
            return $result;
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function generateSQL()
    {
        $table = $this->model->getTableExpressForSQL();

        $fields = [];
        foreach ($this->selectFields as $fieldMeta) {
            $fields[] = $fieldMeta->generateSQLComponent();
        }
        $fields = implode(',', $fields);

        $condition_sql = [];
        foreach ($this->conditions as $condition) {
            $condition_sql[] = $condition->makeConditionSQL();
        }
        if (empty($condition_sql)) {
            $condition_sql = '1=1';
        } else {
            $condition_sql = implode(' AND ', $condition_sql);
        }

        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition_sql} ";

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
}