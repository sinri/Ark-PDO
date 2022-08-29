<?php

use sinri\ark\database\model\ArkDatabaseDynamicTableModel;
use sinri\ark\database\model\ArkDatabaseTableFieldDefinition;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\engine\ArkPDOConfigForMySQL;

require_once __DIR__ . '/../../../vendor/autoload.php';

$config = new ArkPDOConfigForMySQL();
require __DIR__ . '/../../../config/config-MySQL-8.0.php';

$db = new ArkPDO();
$db->setPdoConfig($config);
$db->connect();

$table = 'dt_chat_spider_task';

$model = new ArkDatabaseDynamicTableModel($db, $table, 'sinri');

ArkDatabaseTableFieldDefinition::devShowFieldsForPHPDoc($model);