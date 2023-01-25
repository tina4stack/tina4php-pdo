<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

class DataPDO implements DataBase
{
    use DataBaseCore;

    /**
     * @var null database metadata
     */
    private $databaseMetaData;

    /**
     * Open a PDO database connection
     */
    public function __construct(string $database, string $username = "", string $password = "", string $dateFormat = "Y-m-d", array $options=[])
    {
        if (empty($options)){
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
        }

        $this->dbh = (new PDOConnection($database, $username, $password, $options))->getConnection();
        $this->dateFormat = $dateFormat;
    }

    /**
     * @inheritDoc
     */
    final public function open()
    {
        return $this->dbh;
    }

    /**
     * @inheritDoc
     */
    final public function close()
    {
        $this->dbh = null;
    }

    /**
     * Executes a query on a PDO database
     */
    public function exec()
    {
        $params = $this->parseParams(func_get_args());

        $tranId = $params["tranId"];
        $params = $params["params"];

        if (isset($params[0]) && stripos($params[0], "returning") !== false) {
            return $this->fetch($params);
        }

        (new PDOExec($this))->exec($params, $tranId);

        return $this->error();
    }

    /**
     * @inheritDoc
     */
    final public function getLastId(): string
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * @inheritDoc
     */
    final public function tableExists(string $tableName): bool
    {
        if (!empty($tableName)) {
            // Try a select statement against the table
            // Run it in try-catch in case PDO is in ERRMODE_EXCEPTION.
            try {
                $result = $this->dbh->query("SELECT 1 FROM {$tableName} LIMIT 1");
            } catch (Exception $e) {
                // We got an exception (table not found)
                return false;
            }

            // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
            return $result !== false;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    final public function fetch($sql, int $noOfRecords = 10, int $offSet = 0, array $fieldMapping = []): ?DataResult
    {
        return (new PDOQuery($this))->query($sql, $noOfRecords, $offSet, $fieldMapping);
    }

    /**
     * @inheritDoc
     */
    final public function commit($transactionId = null)
    {
        return $this->dbh->commit();
    }

    /**
     * @inheritDoc
     */
    final public function rollback($transactionId = null)
    {
        return $this->dbh->rollBack();
    }

    /**
     * @inheritDoc
     */
    final public function autoCommit(bool $onState = true): void
    {
        $this->dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, $onState);
    }

    /**
     * @inheritDoc
     */
    final public function startTransaction()
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    final public function error()
    {
        return (new DataError($this->dbh->errorCode(), json_encode($this->dbh->errorInfo())));
    }

    /**
     * @inheritDoc
     */
    final public function getDatabase(): array
    {
        if (!empty($this->databaseMetaData)) {
            return $this->databaseMetaData;
        }

        $this->databaseMetaData = (new PDOMetaData($this))->getDatabaseMetaData();

        return $this->databaseMetaData;
    }

    /**
     * @inheritDoc
     */
    final public function getDefaultDatabaseDateFormat(): string
    {
        return "Y-m-d";
    }

    /**
     * @inheritDoc
     */
    final public function getDefaultDatabasePort(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    final public function getQueryParam(string $fieldName, int $fieldIndex): string
    {
        return "?";
    }

    /**
     * @inheritDoc
     */
    final public function isNoSQL(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    final public function getShortName(): string
    {
        return "pdo";
    }
}