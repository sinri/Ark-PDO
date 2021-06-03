<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 14:37
 */

namespace sinri\ark\database\model;


use sinri\ark\database\exception\ArkPDOSQLBuilderError;
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

    const MACRO_CASE = 'MACRO_CASE';

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
    protected $addition;
    /**
     * @var bool
     */
    protected $isFieldAsName;

    /**
     * ArkSQLCondition constructor.
     * @param string $field
     * @param string $operate
     * @param string|int|array $value
     * @param null|string $addition
     * @param bool $isFieldAsName
     */
    public function __construct(string $field, string $operate, $value, $addition = null, $isFieldAsName = true)
    {
        $this->field = $field;
        $this->operate = $operate;
        $this->value = $value;
        $this->addition = $addition;
        $this->isFieldAsName = $isFieldAsName;

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
     * @param scalar|scalar[] $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     * @since 2.0.9
     */
    public static function makeEqualOrInArray(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        if (is_array($value)) {
            return self::makeInArray($field, $value, $isFieldAsName);
        } else {
            return self::makeEqual($field, $value, $isFieldAsName);
        }
    }

    /**
     * @param string $field
     * @param scalar|scalar[] $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     * @since 2.0.9
     */
    public static function makeNotEqualNorInArray(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        if (is_array($value)) {
            return self::makeNotInArray($field, $value, $isFieldAsName);
        } else {
            return self::makeNotEqual($field, $value, $isFieldAsName);
        }
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeEqual(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_EQ, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeGreaterThan(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_GT, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeNoLessThan(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_EGT, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeLessThan(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_LT, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeNoGreaterThan(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_ELT, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeNotEqual(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NEQ, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeEqualNullSafe(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NULL_SAFE_EQUAL, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeIsNull(string $field, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_IS, self::CONST_NULL, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeIsNotNull(string $field, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_IS_NOT, self::CONST_NULL, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar[] $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeInArray(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_IN, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar[] $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeNotInArray(string $field, $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NOT_IN, $value, null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value1
     * @param scalar $value2
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeBetween(string $field, $value1, $value2, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_BETWEEN, [$value1, $value2], null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param scalar $value1
     * @param scalar $value2
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeNotBetween(string $field, $value1, $value2, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NOT_BETWEEN, [$value1, $value2], null, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeStringHasPrefix(string $field, string $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_LIKE, $value, self::LIKE_RIGHT_WILDCARD, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeStringHasSuffix(string $field, string $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_LIKE, $value, self::LIKE_LEFT_WILDCARD, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     */
    public static function makeStringContainsText(string $field, string $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_LIKE, $value, self::LIKE_BOTH_WILDCARD, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     * @since 2.0.8
     */
    public static function makeStringDoesNotHavePrefix(string $field, string $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NOT_LIKE, $value, self::LIKE_RIGHT_WILDCARD, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     * @since 2.0.8
     */
    public static function makeStringDoesNotHaveSuffix(string $field, string $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NOT_LIKE, $value, self::LIKE_LEFT_WILDCARD, $isFieldAsName);
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     * @since 2.0.8
     */
    public static function makeStringDoesNotContainText(string $field, string $value, $isFieldAsName = true): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::OP_NOT_LIKE, $value, self::LIKE_BOTH_WILDCARD, $isFieldAsName);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkSQLCondition
     */
    public static function makeConditionsIntersect(array $conditions): ArkSQLCondition
    {
        return new ArkSQLCondition('', self::OP_PARENTHESES_AND, $conditions);
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return ArkSQLCondition
     */
    public static function makeConditionsUnion(array $conditions): ArkSQLCondition
    {
        return new ArkSQLCondition('', self::OP_PARENTHESES_OR, $conditions);
    }

    /**
     * @param string $field
     * @return ArkSQLCondition
     * @since 1.7.3
     */
    public static function makeFieldIsNullOrEmptyString(string $field): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::MACRO_IS_NULL_OR_EMPTY_STRING, null);
    }

    /**
     * @param string $field
     * @return ArkSQLCondition
     * @since 1.7.3
     */
    public static function makeFieldIsNotNullNorEmptyString(string $field): ArkSQLCondition
    {
        return new ArkSQLCondition($field, self::MACRO_IS_NOT_NULL_NOR_EMPTY_STRING, null);
    }

    /**
     * @param string $rawExpression
     * @return ArkSQLCondition
     * @since 2.0
     */
    public static function makeRawConditionExpression(string $rawExpression): ArkSQLCondition
    {
        return new ArkSQLCondition('', self::MACRO_RAW_EXPRESSION, $rawExpression);
    }

    /**
     * @param string $expression such as A Sub SQL
     * @return ArkSQLCondition
     * @since 1.8.8
     */
    public static function makeExists(string $expression): ArkSQLCondition
    {
        return new ArkSQLCondition('', self::OP_EXISTS, $expression);
    }

    /**
     * @param string $expression such as A Sub SQL
     * @return ArkSQLCondition
     * @since 1.8.8
     */
    public static function makeNotExists(string $expression): ArkSQLCondition
    {
        return new ArkSQLCondition('', self::OP_NOT_EXISTS, $expression);
    }

    /**
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/flow-control-functions.html#operator_case
     *
     * @param string $target
     * @param array $whenThenPairs
     * @param string|null $else
     * @param bool $isFieldAsName
     * @return ArkSQLCondition
     *
     * @since 2.0.24
     */
    public static function makeCase(string $target, array $whenThenPairs, $else = null, $isFieldAsName = true)
    {
        return new ArkSQLCondition($target, self::MACRO_CASE, $whenThenPairs, $else, $isFieldAsName);
    }

    /**
     * @return string
     */
    private function getFieldExpression()
    {
        if ($this->isFieldAsName) {
            return "`{$this->field}`";
        } else {
            return "{$this->field}";
        }
    }


    /**
     * @return string
     * @throws ArkPDOSQLBuilderError
     */
    public function makeConditionSQL(): string
    {
        switch ($this->operate) {
            case self::OP_EQ:
            case self::OP_GT:
            case self::OP_EGT:
            case self::OP_LT:
            case self::OP_ELT:
            case self::OP_NEQ:
            case self::OP_NULL_SAFE_EQUAL:
                return $this->getFieldExpression() . " " . $this->operate . " " . ArkPDO::dryQuote($this->value);
            case self::OP_IS:
            case self::OP_IS_NOT:
                if (!in_array($this->value, [self::CONST_FALSE, self::CONST_TRUE, self::CONST_NULL])) {
                    throw new ArkPDOSQLBuilderError(
                        "ERROR, YOU MUST USE CONSTANT FOR IS COMPARISON!",
                        "{$this->field} {$this->operate} " . json_encode($this->value)
                    );
                }
                return $this->getFieldExpression() . " " . $this->operate . " " . $this->value;
            case self::OP_IN:
            case self::OP_NOT_IN:
                if (!is_array($this->value) || empty($this->value)) {
                    throw new ArkPDOSQLBuilderError(
                        "ERROR, YOU MUST GIVE AN ARRAY OF STRING FOR IN OPERATION!",
                        "{$this->field} {$this->operate} (" . json_encode($this->value) . ")"
                    );
                }
                $group = [];
                foreach ($this->value as $item) {
                    $group[] = ArkPDO::dryQuote($item);
                }
            return $this->getFieldExpression() . " " . $this->operate . " (" . implode(",", $group) . ")";
            case self::OP_LIKE:
            case self::OP_NOT_LIKE:
                // NOTE: value is preprocessed in constructor
            return $this->getFieldExpression() . " " . $this->operate . " " . ($this->value);
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
            return $this->getFieldExpression() . " " . $this->operate . " " . ArkPDO::dryQuote($this->value[0]) . " AND " . ArkPDO::dryQuote($this->value[1]);
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
                    throw new ArkPDOSQLBuilderError(
                        "Condition Set Empty",
                        "{$this->operate} ()"
                    );
                }
            return '(' . implode(" " . $this->operate . " ", $parts) . ')';
            case self::MACRO_IS_NULL_OR_EMPTY_STRING:
                return "(`{$this->field}` IS NULL OR `{$this->field}` = '')";
            case self::MACRO_IS_NOT_NULL_NOR_EMPTY_STRING:
                return "(`{$this->field}` IS NOT NULL AND `{$this->field}` <> '')";
            case self::MACRO_RAW_EXPRESSION:
                return $this->value;
            case self::MACRO_CASE:
                $x = 'CASE ';
                if (strlen(trim($this->field)) > 0) {
                    $x .= $this->getFieldExpression() . " ";
                }
                if (!is_array($this->value)) {
                    throw new ArkPDOSQLBuilderError('CASE branches is not set correctly', json_encode($this->value));
                }
                foreach ($this->value as $when => $then) {
                    $x .= "WHEN " . $when . " THEN " . $then . ' ';
                }
                if (is_string($this->addition) && strlen(trim($this->addition)) > 0) {
                    $x .= "ELSE " . $this->addition . " ";
                }
                $x .= "END";
                return $x;
            default:
                throw new ArkPDOSQLBuilderError("ERROR, UNKNOWN OPERATE", json_encode($this->operate));
        }
    }

    /**
     * @param ArkSQLCondition[] $conditions
     * @return string
     * @throws ArkPDOSQLBuilderError
     * @since 2.0
     */
    public static function generateConditionSQLComponent(array $conditions): string
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