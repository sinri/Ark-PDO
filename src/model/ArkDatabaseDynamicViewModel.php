<?php

namespace sinri\ark\database\model;

use sinri\ark\database\pdo\ArkPDO;

/**
 * @since 2.1.x
 */
class ArkDatabaseDynamicViewModel extends ArkDatabaseTableReaderModel
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
     */
    public function mappingSchemeName(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
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