<?php
// FOR 1.8.x

use sinri\ark\core\ArkLogger;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

require_once __DIR__ . '/../../../vendor/autoload.php';


$logger = new ArkLogger(
//__DIR__ . '/../../log', 'pdo-mysql'
);
//$logger->setIgnoreLevel(\Psr\Log\LogLevel::INFO);

$config = new ArkPDOConfig();

require __DIR__ . '/config.php';

$db = new ArkPDO();
$db->setPdoConfig($config);
$db->setLogger($logger);
$db->connect();

//$x = $db->safeQueryOne("select id from sinri.a where data=?",[9999]);
//var_dump($x);

//$x=$db->insertIntoTableForRawPK("insert into sinri.a(id,data)values('1','2')");
//var_dump($x);

//$x=$db->insertIntoTableForRawPK("replace into sinri.a(id,data)values('2','2037')");
//var_dump($x);

$x = $db->insertIntoTableForRawPK("insert ignore into sinri.a(id,data)values('2','2037')");
var_dump($x);