<?php


namespace sinri\ark\database\model\query;

/**
 * Class ArkDatabaseSelectFieldMeta
 * @package sinri\ark\database\model\query
 * @since 2.0
 */
class ArkDatabaseSelectFieldMeta
{
    /**
     * @var string
     */
    protected $fieldExpression;
    /**
     * @var string
     */
    protected $alias;

    public function __construct(string $fieldExpression, string $alias = '')
    {
        $this->fieldExpression = $fieldExpression;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getFieldExpression(): string
    {
        return $this->fieldExpression;
    }

    /**
     * @param string $fieldExpression
     * @return ArkDatabaseSelectFieldMeta
     */
    public function setFieldExpression(string $fieldExpression): ArkDatabaseSelectFieldMeta
    {
        $this->fieldExpression = $fieldExpression;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return ArkDatabaseSelectFieldMeta
     */
    public function setAlias(string $alias): ArkDatabaseSelectFieldMeta
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function generateSQLComponent(): string
    {
        return $this->getFieldExpression() . ($this->alias === '' ? '' : ' as ' . $this->alias);
    }

    /**
     * @param ArkDatabaseSelectFieldMeta[] $selectFields
     * @return string
     */
    public static function generateFieldSQLComponent(array $selectFields)
    {
        $fields = [];
        foreach ($selectFields as $fieldMeta) {
            $fields[] = $fieldMeta->generateSQLComponent();
        }
        return implode(',', $fields);
    }
}