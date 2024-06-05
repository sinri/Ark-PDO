<?php


use sinri\ark\core\ArkLogger;
use sinri\ark\database\exception\ArkPDOConfigError;
use sinri\ark\database\exception\ArkPDOQueryResultFinishedStreamingSituation;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotQueriedError;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotStreamingError;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\engine\ArkPDOConfigForMySQL;
use sinri\ark\database\test\database\entity\ArkTestTableRow;

require_once __DIR__ . '/../../../vendor/autoload.php';

$logger = new ArkLogger(__DIR__ . '/../../log', 'pdo-mysql-row');

$config = new ArkPDOConfigForMySQL();


require __DIR__ . '/../../../config/config-MySQL-8.0.php';

$db = new ArkPDO();
try {
    $db->setPdoConfig($config);
    $db->setLogger($logger);
    $db->connect();

    $table_d = new ArkDatabaseDynamicTableModel($db, 'd');

    $selection = $table_d->selectInTable();
    $rows = ArkTestTableRow::fetchRowsWithSelection($selection, $result);
    foreach ($rows as $row) {
        $logger->info('ROWS: ' . $row->getId(), ['value' => $row->getValue(), 'score' => $row->score]);
    }

    $result_as_stream = $selection->queryForStream();
    while (true) {
        try {
            $row = ArkTestTableRow::fetchRowFromStream($result_as_stream);
        } catch (ArkPDOQueryResultFinishedStreamingSituation $e) {
            break;
        }
        $logger->info('STREAMING: ' . $row->getId(), ['value' => $row->getValue(), 'score' => $row->score]);
    }
    $logger->info('now ' . $result_as_stream->getStatus());

} catch (ArkPDOQueryResultIsNotQueriedError|ArkPDOQueryResultIsNotStreamingError|ArkPDOConfigError $e) {
    $logger->error($e->getMessage());
}