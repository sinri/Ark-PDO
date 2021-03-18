<?php


namespace sinri\ark\database\Exception;


/**
 * Class LengthDiffersInMatrixError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 *
 * Used in `batch write into` (insert/replace into) with matrix of source data,
 * when any data rows in matrix hold different length with the first row.
 */
class ArkPDOMatrixRowsLengthDifferError extends ArkPDOSQLBuilderError
{

}