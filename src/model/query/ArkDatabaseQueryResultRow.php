<?php


namespace sinri\ark\database\model\query;

use sinri\ark\core\ArkHelper;
use sinri\ark\database\exception\ArkPDOQueryResultFinishedStreamingSituation;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotQueriedError;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotStreamingError;

/**
 * Class ArkDatabaseQueryResultRow
 * @package sinri\ark\database\model\query
 * @since 2.0
 */
class ArkDatabaseQueryResultRow
{
    /**
     * @var array Key-Value Pair
     */
    protected $row;

    /**
     * ArkDatabaseQueryResultRow constructor.
     * @param array $row
     * @since 2.0.22 row could be empty so it could be omitted now.
     */
    public function __construct(array $row = [])
    {
        $this->row = $row;
    }

    public function __isset($name): bool
    {
        return isset($this->row[$name]);
    }

    public function __get($name)
    {
        return $this->row[$name];
    }

    /**
     * @return array
     */
    public function getRawRow(): array
    {
        return $this->row;
    }

    /**
     * @param string $fieldName
     * @param mixed $default
     * @return mixed
     */
    public function getField(string $fieldName, $default = null)
    {
        return ArkHelper::readTarget($this->row, [$fieldName], $default);
    }

    /**
     * @param ArkDatabaseSelectTableQuery $selection
     * @param ArkDatabaseQueryResult $result
     * @return static[]
     * @throws ArkPDOQueryResultIsNotQueriedError
     * @since 2.0.21
     */
    public static function fetchRowsWithSelection(ArkDatabaseSelectTableQuery $selection, &$result)
    {
        $result = $selection->queryForRows(static::class);
        return $result->getResultRows();
    }

    /**
     * @param ArkDatabaseQueryResult $result
     * @return static
     * @throws ArkPDOQueryResultFinishedStreamingSituation
     * @throws ArkPDOQueryResultIsNotStreamingError
     * @since 2.0.21
     */
    public static function fetchRowFromStream(ArkDatabaseQueryResult $result)
    {
        return $result->readNextRow(static::class);
    }

    /**
     * @param ArkDatabaseQueryResult $result
     * @return static
     * @throws ArkPDOQueryResultFinishedStreamingSituation
     * @throws ArkPDOQueryResultIsNotStreamingError
     * @since 2.0.25
     */
    public function fetchRowFromStreamAndReloadThis(ArkDatabaseQueryResult $result)
    {
        return $result->readNextRowAndReloadRowClassInstance($this);
    }
}