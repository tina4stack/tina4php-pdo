<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

class PDOMetaData extends DataConnection implements DataBaseMetaData
{

    /**
     * @inheritDoc
     */
    public function __construct(Database $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function getTables(): array
    {
        // TODO: Implement getTables() method.
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryKeys(string $tableName): array
    {
        // TODO: Implement getPrimaryKeys() method.
    }

    /**
     * @inheritDoc
     */
    public function getForeignKeys(string $tableName): array
    {
        // TODO: Implement getForeignKeys() method.
    }

    /**
     * @inheritDoc
     */
    public function getTableInformation(string $tableName): array
    {
        // TODO: Implement getTableInformation() method.
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseMetaData(): array
    {
        // TODO: Implement getDatabaseMetaData() method.
    }
}