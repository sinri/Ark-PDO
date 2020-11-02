<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/13
 * Time: 10:04
 */


//require_once __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../../autoload.php';

use sinri\ark\database\pdo\ArkPDOCompareTool;
use sinri\ark\database\pdo\ArkPDOConfig;

$config_bi_test = (new ArkPDOConfig());
$config_drds_oms = (new ArkPDOConfig());
$config_drds_omssync = (new ArkPDOConfig());

// omssync

require __DIR__ . '/config.php';
$config_bi_test->setDatabase('omssync');
$compareTool = new ArkPDOCompareTool($config_drds_omssync, $config_bi_test);
$compareTool->setBigTablesToAvoidRowCount(['DB_LOCATE']);
echo "Compare OMSSYNC" . PHP_EOL;
$compareTool->compareTableStructure(null);

// oms

require __DIR__ . '/config.php';
$config_bi_test->setDatabase('oms');
$compareTool = new ArkPDOCompareTool($config_drds_oms, $config_bi_test);
$compareTool->setBigTablesToAvoidRowCount(['DB_LOCATE']);
echo "Compare OMS" . PHP_EOL;
$compareTool->compareTableStructure(null);