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
     * @var ArkSQLCondition[]
     */
    protected $onConditions;
    /**
     * @var string
     */
    protected $indexHint;

    public function __construct($joinType, $tableExpression, $onConditions = [], $indexHint = '')
    {
        $this->joinType = $joinType;
        $this->tableExpression = $tableExpression;
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
        $anotherTable = $this->joinType . ' ' . $this->tableExpression;
        if (!empty($this->onConditions)) {
            $anotherTable .= ' ON ';
            $anotherTable .= ArkSQLCondition::generateConditionSQLComponent($this->onConditions);
        }
        $anotherTable .= ' ' . $this->indexHint;
        return $anotherTable;
    }
}