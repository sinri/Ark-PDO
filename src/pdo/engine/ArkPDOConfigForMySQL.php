<?php

namespace sinri\ark\database\pdo\engine;

use PDO;
use sinri\ark\database\exception\ArkPDOConfigError;
use sinri\ark\database\pdo\ArkPDOConfig;

/**
 * @inheritDoc
 * @property string $host
 * @property int $port
 * @property string $username
 * @property string $password
 * @property string $database
 * @property string $charset
 * @property null|array options
 * @since 2.2
 */
class ArkPDOConfigForMySQL extends ArkPDOConfig
{
    const ENGINE = "mysql";

    const CHARSET_UTF8 = "utf8";
    const CHARSET_UTF8MB4 = "utf8mb4";

    const CONFIG_HOST = "host";
    const CONFIG_PORT = "port";
    const CONFIG_USERNAME = "username";
    const CONFIG_PASSWORD = "password";
    const CONFIG_DATABASE = "database";
    const CONFIG_CHARSET = "charset";
    const CONFIG_OPTIONS = "options";

    public function __construct($dict = null)
    {
        parent::__construct($dict);
        $this->setEngine(self::ENGINE);

        if (null === $this->getConfigField(self::CONFIG_CHARSET)) {
            $this->setCharset("utf8");
        }
        if (null === $this->getConfigField(self::CONFIG_PORT)) {
            $this->setPort("3306");
        }
        if (null === $this->getConfigField(self::CONFIG_TITLE)) {
            $this->setTitle(uniqid('ArkPDO-'));
        }
    }

    public function setCharset($value): ArkPDOConfig
    {
        $field = self::CONFIG_CHARSET;
        $this->$field = $value;
        return $this;
    }

    public function setPort($value): ArkPDOConfig
    {
        $field = self::CONFIG_PORT;
        $this->$field = intval($value);
        return $this;
    }

    public function setHost($value): ArkPDOConfig
    {
        $field = self::CONFIG_HOST;
        $this->$field = $value;
        return $this;
    }

    public function setUsername($value): ArkPDOConfig
    {
        $field = self::CONFIG_USERNAME;
        $this->$field = $value;
        return $this;
    }

    public function setPassword($value): ArkPDOConfig
    {
        $field = self::CONFIG_PASSWORD;
        $this->$field = $value;
        return $this;
    }

    public function setDatabase($value): ArkPDOConfig
    {
        $field = self::CONFIG_DATABASE;
        $this->$field = $value;
        return $this;
    }

    /**
     * NOTE, Aliyun DRDS need [\PDO::ATTR_EMULATE_PREPARES=>true] here!
     * @param $value
     * @return $this
     */
    public function setOptions($value): ArkPDOConfig
    {
        $field = self::CONFIG_OPTIONS;
        $this->$field = $value;
        return $this;
    }

    public function initializePDO(): PDO
    {
        $options = $this->options;
        if ($options === null) {
            $options = [
                PDO::ATTR_EMULATE_PREPARES => false
            ];
        }
        $pdo = new PDO(
            $this->getDSN(),
            $this->username,
            $this->password,
            $options
        );
        if (!empty($this->database)) {
            $pdo->exec("use `{$this->database}`;");
        }
        if (!empty($this->charset)) {
            $pdo->query("set names " . $this->charset);
        }

        return $pdo;
    }

    protected function getDSN(): string
    {
        $pairs = [
            self::CONFIG_HOST => $this->host,
            self::CONFIG_PORT => $this->port,
            self::CONFIG_USERNAME => $this->username,
            self::CONFIG_PASSWORD => $this->password,
            self::CONFIG_CHARSET => $this->charset,
        ];
        foreach ($pairs as $fieldName => $fieldValue) {
            if (empty($fieldValue)) {
                throw new ArkPDOConfigError($fieldName, $fieldValue);
            }
        }

        $dsn = "mysql:host={$this->host};port={$this->port};charset={$this->charset}";
        if (!empty($this->database)) {
            $dsn .= ";dbname={$this->database}";
        }
        return $dsn;
    }
}