<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 14:33
 */

namespace sinri\ark\database\pdo;


use Exception;
use PDO;
use PDOStatement;
use sinri\ark\core\ArkLogger;
use sinri\ark\database\exception\ArkPDOConfigError;
use sinri\ark\database\exception\ArkPDORollbackSituation;
use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\exception\ArkPDOStatementException;

class ArkPDO
{
    /**
     * @var ArkPDOConfig
     */
    protected $pdoConfig;
    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var ArkLogger
     */
    protected $logger;

    /**
     * ArkPDO constructor.
     * @param ArkPDOConfig|null $config
     */
    public function __construct($config = null)
    {
        $this->logger = ArkLogger::makeSilentLogger();
        $this->pdoConfig = $config;
    }

    /**
     * @return ArkPDOConfig
     */
    public function getPdoConfig(): ArkPDOConfig
    {
        return $this->pdoConfig;
    }

    /**
     * @param ArkPDOConfig $pdoConfig
     */
    public function setPdoConfig(ArkPDOConfig $pdoConfig)
    {
        $this->pdoConfig = $pdoConfig;
    }

    /**
     * Connect to Database and make self::pdo an instance.
     * @throws ArkPDOConfigError
     */
    public function connect()
    {
        if (!is_a($this->pdoConfig, ArkPDOConfig::class)) {
            throw new ArkPDOConfigError();
        }

        $engine = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_ENGINE, ArkPDOConfig::ENGINE_MYSQL);
        $host = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_HOST);
        $port = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_PORT);
        $username = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_USERNAME);
        $password = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_PASSWORD);
        $database = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_DATABASE);
        $charset = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_CHARSET, ArkPDOConfig::CHARSET_UTF8);
        $options = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_OPTIONS);

        $pairs = [
            ArkPDOConfig::CONFIG_HOST => $host,
            ArkPDOConfig::CONFIG_PORT => $port,
            ArkPDOConfig::CONFIG_USERNAME => $username,
            ArkPDOConfig::CONFIG_PASSWORD => $password,
            ArkPDOConfig::CONFIG_CHARSET => $charset,
        ];
        foreach ($pairs as $fieldName => $fieldValue) {
            if (empty($fieldValue)) {
                throw new ArkPDOConfigError($fieldName, $fieldValue);
            }
        }

        $engine = strtolower($engine);
        switch ($engine) {
            case ArkPDOConfig::ENGINE_MYSQL:
                if ($options === null) {
                    $options = [
                        PDO::ATTR_EMULATE_PREPARES => false
                    ];
                }
                $dsn = "mysql:host={$host};port={$port};charset={$charset}";
                if (!empty($database)) {
                    $dsn .= ";dbname={$database}";
                }
                $this->pdo = new PDO(
                    $dsn,
                    $username,
                    $password,
                    $options
                );
                if (!empty($database)) {
                    $this->pdo->exec("use `{$database}`;");
                }
                if (!empty($charset)) {
                    $this->pdo->query("set names " . $charset);
                }
                break;
            default:
                throw new ArkPDOConfigError(ArkPDOConfig::CONFIG_ENGINE, $engine);
        }
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $sql
     * @return array
     * @throws ArkPDOStatementException
     */
    public function getAll(string $sql): array
    {
        $stmt = $this->buildPDOStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param string $rowClass
     * @param array $constructArguments
     * @return array array of instances of given `row class`
     */
    public function getAllAsClassInstanceArray(string $sql, string $rowClass, array $constructArguments = []): array
    {
        $statement = $this->buildPDOStatement($sql);
        return $statement->fetchAll(PDO::FETCH_CLASS, $rowClass, $constructArguments);
    }

    /**
     * @param string $sql
     * @param bool $usePrepare
     * @return PDOStatement
     * @throws ArkPDOStatementException
     */
    protected function buildPDOStatement(string $sql, $usePrepare = false): PDOStatement
    {
        if ($usePrepare) {
            $statement = $this->pdo->prepare($sql);
        } else {
            $statement = $this->pdo->query($sql);
        }
        if (!$statement) {
            $this->logger->error(
                "PDO Statement Building Failure Occurred.",
                ["sql" => $sql,]
            );
            throw new ArkPDOStatementException($sql);
        } else {
            $this->logger->debug("PDO Statement Generated.", ["sql" => $sql]);
        }
        return $statement;
    }

    /**
     * @param string $sql
     * @param null|string|int $field
     * @return array
     * @throws ArkPDOStatementException
     */
    public function getCol(string $sql, $field = null): array
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_BOTH);
        if ($field === null) $field = 0;
        return array_column($rows, $field);
    }

    /**
     * @param string $sql
     * @return array|bool
     * @throws ArkPDOStatementException
     */
    public function getRow(string $sql)
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows) || count($rows) < 1) return false;
        return $rows[0];
    }

    /**
     * @param string $sql
     * @return mixed|bool
     * @throws ArkPDOStatementException
     */
    public function getOne(string $sql): bool
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows) || count($rows) < 1) return false;
        $row = $rows[0];
        if (!is_array($row) || count($row) < 1) return false;
        $row = array_values($row);
        return $row[0];
    }

    /**
     * @param string $sql
     * @param callable|array $callback function($row,$index):bool; format of $row is decided by fetchStyle and $index starts since 1; return false would stop fetching next row.
     * @param int $fetchStyle
     * @return int How many rows fetched and processed
     * @throws ArkPDOStatementException
     * @since 1.6.0
     */
    public function getAllAsStream(string $sql, $callback, $fetchStyle = PDO::FETCH_ASSOC): int
    {
        return $this->safeQueryAllAsStream($sql, [], $callback, $fetchStyle);
    }

    /**
     * @param string $sql
     * @return int|false affected row count(might be zero anyway), or false on error
     */
    public function exec(string $sql)
    {
        $this->logger->debug("Ready to execute sql", ["sql" => $sql]);
        return $this->pdo->exec($sql);
    }

    /**
     * @param string $sql
     * @param null $pk
     * @return int|false 0 for no row inserted, false for error
     */
    public function insert(string $sql, $pk = null)
    {
        $this->logger->debug("Ready to execute insert sql", ["sql" => $sql]);
        $rows = $this->pdo->exec($sql);
        if ($rows === false) return false;
        if ($rows === 0) return 0;
        return $this->pdo->lastInsertId($pk);
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @param callable|array $callback such as function(...$parameters)
     * @param array $parameters
     * @return mixed only return when success
     * @throws ArkPDORollbackSituation throw one when error, any exception as its `previous`
     * @since 1.1
     * @since 2.0.20 throw certainly `ArkPDORollbackSituation`
     */
    public function executeInTransaction($callback, $parameters = [])
    {
        $this->beginTransaction();
        try {
            $result = call_user_func_array($callback, $parameters);
            $this->commit();
            return $result;
        } catch (Exception $exception) {
            $this->rollBack();
            throw new ArkPDORollbackSituation($exception);
        }
    }

    /**
     * @return mixed
     */
    public function getPDOErrorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * @return array
     */
    public function getPDOErrorInfo(): array
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @return string
     * @since 1.6.0
     * @since 2.0.16 changed format
     */
    public function getPDOErrorDescription(): string
    {
        return "PDO ERROR: " . implode(";", $this->getPDOErrorInfo());
    }

    /**
     * @param string $sql
     * @param array $values
     * @param callable|array $callback function($row,$index):bool; format of $row is decided by fetchStyle and $index starts since 1; return false would stop fetching next row.
     * @param int $fetchStyle
     * @return int How many rows fetched and processed
     * @throws ArkPDOStatementException
     * @since 1.6.0
     */
    public function safeQueryAllAsStream(string $sql, array $values, $callback, $fetchStyle = PDO::FETCH_ASSOC): int
    {
        $stmt = $this->buildPDOStatement($sql, true);
        $stmt->execute($values);
        $index = 0;
        while (true) {
            $row = $stmt->fetch($fetchStyle);
            if ($row === false) break;
            $index += 1;
            $shouldContinue = call_user_func_array($callback, [$row, $index]);
            if (!$shouldContinue) break;
        }
        return $index;
    }

    /**
     * @param string $sql
     * @param array $values
     * @param int $fetchStyle
     * @return array
     * @throws ArkPDOStatementException
     */
    public function safeQueryAll(string $sql, $values = array(), $fetchStyle = PDO::FETCH_ASSOC): array
    {
        $sth = $this->buildPDOStatement($sql, true);
        $sth->execute($values);
        return $sth->fetchAll($fetchStyle);
    }

    /**
     * @param string $sql
     * @param array $values
     * @return mixed
     * @throws ArkPDOStatementException
     */
    public function safeQueryRow(string $sql, $values = array()): bool
    {
        $sth = $this->buildPDOStatement($sql, true);
        if ($sth->execute($values)) {
            return $sth->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * @param string $sql
     * @param array $values
     * @return string
     * @throws ArkPDOStatementException
     */
    public function safeQueryOne(string $sql, $values = array())
    {
        $sth = $this->buildPDOStatement($sql, true);
        if ($sth->execute($values)) {
            return $sth->fetchColumn();
        }
        return false;
    }

    /**
     * @param string $sql
     * @param array $values
     * @param int $insertedId
     * @param null $pk
     * @return bool
     * @throws ArkPDOStatementException
     */
    public function safeInsertOne(string $sql, $values = array(), &$insertedId = 0, $pk = null): bool
    {
        $sth = $this->buildPDOStatement($sql, true);
        $done = $sth->execute($values);
        if ($done)
            $insertedId = $this->pdo->lastInsertId($pk);
        return $done;
    }

    /**
     * @param string $sql
     * @param array $values
     * @param PDOStatement|null $sth @since 1.3.3
     * @return bool
     * @throws ArkPDOStatementException
     */
    public function safeExecute(string $sql, $values = array(), &$sth = null): bool
    {
        $sth = $this->buildPDOStatement($sql, true);
        return $sth->execute($values);
    }

    /**
     * @param null|string $pk
     * @return string
     * @since 1.3.3
     */
    public function getLastInsertID($pk = null): string
    {
        return $this->pdo->lastInsertId($pk);
    }

    /**
     * PDOStatement::rowCount() 返回上一个由对应的 PDOStatement 对象执行 DELETE 、 INSERT 、或 UPDATE 语句受影响的行数。
     * 如果上一条由相关 PDOStatement 执行的 SQL 语句是一条 SELECT 语句，有些数据可能返回由此语句返回的行数。
     * 但这种方式不能保证对所有数据有效，且对于可移植的应用不应依赖于此方式。
     * @param PDOStatement $statement
     * @return int
     */
    public function getAffectedRowCount(PDOStatement $statement): int
    {
        return $statement->rowCount();
    }

    /**
     * 比PDO更加丧心病狂的SQL模板
     * @param $template
     * @param array $parameters
     * @return string
     * @throws ArkPDOSQLBuilderError
     * @since 2.1.11
     *  Sample SQL:
     * select key_field,value,`?`
     * from `?`.`?`
     * where key_field in (?)
     * and status = ?
     * limit [?] , [?]
     *  RULE:
     * (1) `?` => $p
     * (2)  ?  => quote($p)
     * (3) (?) => (quote($p[]), ...)
     * (4) [?] => integer_value($p)
     * (5) {?} => float_value($p)
     */
    public function safeBuildSQL($template, $parameters = []): string
    {
        $this->logger->debug($template, ['parameters' => $parameters]);
        $count = preg_match_all('/\?|`\?`|\(\?\)|\[\?]|{\?}/', $template, $matches, PREG_OFFSET_CAPTURE);
        $this->logger->debug("preg_match_all count=" . json_encode($count), ['matches' => $matches]);
        if ($count === 0) {
            return $template;
        }
//        if (!$count) {
//            throw new ArkPDOSQLBuilderError("The sql template is not correct.",$template);
//        }
        if ($count !== count($parameters)) {
            throw new ArkPDOSQLBuilderError(
                "The sql template has not correct number of parameters.",
                $template . ' <--- ' . json_encode($parameters)
            );
        }

        $parts = [];
        $currentIndex = 0;
        for ($x = 0; $x < $count; $x++) {
            $sought = $matches[0][$x];
            $keyword = $sought[0];
            $index = $sought[1];

            if ($index != $currentIndex) {
                $piece = substr($template, $currentIndex, $index - $currentIndex);
                //$this->debug(__METHOD__.'@'.__LINE__." piece: ".$piece,[$currentIndex,($index - $currentIndex)]);
                $parts[] = $piece;
                $currentIndex = $index;
                //$this->debug(__METHOD__.'@'.__LINE__." current index -> ".$currentIndex);
            }
            $parts[] = $keyword;
            $currentIndex = $currentIndex + strlen($keyword);
            //$this->debug(__METHOD__.'@'.__LINE__." piece: ",$keyword);
            //$this->debug(__METHOD__.'@'.__LINE__." current index -> ",$currentIndex);
        }
        if ($currentIndex < strlen($template)) {
            $piece = substr($template, $currentIndex);
            $parts[] = $piece;
            //$this->debug(__METHOD__ . '@' . __LINE__ . " piece: ", $piece);
        }

        $this->logger->debug("parts", ['parts' => $parts]);

        $sql = "";
        $ptr = 0;
        foreach ($parts as $part) {
            switch ($part) {
                // RULE:
                // (1) `?` => $p
                case '`?`':
                    {
                        $sql .= '`' . $parameters[$ptr] . '`';
                        $ptr++;
                    }
                    break;
                // (2)  ?  => quote($p)
                case '?':
                    {
                        $sql .= $this->quote($parameters[$ptr]);
                        $ptr++;
                    }
                    break;
                // (3) (?) => (quote($p[]),...)
                case '(?)':
                    {
                        if (is_array($parameters[$ptr])) {
                            $group = [];
                            foreach ($parameters[$ptr] as $object) {
                                $group[] = $this->quote($object);
                            }
                            $sql .= '(' . implode(",", $group) . ')';
                        } else {
                            $sql .= '(' . $parameters[$ptr] . ')';
                        }
                        $ptr++;
                    }
                    break;
                // (4) [?] => int val of ($p)
                case '[?]':
                    {
                        $sql .= intval($parameters[$ptr]);
                        $ptr++;
                    }
                    break;
                // (5) {?} => float val of ($p)
                case '{?}':
                    {
                        $sql .= floatval($parameters[$ptr]);
                        $ptr++;
                    }
                    break;
                default:
                    $sql .= $part;
            }
        }

        return $sql;
    }

    /**
     * @param $string
     * @param int $parameterType \PDO::PARAM_STR or \PDO::PARAM_INT
     * @return string
     */
    public function quote($string, $parameterType = PDO::PARAM_STR)
    {
        if (!$this->pdo) {
            if ($parameterType == PDO::PARAM_INT) {
                return intval($string);
            }
            return self::dryQuote($string);
        }
        return $this->pdo->quote($string, $parameterType);
    }

    /**
     * @param $inp
     * @return array|mixed
     * @since 2.1.11
     * @since 2.0.31 dry quote '' => "''"
     */
    public static function dryQuote($inp)
    {
        if (is_array($inp))
            return array_map([__CLASS__, __METHOD__], $inp);

        if (is_string($inp)) {
            $x = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
            return "'{$x}'";
        }

        return $inp;
    }

    const QUOTE_TYPE_RAW = 'RAW';
    const QUOTE_TYPE_FIELD = 'FIELD';
    const QUOTE_TYPE_VALUE = 'VALUE';
    const QUOTE_TYPE_INT = 'INT';
    const QUOTE_TYPE_FLOAT = 'FLOAT';
    const QUOTE_TYPE_STRING = 'STRING';

    const CONST_TRUE = "TRUE";
    const CONST_FALSE = "FALSE";
    const CONST_NULL = "NULL";

    /**
     * @param scalar $x
     * @param string $quoteType
     * @return string
     * @since 2.1.x
     */
    public static function quoteScalar($x, $quoteType): string
    {
        if ($quoteType === self::QUOTE_TYPE_RAW) {
            // $x must be a variable that could be transformed into string
            return $x;
        }
        if ($quoteType === self::QUOTE_TYPE_FIELD) {
            // $x must be a string
            return '`' . $x . '`';
        }
        if ($quoteType === self::QUOTE_TYPE_VALUE) {
            if ($x === null) {
                return self::CONST_NULL;
            }
            if ($x === false) {
                return self::CONST_FALSE;
            }
            if ($x === true) {
                return self::CONST_TRUE;
            }
            if (is_int($x)) {
                $quoteType = self::QUOTE_TYPE_INT;
            } elseif (is_float($x)) {
                $quoteType = self::QUOTE_TYPE_FLOAT;
            } else {
                $quoteType = self::QUOTE_TYPE_STRING;
            }
        }
        switch ($quoteType) {
            case self::QUOTE_TYPE_INT:
                return '' . intval($x);
            case self::QUOTE_TYPE_FLOAT:
                return '' . floatval($x);
            case self::QUOTE_TYPE_STRING:
            default:
                return self::dryQuote($x);
        }
    }
}