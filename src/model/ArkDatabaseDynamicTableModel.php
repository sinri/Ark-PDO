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
    public function __construct($pdo, $table, $scheme = null)
    {
        $this->pdo = $pdo;
        $this->scheme = $scheme;
        $this->table = $table;
    }

    /**
     * @return null|string
     * @since 1.6.2
     */
    public function mappingSchemeName()
    {
        return $this->scheme;
    }

    /**
     * @return string
     * @since 1.6.2
     */
    public function mappingTableName()
    {
        return $this->table;
    }

    /**
     * @return ArkPDO
     */
    public function db()
    {
        return $this->pdo;
    }
}