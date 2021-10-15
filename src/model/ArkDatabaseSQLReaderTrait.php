<?php

namespace sinri\ark\database\model;

use sinri\ark\database\exception\ArkPDOSQLBuilderError;
use sinri\ark\database\exception\ArkPDOStatementException;
use sinri\ark\database\model\query\ArkDatabaseQueryResult;
use sinri\ark\database\model\query\ArkDatabaseQueryResultRow;
use sinri\ark\database\pdo\ArkPDO;

/**
 * @since 2.1 reconstructed
 */
trait ArkDatabaseSQLReaderTrait
{
    use ArkDatabaseSQLBuilderTrait;

    abstract public function getTargetPDO(): ArkPDO;

    /**
     * @param string $resultRowCustomizedClass // I wonder if it is useful.
     * @return ArkDatabaseQueryResult
     */
    public function queryForRows($resultRowCustomizedClass = ArkDatabaseQueryResultRow::class): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $sql = $this->generateSQL();
            $result->setSql($sql);

            // old implementation
            $all = $this->getTargetPDO()->getAll($sql);
            if (is_array($all)) {
                foreach ($all as $row) {
                    $result->addResultRow(new $resultRowCustomizedClass($row));
                }
            }

            // new implementation with raw PDO
//            $rows=$this->model->db()->getAllAsClassInstanceArray($sql,$resultRowCustomizedClass);
//            $result->addResultRows($rows);

            $result->setStatus(ArkDatabaseQueryResult::STATUS_QUERIED);
        } catch (ArkPDOStatementException $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                'ArkPDOStatementException: ' . $e->getMessage() . ';'
                . ' SQL: ' . $e->getSql()
                . ' PDO Last Error: ' . $this->getTargetPDO()->getPDOErrorDescription()
            );
        } catch (ArkPDOSQLBuilderError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError('ArkPDOSQLBuilderError: ' . $e->getMessage() . ' SQL: ' . $e->getWrongSQLPiece());
        }
        return $result;
    }

    /**
     * @return ArkDatabaseQueryResult
     */
    public function queryForStream(): ArkDatabaseQueryResult
    {
        $result = new ArkDatabaseQueryResult();
        try {
            $sql = $this->generateSQL();
            $result->setSql($sql);

            $statement = $this->getTargetPDO()->getPdo()->query($sql);
            if ($statement === false) {
                throw new ArkPDOStatementException($sql);
            }

            $result->setResultRowStream($statement);
            $result->setStatus(ArkDatabaseQueryResult::STATUS_STREAMING);

        } catch (ArkPDOSQLBuilderError $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError('ArkPDOSQLBuilderError: ' . $e->getMessage() . ' SQL: ' . $e->getWrongSQLPiece());
        } catch (ArkPDOStatementException $e) {
            $result->setStatus(ArkDatabaseQueryResult::STATUS_ERROR);
            $result->setError(
                'ArkPDOStatementException: ' . $e->getMessage() . ';'
                . ' SQL: ' . $e->getSql()
                . ' PDO Last Error: ' . $this->getTargetPDO()->getPDOErrorDescription()
            );
        }
        return $result;
    }


}