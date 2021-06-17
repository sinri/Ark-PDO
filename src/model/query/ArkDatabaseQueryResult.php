<?php


namespace sinri\ark\database\model\query;

use PDO;
use PDOStatement;
use sinri\ark\database\exception\ArkPDOInvalidIndexError;
use sinri\ark\database\exception\ArkPDOQueryResultEmptySituation;
use sinri\ark\database\exception\ArkPDOQueryResultFinishedStreamingSituation;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotExecutedError;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotQueriedError;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotStreamingError;

/**
 * Class ArkDatabaseQueryResult
 * @package sinri\ark\database\model
 * @since 2.0
 */
class ArkDatabaseQueryResult
{
    const STATUS_INIT = "INIT";
    const STATUS_QUERIED = "QUERIED";
    const STATUS_EXECUTED = "EXECUTED";
    const STATUS_STREAMING = "STREAMING";
    const STATUS_STREAMED = "STREAMED";
    const STATUS_ERROR = "ERROR";
    /**
     * @var string
     */
    protected $sql;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var string
     */
    protected $error;
    /**
     * @var int
     */
    protected $lastInsertedID;
    /**
     * @var int
     */
    protected $affectedRowsCount;
    /**
     * @var ArkDatabaseQueryResultRow[]
     */
    protected $resultRows;
    /**
     * @var PDOStatement|null
     */
    protected $resultRowStream;
    /**
     * @var ArkDatabaseQueryResultFieldMeta[]
     * @since 2.0.25
     * @experimental
     */
    protected $resultRowStreamFieldMetaList;

    /**
     * @return ArkDatabaseQueryResultFieldMeta[]
     */
    public function getResultRowStreamFieldMetaList(): array
    {
        return $this->resultRowStreamFieldMetaList;
    }

    public function __construct()
    {
        $this->sql = '';
        $this->status = self::STATUS_INIT;
        $this->error = 'Not Executed Yet';
        $this->lastInsertedID = -1;
        $this->affectedRowsCount = -1;
        $this->resultRows = [];
        $this->resultRowStream = null;
    }

    /**
     * @param string $errorMessage
     * @return ArkDatabaseQueryResult
     */
    public static function makeErrorResult(string $errorMessage): ArkDatabaseQueryResult
    {
        return (new ArkDatabaseQueryResult())
            ->setStatus(ArkDatabaseQueryResult::STATUS_ERROR)
            ->setError($errorMessage);
    }

    /**
     * @return bool
     * @since 2.0.5
     */
    public function isStatusAsQueried(): bool
    {
        return $this->status === self::STATUS_QUERIED;
    }

    /**
     * @return bool
     * @since 2.0.5
     */
    public function isStatusAsExecuted(): bool
    {
        return $this->status === self::STATUS_EXECUTED;
    }

    /**
     * @return bool
     * @since 2.0.5
     */
    public function isStatusAsStreamed(): bool
    {
        return $this->status === self::STATUS_STREAMED;
    }

    /**
     * @return int If equals to -1, something wrong
     */
    public function getLastInsertedID(): int
    {
        return $this->lastInsertedID;
    }

    /**
     * @param int $lastInsertedID
     * @return ArkDatabaseQueryResult
     */
    public function setLastInsertedID(int $lastInsertedID): ArkDatabaseQueryResult
    {
        $this->lastInsertedID = $lastInsertedID;
        return $this;
    }

    /**
     * @return int If equals to -1, something wrong
     */
    public function getAffectedRowsCount(): int
    {
        return $this->affectedRowsCount;
    }

