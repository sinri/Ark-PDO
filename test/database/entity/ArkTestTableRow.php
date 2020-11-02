<?php


namespace sinri\ark\database\test\database\entity;


use sinri\ark\database\model\query\ArkDatabaseQueryResultRow;

/**
 * Class ArkTestTableRow
 * @package sinri\ark\database\test\database\entity
 * @property-read int id
 * @property-read string value
 * @property-read int|null score
 */
class ArkTestTableRow extends ArkDatabaseQueryResultRow
{
    /**
     * param ArkDatabaseQueryResultRow[] $rows
     * @param array $rows
     * @return ArkTestTableRow[]
     */
    public static function washRowsArray(array $rows)
    {
        return $rows;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return int|null
     */
    public function getScore()
    {
        return $this->score;
    }
}