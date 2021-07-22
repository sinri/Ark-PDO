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
use sinri\ark\database\exception\ArkPDOExecutedWithEmptyResultSituation;
use sinri\ark\database\exception\ArkPDOExecuteFailedError;
use sinri\ark\database\exception\ArkPDOExecuteNotAffectedError;
use sinri\ark\database\exception\ArkPDORollbackSituation;
use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\exception\ArkPDOStatementException;
use UnexpectedValueException;

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

        if (empty($host)) {
            throw new ArkPDOConfigError(ArkPDOConfig::CONFIG_HOST, $host);
        }
        if (empty($port)) {
            throw new ArkPDOConfigError(ArkPDOConfig::CONFIG_PORT, $port);
        }
        if (empty($username)) {
            throw new ArkPDOConfigError(ArkPDOConfig::CONFIG_USERNAME, $username);
        }
        if (empty($password)) {
            throw new ArkPDOConfigError(ArkPDOConfig::CONFIG_PASSWORD, $password);
        }
        if (empty($charset)) {
            throw new ArkPDOConfigError(ArkPDOConfig::CONFIG_CHARSET, $charset);
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
                $this->pdo->query("set names " . $charset);
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
        $stmt = $this->querySQLForStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @param string $sql
     * @return PDOStatement
     * @throws ArkPDOStatementException
     * @since 1.8.1
     */
    protected function prepareSQLForStatement(string $sql): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        if (!$statement) {
            $this->logger->error("PDO Statement Prepare Failure Occurred.", ["sql" => $sql]);
            throw new ArkPDOStatementException($sql);
        } else {
            $this->logger->debug("PDO Statement Prepared.", ["sql" => $sql]);
        }
        return $statement;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     * @param string $sql
     * @return PDOStatement
     * @throws ArkPDOStatementException
     * @since 1.8.1
     */
    protected function querySQLForStatement(string $sql): PDOStatement
    {
        $statement = $this->pdo->query($sql);
        if (!$statement) {
            $this->logger->error("PDO Statement Query Failure Occurred.", ["sql" => $sql]);
            throw new ArkPDOStatementException($sql);
        }
        $this->logger->debug("PDO Statement Queried.", ["sql" => $sql]);
        return $statement;
    }

    /**
     * @param string $sql
     * @param int|string $field
     * @return array
     * @throws ArkPDOStatementException
     */
    public function getCol(string $sql, $field = 0): array
    {
        $stmt = $this->querySQLForStatement($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_BOTH);
        return array_column($rows, $field);
    }

    /**
     * @param string $sql
     * @return array
     * @throws ArkPDOExecutedWithEmptyResultSituation
     * @throws ArkPDOStatementException
     * @since 1.8.1 use
     */
    public function getRow(string $sql): array
    {
        $stmt = $this->querySQLForStatement($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new ArkPDOExecutedWithEmptyResultSituation($sql);
        }
        return $row;
    }

    /**
     * @param string $sql
     * @return numeric|string|null May be number, string, or NULL
     * @throws ArkPDOExecutedWithEmptyResultSituation
     * @since 1.8.1 use fetch way
     */
    public function getOne(string $sql)
    {
        $stmt = $this->querySQLForStatement($sql);

        $x = $stmt->fetchColumn();
        if ($x === false) {
            throw new ArkPDOExecutedWithEmptyResultSituation($sql);
        }
        return $x;
    }

    /**
     * @param string $sql
     * @param callable $callback function($row,$index):bool; format of $row is decided by fetchStyle and $index starts since 1; return false would stop fetching next row.
     * @param int $fetchStyle
     * @return int How many rows fetched and processed
     * @throws ArkPDOStatementException
     * @since 1.6.0
     */
    public function getAllAsStream(string $sql, $callback, int $fetchStyle = PDO::FETCH_ASSOC): int
    {
        $stmt = $this->querySQLForStatement($sql);
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
     * @return int affected row count
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0, failure or no affection would throw exceptions
     */
    public function exec(string $sql)
    {
        $this->logger->debug("Ready to execute sql", ["sql" => $sql]);
        $afx = $this->pdo->exec($sql);
        if ($afx === false) {
            throw new ArkPDOExecuteFailedError($sql, $this->getPDOErrorDescription());
        } elseif ($afx === 0) {
            throw new ArkPDOExecuteNotAffectedError($sql);
        }
        return $afx;
    }

    /**
     * @param string $sql
     * @param string|null $pk
     * @return int
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0, failure or no affection would throw exceptions, and return int exactly
     */
    public function insert(string $sql, $pk = null): int
    {
        return intval($this->insertIntoTableForRawPK($sql, $pk));
    }

    /**
     * @param string $sql
     * @param string|null $pk
     * @return string
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0, failure or no affection would throw exceptions
     */
    public function insertIntoTableForRawPK(string $sql, $pk = null): string
    {
        $this->logger->debug("Ready to execute insert sql", ["sql" => $sql]);

//        $sth = $this->prepareSQLForStatement($sql);
//        $done = $sth->execute();
//        if ($done === false) {
//            throw new ArkPDOExecuteFailedError('Execute insertion with SQL got false.');
//        }
//        $afx=$sth->rowCount();
//        if($afx===0){
//            throw new ArkPDOExecuteNotAffectedError('Execute insertion with SQL got 0 row affected.');
//        }

        $rows = $this->pdo->exec($sql);
        if ($rows === false) {
            // for primary key or unique index conflict
            throw new ArkPDOExecuteFailedError($sql, $this->getPDOErrorDescription());
        } elseif ($rows === 0) {
            // for `insert ignore into ...`
            throw new ArkPDOExecuteNotAffectedError($sql);
        }

        return $this->pdo->lastInsertId($pk);
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @param callable $callback such as function(...$parameters)
     * @param array $parameters
     * @return mixed only return when success
     * @throws ArkPDORollbackSituation @since 1.8.6
     * @since 1.1
     */
    public function executeInTransaction($callback, array $parameters = [])
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
     * @return string
     * @since 1.8.0, return string exactly
     */
    public function getPDOErrorCode(): string
    {
        $code = $this->pdo->errorCode();
        if ($code === null) {
            return '';
        }
        if (is_numeric($code) || is_string($code)) {
            return "$code";
        }
        $x = json_encode($code);
        if ($x) return $x; else return 'JSON ENCODE ERROR';
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
     */
    public function getPDOErrorDescription(): string
    {
        return "PDO ERROR #" . $this->getPDOErrorCode() . ": " . implode(";", $this->getPDOErrorInfo());
    }

    public static function getPDOStatementErrorDescription(PDOStatement $statement): string
    {
        $code = $statement->errorCode();
        $info = $statement->errorInfo();
        return "PDO Statement ERROR #" . $code . ": " . implode(";", $info);
    }

    /**
     * @param string $sql
     * @param array $values
     * @param callable $callback function($row,$index):bool; format of $row is decided by fetchStyle and $index starts since 1; return false would stop fetching next row.
     * @param int $fetchStyle
     * @return int How many rows fetched and processed
     * @throws ArkPDOStatementException
     * @since 1.6.0
     */
    public function safeQueryAllAsStream(string $sql, array $values, $callback, int $fetchStyle = PDO::FETCH_ASSOC): int
    {
        $stmt = $this->prepareSQLForStatement($sql);
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
     * @throws ArkPDOExecuteFailedError
     * @since 1.8.0, failure would throw exceptions
     */
    public function safeQueryAll(string $sql, array $values = [], int $fetchStyle = PDO::FETCH_ASSOC): array
    {
        $sth = $this->prepareSQLForStatement($sql);
        $done = $sth->execute($values);
        if ($done === false) {
            throw new ArkPDOExecuteFailedError($sql, self::getPDOStatementErrorDescription($sth));
        }
        return $sth->fetchAll($fetchStyle);
    }

    /**
     * @param string $sql
     * @param array $values
     * @return array
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecutedWithEmptyResultSituation
     * @throws ArkPDOStatementException
     * @since 1.8.0, failure would throw exceptions
     */
    public function safeQueryRow(string $sql, array $values = [])
    {
        $sth = $this->prepareSQLForStatement($sql);
        $done = $sth->execute($values);
        if ($done === false) {
            throw new ArkPDOExecuteFailedError($sql, self::getPDOStatementErrorDescription($sth));
        }
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new ArkPDOExecutedWithEmptyResultSituation($sql);
        }
        return $result;
    }

    /**
     * @param string $sql
     * @param array $values
     * @return scalar
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOExecutedWithEmptyResultSituation
     * @throws ArkPDOStatementException
     * @since 1.8.0, failure would throw exceptions
     */
    public function safeQueryOne(string $sql, array $values = [])
    {
        $sth = $this->prepareSQLForStatement($sql);

        $done = $sth->execute($values);
        if ($done === false) {
            throw new ArkPDOExecuteFailedError($sql, self::getPDOStatementErrorDescription($sth));
        }
        $result = $sth->fetchColumn();
        if ($result === false) {
            throw new ArkPDOExecutedWithEmptyResultSituation($sql);
        }
        return $result;
    }

    /**
     * @param string $sql
     * @param array $values
     * @param int $insertedId
     * @param string|null $pk
     * @return true
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOStatementException
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0, failure or no affection would throw exceptions, always return true, write int exactly
     */
    public function safeInsertOne(string $sql, array $values = [], int &$insertedId = 0, $pk = null)
    {
        $rawLastInsertedId = $this->safeInsertOneForRawPK($sql, $values, $pk);
        $insertedId = intval($rawLastInsertedId);
        return true;
    }

    /**
     * @param string $sql
     * @param array $values
     * @param string|null $pk
     * @return string
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOStatementException
     * @throws ArkPDOExecuteNotAffectedError
     * @since 1.8.0, failure or no affection would throw exceptions
     */
    public function safeInsertOneForRawPK(string $sql, array $values = [], $pk = null)
    {
        $sth = $this->prepareSQLForStatement($sql);
        $done = $sth->execute($values);

        if ($done === false) {
            throw new ArkPDOExecuteFailedError($sql, self::getPDOStatementErrorDescription($sth));
        }

        $afx = $sth->rowCount();
        if ($afx === 0) {
            throw new ArkPDOExecuteNotAffectedError($sql);
        }

        return $this->pdo->lastInsertId($pk);
    }

    /**
     * @param string $sql
     * @param array $values
     * @param PDOStatement|null $sth @since 1.3.3
     * @return true
     * @throws ArkPDOExecuteFailedError
     * @throws ArkPDOStatementException
     * @since 1.8.0, failure or no affection would throw exceptions
     */
    public function safeExecute(string $sql, array $values = [], &$sth = null)
    {
        $sth = $this->prepareSQLForStatement($sql);
        $done = $sth->execute($values);
        if ($done === false) {
            throw new ArkPDOExecuteFailedError($sql, self::getPDOStatementErrorDescription($sth));
        }
        return true;
    }

    /**
     * @param string|null $pk
     * @return int
     * @since 1.8.0, return int exactly
     */
    public function getLastInsertID($pk = null): int
    {
        return intval($this->pdo->lastInsertId($pk));
    }

    /**
     * @param null|string $pk
     * @return string
     * @since 1.8.0
     */
    public function getRawLastInsertID($pk = null): string
    {
        return $this->pdo->lastInsertId($pk);
    }

    /**
     * PDOStatement::rowCount() 返回上一个由对应的 PDOStatement 对象执行 DELETE 、 INSERT 或 UPDATE 语句受影响的行数。
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
     * @param string $template
     * @param array $parameters
     * @return string
     * @throws ArkPDOSQLBuilderError
     * @since 2.1.11
     *
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
    public function safeBuildSQL(string $template, array $parameters = []): string
    {
        $this->logger->debug($template, ['parameters' => $parameters]);
        $count = preg_match_all('/\?|`\?`|\(\?\)|\[\?]|{\?}/', $template, $matches, PREG_OFFSET_CAPTURE);
        $this->logger->debug("preg_match_all count=" . json_encode($count), ['matches' => $matches]);
        if ($count === 0) {
            return $template;
        }
        if (!$count) {
            throw new ArkPDOSQLBuilderError("The sql template is not correct.", $template);
        }
        if ($count != count($parameters)) {
            throw new ArkPDOSQLBuilderError("The sql template has not correct number (" . count($parameters) . ") of parameters.", $template);
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
     * @param scalar $string
     * @param int $parameterType \PDO::PARAM_STR or \PDO::PARAM_INT
     * @return string
     * @since 1.8.0, return string exactly
     */
    public function quote($string, int $parameterType = PDO::PARAM_STR): string
    {
        if (!$this->pdo) {
            if ($parameterType == PDO::PARAM_INT) {
                $x = intval($string);
                return "$x";
            }
            return self::dryQuote($string);
        }
        $result = $this->pdo->quote($string, $parameterType);
        if ($result === false) {
            // it should not happen in common sense...
            throw new UnexpectedValueException('Cannot quote this string with PDO: ' . $string);
        }
        return $result;
    }

    /**
     * @param mixed $inp anything to be quote
     * @return array|mixed
     * @since 1.0
     * @since 1.8.13 let '' -> "''"
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
}