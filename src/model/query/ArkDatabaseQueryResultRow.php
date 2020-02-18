<?php


namespace sinri\ark\database\model\query;


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
}