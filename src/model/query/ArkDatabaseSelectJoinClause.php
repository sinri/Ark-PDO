<?php

namespace sinri\ark\database\model\query;

use sinri\ark\database\model\ArkDatabaseSQLBuilderTrait;
use sinri\ark\database\model\ArkSQLCondition;

class ArkDatabaseSelectJoinClause
{
    use ArkDatabaseSQLBuilderTrait;

    const INNER_JOIN = 'INNER JOIN';
    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';
    const STRAIGHT_JOIN = 'STRAIGHT_JOIN';

    /**
     * @var string
     */
    protected $joinType;
    /**
     * @var string
     */
    protected $tableExpression;
    /**
     * @var string
     */
    protected $alias;
    /**
     * @var ArkSQLCondition[]
     */
    protected $onConditions;
    /**
     * @var string
     */
    protected $indexHint;

    /**
     * @param $joinType
     * @param string|ArkDatabaseSQLBuilderTrait $tableExpression
     * @param array $onConditions
     * @param string $alias
     * @param string $indexHint
     */
    public function __construct($joinType, $tableExpression, $onConditions = [], $alias = '', $indexHint = '')
    {
        $this->joinType = $joinType;
        $this->tableExpression = $tableExpression;
        $this->alias = $alias;
        $this->onConditions = $onConditions;
        $this->indexHint = $indexHint;
    }

    public function addOnCondition(ArkSQLCondition $onCondition)
    {
        $this->onConditions[] = $onCondition;
        return $this;
    }

    public function generateSQL(): string
    {
        $anotherTable = $this->joinType . ' ';
        if (is_string($this->tableExpression)) {
            $anotherTable .= $this->tableExpression;
        } else {
            $anotherTable .= '(' . $this->tableExpression . ')';
        }
        if ($this->alias) {
            $anotherTable .= ' AS ' . $this->alias;
        }
        if (!empty($this->onConditions)) {
            $anotherTable .= ' ON ';
            $anotherTable .= ArkSQLCondition::generateConditionSQLComponent($this->onConditions);
        }
        $anotherTable .= ' ' . $this->indexHint;
        return $anotherTable;
    }
}