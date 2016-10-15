<?php
namespace Srvlets;

use \RuntimeException;

/**
 * An event-driven, multi-threaded SCGI server.
 */
final class Server
{
    /**
     * @var resource
     */
    private $socket;

    /**
     * @var array
     */
    private $threads;

    /**
     * @param Bootstrapper $bootstrapper
     * @param int          $port
     * @param int          $threads
     * @param int          $workers
     *
     * @uses Thread
     *
     * @throws RuntimeException
     */
    public function __construct(Bootstrapper $bootstrapper, int $port, int $threads, int $workers)
    {
        $uri = "tcp://0.0.0.0:{$port}";

        if (!is_resource($socket = stream_socket_server($uri, $code, $message))) {

            throw new RuntimeException;
        }

        $this->socket  = $socket;
        $this->threads = [];

        foreach (range(1, $threads) as $count) {

            $thread = new Thread($bootstrapper, $workers, $socket);

            if (!$thread->start()) {

                throw new RuntimeException;
            }

            $this->threads[] = $thread;
        }
    }

    /**
     * @internal
     */
    public function __destruct()
    {
        foreach ($this->threads as $thread) {

            if (!$thread->join()) {

                // Log something...
            }
        }

        if (!stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR)) {

            // Log something...
        }
    }
}
