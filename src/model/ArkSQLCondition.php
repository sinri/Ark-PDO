<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 14:37
 */

namespace sinri\ark\database\model;


use Exception;
use sinri\ark\database\pdo\ArkPDO;

class ArkSQLCondition
{
    const OP_EQ = "=";
    const OP_GT = ">";
    const OP_EGT = ">=";
    const OP_LT = "<";
    const OP_ELT = "<=";
    const OP_NEQ = "<>";
    const OP_NULL_SAFE_EQUAL = "<=>";
    const OP_IS = "IS";
    const OP_IS_NOT = "IS NOT";
    const OP_IN = "IN";
    const OP_NOT_IN = "NOT IN";
    const OP_LIKE = "LIKE";
    const OP_NOT_LIKE = "NOT LIKE";
    const OP_BETWEEN = "BETWEEN";
    const OP_NOT_BETWEEN = "NOT BETWEEN";
//    const OP_GREATEST="GREATEST";
//    const OP_LEAST="LEAST";

    const MACRO_IS_NULL_OR_EMPTY_STRING = "MACRO_IS_NULL_OR_EMPTY_STRING";
    const MACRO_IS_NOT_NULL_NOR_EMPTY_STRING = "MACRO_IS_NOT_NULL_NOR_EMPTY_STRING";

    const MACRO_RAW_EXPRESSION = "MACRO_RAW_EXPRESSION";

    const OP_EXISTS = "EXISTS";
    const OP_NOT_EXISTS = "NOT EXISTS";

    const OP_PARENTHESES_AND = "AND";
    const OP_PARENTHESES_OR = "OR";

    const CONST_TRUE = "TRUE";
    const CONST_FALSE = "FALSE";
    const CONST_NULL = "NULL";

    const LIKE_LEFT_WILDCARD = "LIKE_LEFT_WILDCARD";
    const LIKE_RIGHT_WILDCARD = "LIKE_RIGHT_WILDCARD";
    const LIKE_BOTH_WILDCARD = "LIKE_BOTH_WILDCARD";

    protected $operate;
    protected $field;
    protected $value;

    /**
     * ArkSQLCondition constructor.
     * @param string $field
     * @param string $operate
     * @param string|int|array $value
     * @param null|string $addition
     */
    public function __construct(string $field, string $operate, $value, $addition = null)
    {
        $this->field = $field;
        $this->operate = $operate;
        $this->value = $value;

        if ($this->operate === self::OP_LIKE || $this->operate === self::OP_NOT_LIKE) {
            $this->value = ArkPDO::dryQuote($this->value);
            switch ($addition) {
                case self::LIKE_LEFT_WILDCARD:
                    $this->value = "concat('%'," . $this->value . ")";
                    break;
                case self::LIKE_RIGHT_WILDCARD:
                    $this->value = "concat(" . $this->value . ",'%')";
                    break;
                case self::LIKE_BOTH_WILDCARD:
                    $this->value = "concat('%'," . $this->value . ",'%')";
                    break;
            }
        }
    }

    /**
     * @param string $field
     * @param int|string|array $value
     * @return ArkSQLCondition
     * @since 2.0.9
     */
    public static function makeEqualOrInArray(string $field, $value)
    {
        if (is_array($value)) {
            return self::makeInArray($field, $value);
        } else {
            return self::makeEqual($field, $value);
        }
    }

    /**
     * @param string $field
     * @param int|string|array $value
     * @return ArkSQLCondition
     * @since 2.0.9
     */
    public static function makeNotEqualNorInArray(string $field, $value)
    {
        if (is_array($value)) {
            return self::makeNotInArray($field, $value);
        } else {
            return self::makeNotEqual($field, $value);
        }
    }

