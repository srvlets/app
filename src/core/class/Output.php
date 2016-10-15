<?php
namespace Srvlets;

/**
 * Threaded output buffer, transports
 * response data between worker threads
 * and IO threads.
 */
final class Output extends \Threaded
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @internal
     */
    public function __construct()
    {
        $this->buffer = '';
    }

    /**
     * Write new data to the buffer.
     */
    public function buffer(string $data)
    {
        $this->synchronized(function (string $data) {

            $this->buffer = $this->buffer . $data;

        }, $data);
    }

    /**
     * Writes buffered data to a connection.
     *
     * @param Connection $connection
     */
    public function writeTo(Connection $connection)
    {
        // Check if buffer has data.

        if (!strlen($this->buffer)) {

            return;
        }

        // Write data and check how many bytes have been written.

        if (!$written = $connection->write($this->buffer)) {

            return;
        }

        // Remove written data from buffer.

        $this->synchronized(function (int $written) {

            $this->buffer = substr($this->buffer, $written);

        }, $written);

        // Check if everything has been written.

        if (!strlen($this->buffer)) {

            // Response sent, close connection
            // as required by SCGI protocol.

            $connection->close();
        }
    }
}
