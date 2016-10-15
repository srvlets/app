<?php
namespace Srvlets;

use \Ev;
use \EvLoop;
use \InvalidArgumentException;
use \LogicException;
use \Pool;
use \Throwable;

/**
 * Thread that listens for incoming connections on
 * a socket and handles IO events concurrently.
 */
final class Thread extends \Thread
{
    /**
     * @var Connections
     */
    private static $connections;

    /**
     * @var EvLoop
     */
    private static $evloop;

    /**
     * @var Pool
     */
    private static $pool;

    /**
     * @var Bootstrapper
     */
    private $bootstrapper;

    /**
     * @var int
     */
    private $workers;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var bool
     */
    private $join;

    /**
     * @param Bootstrapper $bootstrapper
     * @param int          $workers
     * @param resource     $socket
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Bootstrapper $bootstrapper, int $workers, $socket)
    {
        if (!is_resource($socket)) {

            throw new InvalidArgumentException;
        }

        $this->bootstrapper = $bootstrapper;
        $this->workers      = $workers;
        $this->socket       = $socket;
    }

    /**
     * Listens on the socket for incoming connections,
     * handles IO concurrently in an event loop.
     *
     * @uses Connection
     * @uses Connections
     * @uses Ev
     * @uses EvLoop
     * @uses LogicException
     * @uses Pool
     * @uses Request
     * @uses Throwable
     * @uses Worker
     */
    public function run()
    {
        require '/opt/vendor/autoload.php';

        // Thread-local stuff.

        self::$connections = new Connections;
        self::$evloop      = new EvLoop;
        self::$pool        = new Pool($this->workers, Worker::class, [ $this->bootstrapper ]);

        // Accept new socket connections.

        $watchers[] = self::$evloop->io($this->socket, Ev::READ, function () {

            try {

                self::$connections->accept(
                    new Connection(
                        self::$connections,
                        self::$pool,
                        self::$evloop,
                        new Output,
                        stream_socket_accept($this->socket, 0)
                    )
                );

            } catch (Throwable $exception) {}
        });

        // Clean the pool from garbage.

        $watchers[] = self::$evloop->prepare(function () {

            self::$pool->collect(function (Collectable $collectable) {

                return $collectable->isGarbage();
            });
        });

        // Ugly workaround because EvLoop
        // doesn’t receive signals in threads.

        $watchers[] = self::$evloop->timer(1.0, 1.0, function () {

            if ($this->join) {

                self::$evloop->stop();
            }
        });

        // Wait for events.

        self::$evloop->run();

        // Closing connections.

        self::$connections->close();

        // Shutting down workers.

        self::$pool->shutdown();
    }

    /**
     * @inheritdoc
     */
    public function start(int $options = null): bool
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }

    /**
     * @inheritdoc
     */
    public function join(): bool
    {
        $this->join = true;

        return parent::join();
    }
}
