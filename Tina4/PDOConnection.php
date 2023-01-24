<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * PDOConnection
 * Establishes a connection to a database using PDO
 */
class PDOConnection
{
    /**
     * Database connection
     * @var false|resource
     */
    private $connection;

    /**
     * Creates a Firebird Database Connection
     * @param string $databasePath hostname/port:path
     * @param string $username database username
     * @param string $password password of the user
     * @param array $options
     */
    public function __construct(string $databasePath, string $username, string $password, array $options)
    {
        try {
            $this->connection = new \PDO($databasePath, $username, $password, $options);
        } catch (\PDOException $e) {
            \Tina4\Debug::message("Could not connect to database {$databasePath} with username {$username} and password {$password}", TINA4_LOG_ERROR);
            $this->connection = false;
        }
    }

    /**
     * Returns a database connection or false if failed
     * @return false|resource
     */
    final public function getConnection()
    {
        return $this->connection;
    }

}