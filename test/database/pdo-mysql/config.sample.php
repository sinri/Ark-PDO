<?php
//require_once __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../../autoload.php';


use sinri\ark\database\pdo\ArkPDOConfig;

$pdoInfo = [
    "host" => "",
    "port" => "",
    "username" => "",
    "password" => "",
    "database" => "",
    "charset" => ArkPDOConfig::CHARSET_UTF8,
    "engine" => ArkPDOConfig::ENGINE_MYSQL,
];

$config = new ArkPDOConfig();

$config->setHost($pdoInfo['host'])
    ->setPort($pdoInfo['port'])
    ->setUsername($pdoInfo['username'])
    ->setPassword($pdoInfo['password'])
    ->setDatabase($pdoInfo['database'])
    ->setCharset($pdoInfo['charset'])
    ->setEngine($pdoInfo['engine']);
