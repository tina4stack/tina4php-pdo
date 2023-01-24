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
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        // TODO: Implement open() method.
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        // TODO: Implement close() method.
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
    public function getLastId(): string
    {
        // TODO: Implement getLastId() method.
    }

    /**
     * @inheritDoc
     */
    public function tableExists(string $tableName): bool
    {
        // TODO: Implement tableExists() method.
    }

    /**
     * @inheritDoc
     */
    public function fetch($sql, int $noOfRecords = 10, int $offSet = 0, array $fieldMapping = []): ?DataResult
    {
        return (new PDOQuery($this))->query($sql, $noOfRecords, $offSet, $fieldMapping);
    }

    /**
     * @inheritDoc
     */
    public function commit($transactionId = null)
    {
        return $this->dbh->commit();
    }

    /**
     * @inheritDoc
     */
    public function rollback($transactionId = null)
    {
        return $this->dbh->rollBack();
    }

    /**
     * @inheritDoc
     */
    public function autoCommit(bool $onState = true): void
    {
        $this->dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, $onState);
    }

    /**
     * @inheritDoc
     */
    public function startTransaction()
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function error()
    {
        return (new DataError($this->dbh->errorCode(), json_encode($this->dbh->errorInfo())));
    }

    /**
     * @inheritDoc
     */
    public function getDatabase(): array
    {
        // TODO: Implement getDatabase() method.
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDatabaseDateFormat(): string
    {
        return "Y-m-d";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDatabasePort(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParam(string $fieldName, int $fieldIndex): string
    {
        return "?";
    }

    /**
     * @inheritDoc
     */
    public function isNoSQL(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getShortName(): string
    {
        return "pdo";
    }
}