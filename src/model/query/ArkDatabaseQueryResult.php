<?php


namespace sinri\ark\database\model\query;

use Exception;
use OutOfBoundsException;
use PDO;
use PDOStatement;

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
     * @return int
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
     * @return int
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
    public function setStatus(string $status)
    {
        $this->status = $status;
        if (in_array($status, [self::STATUS_QUERIED, self::STATUS_STREAMING, self::STATUS_STREAMED, self::STATUS_EXECUTED])) {
            $this->error = 'No Error';
        }
        return $this;
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
     */
    public function setError(string $error)
    {
        $this->error = $error;
    }

    /**
     * @param array[] $matrix
     * @return $this
     */
    public function addRowsByRawMatrix(array $matrix)
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
    public function addResultRow(ArkDatabaseQueryResultRow $row)
    {
        $this->resultRows[] = $row;
        return $this;
    }

    /**
     * @return ArkDatabaseQueryResultRow[]
     * @throws Exception
     */
    public function getResultRows()
    {
        if ($this->status !== self::STATUS_QUERIED) {
            throw new Exception("Illegal Call");
        }
        return $this->resultRows;
    }

    /**
     * @param int $index Since 0
     * @return ArkDatabaseQueryResultRow
     * @throws Exception
     * @since 2.0.1
     */
    public function getResultRowByIndex($index)
    {
        if ($this->status !== self::STATUS_QUERIED) {
            throw new Exception("Illegal Call");
        }
        if ($index < 0 || $index >= count($this->resultRows)) {
            throw new OutOfBoundsException("Out of Bounds");
        }
        return $this->resultRows[$index];
    }

    /**
     * @param string $columnName
     * @param null|mixed $default
     * @return array
     * @throws Exception
     * @since 2.0.1
     */
    public function getResultColumn($columnName, $default = null)
    {
        if ($this->status !== self::STATUS_QUERIED) {
            throw new Exception("Illegal Call");
        }
        $column = [];
        foreach ($this->resultRows as $resultRow) {
            $column[] = $resultRow->getField($columnName, $default);
        }
        return $column;
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function getRawMatrix()
    {
        if ($this->status !== self::STATUS_QUERIED) {
            throw new Exception("Illegal Call");
        }
        $matrix = [];
        foreach ($this->resultRows as $resultRow) {
            $matrix[] = $resultRow->getRawRow();
        }
        return $matrix;
    }

    /**
     * @return PDOStatement|null
     */
    public function getResultRowStream()
    {
        return $this->resultRowStream;
    }

    /**
     * @param PDOStatement|null $resultRowStream
     */
    public function setResultRowStream(PDOStatement $resultRowStream)
    {
        $this->resultRowStream = $resultRowStream;
    }

    /**
     * @return false|ArkDatabaseQueryResultRow
     */
    public function readNextRow()
    {
        if ($this->status !== self::STATUS_STREAMING) {
            return false;
        }
        $fetchedRow = $this->resultRowStream->fetch(PDO::FETCH_ASSOC);
        if ($fetchedRow === false) {
            $this->status = self::STATUS_STREAMED;
            $this->resultRowStream->closeCursor();
            $this->resultRowStream = null;
            return false;
        }
        return new ArkDatabaseQueryResultRow($fetchedRow);
    }
}