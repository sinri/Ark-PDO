<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 14:38
 */

namespace sinri\ark\database\model;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\exception\LookUpTargetException;
use sinri\ark\database\exception\ArkPDOStatementException;
use sinri\ark\database\pdo\ArkPDO;

class ArkDatabaseTableFieldDefinition
{
    protected string $name;
    protected string $type;
    protected string $typeCategory;
    protected bool $nullable;

    protected function __construct()
    {
    }

    /**
     * @param $row
     * @return ArkDatabaseTableFieldDefinition
     */
    public static function makeInstanceWithDescResultRow($row): ArkDatabaseTableFieldDefinition
    {
        $field = new ArkDatabaseTableFieldDefinition();
        $field->name = ArkHelper::readTarget($row, 'Field');
        $field->nullable = ArkHelper::readTarget($row, 'Null', 'NO') === 'YES';

        $field->type = ArkHelper::readTarget($row, 'Type', '');
        if (preg_match('/^[A-Za-z0-9]+/', $field->type, $matches)) {
            $field->typeCategory = self::determineTypeCategory($matches[0]);
        } else {
            $field->typeCategory = $field->type;
        }

        return $field;
    }

    protected static function determineTypeCategory($type): string
    {
        $type = strtolower($type);
        //for bigint it sometimes sucks for PHP when number too large
        //SERIAL is an alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE.
        //boolean: actually tinyint(1)
        //year: maybe timestamp or time need integer?
        return match ($type) {
            'bit', 'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint' => "integer",
            'decimal', 'dec', 'double', 'real' => 'double',
            'float' => 'float',
            'bool', 'boolean' => "int",
            'data', 'datetime', 'timestamp', 'time', 'year', 'char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'tinytext', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob', 'longtext', 'enum', 'set' => 'string',
            default => "string",
        };
    }

    /**
     * @return string
     */
    public function getTypeCategory(): string
    {
        return $this->typeCategory;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function getNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     */
    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    /**
     * @param ArkPDO $db
     * @param string $tableExpression
     * @return ArkDatabaseTableFieldDefinition[]
     * @throws ArkPDOStatementException
     * @throws LookUpTargetException
     */
    public static function loadTableDesc(ArkPDO $db, string $tableExpression): array
    {
        $fieldDefinition = [];
        $field_list = $db->getAll("desc " . $tableExpression);
        if (empty($field_list)) {
            throw new LookUpTargetException("Seems no such table " . $tableExpression);
        }
        foreach ($field_list as $field) {
            $fieldDefinition[$field['Field']] = ArkDatabaseTableFieldDefinition::makeInstanceWithDescResultRow($field);
        }
        return $fieldDefinition;
    }

    /**
     * When you design a model for a certain table which is eventually designed,
     * you might run this method to get `@property` lines for the model class PHPDoc.
     * @param ArkDatabaseTableCoreModel $model
     * @throws ArkPDOStatementException
     * @throws LookUpTargetException
     */
    public static function devShowFieldsForPHPDoc(ArkDatabaseTableCoreModel $model): void
    {
        echo "THIS IS A HELPER FOR DEV." . PHP_EOL;
        $fieldDefinition = self::loadTableDesc($model->db(), $model->getTableExpressForSQL());
        foreach ($fieldDefinition as $definition) {
            echo " * @property " . $definition->getTypeCategory() . ' ' . $definition->getName() . PHP_EOL;
        }
    }
}