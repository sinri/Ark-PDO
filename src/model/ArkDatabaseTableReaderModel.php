<?php

namespace sinri\ark\database\model;

use sinri\ark\database\exception\ArkPDOQueryResultEmptySituation;
use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\model\query\ArkDatabaseSelectFieldMeta;
use sinri\ark\database\model\query\ArkDatabaseSelectTableQuery;
use sinri\ark\database\pdo\ArkPDO;

abstract class ArkDatabaseTableReaderModel
{
    /**
     * @return false|string
     * Alternative: \sinri\ark\database\model\implement\functions\ArkSQLDateTimeFunction::makeNow
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @return ArkPDO
     */
    abstract public function db(): ArkPDO;

    /**
     * @return string
     * @since 1.6.2 Old name: getTableExpressForSQL
     * @since 2.1.x use current method name
     */
    public function getTableExpression(): string
    {
        $e = ($this->mappingSchemeName() === '' ? "" : '`' . $this->mappingSchemeName() . "`.");
        $e .= "`" . $this->mappingTableName() . "`";
        return $e;
    }

    /**
     * @param string $fieldName
     * @return string
     * @since 2.1.x
     */
    public function getFieldExpression(string $fieldName): string
    {
        return $this->getTableExpression() . '.`' . $fieldName . '`';
    }

    /**
     * @return string return empty string for using default schema
     * @since 1.6.2
     */
    abstract public function mappingSchemeName(): string;

    /**
     * @return string
     * @since 1.6.2
     */
    abstract public function mappingTableName(): string;

    /**
     * @param int $page from 1 to infinite
     * @param int $pageSize
     * @param ArkDatabaseSelectFieldMeta[] $fieldMataList
     * @param ArkSQLCondition[] $conditions
     * @param string $sortExpression
     * @param int|null $totalRows
     * @return array[]
     * @since 2.0.10
     * @since 2.0.11 loose $totalRows type check, allow unassigned variable to be there
     * @deprecated since 2.0.33 use `\sinri\ark\database\model\query\ArkDatabaseSelectTableQuery::queryForMatrixWithPaging` instead
     */
    public function fetchByPaging(int $page, int $pageSize, array $fieldMataList, array $conditions, string $sortExpression = '', int &$totalRows = 0): array
    {
        if ($page < 1 || $pageSize <= 0) {
            throw new ArkPDOSQLBuilderError("Page number or page size is not correct.", "LIMIT $pageSize OFFSET " . ($pageSize * ($page - 1)));
        }
        $rowsForOnePage = $this->selectInTable()
            ->addSelectFields($fieldMataList)
            ->addConditions($conditions)
            ->setSortExpression($sortExpression)
            ->setLimit($pageSize)
            ->setOffset($pageSize * ($page - 1))
            ->queryForRows()
            ->getRawMatrix();
        try {
            $totalRows = $this->selectInTable()
                ->addSelectFieldByDetail('count(*)', 'total')
                ->addConditions($conditions)
                ->queryForRows()
                ->getResultRowByIndex(0)
                ->getField('total');
        } catch (ArkPDOQueryResultEmptySituation $e) {
            $totalRows = 0;
        }
        return $rowsForOnePage;
    }

    /**
     * @return ArkDatabaseSelectTableQuery
     * @since 2.0
     */
    public function selectInTable(): ArkDatabaseSelectTableQuery
    {
        return new ArkDatabaseSelectTableQuery($this);
    }
}