<?php

namespace Tina4;

class PDOQuery extends DataConnection implements DataBaseQuery
{

    /**
     * @inheritDoc
     */
    public function query($sql, int $noOfRecords = 10, int $offSet = 0, array $fieldMapping = []): ?DataResult
    {
        // TODO: Implement query() method.
    }
}