    /**
     * @param int $affectedRowsCount
     * @return ArkDatabaseQueryResult
     */
    public function setAffectedRowsCount(int $affectedRowsCount): ArkDatabaseQueryResult
    {
        $this->affectedRowsCount = $affectedRowsCount;
        return $this;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return ArkDatabaseQueryResult
     */
    public function setSql(string $sql): ArkDatabaseQueryResult
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ArkDatabaseQueryResult
     */
    public function setStatus(string $status): ArkDatabaseQueryResult
    {
        $this->status = $status;
        if (in_array($status, [self::STATUS_QUERIED, self::STATUS_STREAMING, self::STATUS_STREAMED, self::STATUS_EXECUTED])) {
            $this->error = 'No Error';
        }
        return $this;
    }

    /**
     * @param array[] $matrix
     * @return $this
     */
    public function addRowsByRawMatrix(array $matrix): ArkDatabaseQueryResult
    {
        foreach ($matrix as $row) {
            $this->addResultRow(new ArkDatabaseQueryResultRow($row));
        }
        return $this;
    }

    /**
     * @param ArkDatabaseQueryResultRow $row
     * @return ArkDatabaseQueryResult
     */
    public function addResultRow(ArkDatabaseQueryResultRow $row): ArkDatabaseQueryResult
    {
        $this->resultRows[] = $row;
        return $this;
    }

    /**
     * @param ArkDatabaseQueryResultRow[] $rows
     * @return ArkDatabaseQueryResult
     */
    public function addResultRows(array $rows): ArkDatabaseQueryResult
    {
        $this->resultRows += $rows;
        return $this;
    }

    /**
     * @return ArkDatabaseQueryResultRow[]
     * @throws ArkPDOQueryResultIsNotQueriedError
     */
    public function getResultRows(): array
    {
        $this->assertStatusIsQueried(__METHOD__);
        return $this->resultRows;
    }

    /**
     * @param string $action
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.12
     * @since 2.0.19 make it public
     */
    public function assertStatusIsQueried(string $action)
    {
        if ($this->status !== self::STATUS_QUERIED) {
            throw new ArkPDOQueryResultIsNotQueriedError($action, $this->status, $this->getError(), $this->sql);
        }
    }

    /**
     * @param string $action
     * @throws ArkPDOQueryResultIsNotExecutedError
     * @since 2.0.18
     * @since 2.0.19 make it public
     */
    public function assertStatusIsExecuted(string $action)
    {
        if ($this->status !== self::STATUS_EXECUTED) {
            throw new ArkPDOQueryResultIsNotExecutedError($action, $this->status, $this->getError(), $this->sql);
        }
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return ArkDatabaseQueryResult
     * @since 2.0.10 Fixed the return design.
     */
    public function setError(string $error): ArkDatabaseQueryResult
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return PDOStatement|null
     */
    public function getResultRowStream(): PDOStatement
    {
        return $this->resultRowStream;
    }

    /**
     * @param PDOStatement|null $resultRowStream
     */
    public function setResultRowStream(PDOStatement $resultRowStream)
    {
        $this->resultRowStream = $resultRowStream;
        $this->loadStreamResultFieldsMeta();
    }

    /**
     * @return $this
     * @since 2.0.25
     * @experimental
     */
    protected function loadStreamResultFieldsMeta()
    {
        $this->resultRowStreamFieldMetaList = [];
        for ($i = 0; $i < $this->resultRowStream->columnCount(); $i++) {
            $this->resultRowStreamFieldMetaList[] = new ArkDatabaseQueryResultFieldMeta($this->resultRowStream->getColumnMeta($i));
        }
        return $this;
    }

    /**
     * @param string $rowClass
     * @return ArkDatabaseQueryResultRow
     * @throws ArkPDOQueryResultFinishedStreamingSituation When all rows fetched
     * @throws ArkPDOQueryResultIsNotStreamingError When now is not streaming
     */
    public function readNextRow($rowClass = ArkDatabaseQueryResultRow::class)
    {
        if ($this->status !== self::STATUS_STREAMING) {
            throw new ArkPDOQueryResultIsNotStreamingError(__METHOD__, $this->getStatus(), $this->getError(), $this->getSql());
        }

        // old implementation
        $fetchedRow = $this->resultRowStream->fetch(PDO::FETCH_ASSOC);
        if ($fetchedRow !== false) {
            return new $rowClass($fetchedRow);
        }

        // new implementation with raw PDO
//        $this->resultRowStream->setFetchMode(PDO::FETCH_CLASS,$rowClass);
//        $rowClassInstance = $this->resultRowStream->fetch(PDO::FETCH_CLASS);
//        if($rowClassInstance!==false){
//            return $rowClassInstance;
//        }

        // FALSE fetched as no more rows...
        $this->status = self::STATUS_STREAMED;
        $this->resultRowStream->closeCursor();
        $this->resultRowStream = null;
        throw new ArkPDOQueryResultFinishedStreamingSituation();
    }

    /**
     * @param ArkDatabaseQueryResultRow $rowClassInstance
     * @return ArkDatabaseQueryResultRow
     * @throws ArkPDOQueryResultFinishedStreamingSituation
     * @throws ArkPDOQueryResultIsNotStreamingError
     * @since 2.0.25
     */
    public function readNextRowAndReloadRowClassInstance($rowClassInstance)
    {
        if ($this->status !== self::STATUS_STREAMING) {
            throw new ArkPDOQueryResultIsNotStreamingError(__METHOD__, $this->getStatus(), $this->getError(), $this->getSql());
        }

        $this->resultRowStream->setFetchMode(PDO::FETCH_INTO, $rowClassInstance);
        $rowClassInstance = $this->resultRowStream->fetch(PDO::FETCH_INTO);
        if ($rowClassInstance !== false) {
            return $rowClassInstance;
        }

        // FALSE fetched as no more rows...
        $this->status = self::STATUS_STREAMED;
        $this->resultRowStream->closeCursor();
        $this->resultRowStream = null;
        throw new ArkPDOQueryResultFinishedStreamingSituation();
    }

    /**
     * @return $this
     * @throws ArkPDOQueryResultEmptySituation
     * @throws ArkPDOQueryResultIsNotQueriedError
     */
    public function assertResultMatrixIsNotEmpty()
    {
        $this->assertStatusIsQueried(__METHOD__);
        if (empty($this->resultRows)) {
            throw new ArkPDOQueryResultEmptySituation($this->getSql());
        }
        return $this;
    }

    /**
     * @return array[]|false False for Error
     * @since 2.0.5
     */
    public function tryGetRawRowsFromResultRowSet()
    {
        try {
            return $this->getRawMatrix();
        } catch (ArkPDOQueryResultIsNotQueriedError $e) {
            return false;
        }
    }

    /**
     * @return array[]
     * @throws ArkPDOQueryResultIsNotQueriedError
     */
    public function getRawMatrix(): array
    {
        $this->assertStatusIsQueried(__METHOD__);
        $matrix = [];
        foreach ($this->resultRows as $resultRow) {
            $matrix[] = $resultRow->getRawRow();
        }
        return $matrix;
    }

    /**
     * @return array|false|null False for Error, Null for Empty
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.5
     */
    public function tryGetFirstRawRowFromResultRowSet()
    {
        try {
            return $this->getResultRowByIndex(0)->getRawRow();
        } catch (ArkPDOQueryResultEmptySituation $e) {
            return false;
        } catch (ArkPDOInvalidIndexError $e) {
            return null;
        }
    }

    /**
     * @param int $index Since 0
     * @return ArkDatabaseQueryResultRow
     * @throws ArkPDOQueryResultEmptySituation
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.1
     */
    public function getResultRowByIndex(int $index): ArkDatabaseQueryResultRow
    {
        $this->assertStatusIsQueried(__METHOD__ . "({$index})");
        $totalResultRows = count($this->resultRows);
        if ($totalResultRows === 0) {
            throw new ArkPDOQueryResultEmptySituation($this->getSql());
        }
        if ($index < 0 || $index >= $totalResultRows) {
            throw new ArkPDOInvalidIndexError("SQL query result actually contains $totalResultRows row(s)", $index);
        }
        return $this->resultRows[$index];
    }

    /**
     * @param string $fieldName
     * @param mixed $default
     * @return array|false False for Error
     * @since 2.0.5
     */
    public function tryGetRawColumnsFromResultRowSet(string $fieldName, $default = null)
    {
        try {
            return $this->getResultColumn($fieldName, $default);
        } catch (ArkPDOQueryResultIsNotQueriedError $e) {
            return false;
        }
    }

    /**
     * @param string $columnName
     * @param null|mixed $default
     * @return array
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.1
     */
    public function getResultColumn(string $columnName, $default = null): array
    {
        $this->assertStatusIsQueried(__METHOD__ . "({$columnName})");
        $column = [];
        foreach ($this->resultRows as $resultRow) {
            $column[] = $resultRow->getField($columnName, $default);
        }
        return $column;
    }

    /**
     * @param string $fieldName
     * @param mixed $default
     * @return scalar|false|null False for Error, Null for Empty
     * @throws ArkPDOQueryResultEmptySituation
     * @since 2.0.6
     */
    public function tryGetRawCellFromResultRowSet(string $fieldName, $default = null)
    {
        try {
            return $this->getResultRowByIndex(0)->getField($fieldName, $default);
        } catch (ArkPDOQueryResultIsNotQueriedError $e) {
            return false;
        } catch (ArkPDOInvalidIndexError $e) {
            return null;
        }
    }

    /**
     * @param string $fieldName the name of the key field
     * @return ArkDatabaseQueryResultRow[][] [key_filed_name=>ROW, ...]
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.12
     */
    public function getResultKeyRowMap(string $fieldName): array
    {
        $this->assertStatusIsQueried(__METHOD__ . "({$fieldName})");
        $map = [];
        foreach ($this->resultRows as $resultRow) {
            $map[$resultRow->getField($fieldName, '')] = $resultRow;
        }
        return $map;
    }

    /**
     * @param string $keyFieldName
     * @param string $valueFieldName
     * @param mixed $defaultValue
     * @return array [key_filed_name=>value_field_value, ...]
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.12
     */
    public function getResultKeyValueMap(string $keyFieldName, string $valueFieldName, $defaultValue = null): array
    {
        $this->assertStatusIsQueried(__METHOD__ . "({$keyFieldName}=>{$valueFieldName})");
        $map = [];
        foreach ($this->resultRows as $resultRow) {
            $map[$resultRow->getField($keyFieldName, '')] = $resultRow->getField($valueFieldName, $defaultValue);
        }
        return $map;
    }
}