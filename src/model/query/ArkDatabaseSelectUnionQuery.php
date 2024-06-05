<?php

namespace sinri\ark\database\model\query;

use sinri\ark\database\model\ArkDatabaseSQLReaderTrait;
use sinri\ark\database\pdo\ArkPDO;

/**
 * @see https://dev.mysql.com/doc/refman/8.0/en/union.html
 * @since 2.1.x
 */
class ArkDatabaseSelectUnionQuery
{
    use ArkDatabaseSQLReaderTrait;

    /**
     * @var ArkDatabaseSelectTableQuery[]
     */
    protected array $selections;
    /**
     * @var string
     */
    protected string $sortExpression;

    public function __construct(ArkDatabaseSelectTableQuery $startSelection)
    {
        $this->selections = [$startSelection];
        $this->sortExpression = '';
    }

    public function unionAll(ArkDatabaseSelectTableQuery $selection): static
    {
        $selection->unionType = 'UNION ALL'; //
        $this->selections[] = $selection;
        return $this;
    }

    public function unionDistinct(ArkDatabaseSelectTableQuery $selection): static
    {
        return $this->union($selection);
    }

    public function union(ArkDatabaseSelectTableQuery $selection): static
    {
        $selection->unionType = 'UNION';// i.e. UNION DISTINCT
        $this->selections[] = $selection;
        return $this;
    }

    public function setSortExpression(string $sortExpression): static
    {
        $this->sortExpression = trim($sortExpression);
        return $this;
    }

    public function generateSQL(): string
    {
        $sql = '';
        foreach ($this->selections as $selection) {
            $sql .= $selection->unionType . ' (' . $selection->generateSQL() . ') ';
        }
        if (!empty($this->sortExpression)) {
            $sql .= 'ORDER BY ' . $this->sortExpression;
        }
        return $sql;
    }


    public function getTargetPDO(): ArkPDO
    {
        return $this->selections[0]->getTargetPDO();
    }
}