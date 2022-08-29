<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 14:33
 */

namespace sinri\ark\database\pdo;


use PDO;
use sinri\ark\core\ArkHelper;

/**
 * Class ArkMySQLiConfig
 * @package sinri\ark\database\mysql
 * @property string $title
 * @property-read  string engine
 * @since 2.2 abstract
 */
abstract class ArkPDOConfig
{
    const CONFIG_TITLE = "title";

    const CONFIG_ENGINE = "engine";


    protected $dict;

    public function __construct($dict = null)
    {
        $this->dict = is_array($dict) ? $dict : [];
    }

    public function __set($name, $value)
    {
        ArkHelper::writeIntoArray($this->dict, $name, $value);
    }

    public function __get($name)
    {
        return ArkHelper::readTarget($this->dict, $name);
    }

    public function __isset($name): bool
    {
        return (isset($this->dict) && isset($this->dict[$name]));
    }

    protected function setEngine($value): ArkPDOConfig
    {
        $field = self::CONFIG_ENGINE;
        $this->$field = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value): ArkPDOConfig
    {
        $field = self::CONFIG_TITLE;
        $this->$field = $value;
        return $this;
    }

    public function getConfigField($name, $default = null)
    {
        return ArkHelper::readTarget($this->dict, $name, $default);
    }

    abstract protected function getDSN(): string;

    abstract public function initializePDO(): PDO;
}