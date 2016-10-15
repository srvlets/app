<?php
namespace Srvlets;

use \Ev;
use \EvLoop;
use \DomainException;
use \InvalidArgumentException;
use \LogicException;
use \Pool;
use \RuntimeException;

/**
 * Encapsulates a non-blocking stream resource
 * and handles IO events asynchronously.
 */
final class Connection
{
    /**
     * @var Connections
     */
    private $connections;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Output
     */
    private $output;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @var array
     */
    private $watchers;

    /**
     * @var string
     */
    private $input;

    /**
     * @param Connections $connections
     * @param Pool        $pool
     * @param EvLoop      $evloop
     * @param Output      $output
     * @param resource    $stream
     *
     * @uses Ev
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct(Connections $connections, Pool $pool, EvLoop $evloop, Output $output, $stream)
    {
        if (!is_resource($stream)) {

            throw new InvalidArgumentException;
        }

        if (!stream_set_blocking($stream, false)) {

            throw new RuntimeException;
        }

        $this->connections = $connections;
        $this->pool        = $pool;
        $this->output      = $output;
        $this->stream      = $stream;
        $this->watchers    = [];
        $this->input       = '';

        // Set up IO watchers.

        $this->watchers[] = $evloop->io($stream, Ev::READ, function () {

            $this->read();
        });
        $this->watchers[] = $evloop->io($stream, Ev::WRITE, function () {

            $this->output->writeTo($this);
        });
    }

    /**
     * @internal
     */
    public function __destruct()
    {
        if (!stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR)) {

            // Log something...
        }
    }

    /**
     * Removes circular references.
     *
     * @uses LogicException
     */
    public function close()
    {
        $this->watchers = [];

        try {

            $this->connections->remove($this);

        } catch (LogicException $exception) {}
    }

    /**
     * Writes asynchronously to the
     * underlying stream resource.
     *
     * @param string $data
     *
     * @return int
     */
    public function write(string $data): int
    {
        if ($written = fwrite($this->stream, $data)) {

            return $written;
        }

        return 0;
    }

    /**
     * Reads asynchronously from the
     * underlying stream resource.
     *
     * @uses Collectable
     * @uses DomainException
     * @uses Request
     */
    private function read()
    {
        if (!$data = stream_get_contents($this->stream)) {

            $this->close();

            return;
        }

        $data = $this->input . $data;

        try {

            // Parse the request.

            $request = new Request($data);

        } catch (DomainException $exception) {

            // Request incomplete.

            $this->input = $data;

            return;
        }

        // Submit request to workers.

        $this->pool->submit(new Collectable($request, $this->output));

        // Reset input buffer for next request.

        $this->input = '';
    }
}
