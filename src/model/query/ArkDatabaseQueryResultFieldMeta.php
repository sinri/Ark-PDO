<?php


namespace sinri\ark\database\model\query;

/**
 * Class ArkDatabaseQueryResultFieldMeta
 * @package sinri\ark\database\model\query
 * @since 2.0.25
 */
class ArkDatabaseQueryResultFieldMeta
{
    /**
     * @var array
     */
    protected $meta;

    public function __construct(array $meta)
    {
        $this->meta = $meta;
    }

    /**
     * The PHP native type used to represent the column value.
     * @return string
     */
    public function getNativeType(): string
    {
        return $this->meta['native_type'];
    }

    /**
     * The type of this column as represented by the [PDO::PARAM_* constants]
     * @see https://www.php.net/manual/en/pdo.constants.php
     * @return int
     */
    public function getPDOType(): int
    {
        return $this->meta['pdo_type'];
    }

    /**
     * Any flags set for this column.
     * @return array
     */
    public function getFlags()
    {
        return $this->meta['flags'];
    }

    /**
     * The name of this column's table as returned by the database.
     * @return string
     */
    public function getTable(): string
    {
        return $this->meta['table'];
    }

    /**
     * The name of this column as returned by the database.
     * @return string
     */
    public function getName(): string
    {
        return $this->meta['name'];
    }

    /**
     * The length of this column.
     * Normally -1 for types other than floating point decimals.
     * @return int
     */
    public function getLen(): int
    {
        return $this->meta['len'];
    }

    /**
     * The numeric precision of this column.
     * Normally 0 for types other than floating point decimals.
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->meta['precision'];
    }
}