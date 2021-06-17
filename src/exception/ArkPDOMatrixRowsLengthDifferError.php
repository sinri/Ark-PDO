<?php


namespace sinri\ark\database\exception;


use Throwable;

/**
 * Class LengthDiffersInMatrixError
 * @package sinri\ark\database\Exception
 * @since 2.0.13
 * @since 2.0.25 changed construction
 *
 * Used in `batch write into` (insert/replace into) with matrix of source data,
 * when any data rows in matrix hold different length with the first row.
 */
class ArkPDOMatrixRowsLengthDifferError extends ArkPDOSQLBuilderError
{
    public function __construct(int $expectedFieldsCount, string $wrongSQLPiece, $code = 0, Throwable $previous = null)
    {
        $message = 'Each row in matrix should contains exactly ' . $expectedFieldsCount . ' fields, ';
        $message .= "this row does not follow this rule: " . $wrongSQLPiece;
        parent::__construct($message, $wrongSQLPiece, $code, $previous);
    }
}