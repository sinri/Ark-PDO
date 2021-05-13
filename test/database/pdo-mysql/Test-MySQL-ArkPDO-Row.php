<?php


use sinri\ark\core\ArkLogger;
use sinri\ark\database\exception\ArkPDOConfigError;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotQueriedError;
use sinri\ark\database\exception\ArkPDOQueryResultIsNotStreamingError;
use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;
use sinri\ark\database\test\database\entity\ArkTestTableRow;

require_once __DIR__ . '/../../../vendor/autoload.php';

$logger = new ArkLogger(__DIR__ . '/../../log', 'pdo-mysql-row');

$config = new ArkPDOConfig();


require __DIR__ . '/config.php';

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
        $row = ArkTestTableRow::fetchRowFromStream($result_as_stream);
        if ($row === null) break;
        $logger->info('STREAMING: ' . $row->getId(), ['value' => $row->getValue(), 'score' => $row->score]);
    }
    $logger->info('now ' . $result_as_stream->getStatus());

} catch (ArkPDOQueryResultIsNotQueriedError $e) {
    $logger->error($e->getMessage());
} catch (ArkPDOConfigError $e) {
    $logger->error($e->getMessage());
} catch (ArkPDOQueryResultIsNotStreamingError $e) {
    $logger->error($e->getMessage());
}