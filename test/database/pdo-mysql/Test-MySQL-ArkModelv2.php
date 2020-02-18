<?php

use sinri\ark\core\ArkLogger;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

require_once __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../../autoload.php';

$logger = new ArkLogger(__DIR__ . '/../../log', 'pdo-mysql');
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

$logger = new ArkLogger();

try {
    $logger->info("Test for Ark Model v2");
    $db = new ArkPDO();
    $db->setPdoConfig($config);
    $db->setLogger($logger);
    $db->connect();

    $table = 'ark_test_table';

    $model = new ArkDatabaseDynamicTableModel($db, $table, 'test');

    // create
    $sql = "CREATE TABLE `ark_test_table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `value` VARCHAR(200) NOT NULL,
      `score` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $afx = $model->db()->exec($sql);
    $logger->info('Created Table: ', ['afx' => $afx]);

    // insert
    $afx = $model->insert(['value' => 'A', 'score' => 1]);
    $logger->smartLogLite($afx, 'INSERT ONE RAW');
    $afx = $model->insert(['value' => 'B', 'score' => 2]);
    $logger->smartLogLite($afx, 'INSERT ONE RAW');
    $afx = $model->insert(['value' => 'C']);
    $logger->smartLogLite($afx, 'INSERT ONE RAW');

    // select
    $result = $model->selectInTable()
        ->addSelectFieldByDetail('value')
        ->addSelectFieldByDetail('concat(value,"=",score)', 'mixed')
        ->addSelectFields([
            new \sinri\ark\database\model\query\ArkDatabaseSelectFieldMeta('score'),
            new \sinri\ark\database\model\query\ArkDatabaseSelectFieldMeta('score*2', 'twice'),
        ])
        ->addCondition(\sinri\ark\database\model\ArkSQLCondition::makeIsNotNull('score'))
        ->setGroupByFields(['id'])
        ->setLimit(10)
        ->setOffset(0)
        ->queryForRows();
    $logger->smartLogLite(
        $result->getStatus() === \sinri\ark\database\model\query\ArkDatabaseQueryResult::STATUS_QUERIED,
        'SELECTED',
        [
            'status' => $result->getStatus(),
            'error' => $result->getError(),
            'sql' => $result->getSql(),
            'data' => $result->getRawMatrix(),
        ]
    );

    // drop
    $sql = "DROP TABLE ark_test_table";
    $afx = $model->db()->exec($sql);
    $logger->info('Dropped Table: ', ['afx' => $afx]);

} catch (Exception $e) {
    $logger->error("Exception: " . $e->getMessage());
}