    public static function makeEqual(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_EQ, $value);
    }

    public static function makeGreaterThan(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_GT, $value);
    }

    public static function makeNoLessThan(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_EGT, $value);
    }

    public static function makeLessThan(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_LT, $value);
    }

    public static function makeNoGreaterThan(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_ELT, $value);
    }

    public static function makeNotEqual(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_NEQ, $value);
    }

    public static function makeEqualNullSafe(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_NULL_SAFE_EQUAL, $value);
    }

    public static function makeIsNull(string $field)
    {
        return new ArkSQLCondition($field, self::OP_IS, self::CONST_NULL);
    }

    public static function makeIsNotNull(string $field)
    {
        return new ArkSQLCondition($field, self::OP_IS_NOT, self::CONST_NULL);
    }

    public static function makeInArray(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_IN, $value);
    }

    public static function makeNotInArray(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_NOT_IN, $value);
    }

    public static function makeBetween(string $field, $value1, $value2)
    {
        return new ArkSQLCondition($field, self::OP_BETWEEN, [$value1, $value2]);
    }

    public static function makeNotBetween(string $field, $value1, $value2)
    {
        return new ArkSQLCondition($field, self::OP_NOT_BETWEEN, [$value1, $value2]);
    }

    public static function makeStringHasPrefix(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_LIKE, $value, self::LIKE_RIGHT_WILDCARD);
    }

    public static function makeStringHasSuffix(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_LIKE, $value, self::LIKE_LEFT_WILDCARD);
    }

    public static function makeStringContainsText(string $field, $value)
    {
        return new ArkSQLCondition($field, self::OP_LIKE, $value, self::LIKE_BOTH_WILDCARD);
    }

    /**
     * @param string $field
     * @param string $value
     * @return ArkSQLCondition
     * @since 2.0.8
     */
    public static function makeStringDoesNotHavePrefix(string $field, string $value)
    {
        return new ArkSQLCondition($field, self::OP_NOT_LIKE, $value, self::LIKE_RIGHT_WILDCARD);
    }

    /**
     * @param string $field
     * @param string $value
     * @return ArkSQLCondition
     * @since 2.0.8
     */
    public static function makeStringDoesNotHaveSuffix(string $field, string $value)
    {
        return new ArkSQLCondition($field, self::OP_NOT_LIKE, $value, self::LIKE_LEFT_WILDCARD);
    }

    /**
     * @param string $field
     * @param string $value
     * @return ArkSQLCondition
     * @since 2.0.8
     */
    public static function makeStringDoesNotContainText(string $field, string $value)
    {
        return new ArkSQLCondition($field, self::OP_NOT_LIKE, $value, self::LIKE_BOTH_WILDCARD);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkSQLCondition
     */
    public static function makeConditionsIntersect(array $conditions)
    {
        return new ArkSQLCondition('', self::OP_PARENTHESES_AND, $conditions);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkSQLCondition
     */
    public static function makeConditionsUnion(array $conditions)
    {
        return new ArkSQLCondition('', self::OP_PARENTHESES_OR, $conditions);
    }

    /**
     * @param $field
     * @return ArkSQLCondition
     * @since 1.7.3
     */
    public static function makeFieldIsNullOrEmptyString(string $field)
    {
        return new ArkSQLCondition($field, self::MACRO_IS_NULL_OR_EMPTY_STRING, null);
    }

    /**
     * @param $field
     * @return ArkSQLCondition
     * @since 1.7.3
     */
    public static function makeFieldIsNotNullNorEmptyString(string $field)
    {
        return new ArkSQLCondition($field, self::MACRO_IS_NOT_NULL_NOR_EMPTY_STRING, null);
    }

    /**
     * @param string $rawExpression
     * @return ArkSQLCondition
     * @since 2.0
     */
    public static function makeRawConditionExpression(string $rawExpression)
    {
        return new ArkSQLCondition('', self::MACRO_RAW_EXPRESSION, $rawExpression);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function makeConditionSQL()
    {
        switch ($this->operate) {
            case self::OP_EQ:
            case self::OP_GT:
            case self::OP_EGT:
            case self::OP_LT:
            case self::OP_ELT:
            case self::OP_NEQ:
            case self::OP_NULL_SAFE_EQUAL:
                return "`{$this->field}` " . $this->operate . " " . ArkPDO::dryQuote($this->value);
            case self::OP_IS:
            case self::OP_IS_NOT:
                if (!in_array($this->value, [self::CONST_FALSE, self::CONST_TRUE, self::CONST_NULL])) {
                    throw new Exception("ERROR, YOU MUST USE CONSTANT FOR IS COMPARISION!");
                }
                return "`{$this->field}` " . $this->operate . " " . $this->value;
            case self::OP_IN:
            case self::OP_NOT_IN:
                if (!is_array($this->value) || empty($this->value)) {
                    throw new Exception("ERROR, YOU MUST GIVE AN ARRAY OF STRING FOR IN OPERATION!");
                }
                $group = [];
                foreach ($this->value as $item) {
                    $group[] = ArkPDO::dryQuote($item);
                }
                return "`{$this->field}` " . $this->operate . " (" . implode(",", $group) . ")";
            case self::OP_LIKE:
            case self::OP_NOT_LIKE:
                // NOTE: value is preprocessed in constructor
                return "`{$this->field}` " . $this->operate . " " . ($this->value);
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                return "`{$this->field}` " . $this->operate . " " . ArkPDO::dryQuote($this->value[0]) . " AND " . ArkPDO::dryQuote($this->value[1]);
            case self::OP_EXISTS:
            case self::OP_NOT_EXISTS:
                // NOTE: only value is used as raw sql string @since 1.5
                return "{$this->operate} (" . $this->value . ")";
            case self::OP_PARENTHESES_AND:
            case self::OP_PARENTHESES_OR:
                // NOTE: to support the parentheses @since 1.7.1
                $parts = [];
                if (is_array($this->value)) {
                    foreach ($this->value as $subSQLCondition) {
                        $parts[] = $subSQLCondition->makeConditionSQL();
                    }
                }
                if (empty($parts)) {
                    throw new Exception("Condition Set Empty");
                }
                return '(' . implode(" " . $this->operate . " ", $parts) . ')';
            case self::MACRO_IS_NULL_OR_EMPTY_STRING:
                return "(`{$this->field}` IS NULL OR `{$this->field}` = '')";
            case self::MACRO_IS_NOT_NULL_NOR_EMPTY_STRING:
                return "(`{$this->field}` IS NOT NULL AND `{$this->field}` <> '')";
            case self::MACRO_RAW_EXPRESSION:
                return $this->value;
            default:
                throw new Exception("ERROR, UNKNOWN OPERATE");
        }
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return string
     * @throws Exception
     * @since 2.0
     */
    public static function generateConditionSQLComponent(array $conditions)
    {
        $condition_sql = [];
        foreach ($conditions as $condition) {
            $condition_sql[] = $condition->makeConditionSQL();
        }
        if (empty($condition_sql)) {
            $condition_sql = '1=1';
        } else {
            $condition_sql = implode(' AND ', $condition_sql);
        }
        return $condition_sql;
    }
}