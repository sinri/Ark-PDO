<?php


use sinri\ark\core\ArkLogger;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\engine\ArkPDOConfigForMySQL;

require_once __DIR__ . '/../../../vendor/autoload.php';

$logger = new ArkLogger(__DIR__ . '/../../log', 'pdo-mysql-wis');
//$logger->setIgnoreLevel(\Psr\Log\LogLevel::INFO);

$config = new ArkPDOConfigForMySQL();

// REQUIRE config.php (you might generate one besides) to do the commented job
//$config->setHost('db.com')
//    ->setPort(3306)
//    ->setUsername('')
//    ->setPassword('')
//    ->setDatabase('test')
//    ->setCharset(\sinri\ark\database\ArkPDOConfig::CHARSET_UTF8)
//    ->setEngine(\sinri\ark\database\ArkPDOConfig::ENGINE_MYSQL);

require __DIR__ . '/../../../config/config-MySQL-8.0.php';

$db = new ArkPDO();
$db->setPdoConfig($config);
$db->setLogger($logger);
$db->connect();

$table = new ArkDatabaseDynamicTableModel($db, 'd');

$before = $table->selectInTable()
    ->addCondition(
        ArkSQLCondition::for('id')->lessThan(10)
    )
    ->queryForRows()
    ->getRawMatrix();
echo 'before: ' . json_encode($before) . PHP_EOL;

$result = $table->insertOnDuplicateKeyUpdate(
    [
        ['id' => 1, 'value' => 'AAA', 'score' => 1,],
        ['id' => 2, 'value' => 'BBB', 'score' => 2,],
        ['id' => 3, 'value' => 'CCC', 'score' => 3,],
    ],
    ['value' => 'VALUES(value)']
);

echo $result->getSql() . PHP_EOL;
echo 'Status: ' . $result->getStatus() . PHP_EOL;
echo 'Error: ' . $result->getError() . PHP_EOL;
echo 'AFX: ' . $result->getAffectedRowsCount() . PHP_EOL;
echo 'LID: ' . $result->getLastInsertedID() . PHP_EOL;


$after = $table->selectInTable()
    ->addCondition(ArkSQLCondition::for('id')->lessThan(10))
    ->queryForRows()
    ->getRawMatrix();
echo 'after: ' . json_encode($after) . PHP_EOL;