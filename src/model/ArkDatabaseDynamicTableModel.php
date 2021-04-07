<?php


namespace sinri\ark\database\model;


use sinri\ark\database\pdo\ArkPDO;

/**
 * Class ArkDatabaseDynamicTableModel
 * @package sinri\ark\database\model
 * @since 1.7.0
 */
class ArkDatabaseDynamicTableModel extends ArkDatabaseTableCoreModel
{
    protected $pdo;
    protected $scheme;
    protected $table;

    /**
     * ArkDatabaseDynamicTableModel constructor.
     * @param ArkPDO $pdo
     * @param string $table
     * @param string $scheme
     */
    public function __construct(ArkPDO $pdo, string $table, string $scheme = '')
    {
        $this->pdo = $pdo;
        $this->scheme = $scheme;
        $this->table = $table;
    }

    /**
     * @return string
     * @since 1.6.2
     */
    public function mappingSchemeName(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     * @since 1.6.2
     */
    public function mappingTableName(): string
    {
        return $this->table;
    }

    /**
     * @return ArkPDO
     */
    public function db(): ArkPDO
    {
        return $this->pdo;
    }
}