<?php
namespace Srvlets;

use \LogicException;

/**
 * Stores connections to keep
 * references for event watchers.
 */
final class Connections
{
    /**
     * @var array
     */
    private $connections;

    /**
     * @param Connection $connection
     */
    public function accept(Connection $connection)
    {
        $hash = spl_object_hash($connection);

        $this->connections[$hash] = $connection;
    }

    /**
     * @param Connection $connection
     *
     * @throws LogicException
     */
    public function remove(Connection $connection)
    {
        $hash = spl_object_hash($connection);

        if (!isset($this->connections[$hash])) {

            throw new LogicException;
        }

        unset($this->connections[$hash]);
    }

    /**
     * Removes circular references.
     */
    public function close()
    {
        foreach ($this->connections as $connection) {

            $connection->close();
        }
    }
}
