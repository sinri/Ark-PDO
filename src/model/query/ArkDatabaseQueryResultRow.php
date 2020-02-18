<?php


namespace sinri\ark\database\model\query;

use sinri\ark\core\ArkHelper;

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

    public function __isset($name)
    {
        return isset($this->row[$name]);
    }

    public function __construct(array $row)
    {
        $this->row = $row;
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
}