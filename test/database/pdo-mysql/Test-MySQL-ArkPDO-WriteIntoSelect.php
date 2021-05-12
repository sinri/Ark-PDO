<?php

use sinri\ark\core\ArkLogger;
use sinri\ark\database\exception\ArkPDOConfigError;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

require_once __DIR__ . '/../../../vendor/autoload.php';

$logger = new ArkLogger(__DIR__ . '/../../log', 'pdo-mysql-wis');
//$logger->setIgnoreLevel(\Psr\Log\LogLevel::INFO);

$config = new ArkPDOConfig();

// REQUIRE config.php (you might generate one besides) to do the commented job
//$config->setHost('db.com')
//    ->setPort(3306)
//    ->setUsername('')
//    ->setPassword('')
//    ->setDatabase('test')
//    ->setCharset(\sinri\ark\database\ArkPDOConfig::CHARSET_UTF8)
//    ->setEngine(\sinri\ark\database\ArkPDOConfig::ENGINE_MYSQL);

require __DIR__ . '/config.php';

$db = new ArkPDO();
try {
    $db->setPdoConfig($config);
    $db->setLogger($logger);
    $db->connect();

    $table_x1 = new ArkDatabaseDynamicTableModel($db, 'x1');
    $table_x2 = new ArkDatabaseDynamicTableModel($db, 'x2');

    $result = $table_x2->insert_into_select(
        $table_x1->selectInTable()->addCondition(ArkSQLCondition::makeEqual('x_id', 1))
    );
    $logger->notice(
        'x1 --[x_id=1]-> x2',
        [
            'status' => $result->getStatus(),
            'sql' => $result->getSql(),
            'afx' => $result->getAffectedRowsCount(),
            'id' => $result->getLastInsertedID(),
        ]
    );

    $result = $table_x2->replace_into_select(
        $table_x1->selectInTable()->addCondition(ArkSQLCondition::makeEqual('x_id', 1))
    );
    $logger->notice(
        'x1 ==[x_id=1]=> x2',
        [
            'status' => $result->getStatus(),
            'sql' => $result->getSql(),
            'afx' => $result->getAffectedRowsCount(),
            'id' => $result->getLastInsertedID(),
        ]
    );
} catch (ArkPDOConfigError $e) {
    $logger->error($e->getMessage());
}