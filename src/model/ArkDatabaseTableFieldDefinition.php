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
    protected string $comment;

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
        $field->comment = ArkHelper::readTarget($row, 'Comment', '');

        $field->type = ArkHelper::readTarget($row, 'Type', '');
        if (preg_match('/^[A-Za-z0-9]+/', $field->type, $matches)) {
            $field->typeCategory = self::determineTypeCategory($matches[0]);
        } else {
            $field->typeCategory = $field->type;
        }

        return $field;
    }

    protected static function determineTypeCategory(string $type): string
    {
        $type = strtolower($type);
        return match ($type) {
            'bit', 'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint' => "int",
            'SERIAL' => "string",
            'decimal', 'dec', 'double', 'real', 'float' => 'float',
            'bool', 'boolean' => "int",
            'data', 'datetime', 'timestamp', 'time', 'year' => 'string',
            'char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'tinytext', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob', 'longtext', 'enum', 'set' => 'string',
            default => "string",
        };
    }

    public function getTypeCategory(): string
    {
        return $this->typeCategory;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

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

    public function getComment(): string
    {
        return $this->comment;
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
//        $field_list = $db->getAll("desc " . $tableExpression);
        $field_list = $db->getAll("show full columns in " . $tableExpression);
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
        echo "THIS IS A HELPER FOR DEVELOPER TO GENERATE PHPDOC OF ArkDatabaseQueryResultRow." . PHP_EOL;
        echo "/**" . PHP_EOL;
//        echo " * DB: ".$model->db()->getPdoConfig()->title . PHP_EOL;
        echo " * TABLE: " . $model->getTableExpression() . PHP_EOL;
        $fieldDefinition = self::loadTableDesc($model->db(), $model->getTableExpression());
        foreach ($fieldDefinition as $definition) {
            echo " * @property-read "
                . $definition->getTypeCategory()
                . ($definition->nullable ? '|null' : '')
                . ' ' . $definition->getName()
                . ' [' . $definition->type . ']'
                . ' ' . $definition->getComment()
                . PHP_EOL;
        }
        echo " */" . PHP_EOL;
    }
}