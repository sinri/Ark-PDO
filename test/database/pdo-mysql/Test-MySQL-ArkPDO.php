<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 14:31
 */

use sinri\ark\core\ArkLogger;
use sinri\ark\database\model\ArkDatabaseTableFieldDefinition;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\engine\ArkPDOConfigForMySQL;

require_once __DIR__ . '/../../../vendor/autoload.php';

$logger = new ArkLogger(__DIR__ . '/../../log', 'pdo-mysql');
//$logger->setIgnoreLevel(\Psr\Log\LogLevel::INFO);

$config = new ArkPDOConfigForMySQL();

// REQUIRE config.php (you might generate one besides) to do the commented job
//$config->setHost('db.com')
//    ->setPort(3306)
//    ->setUsername('')
//    ->setPassword('')
//    ->setDatabase('test')
//    ->setCharset(\sinri\ark\database\ArkPDOConfig::CHARSET_UTF8)
//    ->setEngine(\sinri\ark\database\ArkPDOConfig::ENGINE_MYSQL);

require __DIR__ . '/../../../config/config-MySQL-8.0.php';

$db = new ArkPDO();
try {
    $db->setPdoConfig($config);
    $db->setLogger($logger);
    $db->connect();

    $table = 'ark_test_table';

    $r = $db->exec(
        "CREATE TABLE `ark_test_table` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `value` VARCHAR(200) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"
    );
    $logger->info("created table", [$r]);

    $r = $db->insert("INSERT INTO `ark_test_table` (`id`,`value`) VALUES (NULL,'A')");
    $logger->info("inserted A", [$r]);
    $r = $db->insert("INSERT INTO `ark_test_table` (`id`,`value`) VALUES (NULL,'B')");
    $logger->info("inserted B", [$r]);

    $r = $db->getAll("SELECT * FROM `ark_test_table`");
    $logger->info("get all: A, B", [$r]);

    $r = $db->getRow("SELECT * FROM `ark_test_table` ORDER BY id LIMIT 1");
    $logger->info("get row: A", [$r]);

    $r = $db->getOne("SELECT value FROM `ark_test_table` ORDER BY id LIMIT 1");
    $logger->info("get one: A", [$r]);

    $r = $db->getCol("SELECT value,id FROM `ark_test_table`");
    $logger->info("get col by index: A,B", [$r]);

    $r = $db->getCol("SELECT id,value FROM `ark_test_table`", 'value');
    $logger->info("get col by field: A,B", [$r]);

    $r = $db->getAll("desc ark_test_table");
    $logger->info("desc table", [$r]);

    $model = new class($db) extends ArkDatabaseTableModel
    {

        protected $tmpDB;

        public function __construct($db)
        {
            $this->tmpDB = $db;
        }

        /**
         * @return string
         */
        public function mappingTableName(): string
        {
            return 'ark_test_table';
        }

        /**
         * @return ArkPDO
         */
        public function db(): ArkPDO
        {
            return $this->tmpDB;
        }
    };
    ArkDatabaseTableFieldDefinition::devShowFieldsForPHPDoc($model);

    $r = $db->exec(
        "DROP TABLE `ark_test_table`;"
    );
    $logger->info("dropped table", [$r]);


} catch (Exception $exception) {
    $logger->error("TEST MET ERROR: " . $exception->getMessage());
    $logger->error("PDO ERROR: " . $db->getPDOErrorCode(), $db->getPDOErrorInfo());
}