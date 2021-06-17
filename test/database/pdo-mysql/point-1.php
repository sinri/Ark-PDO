<?php

// FOR 1.8.x

use sinri\ark\core\ArkLogger;
use sinri\ark\database\exception\ArkPDOConfigError;
use sinri\ark\database\exception\ArkPDOExecutedWithEmptyResultSituation;
use sinri\ark\database\exception\ArkPDOExecuteFailedError;
use sinri\ark\database\exception\ArkPDOExecuteNotAffectedError;
use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\exception\ArkPDOStatementException;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

require_once __DIR__ . '/../../../vendor/autoload.php';


$logger = new ArkLogger(
//__DIR__ . '/../../log', 'pdo-mysql'
);
//$logger->setIgnoreLevel(\Psr\Log\LogLevel::INFO);

$config = new ArkPDOConfig();

require __DIR__ . '/config.php';

try {
    $db = new ArkPDO();
    $db->setPdoConfig($config);
    $db->setLogger($logger);
    $db->connect();

    $table = (new ArkDatabaseDynamicTableModel($db, 'a', 'sinri'));

    try {
        $result = $table->selectRow(['id' => 100]);
        $logger->info('result', [$result]);
    } catch (ArkPDOSQLBuilderError $e) {
        $logger->error(get_class($e) . ' -> ' . $e->getMessage());
    } catch (ArkPDOStatementException $e) {
        $logger->error(get_class($e) . ' -> ' . $e->getMessage());
    } catch (ArkPDOExecutedWithEmptyResultSituation $e) {
        $logger->error(get_class($e) . ' -> ' . $e->getMessage());
    }
    try {
        $result = $table->delete(['id' => 100]);
        $logger->info('result', [$result]);
    } catch (ArkPDOExecuteFailedError $e) {
        $logger->error(get_class($e) . ' -> ' . $e->getMessage());
    } catch (ArkPDOExecuteNotAffectedError $e) {
        $logger->error(get_class($e) . ' -> ' . $e->getMessage());
    } catch (ArkPDOSQLBuilderError $e) {
        $logger->error(get_class($e) . ' -> ' . $e->getMessage());
    }
} catch (ArkPDOConfigError $e) {
    $logger->error($e->getMessage());
}