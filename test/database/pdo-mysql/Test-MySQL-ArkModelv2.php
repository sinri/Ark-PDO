<?php

use sinri\ark\core\ArkLogger;
use sinri\ark\database\exception\ArkPDOQueryResultFinishedStreamingSituation;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\model\ArkSQLCondition;
use sinri\ark\database\model\query\ArkDatabaseQueryResult;
use sinri\ark\database\model\query\ArkDatabaseSelectFieldMeta;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;
use sinri\ark\database\test\database\entity\ArkTestTableRow;

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

    $model = new ArkDatabaseDynamicTableModel($db, $table, 'sinri');

    // create
    $sql = "CREATE TABLE `ark_test_table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `value` VARCHAR(200) NOT NULL,
      `score` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $afx = $model->db()->exec($sql);
    if ($afx === false) {
        $logger->error('Create the table failed', ['error' => $model->db()->getPDOErrorDescription()]);
    } else {
        $logger->info('Created Table: ', ['afx' => $afx]);
    }
    // insert
    $result = $model->insertOneRow(['value' => 'A', 'score' => 1]);
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'INSERT ONE RAW', ['id' => $result->getLastInsertedID()]);
    $result = $model->insertOneRow(['value' => 'B', 'score' => 2]);
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'INSERT ONE RAW', ['id' => $result->getLastInsertedID()]);
    $result = $model->insertOneRow(['value' => 'C']);
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'INSERT ONE RAW', ['id' => $result->getLastInsertedID()]);

    $result = $model->replaceOneRow(['value' => 'B', 'score' => 3]);
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'REPLACE ONE RAW', ['id' => $result->getLastInsertedID()]);
    $result = $model->replaceOneRow(['value' => 'D', 'score' => 4]);
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'REPLACE ONE RAW', ['id' => $result->getLastInsertedID()]);

    // update
    $result = $model->updateRows(
        [
            ArkSQLCondition::makeGreaterThan('score', 3),
        ],
        [
            'score' => 5
        ]
    );
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'UPDATE ONE RAW', ['afx' => $result->getAffectedRowsCount()]);

    // delete
    $result = $model->deleteRows([ArkSQLCondition::makeEqual('score', 1)]);
    $logger->smartLogLite($result->getStatus() === ArkDatabaseQueryResult::STATUS_EXECUTED, 'DELETE ONE RAW', ['afx' => $result->getAffectedRowsCount()]);


    // select
    $result = $model->selectInTable()
        ->addSelectFieldByDetail('value')
        ->addSelectFieldByDetail('concat(value,"=",score)', 'mixed')
        ->addSelectFields([
            new ArkDatabaseSelectFieldMeta('score'),
            new ArkDatabaseSelectFieldMeta('score*2', 'twice'),
        ])
        //->addCondition(\sinri\ark\database\model\ArkSQLCondition::makeIsNotNull('score'))
        ->setGroupByFields(['id'])
        ->setLimit(10)
        ->setOffset(0)
        ->queryForRows(ArkTestTableRow::class);
    $logger->smartLogLite(
        $result->getStatus() === ArkDatabaseQueryResult::STATUS_QUERIED,
        'SELECTED',
        [
            'status' => $result->getStatus(),
            'error' => $result->getError(),
            'sql' => $result->getSql(),
            'data' => $result->getRawMatrix(),
        ]
    );
    foreach (ArkTestTableRow::washRowsArray($result->getResultRows()) as $resultRow) {
        $logger->info("row", [
            'score' => $resultRow->getScore(),
            'mixed' => $resultRow->getField('mixed', false),
            'all' => $resultRow->getRawRow()
        ]);
    }

    // stream
    $result = $model->selectInTable()
        ->addSelectFieldByDetail('value')
        ->addSelectFieldByDetail('concat(value,"=",score)', 'mixed')
        ->addSelectFields([
            new ArkDatabaseSelectFieldMeta('score'),
            new ArkDatabaseSelectFieldMeta('score*2', 'twice'),
        ])
        //->addCondition(\sinri\ark\database\model\ArkSQLCondition::makeIsNotNull('score'))
        ->setGroupByFields(['id'])
        ->setLimit(10)
        ->setOffset(0)
        ->queryForStream();

//    $result->debugGetFieldsMeta();
    while (true) {
        try {
            $nextRow = $result->readNextRow();
            var_dump($nextRow);
            $logger->info("Streaming and fetching", $nextRow->getRawRow());
        } catch (ArkPDOQueryResultFinishedStreamingSituation $e) {
            $logger->notice('Streaming Finished');
            break;
        }
    }

    // drop
    $sql = "DROP TABLE ark_test_table";
    $afx = $model->db()->exec($sql);
    $logger->info('Dropped Table: ', ['afx' => $afx]);

} catch (Exception $e) {
    $logger->error("Exception: " . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
}