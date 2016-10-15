<?php
namespace Srvlets;

use \Ev;
use \EvSignal;

/**
 * Stateful PHP application engine.
 */
final class Engine
{
    /**
     * Blocks until SIGTERM or SIGINT are received.
     *
     * @param Server  $server
     * @param Process $process
     *
     * @uses Ev
     * @uses EvSignal
     */
    public static function init(Server $server, Process $process)
    {
        $watchers = [];

        // Set up signal watchers.

        foreach ([ SIGTERM, SIGINT ] as $signal) {

            $watchers[] = new EvSignal($signal, function () {

                echo 'Shutting down...' . PHP_EOL;

                Ev::stop();
            });
        }

        // Wait for signals.

        Ev::run();
    }
}
