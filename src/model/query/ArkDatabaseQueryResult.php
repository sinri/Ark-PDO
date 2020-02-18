<?php


namespace sinri\ark\database\model\query;

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
     * @var ArkDatabaseQueryResultRow[]
     */
    protected $resultRows;
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
     */
    public function getResultRows()
    {
        return $this->resultRows;
    }

    /**
     * @return array[]
     */
    public function getRawMatrix()
    {
        $matrix = [];
        foreach ($this->resultRows as $resultRow) {
            $matrix[] = $resultRow->getRawRow();
        }
        return $matrix;
    }
}