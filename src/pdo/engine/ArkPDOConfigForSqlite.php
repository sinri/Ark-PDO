<?php

namespace sinri\ark\database\pdo\engine;

use PDO;
use sinri\ark\database\pdo\ArkPDOConfig;

/**
 * @inheritDoc
 * @property string address
 * @since 2.2
 */
class ArkPDOConfigForSqlite extends ArkPDOConfig
{
    const ENGINE = "sqlite";

    const ADDRESS_IN_MEMORY = ":memory:";

    const CONFIG_ADDRESS = "address";

    public function __construct($dict = null)
    {
        parent::__construct($dict);
        $this->setEngine(self::ENGINE);
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
        return $this;
    }

    public function initializePDO(): PDO
    {
        return new PDO($this->getDSN());
    }

    protected function getDSN(): string
    {
        return "sqlite:{$this->address}";
    }
}